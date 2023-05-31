<?php declare(strict_types=1);

namespace h4kuna\Exchange\RatingList;

use h4kuna\CriticalCache\CacheLocking;
use h4kuna\Exchange\Driver;
use h4kuna\Exchange\Exceptions\InvalidStateException;
use Psr\Http\Client\ClientExceptionInterface;

class RatingListCache
{

	public function __construct(
		private CacheLocking $cache,
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
		$ratingList = $this->cache->get($key);
		assert($ratingList === null || $ratingList instanceof RatingList);

		if ($ratingList === null || $ratingList->isValid() === false) {
			$driverObject = $this->driverAccessor->get($driver);
			$ratingList = $this->cache->load(
				$key,
				fn() => $this->criticalSection($ratingList, $driverObject, $date),
				self::countTTL($driverObject->getRefresh(), $date)
			);
		}

		return $ratingList;
	}


	public function rebuild(string $driver, \DateTimeInterface $date = null): ?RatingList
	{
		$key = self::createKey($driver, $date);
		try {
			$driverObject = $this->driverAccessor->get($driver);
			$ratingList = $this->criticalSection(
				null,
				$this->driverAccessor->get($driver), $date
			);

			$this->cache->set($key, $ratingList, self::countTTL($driverObject->getRefresh(), $date));
		} catch (InvalidStateException) {
			return null;
		}

		return $ratingList;
	}


	public function flush(string|Driver\Driver $driver, \DateTimeInterface $date = null): void
	{
		$this->cache->delete(
			self::createKey(is_string($driver) ? $driver : $driver::class, $date),
		);
	}


	private function criticalSection(
		?RatingList $ratingList,
		Driver\Driver $driver,
		?\DateTimeInterface $date,
	): RatingList
	{
		try {
			$ratingList = $this->ratingListBuilder->create($driver, $date);
		} catch (ClientExceptionInterface $e) {
			if ($ratingList === null) {
				throw new InvalidStateException('No data.', $e->getCode(), $e);
			}
			$ratingList->extendTTL();
		}

		return $ratingList;
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


	private static function countTTL(\DateTime $dateTime, \DateTimeInterface $historyDate = null): ?int
	{
		if ($historyDate !== null) {
			return null;
		}

		$currentTime = time();
		if ($dateTime->getTimestamp() < $currentTime) {
			$dateTime->modify('+1 day');
		}

		return $dateTime->getTimestamp() - $currentTime;
	}

}
