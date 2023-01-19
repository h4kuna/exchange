<?php declare(strict_types=1);

namespace h4kuna\Exchange\RatingList;

use h4kuna\Exchange\Driver;
use h4kuna\Exchange\Exceptions\InvalidStateException;
use h4kuna\Serialize\Serialize;
use NinjaMutex\Lock\LockInterface;
use Psr\Http\Client\ClientExceptionInterface;
use Psr\SimpleCache\CacheInterface;

class RatingListCache
{

	public function __construct(
		private CacheInterface $cache,
		private LockInterface $lock,
		private RatingListBuilder $ratingListBuilder,
		private Driver\DriverAccessor $driverAccessor,
	)
	{
	}


	/**
	 * @param class-string $driver
	 */
	public function create(string $driver, \DateTimeInterface $date = null): RatingList
	{
		$key = self::createKey($driver, $date);
		$ratingList = $this->loadData($key);

		if ($ratingList === null || $ratingList->isValid() === false) {
			$ratingList = $this->tryCreateCriticalSection($ratingList, $key, $driver, $date);
		}

		return $ratingList;
	}


	public function flush(string|Driver\Driver $driver, \DateTimeInterface $date = null): void
	{
		$this->cache->delete(
			self::createKey(is_string($driver) ? $driver : $driver::class, $date),
		);
	}


	private function tryCreateCriticalSection(
		?RatingList $ratingList,
		string $key,
		string $driver,
		?\DateTimeInterface $date,
	): RatingList
	{
		if ($this->lock->acquireLock($key)) {
			try {
				$ratingList = $this->criticalSection($key, $driver, $date);
			} catch (ClientExceptionInterface $e) {
				if ($ratingList === null) {
					throw new InvalidStateException('No data.', $e->getCode(), $e);
				}
				$ratingList->extendTTL();
				$this->saveData($key, $ratingList);
			} finally {
				$this->lock->releaseLock($key);
			}
		} else {
			throw new InvalidStateException('No exclusive lock and no data.');
		}

		return $ratingList;
	}


	/**
	 * @throws ClientExceptionInterface
	 */
	private function criticalSection(
		string $key,
		string $driver,
		\DateTimeInterface $date = null,
	): RatingList
	{
		$ratingList = $this->loadData($key);
		if ($ratingList === null) {
			$ratingList = $this->ratingListBuilder->create($this->driverAccessor->get($driver), $date);

			$this->saveData($key, $ratingList);
		}

		return $ratingList;
	}


	private function saveData(string $key, RatingList $data): void
	{
		$this->cache->set($key, Serialize::encode($data));
	}


	private function loadData(string $key): ?RatingList
	{
		$data = $this->cache->get($key); // first fetch without lock

		if ($data !== null) {
			assert(is_string($data));
			$ratingList = Serialize::decode($data);
			assert($ratingList instanceof RatingList);

			return $ratingList;
		}

		return null;
	}


	private static function createKey(string $driver, ?\DateTimeInterface $date): string
	{
		$suffix = $date === null ? '' : $date->format('.Y-m-d');

		return self::driverName($driver) . $suffix;
	}


	private static function driverName(string $driver): string
	{
		return str_replace('\\', '.', $driver);
	}

}
