<?php declare(strict_types=1);

namespace h4kuna\Exchange\RatingList;

use h4kuna\CriticalCache\CacheLocking;
use h4kuna\CriticalCache\Utils\Dependency;
use h4kuna\Exchange\Download\SourceDownloadInterface;
use h4kuna\Exchange\Exceptions\InvalidStateException;
use h4kuna\Exchange\Utils;
use Nette\Utils\DateTime;
use Psr\Http\Client\ClientExceptionInterface;
use Psr\SimpleCache\CacheInterface;

final class RatingListCache
{
	public int $floatTtl = Utils::CacheMinutes - DateTime::MINUTE; // 29 minutes


	public function __construct(
		private CacheLocking $cache,
		private SourceDownloadInterface $sourceDownload,
	)
	{
	}


	/**
	 * @throws ClientExceptionInterface
	 */
	public function build(CacheEntity $cacheEntity): RatingListInterface
	{
		$ratingList = null;

		$this->cache->load($cacheEntity->cacheKeyTtl, function (
			Dependency $dependency,
			CacheInterface $cache,
			string $prefix,
		) use ($cacheEntity, &$ratingList): string {
			[$ratingList, $ttl] = $this->buildCache($cacheEntity, $cache, $prefix);
			$dependency->ttl = $ttl;

			return self::toDate($ratingList);
		});

		if ($ratingList === null) {
			$ratingList = $this->cache->get($cacheEntity->cacheKeyAll);

			if (($ratingList instanceof RatingListInterface) === false) {
				throw new InvalidStateException('Cache is broken.');
			}
		}

		return $ratingList;
	}


	/**
	 * @return array{RatingListInterface, ?int}
	 * @throws ClientExceptionInterface
	 */
	private function buildCache(CacheEntity $cacheEntity, CacheInterface $cache, string $prefix): array
	{
		try {
			$ratingList = $this->sourceDownload->execute($cacheEntity->source, $cacheEntity->date);
		} catch (ClientExceptionInterface $e) {
			$ratingList = $cache->get($prefix . $cacheEntity->cacheKeyAll);
			if (($ratingList instanceof RatingListInterface) === false) {
				throw $e;
			}
			$ratingList->getExpire()?->modify(sprintf('now, +%s seconds', Utils::CacheMinutes));
		}

		$ttl = $ratingList->getExpire() === null ? null : Utils::countTTL($ratingList->getExpire(), $this->floatTtl);
		$this->cache->set($prefix . $cacheEntity->cacheKeyAll, $ratingList);

		return [$ratingList, $ttl];
	}


	/**
	 * @throws ClientExceptionInterface
	 */
	public function rebuild(CacheEntity $cacheEntity): bool
	{
		$oldValue = $this->cache->get($cacheEntity->cacheKeyTtl);
		[$ratingList, $ttl] = $this->buildCache($cacheEntity, $this->cache, '');
		$value = self::toDate($ratingList);
		$this->cache->set($cacheEntity->cacheKeyTtl, $value, $ttl);

		return $oldValue !== $value;
	}


	private static function toDate(RatingListInterface $ratingList): string
	{
		return $ratingList->getDate()->format(DATE_RFC3339);
	}
}
