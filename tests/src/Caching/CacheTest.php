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
		$cache = $exchangeFactory->getRatingListCache();

		$cacheFile = __DIR__ . '/../../temp/exchange/0ea1c449373f12be227083321205c307';

		$cache->create($exchangeFactory->getDriver());
		Assert::true(is_file($cacheFile));

		$cache->flush($exchangeFactory->getDriver());
		Assert::false(is_file($cacheFile));

		$cache->create($exchangeFactory->getDriver());
		Assert::true(is_file($cacheFile));
	}


	public function testHistory(): void
	{
		$exchangeFactory = createExchangeFactory();
		$cache = $exchangeFactory->getRatingListCache();

		$cacheFile = __DIR__ . '/../../temp/exchange/ab0a31063d28b5d1970a3db6b58e8188';
		$date = new \DateTime('2022-12-01');

		$cache->create($exchangeFactory->getDriver(), $date);
		Assert::true(is_file($cacheFile));

		$cache->flush($exchangeFactory->getDriver(), $date);
		Assert::false(is_file($cacheFile));

		$cache->create($exchangeFactory->getDriver(), $date);
		Assert::true(is_file($cacheFile));
	}

}

(new CacheTest())->run();
