<?php declare(strict_types=1);

require_once __DIR__ . '/../vendor/autoload.php';

use h4kuna\CriticalCache;
use h4kuna\Exchange;
use h4kuna\Exchange\RatingList;

$cacheFactory = new CriticalCache\CacheFactory('exchange');

$exchangeFactory = new Exchange\ExchangeFactory(
	from: 'eur',
	to: 'usd',
	allowedCurrencies: [
		'CZK',
		'USD',
		'eur', // lower case will be changed to upper case
	],
	cacheFactory: $cacheFactory
);

$exchange = $exchangeFactory->create();

echo $exchange->change(100) . PHP_EOL; // EUR -> USD = 125.0

// use only upper case
echo $exchange->change(100, 'CZK') . PHP_EOL; // CZK -> USD = 5.0
echo $exchange->change(100, null, 'CZK') . PHP_EOL; // EUR -> CZK = 2500.0
echo $exchange->change(100, 'USD', 'CZK') . PHP_EOL; // USD -> CZK = 2000.0
echo PHP_EOL;

// History
$exchangePast = $exchange->modify(cacheEntity: new RatingList\CacheEntity(new \Datetime('2000-12-30'), Exchange\Driver\Cnb\Day::class));
echo $exchangePast->change(100) . PHP_EOL;
echo PHP_EOL;

// Array access
$property = $exchange['EUR'];
var_dump($property);
echo PHP_EOL;

// Iterator
foreach ($exchange as $code => $property) {
	/* @var $property Exchange\Currency\Property */
	var_dump($code, $property);
}
