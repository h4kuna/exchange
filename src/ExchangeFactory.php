<?php declare(strict_types=1);

namespace h4kuna\Exchange;

use GuzzleHttp\Client;
use GuzzleHttp\Psr7\HttpFactory;
use h4kuna\Exchange\Caching\Cache;
use h4kuna\Exchange\Driver;
use h4kuna\Exchange\Exceptions\InvalidStateException;
use h4kuna\Exchange\RatingList\RatingListBuilder;
use h4kuna\Exchange\RatingList\RatingListRequest;
use NinjaMutex\Lock\FlockLock;
use NinjaMutex\Lock\LockInterface;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\SimpleCache\CacheInterface;

final class ExchangeFactory
{
	private ?LockInterface $lock = null;

	private ?CacheInterface $cache = null;

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
		private ?string $tempDir = null,
		array $allowedCurrencies = [],
		private string $driver = Driver\Cnb\Day::class,
	)
	{
		if ($this->tempDir === null) {
			$this->tempDir = sys_get_temp_dir() . '/exchange';
		}
		!is_dir($this->tempDir) && mkdir($this->tempDir, 0777, true);
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
		$config = $this->createConfiguration();

		return new Exchange(
			new RatingListRequest(
				$this->createRatingListCache()->create(
					$driver ?? $this->driver,
					$date,
				)
			),
			$config
		);
	}


	protected function getLock(): LockInterface
	{
		assert($this->tempDir !== null);

		return $this->lock ??= new FlockLock($this->tempDir);
	}


	public function setLock(LockInterface $lock): void
	{
		$this->lock = $lock;
	}


	public function createRatingListFactory(): RatingList\RatingListBuilder
	{
		return new RatingListBuilder($this->allowedCurrencies);
	}


	public function createConfiguration(): Configuration
	{
		return new Configuration($this->from, $this->to ?? $this->from);
	}


	public function getCache(): CacheInterface
	{
		assert($this->tempDir !== null);

		return $this->cache ??= new Cache($this->tempDir);
	}


	public function setCache(CacheInterface $cache): void
	{
		$this->cache = $cache;
	}


	public function createRatingListCache(): RatingList\RatingListCache
	{
		return new RatingList\RatingListCache($this->getCache(), $this->getLock(), $this->createRatingListFactory(), $this->getDriverAccessor());
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
			self::checkGuzzle(HttpFactory::class);

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
			self::checkGuzzle(Client::class);
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


	/**
	 * @param class-string $class
	 */
	public static function checkGuzzle(string $class): void
	{
		if (class_exists($class) === false) {
			throw new InvalidStateException(sprintf('Guzzle class "%s" not found, let implement own solution via PSR 7,17,18 or install guzzle by: composer require guzzlehttp/guzzle', $class));
		}
	}

}
