<?php declare(strict_types=1);

namespace h4kuna\Exchange;

use GuzzleHttp\Client;
use GuzzleHttp\Psr7\HttpFactory;
use h4kuna\CriticalCache\CacheFactory;
use h4kuna\Exchange\Download\SourceDownload;
use h4kuna\Exchange\Exceptions\MissingDependencyException;
use h4kuna\Exchange\RatingList\CacheEntity;
use h4kuna\Exchange\RatingList\RatingListCache;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestFactoryInterface;

final class ExchangeFactory implements ExchangeFactoryInterface
{
	private RatingListCache $ratingListCache;


	/**
	 * @param array<string> $allowedCurrencies
	 */
	public function __construct(
		private string $from = 'CZK',
		private ?string $to = null,
		?RatingListCache $ratingListCache = null,
		array $allowedCurrencies = [],
	)
	{
		$this->ratingListCache = $ratingListCache ?? self::createRatingListCache(Utils::transformCurrencies($allowedCurrencies));
	}


	/**
	 * @param array<string, int> $allowedCurrencies
	 */
	private static function createRatingListCache(array $allowedCurrencies): RatingListCache
	{
		return new RatingListCache(
			self::createCacheFactory()->create(),
			new SourceDownload(self::createClient(), self::createRequestFactory(), $allowedCurrencies),
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
			$this->ratingListCache->build($cacheEntity ?? new CacheEntity()),
			$to ?? $this->to,
		);
	}


	private static function createCacheFactory(): CacheFactory
	{
		return new CacheFactory('exchange');
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
