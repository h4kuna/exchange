<?php declare(strict_types=1);

namespace h4kuna\Exchange\RatingList;

use DateTimeImmutable;
use h4kuna\CriticalCache\CacheLocking;
use h4kuna\CriticalCache\Utils\Dependency;
use h4kuna\Exchange\Currency\Property;
use h4kuna\Exchange\Driver\Driver;
use h4kuna\Exchange\Driver\DriverAccessor;
use h4kuna\Exchange\Exceptions\InvalidStateException;
use h4kuna\Exchange\Exceptions\UnknownCurrencyException;
use h4kuna\Exchange\Utils;
use h4kuna\Serialize\Serialize;
use Psr\Http\Client\ClientExceptionInterface;
use Psr\SimpleCache\CacheInterface;

/**
 * @phpstan-type cacheType array{date: DateTimeImmutable, expire: ?DateTimeImmutable, ttl: ?int}
 */
final class RatingListCache
{
	public int $floatTtl = 900; // seconds -> 15 minutes


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


	/**
	 * @return cacheType
	 *
	 * @throws ClientExceptionInterface
	 */
	public function build(CacheEntity $cacheEntity): array
	{
		return $this->cache->load($cacheEntity->cacheKeyTtl, function (
			Dependency $dependency,
			CacheInterface $cache,
			string $prefix,
		) use ($cacheEntity): array {
			$cacheType = $this->buildCache($cacheEntity, $cache, $prefix);
			$dependency->ttl = $cacheType['ttl'];

			return $cacheType;
		});
	}


	/**
	 * @throws ClientExceptionInterface
	 */
	public function rebuild(CacheEntity $cacheEntity): bool
	{
		/**
		 * @var ?cacheType $cacheTypeOld
		 */
		$cacheTypeOld = $this->cache->get($cacheEntity->cacheKeyTtl);
		$cacheType = $this->buildCache($cacheEntity, $this->cache, '');
		$this->cache->set($cacheEntity->cacheKeyTtl, $cacheType, $cacheType['ttl']);

		return $cacheTypeOld === null || $cacheTypeOld['expire']?->format(DATE_ATOM) !== $cacheType['expire']?->format(DATE_ATOM);
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
	 * @return array{date: DateTimeImmutable, expire: ?DateTimeImmutable, ttl: ?int}
	 *
	 * @throws ClientExceptionInterface
	 */
	private function buildCache(CacheEntity $cacheEntity, CacheInterface $cache, string $prefix): array
	{
		$provider = $this->driverAccessor->get($cacheEntity->driver);
		$all = [];
		try {
			foreach ($provider->initRequest($cacheEntity->date, $this->allowedCurrencies) as $property) {
				$cache->set($prefix . $cacheEntity->keyCode($property->code), Serialize::encode($property));
				$all[$property->code] = true;
			}
		} catch (ClientExceptionInterface $e) {
			$data = $cache->get($prefix . $cacheEntity->cacheKeyAll) ?? [];

			if ($cacheEntity->date === null && $data !== []) {
				return self::makeCacheData(
					new DateTimeImmutable(),
					new DateTimeImmutable(sprintf('+%s seconds', $this->floatTtl)),
					$this->floatTtl,
				);
			}
			throw $e;
		}

		$cache->set($prefix . $cacheEntity->cacheKeyAll, $all);

		return $cacheEntity->date === null
			? self::countCacheData($provider, $this->floatTtl)
			: self::makeCacheData($provider->getDate());
	}


	/**
	 * @return cacheType
	 */
	private static function makeCacheData(
		DateTimeImmutable $date,
		?DateTimeImmutable $expire = null,
		?int $ttl = null
	): array
	{
		return [
			'date' => $date,
			'expire' => $expire,
			'ttl' => $ttl,
		];
	}


	/**
	 * @return cacheType
	 */
	private static function countCacheData(Driver $provider, int $floatTtl): array
	{
		$expire = $provider->getRefresh();
		$ttl = Utils::countTTL($expire, $floatTtl);

		return self::makeCacheData($provider->getDate(), DateTimeImmutable::createFromMutable($expire), $ttl);
	}
}
