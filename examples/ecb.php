<?php declare(strict_types=1);

use h4kuna\Exchange\Driver\Ecb\Day;
use h4kuna\Exchange\ExchangeFactory;
use h4kuna\Exchange\RatingList\CacheEntity;

require_once __DIR__ . '/../vendor/autoload.php';

{
	$exchangeFactory = new ExchangeFactory(
		from: 'eur',
		to: 'usd',
		allowedCurrencies: [
			'CZK',
			'USD',
			'eur', // lower case will be changed to upper case
		],
		cacheEntity: new CacheEntity(date: null, source: new Day),
		tempDir: new h4kuna\Dir\Dir(__DIR__ . '/../tests/temp/examples'),
	);

	$exchange = $exchangeFactory->create();
}

dump($exchange->change(25, 'CZK', 'EUR')); // +- 1.0 EUR
dump($exchange->change(25)); // +- 29.0 USD
