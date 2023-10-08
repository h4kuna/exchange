<?php declare(strict_types=1);

namespace h4kuna\Exchange\Tests\RatingList;

use h4kuna\CriticalCache\CacheFactory;
use h4kuna\Exchange\ExchangeFactory;
use Tester\Assert;

require_once __DIR__ . '/../../bootstrap.php';

$exchangeFactory = new ExchangeFactory('czk', 'EUR', cacheFactory: new CacheFactory(__DIR__ . '/../../temp'));

$exchange = $exchangeFactory->create(new \DateTimeImmutable('2021-06-18'));

Assert::same(3.918495297805643, $exchange->change(100.0));
Assert::type('float', $exchange->change(100.0));

Assert::same('PHP', $exchange['PHP']->code);
Assert::same(0.44293, $exchange['PHP']->rate);

$count = 0;
foreach ($exchange as $property) {
	++$count;
}

Assert::same(34, $count);
