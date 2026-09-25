Exchange
-------
[![Downloads this Month](https://img.shields.io/packagist/dm/h4kuna/exchange.svg)](https://packagist.org/packages/h4kuna/exchange)
[![Latest Stable Version](https://poser.pugx.org/h4kuna/exchange/v/stable?format=flat)](https://packagist.org/packages/h4kuna/exchange)
[![Coverage Status](https://coveralls.io/repos/github/h4kuna/exchange/badge.svg?branch=main)](https://coveralls.io/github/h4kuna/exchange?branch=main)
[![Total Downloads](https://poser.pugx.org/h4kuna/exchange/downloads?format=flat)](https://packagist.org/packages/h4kuna/exchange)
[![License](https://poser.pugx.org/h4kuna/exchange/license?format=flat)](https://packagist.org/packages/h4kuna/exchange)

Part of the [h4kuna PHP libraries](https://github.com/h4kuna/library), see the overview of all packages.

Exchange is a PHP library for working with currencies: it downloads exchange rates and converts prices between currencies.

Here is the [changelog](changelog.md).

## Framework extensions

- [Nette extension](//github.com/h4kuna/exchange-nette)

## Installation via composer

Requires PHP 8.2 or newer.

```sh
$ composer require h4kuna/exchange
```

Optional packages used by the default setup of `ExchangeFactory`:
```sh
$ composer require guzzlehttp/guzzle guzzlehttp/psr7 h4kuna/dir malkusch/lock nette/caching
```

- `guzzlehttp/guzzle` and `guzzlehttp/psr7` are the default PSR-18 HTTP client and PSR-17 request factory; you can pass your own implementation instead.
- `h4kuna/dir`, `nette/caching` and `malkusch/lock` are used to build the default cache with locking; you can pass your own `RatingListCache` or `CacheLockingFactoryInterface` instead.

The cache is PSR-16 based, provided by [h4kuna/critical-cache](//github.com/h4kuna/critical-cache).

## How to use

Create the [Exchange](src/Exchange.php) object by [ExchangeFactory](src/ExchangeFactory.php). The default driver for downloading rates is [Cnb](src/Driver/Cnb/Day.php) (Czech National Bank), [here are the others](src/Driver): `Ecb\Day` (European Central Bank, current rates only) and `RB\DayBuy`, `RB\DaySell`, `RB\DayCenter` (Raiffeisenbank).

Or create `Exchange` with your own exchange rates, for example:

- 25 CZK = 1 EUR
- 20 CZK = 1 USD

```php
use h4kuna\Exchange\Currency\Property;
use h4kuna\Exchange\Exchange;
use h4kuna\Exchange\ExchangeFactory;
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

{ # or custom RatingList, the results below are for these rates
	$ratingList = new RatingList(new DateTimeImmutable(), new DateTimeImmutable(), null, [
		'EUR' => new Property(1, 25.0, 'EUR'),
		'USD' => new Property(1, 20.0, 'USD'),
		'CZK' => new Property(1, 1.0, 'CZK'),
	]);
	$exchange = new Exchange('EUR', $ratingList, 'USD');
}

echo $exchange->change(100) . PHP_EOL; // EUR -> USD = 125.0

// currency codes passed to change() must be upper case
echo $exchange->change(100, 'CZK') . PHP_EOL; // CZK -> USD = 5.0
echo $exchange->change(100, null, 'CZK') . PHP_EOL; // EUR -> CZK = 2500.0
echo $exchange->change(100, 'USD', 'CZK') . PHP_EOL; // USD -> CZK = 2000.0
```

### Change driver and date

Download historical exchange rates. This creates a new instance of Exchange with the historical rates.

```php
use h4kuna\Exchange\Driver\Cnb\Day;
use h4kuna\Exchange\RatingList\CacheEntity;

$exchangePast = $exchangeFactory->create(cacheEntity: new CacheEntity(new DateTime('2000-12-30'), new Day));
echo $exchangePast->change(100) . PHP_EOL;
```

The second argument of `CacheEntity` is the driver, e.g. `new CacheEntity(null, new \h4kuna\Exchange\Driver\Ecb\Day)` for the current ECB rates.

### Array access and iteration

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

The cache of current rates expires automatically 30 minutes after the refresh time of the driver, the `$refresh` argument of the driver constructor (e.g. `today 14:30:00` for `Cnb\Day`). It is a little better to rebuild the cache by cron, because then no request on the server has to wait for the download. Run the cron within the last 29 minutes before the cache expires.

```php
use h4kuna\Exchange\RatingList\RatingListCache;
use h4kuna\Exchange\RatingList\CacheEntity;
use h4kuna\Exchange\Driver\Cnb\Day;

/** @var RatingListCache $ratingListCache */
$ratingListCache->rebuild(new CacheEntity(null, new Day));
```

In the example, `h4kuna\Exchange\Driver\Cnb\Day` has the refresh time 14:30, so the cache expires at 15:00. Run the cron every day at 14:32.
