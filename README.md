Exchange
-------
[![Downloads this Month](https://img.shields.io/packagist/dm/h4kuna/exchange.svg)](https://packagist.org/packages/h4kuna/exchange)
[![Latest Stable Version](https://poser.pugx.org/h4kuna/exchange/v/stable?format=flat)](https://packagist.org/packages/h4kuna/exchange)
[![Coverage Status](https://coveralls.io/repos/github/h4kuna/exchange/badge.svg?branch=master)](https://coveralls.io/github/h4kuna/exchange?branch=master)
[![Total Downloads](https://poser.pugx.org/h4kuna/exchange/downloads?format=flat)](https://packagist.org/packages/h4kuna/exchange)
[![License](https://poser.pugx.org/h4kuna/exchange/license?format=flat)](https://packagist.org/packages/h4kuna/exchange)

Exchange is PHP script works with currencies. You can convert price.

Here is [changelog](changelog.md).

## Extension for framework

- [Nette extension](//github.com/h4kuna/exchange-nette)

## Installation via composer

```sh
$ composer require h4kuna/exchange
```
Optional packages
```sh
$ composer require guzzlehttp/guzzle guzzlehttp/psr7 h4kuna/dir nette/caching
```

Support PSR-6 for cache.

## How to use

Init object [Exchange](src/Exchange.php) by [ExchangeFactory](src/ExchangeFactory.php). Default Driver for read is [Cnb](src/Driver/Cnb/Day.php), [here are others](src/Driver).

For example define own exchange rates:

- 25 CZK = 1 EUR
- 20 CZK = 1 USD

```php
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
```

### Change driver and date

Download history exchange rates. Make new instance of Exchange with history rate.

```php
use h4kuna\Exchange\RatingList;
use h4kuna\Exchange;

$exchangePast = $exchangeFactory->create(cacheEntity: new CacheEntity(new Datetime('2000-12-30'), new Day));
echo $exchangePast->change(100) . PHP_EOL;
```

### Access and iterator

```php
use h4kuna\Exchange\Currency\Property;
/* @var $property Property */
$property = $exchange['EUR'];
var_dump($property);
echo PHP_EOL;

foreach ($exchange as $code => $property) {
	/* @var $property Property */
	var_dump($code, $property);
}
```

## Caching

The cache invalid automatic at some time, defined by property `SourceData::$refresh`. From this property is counted time to live. Little better is invalid cache by cron. Because one request on server does not lock other requests. Let's run cron max. 29 minutes before invalidate cache.
```php
use h4kuna\Exchange\RatingList\RatingListCache;
use h4kuna\Exchange\RatingList\CacheEntity;
use h4kuna\Exchange\Driver\Cnb\Day;

/** @var RatingListCache $ratingListCache */
$ratingListCache->rebuild(new CacheEntity(null, new Day));
```

In example, is used `h4kuna\Exchange\Driver\Cnb\Day::$refresh` is defined at 14:30 + 30 minute the cache is valid. Run cron 14:32 every day.
