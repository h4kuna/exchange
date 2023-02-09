<?php declare(strict_types=1);

namespace h4kuna\Exchange;

use GuzzleHttp\Client;
use GuzzleHttp\Psr7\HttpFactory;
use h4kuna\CriticalCache\CacheFactory;
use h4kuna\Exchange\Driver;
use h4kuna\Exchange\Exceptions\MissingDependencyException;
use h4kuna\Exchange\RatingList\RatingListBuilder;
use h4kuna\Exchange\RatingList\RatingListRequest;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestFactoryInterface;

final class ExchangeFactory
{
	private ?CacheFactory $cacheFactory = null;

	private ?RequestFactoryInterface $requestFactory = null;

	private ?ClientInterface $client = null;

	/**
	 * @var array<string, int>
	 */
	private array $allowedCurrencies;

	/**
	 * @var array<class-string, \Closure>
	 */
	private array $drivers = [];


	/**
	 * @param array<string> $allowedCurrencies
	 * @param class-string $driver
	 */
	public function __construct(
		private string $from,
		private ?string $to = null,
		private string $tempDir = 'exchange',
		array $allowedCurrencies = [],
		private string $driver = Driver\Cnb\Day::class,
	)
	{
		$this->allowedCurrencies = Utils::transformCurrencies($allowedCurrencies);
	}


	/**
	 * @param class-string $name
	 */
	public function addDriver(string $name, \Closure $factory): void
	{
		$this->drivers[$name] = $factory;
	}


	/**
	 * @param class-string $driver
	 */
	public function create(\DateTimeInterface $date = null, ?string $driver = null): Exchange
	{
		return new Exchange(
			$this->from,
			$this->to ?? $this->from,
			new RatingListRequest(
				$this->createRatingListCache()->create(
					$driver ?? $this->driver,
					$date,
				)
			),
		);
	}


	public function createRatingListFactory(): RatingList\RatingListBuilder
	{
		return new RatingListBuilder($this->allowedCurrencies);
	}


	public function getCacheFactory(): CacheFactory
	{
		if ($this->cacheFactory === null) {
			$this->cacheFactory = new CacheFactory($this->tempDir);
		}

		return $this->cacheFactory;
	}


	public function setCacheFactory(CacheFactory $cacheFactory): void
	{
		$this->cacheFactory = $cacheFactory;
	}


	public function createRatingListCache(): RatingList\RatingListCache
	{
		$cacheFactory = $this->getCacheFactory();

		return new RatingList\RatingListCache($cacheFactory->create(), $this->createRatingListFactory(), $this->getDriverAccessor());
	}


	protected function createCnb(): Driver\Driver
	{
		return new Driver\Cnb\Day($this->getClient(), $this->getRequestFactory());
	}


	protected function createEcb(): Driver\Driver
	{
		return new Driver\Ecb\Day($this->getClient(), $this->getRequestFactory());
	}


	protected function getRequestFactory(): RequestFactoryInterface
	{
		if ($this->requestFactory === null) {
			MissingDependencyException::guzzleFactory();

			return $this->requestFactory = new HttpFactory();
		}

		return $this->requestFactory;
	}


	public function setRequestFactory(RequestFactoryInterface $requestFactory): void
	{
		$this->requestFactory = $requestFactory;
	}


	public function setClient(ClientInterface $client): void
	{
		$this->client = $client;
	}


	protected function getClient(): ClientInterface
	{
		if ($this->client === null) {
			MissingDependencyException::guzzleClient();
			$this->client = new Client();
		}

		return $this->client;
	}


	protected function getDriverAccessor(): Driver\DriverAccessor
	{
		$this->addDriver(Driver\Cnb\Day::class, fn () => $this->createCnb());
		$this->addDriver(Driver\Ecb\Day::class, fn () => $this->createEcb());

		return new Driver\DriverCollection($this->drivers);
	}

}
