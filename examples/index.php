<?php declare(strict_types=1);

require_once __DIR__ . '/../vendor/autoload.php';

use h4kuna\Exchange\Currency\Property;
use h4kuna\Exchange\Driver\Cnb\Day;
use h4kuna\Exchange\Exchange;
use h4kuna\Exchange\ExchangeFactory;
use h4kuna\Exchange\RatingList\CacheEntity;
use h4kuna\Exchange\RatingList\RatingList;

{ # by factory
	$exchangeFactory = new ExchangeFactory(
		from: 'eur',
		to: 'usd',
		allowedCurrencies: [
			'CZK',
			'USD',
			'eur', // lower case will be changed to upper case
		],
	);

	$exchange = $exchangeFactory->create();
}

{ # custom RatingList
	$ratingList = new RatingList(new DateTimeImmutable(), new DateTimeImmutable(), null, [
		'EUR' => new Property(1, 25.0, 'EUR'),
		'USD' => new Property(1, 20.0, 'USD'),
		'CZK' => new Property(1, 1.0, 'CZK'),
	]);
	$exchange = new Exchange('EUR', $ratingList, 'USD');
}

echo $exchange->change(100) . PHP_EOL; // EUR -> USD = 125.0

// use only upper case
echo $exchange->change(100, 'CZK') . PHP_EOL; // CZK -> USD = 5.0
echo $exchange->change(100, null, 'CZK') . PHP_EOL; // EUR -> CZK = 2500.0
echo $exchange->change(100, 'USD', 'CZK') . PHP_EOL; // USD -> CZK = 2000.0
echo PHP_EOL;

// History
$exchangePast = $exchangeFactory->create(cacheEntity: new CacheEntity(new Datetime('2000-12-30'), new Day));
echo $exchangePast->change(100) . PHP_EOL;
echo PHP_EOL;

// Array access
$property = $exchange['EUR'];
var_dump($property);
echo PHP_EOL;

// Iterator
foreach ($exchange as $code => $property) {
	/* @var $property Property */
	var_dump($code, $property);
}
