<?php declare(strict_types=1);

namespace h4kuna\Exchange;

use GuzzleHttp\Client;
use GuzzleHttp\Psr7\HttpFactory;
use h4kuna\CriticalCache\PSR16\CacheLockingFactoryInterface;
use h4kuna\CriticalCache\PSR16\Locking\CacheLockingFactory;
use h4kuna\Dir\Dir;
use h4kuna\Exchange\Download\SourceDownload;
use h4kuna\Exchange\Exceptions\MissingDependencyException;
use h4kuna\Exchange\RatingList\CacheEntity;
use h4kuna\Exchange\RatingList\RatingListCache;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestFactoryInterface;

final class ExchangeFactory implements ExchangeFactoryInterface
{
	private RatingListCache $ratingListCache;

	private CacheEntity $cacheEntity;


	/**
	 * @param array<string> $allowedCurrencies
	 */
	public function __construct(
		private string $from = 'CZK',
		private ?string $to = null,
		?RatingListCache $ratingListCache = null,
		array $allowedCurrencies = [],
		?ClientInterface $client = null,
		?RequestFactoryInterface $requestFactory = null,
		?CacheEntity $cacheEntity = null,
		string|Dir|CacheLockingFactoryInterface $tempDir = 'exchange',
	)
	{
		$this->cacheEntity = $cacheEntity ?? new CacheEntity();
		$this->ratingListCache = $ratingListCache ?? self::createRatingListCache(Utils::transformCurrencies($allowedCurrencies), $client, $requestFactory, $tempDir);
	}


	/**
	 * @param array<string, int> $allowedCurrencies
	 */
	private static function createRatingListCache(
		array $allowedCurrencies,
		?ClientInterface $client,
		?RequestFactoryInterface $requestFactory,
		string|Dir|CacheLockingFactoryInterface $tempDir,
	): RatingListCache
	{
		$cacheLockingFactory = $tempDir instanceof CacheLockingFactoryInterface
			? $tempDir
			: self::createCacheFactory($tempDir);

		return new RatingListCache(
			$cacheLockingFactory->create(),
			new SourceDownload($client ?? self::createClient(), $requestFactory ?? self::createRequestFactory(), $allowedCurrencies),
		);
	}


	public function create(
		?string $from = null,
		?string $to = null,
		?CacheEntity $cacheEntity = null,
	): Exchange
	{
		return new Exchange(
			$from ?? $this->from,
			$this->ratingListCache->build($cacheEntity ?? $this->cacheEntity),
			$to ?? $this->to,
		);
	}


	private static function createCacheFactory(string|Dir $tempDir): CacheLockingFactoryInterface
	{
		return new CacheLockingFactory($tempDir);
	}


	private static function createClient(): ClientInterface
	{
		MissingDependencyException::guzzleClient();
		return new Client();
	}


	private static function createRequestFactory(): RequestFactoryInterface
	{
		MissingDependencyException::guzzleFactory();
		return new HttpFactory();
	}

}
