<?php declare(strict_types=1);

namespace h4kuna\Exchange\Caching;

use Tester\Assert;

require_once __DIR__ . '/../../bootstrap.php';

/**
 * @testCase
 */
class CacheTest extends \Tester\TestCase
{

	public function testBasic()
	{
		$driver = new \h4kuna\Exchange\Test\Driver();
		$tempDir = TEMP_DIR . '/cache/test';
		$cache = new Cache($tempDir);
		$cache->setRefresh('00:00');
		$cache->flushCache($driver);

		$allowed = ['EUR', 'CZK'];
		$cache->setAllowedCurrencies($allowed);
		$rateList = $cache->loadListRate($driver); // from source
		Assert::same($allowed, array_keys($rateList->getCurrencies()));

		Assert::same($rateList, $cache->loadListRate($driver)); // from property

		$cache2 = new Cache($tempDir);
		Assert::equal($rateList, $cache2->loadListRate($driver)); // from file

		$cache2->invalidForce($driver, new \DateTime('2015-12-30'));
		Assert::true(is_file($tempDir . '/h4kuna.exchange.test.driver/2015-12-30'));
		$cache2->flushCache($driver, new \DateTime('2015-12-30'));
		Assert::false(is_file($tempDir . '/h4kuna.exchange.test.driver/2015-12-30'));
	}

}

(new CacheTest())->run();
