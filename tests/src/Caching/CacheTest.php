<?php declare(strict_types=1);

namespace h4kuna\Exchange\Tests\Caching;

use h4kuna\Exchange;
use Tester\Assert;
use Tester\TestCase;

require_once __DIR__ . '/../../bootstrap.php';

/**
 * @testCase
 */
final class CacheTest extends TestCase
{

	public function testBasic(): void
	{
		$exchangeFactory = createExchangeFactory();
		$cache = $exchangeFactory->createRatingListCache();

		$cacheEntity = new Exchange\RatingList\CacheEntity(null, Exchange\Driver\Cnb\Day::class);
		$date = $cache->build($cacheEntity);
		Assert::same('2022-12-21', $date->format('Y-m-d'));

		$cache->rebuild($cacheEntity);

		Assert::count(3, $cache->all($cacheEntity));
	}


	public function testHistory(): void
	{
		$exchangeFactory = createExchangeFactory();
		$cache = $exchangeFactory->createRatingListCache();
		$cacheEntity = new Exchange\RatingList\CacheEntity(new \DateTime('2022-12-01'), Exchange\Driver\Cnb\Day::class);

		$date = $cache->build($cacheEntity);
		Assert::same('2022-12-01', $date->format('Y-m-d'));

		$cache->rebuild($cacheEntity);

		Assert::count(3, $cache->all($cacheEntity));
	}

}

(new CacheTest())->run();
