<?php

declare(strict_types=1);

namespace h4kuna\Exchange\Tests\RatingList;

require_once __DIR__ . '/../../bootstrap.php';

use Closure;
use h4kuna\CriticalCache\PSR16\CacheLocking;
use h4kuna\CriticalCache\Utils\Dependency;
use h4kuna\Exchange\Currency\Property;
use h4kuna\Exchange\Download\SourceDownloadInterface;
use h4kuna\Exchange\RatingList\CacheEntity;
use h4kuna\Exchange\RatingList\RatingList;
use h4kuna\Exchange\RatingList\RatingListCache;
use Mockery\MockInterface;
use Psr\Http\Client\ClientExceptionInterface;
use Psr\SimpleCache\CacheInterface;
use Tester\Assert;
use Tester\TestCase;

final class RatingListCacheTest extends TestCase
{

	public function testSuccessBuild(): void
	{
		$ratingList = self::createRatingList();
		$cacheLocking = self::createCacheLocking($ratingList);
		$source = self::createSourceDownload();
		$source->shouldReceive('execute')
			->andReturn($ratingList);

		$ratingListCache = new RatingListCache($cacheLocking, $source);

		$list = $ratingListCache->build(new CacheEntity());

		Assert::same($ratingList, $list);
	}


	public function testBackupBuild(): void
	{
		$ratingList = self::createRatingList();
		$ratingList2 = self::createRatingList();
		$cache = self::createCache();
		$cache->shouldReceive('get')
			->andReturn($ratingList2);

		$cacheLocking = self::createCacheLocking($ratingList, $cache, 6800);
		$cacheLocking->shouldReceive('set')
			->with('h4kuna.Exchange.Driver.Cnb.Day.all.v7.1', $ratingList2);
		$source = self::createSourceDownload();
		$source->shouldReceive('execute')
			->withArgs(function () {
				throw new class extends \Exception implements ClientExceptionInterface {

				};
			});

		$ratingListCache = new RatingListCache($cacheLocking, $source);

		$ratingListActual = $ratingListCache->build(new CacheEntity());
		Assert::same($ratingList2, $ratingListActual);
	}


	public function testRebuild(): void
	{
		$ratingList = self::createRatingList();
		$ratingList2 = self::createRatingList();

		/** @var MockInterface&CacheLocking $cacheLocking */
		$cacheLocking = mock(CacheLocking::class);
		$cacheLocking->makePartial();
		$cacheLocking // @phpstan-ignore method.nonObject
			->shouldReceive('get')
			->with('h4kuna.Exchange.Driver.Cnb.Day.ttl')
			->andReturn($ratingList, $ratingList2);

		$cacheLocking // @phpstan-ignore method.nonObject
			->shouldReceive('set')
			->with('h4kuna.Exchange.Driver.Cnb.Day.all.v7.1', $ratingList2)
			->andReturn(true);
		$cacheLocking // @phpstan-ignore method.nonObject
			->shouldReceive('set')
			->with('h4kuna.Exchange.Driver.Cnb.Day.ttl', (new \DateTime('now'))->format(\DateTime::RFC3339), 5000)
			->andReturn(true);

		$source = self::createSourceDownload();
		$source->shouldReceive('execute')
			->andReturn($ratingList2);

		$ratingListCache = new RatingListCache($cacheLocking, $source);

		$result = $ratingListCache->rebuild(new CacheEntity());
		Assert::true($result);
	}


	public function testNotingByLoadBuild(): void
	{
		$ratingList = self::createRatingList();
		$ratingList2 = self::createRatingList();
		$cache = self::createCache();

		$cacheLocking = self::createCacheLocking($ratingList, $cache, load: fn () => true);
		$cacheLocking->shouldReceive('get')
			->andReturn($ratingList2);
		$cacheLocking->shouldReceive('set')
			->andReturn(true);

		$cacheLocking->shouldReceive('load')
			->withArgs(function () {
				return true;
			});
		$source = self::createSourceDownload();
		$source->shouldReceive('execute')
			->withArgs(function () {
				throw new class extends \Exception implements ClientExceptionInterface {

				};
			});

		$ratingListCache = new RatingListCache($cacheLocking, $source);

		$ratingListActual = $ratingListCache->build(new CacheEntity());
		Assert::same($ratingList2, $ratingListActual);
	}


	public function testFatalFailedBuild(): void
	{
		$ratingList = self::createRatingList();
		$cache = self::createCache();
		$cache->shouldReceive('get')
			->andReturn(null);

		$cacheLocking = self::createCacheLocking($ratingList, $cache);
		$source = self::createSourceDownload();
		$source->shouldReceive('execute')
			->withArgs(function () {
				throw new class extends \Exception implements ClientExceptionInterface {

				};
			});

		$ratingListCache = new RatingListCache($cacheLocking, $source);

		Assert::exception(fn () => $ratingListCache->build(new CacheEntity()), ClientExceptionInterface::class);
	}


	/**
	 * @return MockInterface&SourceDownloadInterface
	 */
	private static function createSourceDownload()
	{
		/** @var MockInterface&SourceDownloadInterface $source */
		$source = mock(SourceDownloadInterface::class);
		$source->makePartial();

		return $source;
	}


	/**
	 * @return MockInterface&CacheInterface
	 */
	private static function createCache()
	{
		/** @var MockInterface&CacheInterface $cache */
		$cache = mock(CacheInterface::class);
		$cache->makePartial();

		return $cache;
	}


	/**
	 * @return MockInterface&CacheLocking
	 */
	private static function createCacheLocking(
		RatingList $ratingList,
		?CacheInterface $cache = null,
		int $ttl = 5000,
		?Closure $load = null
	)
	{
		$cache = $cache ?? self::createCache();

		$load ??= function (string $key, Closure $callback) use ($cache, $ttl) {
			$dependency = new Dependency();
			$callback($dependency, $cache);
			Assert::same($ttl, $dependency->ttl);

			return true;
		};

		/** @var CacheLocking&MockInterface $cacheLocking */
		$cacheLocking = mock(CacheLocking::class)
			->makePartial();
		$cacheLocking->shouldReceive('set')
			->with('h4kuna.Exchange.Driver.Cnb.Day.all.v7.1', $ratingList);

		$cacheLocking->shouldReceive('load')
			->withArgs($load);

		return $cacheLocking;
	}


	private static function createRatingList(): RatingList
	{
		return new RatingList(new \DateTimeImmutable(), null, new \DateTime('+5000 seconds'), [
			'CZK' => new Property(1, 1, 'CZK'),
			'EUR' => new Property(1, 26, 'EUR'),
			'USD' => new Property(10, 130, 'USD'),
		]);
	}

}

(new RatingListCacheTest())->run();
