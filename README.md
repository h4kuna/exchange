Exchange
-------
[![Downloads this Month](https://img.shields.io/packagist/dm/h4kuna/exchange.svg)](https://packagist.org/packages/h4kuna/exchange)
[![Latest Stable Version](https://poser.pugx.org/h4kuna/exchange/v/stable?format=flat)](https://packagist.org/packages/h4kuna/exchange)
[![Coverage Status](https://coveralls.io/repos/github/h4kuna/exchange/badge.svg?branch=master)](https://coveralls.io/github/h4kuna/exchange?branch=master)
[![Total Downloads](https://poser.pugx.org/h4kuna/exchange/downloads?format=flat)](https://packagist.org/packages/h4kuna/exchange)
[![License](https://poser.pugx.org/h4kuna/exchange/license?format=flat)](https://packagist.org/packages/h4kuna/exchange)

Exchange is PHP script works with currencies. You can convert price, format add VAT or only render exchange rates.

Here is [changelog](changelog.md).

## Extension for framework

- [Nette extension](//github.com/h4kuna/exchange-nette)

Installation via composer
-----------------------

```sh
$ composer require h4kuna/exchange
```

## How to use

Init object [Exchange](src/Exchange.php) by [ExchangeFactory](src/ExchangeFactory.php). Default Driver for read is [Cnb](src/Driver/Cnb/Day.php), [here are others](src/Driver).

For example define own exchange rates:

- 25 CZK = 1 EUR
- 20 CZK = 1 USD

```php
use h4kuna\CriticalCache;use h4kuna\Exchange;

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

echo $exchange->change(100); // EUR -> USD = 125.0

// use only upper case
echo $exchange->change(100, 'CZK'); // CZK -> USD = 5.0
echo $exchange->change(100, NULL, 'CZK'); // EUR -> CZK = 2500.0
echo $exchange->change(100, 'USD', 'CZK'); // USD -> CZK = 2000.0
```

### Change driver and date

Download history exchange rates. Make new instance of Exchange with history rate.

```php
use h4kuna\Exchange\RatingList;
use h4kuna\Exchange;

$exchangePast = $exchange->modify(cacheEntity: new RatingList\CacheEntity(new \Datetime('2000-12-30'), Exchange\Driver\Cnb\Day::class));
$exchangePast->change(100);
```

### Access and iterator

```php
/* @var $property Exchange\Currenry\Property */
$property = $exchange['EUR'];
var_dump($property);


foreach ($exchange as $code => $property) {
    /* @var $property Exchange\Currenry\Property */
    var_dump($property);
}
```

## Caching

The cache invalid automatic at some time, defined by property `Driver::$refresh`. From this property is counted time to live. Little better is invalid cache by cron. Because one request on server does not lock other requests. Let's run cron max. 15 minutes before invalidate cache.
```php
use h4kuna\Exchange\RatingList\RatingListCache;
use h4kuna\Exchange\RatingList\CacheEntity;
use h4kuna\Exchange\Driver\Cnb\Day;

/** @var RatingListCache $ratingListCache */
$ratingListCache->rebuild(new CacheEntity(null, Day::class));
```

In example, is used `h4kuna\Exchange\Driver\Cnb\Day::$refresh` is defined at 15:00. Run cron 14:55 every day.


