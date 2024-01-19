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
		Assert::same('2022-12-21', $date['date']->format('Y-m-d'));
		$expected = new \DateTime('today 14:45:00');
		Exchange\Utils::countTTL($expected);
		assert(isset($date['expire']));
		Assert::same($expected->format('Y-m-d H:i:s'), $date['expire']->format('Y-m-d H:i:s'));

		$cache->rebuild($cacheEntity);

		Assert::count(3, $cache->all($cacheEntity));
	}


	public function testHistory(): void
	{
		$exchangeFactory = createExchangeFactory();
		$cache = $exchangeFactory->createRatingListCache();
		$cacheEntity = new Exchange\RatingList\CacheEntity(new \DateTime('2022-12-01'), Exchange\Driver\Cnb\Day::class);

		$date = $cache->build($cacheEntity);
		Assert::same('2022-12-01', $date['date']->format('Y-m-d'));
		Assert::null($date['expire']);

		$cache->rebuild($cacheEntity);

		Assert::count(3, $cache->all($cacheEntity));
	}

}

(new CacheTest())->run();
