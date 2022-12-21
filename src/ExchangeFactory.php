<?php declare(strict_types=1);

namespace h4kuna\Exchange;

use GuzzleHttp\Client;
use GuzzleHttp\Psr7\HttpFactory;
use h4kuna\Exchange\Caching\Cache;
use h4kuna\Exchange\Driver\Cnb\Day;
use h4kuna\Exchange\Driver\Driver;
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

	private ?RatingList\RatingListBuilder $ratingListFactory = null;

	private ?Configuration $configuration = null;

	private ?RatingList\RatingListCache $ratingListCache = null;

	private ?Driver $driver = null;

	private ?RequestFactoryInterface $requestFactory = null;

	private ?ClientInterface $client = null;

	/**
	 * @var array<string, int>
	 */
	private array $allowedCurrencies;


	/**
	 * @param array<string> $allowedCurrencies
	 */
	public function __construct(
		private string $from,
		private ?string $to = null,
		private ?string $tempDir = null,
		array $allowedCurrencies = [],
	)
	{
		if ($this->tempDir === null) {
			$this->tempDir = sys_get_temp_dir() . '/exchange';
		}
		!is_dir($this->tempDir) && mkdir($this->tempDir, 0777, true);
		$this->allowedCurrencies = Utils::transformCurrencies($allowedCurrencies);
	}


	public function create(\DateTimeInterface $date = null): Exchange
	{
		return new Exchange(
			new RatingListRequest(
				$this->getRatingListCache()->create(
					$this->getDriver(),
					$date,
				)
			),
			$this->getConfiguration()
		);
	}


	public function getLock(): LockInterface
	{
		assert($this->tempDir !== null);

		return $this->lock ??= new FlockLock($this->tempDir);
	}


	public function setLock(LockInterface $lock): void
	{
		$this->lock = $lock;
	}


	public function getRatingListFactory(): RatingList\RatingListBuilder
	{
		return $this->ratingListFactory ??= new RatingListBuilder($this->allowedCurrencies);
	}


	public function getConfiguration(): Configuration
	{
		return $this->configuration ??= new Configuration($this->from, $this->to ?? $this->from);
	}


	public function setConfiguration(Configuration $configuration): void
	{
		$this->configuration = $configuration;
	}


	public function setRatingListFactory(RatingList\RatingListBuilder $ratingListFactory): void
	{
		$this->ratingListFactory = $ratingListFactory;
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


	public function getRatingListCache(): RatingList\RatingListCache
	{
		return $this->ratingListCache ??= new RatingList\RatingListCache($this->getCache(), $this->getLock(), $this->getRatingListFactory());
	}


	public function setRatingListCache(RatingList\RatingListCache $ratingListCache): void
	{
		$this->ratingListCache = $ratingListCache;
	}


	public function getDriver(): Driver
	{
		if ($this->driver === null) {
			$this->driver = new Day($this->getClient(), $this->getRequestFactory());
		}

		return $this->driver;
	}


	public function setDriver(Driver $driver): void
	{
		$this->driver = $driver;
	}


	public function getRequestFactory(): RequestFactoryInterface
	{
		if ($this->requestFactory === null) {
			self::checkGuzzle();

			return $this->requestFactory = new HttpFactory();
		}

		return $this->requestFactory;
	}


	public function setRequestFactory(RequestFactoryInterface $requestFactory): void
	{
		$this->requestFactory = $requestFactory;
	}


	public function getClient(): ClientInterface
	{
		if ($this->client === null) {
			self::checkGuzzle();
			$this->client = new Client();
		}

		return $this->client;
	}


	public function setClient(ClientInterface $client): void
	{
		$this->client = $client;
	}


	public static function checkGuzzle(): void
	{
		if (class_exists(Client::class) === false) {
			throw new InvalidStateException('Guzzle not found, let implement own solution or install guzzle by: composer require guzzlehttp/guzzle');
		}
	}

}
