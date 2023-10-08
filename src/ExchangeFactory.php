<?php declare(strict_types=1);

namespace h4kuna\Exchange;

use DateTimeInterface;
use h4kuna\CriticalCache\CacheFactory;
use h4kuna\Exchange\Driver;
use h4kuna\Exchange\RatingList\CacheEntity;
use h4kuna\Exchange\RatingList\RatingList;
use h4kuna\Exchange\RatingList\RatingListCache;

final class ExchangeFactory
{
	/**
	 * @var array<string, int>
	 */
	private array $allowedCurrencies;

	private Driver\DriverBuilderFactory $driverBuilderFactory;

	private CacheFactory $cacheFactory;


	/**
	 * @param array<string> $allowedCurrencies
	 * @param class-string $driver
	 */
	public function __construct(
		private string $from,
		private ?string $to = null,
		array $allowedCurrencies = [],
		?Driver\DriverBuilderFactory $driverBuilderFactory = null,
		?CacheFactory $cacheFactory = null,
		private string $driver = Driver\Cnb\Day::class,
	)
	{
		$this->allowedCurrencies = Utils::transformCurrencies($allowedCurrencies);
		$this->driverBuilderFactory = $driverBuilderFactory ?? new Driver\DriverBuilderFactory();
		$this->cacheFactory = $cacheFactory ?? $this->createCacheFactory();
	}


	public function create(DateTimeInterface $date = null): Exchange
	{
		$cache = $this->createRatingListCache();

		return new Exchange(
			$this->from,
			new RatingList(new CacheEntity($date, $this->driver), $cache),
			$this->to,
		);
	}


	protected function createCacheFactory(): CacheFactory
	{
		return new CacheFactory('exchange');
	}


	public function createRatingListCache(): RatingListCache
	{
		return new RatingListCache(
			$this->allowedCurrencies,
			$this->cacheFactory->create(),
			$this->driverBuilderFactory->create()
		);
	}

}
