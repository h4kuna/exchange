<?php declare(strict_types=1);

namespace h4kuna\Exchange\Tests\Caching;

use h4kuna\Exchange;
use h4kuna\Exchange\RatingList\RatingListCache;
use Tester\Assert;
use Tester\Environment;
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

		$cacheFile = __DIR__ . '/../../temp/exchange/h4kuna/cache/_.h4kuna.Exchange.Driver.Cnb.Day';

		$cache->create(Exchange\Driver\Cnb\Day::class);
		Assert::true(is_file($cacheFile));

		$cache->flush(Exchange\Driver\Cnb\Day::class);
		Assert::false(is_file($cacheFile));

		$cache->create(Exchange\Driver\Cnb\Day::class);
		Assert::true(is_file($cacheFile));
	}


	public function testHistory(): void
	{
		$exchangeFactory = createExchangeFactory();
		$cache = $exchangeFactory->createRatingListCache();

		$cacheFile = __DIR__ . '/../../temp/exchange/h4kuna/cache/_.h4kuna.Exchange.Driver.Cnb.Day.2022-12-01';
		$date = new \DateTime('2022-12-01');

		$cache->create(Exchange\Driver\Cnb\Day::class, $date);
		Assert::true(is_file($cacheFile));

		$cache->flush(Exchange\Driver\Cnb\Day::class, $date);
		Assert::false(is_file($cacheFile));

		$cache->create(Exchange\Driver\Cnb\Day::class, $date);
		Assert::true(is_file($cacheFile));
	}

}

(new CacheTest())->run();
