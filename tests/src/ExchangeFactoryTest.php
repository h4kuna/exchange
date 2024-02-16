<?php

declare(strict_types=1);

namespace h4kuna\Exchange\Tests;

use h4kuna\Exchange\ExchangeFactory;
use h4kuna\Exchange\RatingList\CacheEntity;
use Tester\Assert;
use Tester\TestCase;

require_once __DIR__ . '/../bootstrap.php';

final class ExchangeFactoryTest extends TestCase
{
	public function testBasic(): void
	{
		$exchangeFactory = new ExchangeFactory();
		$exchange = $exchangeFactory->create(cacheEntity: new CacheEntity(new \DateTime('2000-12-18')));

		$v = $exchange->change(100, 'EUR', 'USD');

		Assert::true($v > 0);
	}
}

(new ExchangeFactoryTest())->run();
