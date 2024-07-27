<?php declare(strict_types=1);

namespace h4kuna\Exchange\Download;

use DateTime;
use DateTimeImmutable;
use DateTimeInterface;
use h4kuna\Exchange\Driver\Source;
use h4kuna\Exchange\RatingList\RatingList;
use h4kuna\Exchange\RatingList\RatingListInterface;
use h4kuna\Exchange\Utils;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\RequestInterface;
use ReflectionClass;

final class SourceDownload implements SourceDownloadInterface
{
	/**
	 * @var array<string, SourceData>
	 */
	private array $cache = [];


	/**
	 * @param array<string, int> $allowedCurrencies
	 */
	public function __construct(
		private ClientInterface $client,
		private RequestFactoryInterface $requestFactory,
		private array $allowedCurrencies = [],
	)
	{
	}


	public function execute(Source $sourceExchange, ?DateTimeInterface $date): RatingListInterface
	{
		$date = Utils::toImmutable($date, $sourceExchange->getTimeZone());
		$key = self::makeKey($sourceExchange, $date);

		$sourceData = $this->cache[$key]
			?? $this->cache[$key] = $sourceExchange->createSourceData(
				$this->client->sendRequest(
					$this->createRequest($sourceExchange, $date)
				)
			);

		$expire = $date === null ? new DateTime($sourceData->refresh . sprintf(', +%s seconds', Utils::CacheMinutes), $sourceExchange->getTimeZone()) : null;

		$properties = [];
		foreach ($sourceData->properties as $item) {
			$property = $sourceExchange->createProperty($item);
			if ($property->getRate() === 0.0 || ($this->allowedCurrencies !== [] && isset($this->allowedCurrencies[$property->getCode()]) === false)) {
				continue;
			}

			$properties[$property->getCode()] = $property;
		}

		return new RatingList($sourceData->date, $date, $expire, $properties);
	}


	private static function makeKey(Source $sourceExchange, ?DateTimeImmutable $date): string
	{
		$rf = new ReflectionClass($sourceExchange);
		do {
			$class = $rf->getName();
			$rf = $rf->getParentClass();
		} while ($rf !== false);

		if ($date === null) {
			$date = new DateTimeImmutable('now', $sourceExchange->getTimeZone());
		}

		return $class . '.' . $date->format(Utils::DateFormat);
	}


	private function createRequest(Source $sourceExchange, ?DateTimeInterface $date): RequestInterface
	{
		$request = $this->requestFactory->createRequest('GET', $sourceExchange->makeUrl($date));
		$request->withHeader('X-Powered-By', 'h4kuna/exchange');

		return $request;
	}

}
