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

		if ($ratingList === null || $ratingList->isValid() === false) {
			$ratingList = $this->cache->load($key, fn (
			) => $this->criticalSection($ratingList, $driver, $date));
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
		string $driver,
		?\DateTimeInterface $date,
	): RatingList
	{
		try {
			$ratingList = $this->ratingListBuilder->create($this->driverAccessor->get($driver), $date);
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

}
