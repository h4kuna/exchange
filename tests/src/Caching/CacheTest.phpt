<?php

namespace h4kuna\Exchange\Caching;

use h4kuna\Number,
	Tester\Assert;

require_once __DIR__ . '/../../bootstrap.php';

class CacheTest extends \Tester\TestCase
{
	public function testBasic()
	{
		$driver = new \h4kuna\Exchange\Test\Driver();
		$tempDir = TEMP_DIR . '/cache/test';
		$cache = new Cache($tempDir);
		$cache->flushCache($driver);

		$allowed = ['EUR', 'CZK'];
		$cache->setAllowedCurrencies($allowed);
		$rateList = $cache->loadListRate($driver); // from source
		Assert::same($allowed, array_keys($rateList->getCurrencies()));

		Assert::same($rateList, $cache->loadListRate($driver)); // from property

		$cache2 = new Cache($tempDir);
		Assert::equal($rateList, $cache2->loadListRate($driver)); // from file
	}
}

(new CacheTest())->run();