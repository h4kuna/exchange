<?php declare(strict_types=1);

namespace h4kuna\Exchange\RatingList;

use DateTime;
use DateTimeImmutable;
use h4kuna\CriticalCache\CacheLocking;
use h4kuna\CriticalCache\Utils\Dependency;
use h4kuna\Exchange\Currency\Property;
use h4kuna\Exchange\Driver\DriverAccessor;
use h4kuna\Exchange\Exceptions\InvalidStateException;
use h4kuna\Exchange\Exceptions\UnknownCurrencyException;
use h4kuna\Serialize\Serialize;
use Psr\Http\Client\ClientExceptionInterface;
use Psr\SimpleCache\CacheInterface;

final class RatingListCache
{
	public int $floatTtl = 600;


	/**
	 * @param array<string, int> $allowedCurrencies
	 */
	public function __construct(
		private array $allowedCurrencies,
		private CacheLocking $cache,
		private DriverAccessor $driverAccessor,
	)
	{
	}


	public function build(CacheEntity $cacheEntity): DateTimeImmutable
	{
		return $this->cache->load($cacheEntity->cacheKeyTtl, function (
			Dependency $dependency,
			CacheInterface $cache,
			string $prefix,
		) use ($cacheEntity): DateTimeImmutable {
			[$date, $ttl] = $this->buildCache($cacheEntity, $cache, $prefix);
			$dependency->ttl = $ttl;

			return $date;
		});
	}


	public function rebuild(CacheEntity $cacheEntity): void
	{
		[$date, $ttl] = $this->buildCache($cacheEntity, $this->cache, '');
		$this->cache->set($cacheEntity->cacheKeyTtl, $date, $ttl);
	}


	public function currency(CacheEntity $cacheEntity, string $code): Property
	{
		$value = $this->cache->get($cacheEntity->keyCode($code));
		if ($value === null) {
			throw new UnknownCurrencyException($code);
		}
		assert(is_string($value));
		$property = Serialize::decode($value);
		assert($property instanceof Property);

		return $property;
	}


	/**
	 * @return array<string, bool>
	 */
	public function all(CacheEntity $cacheEntity): array
	{
		return $this->cache->load($cacheEntity->cacheKeyAll, static fn (
		) => throw new InvalidStateException('Call build() first.'));
	}


	/**
	 * @return array{DateTimeImmutable, ?int}
	 */
	private function buildCache(CacheEntity $cacheEntity, CacheInterface $cache, string $prefix): array
	{
		$provider = $this->driverAccessor->get($cacheEntity->driver);
		try {
			$provider->initRequest($cacheEntity->date);
		} catch (ClientExceptionInterface $e) {
			$data = $cache->get($prefix . $cacheEntity->cacheKeyAll) ?? [];

			if ($cacheEntity->date === null && $data !== []) {
				return [new DateTimeImmutable(), time() + $this->floatTtl];
			}
			throw $e;
		}

		$all = [];
		foreach ($provider->properties($this->allowedCurrencies) as $property) {
			$cache->set($prefix . $cacheEntity->keyCode($property->code), Serialize::encode($property));
			$all[$property->code] = true;
		}

		$cache->set($prefix . $cacheEntity->cacheKeyAll, $all);

		return $cacheEntity->date === null
			? [$provider->getDate(), self::countTTL($provider->getRefresh())]
			: [$provider->getDate(), null];
	}


	private static function countTTL(DateTime $dateTime): int
	{
		if ($dateTime->getTimestamp() < time()) {
			$dateTime->modify('+1 day');
		}

		return $dateTime->getTimestamp();
	}

}
