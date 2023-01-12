Exchange
-------
[![Latest stable](https://img.shields.io/packagist/v/h4kuna/exchange.svg)](https://packagist.org/packages/h4kuna/exchange)
[![Downloads this Month](https://img.shields.io/packagist/dm/h4kuna/exchange.svg)](https://packagist.org/packages/h4kuna/exchange)

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
use h4kuna\Exchange;

$exchangeFactory = new Exchange\ExchangeFactory('eur', 'usd', '/tmp/exchange', [
		'czk',
		'usd',
		'eur',
	]);

$exchange = $exchangeFactory->create();

echo $exchange->change(100); // EUR -> USD = 125.0
echo $exchange->change(100, 'czk'); // CZK -> USD = 5.0
echo $exchange->change(100, NULL, 'czk'); // EUR -> CZK = 2500.0
echo $exchange->change(100, 'usd', 'czk'); // USD -> CZK = 2000.0
```

### Change driver and date

Download history exchange rates. Make new instance of Exchange with history rate.

```php
$exchange = $exchangeFactory->create(new \Datetime('2000-12-30'));
```

### Access and iterator

```php
/* @var $property Exchange\Currenry\Property */
$property = $exchange->getRatingList()['eur'];
var_dump($property);


foreach ($exchange as $code => $property) {
    /* @var $property Exchange\Currenry\Property */
    var_dump($property);
}
```

### Limit for currencies

```php
$cache = new Caching\Cache('/temp/dir');
$cache->setAllowedCurrencies(['CZK', 'USD', 'EUR']);
$exchange = new Exchange\Exchange($cache);

// in cache are only this three currencies
foreach ($exchange as $code => $property) {
    /* @var $property Exchange\Currenry\Property */
    var_dump($property);
}
```
