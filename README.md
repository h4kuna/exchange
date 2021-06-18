Exchange
-------
[![Build Status](https://travis-ci.com/h4kuna/exchange.svg?branch=master)](https://travis-ci.com/h4kuna/exchange)
[![Latest stable](https://img.shields.io/packagist/v/h4kuna/exchange.svg)](https://packagist.org/packages/h4kuna/exchange)
[![Downloads this Month](https://img.shields.io/packagist/dm/h4kuna/exchange.svg)](https://packagist.org/packages/h4kuna/exchange)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/h4kuna/exchange/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/h4kuna/exchange/?branch=master)
[![Coverage Status](https://coveralls.io/repos/github/h4kuna/exchange/badge.svg?branch=master)](https://coveralls.io/github/h4kuna/exchange?branch=master)

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
Init object [Exchange](src/Exchange.php) and [Cache](src/Caching/Cache.php). Default Driver for read is [Cnb](src/Driver/Cnb/Day.php), [here are others](src/Driver).

For example define own exchange rates:
- 25 CZK = 1 EUR
- 20 CZK = 1 USD

```php
use h4kuna\Exchange;

$cache = new Caching\Cache('/temp/dir');
$exchange = new Exchange\Exchange($cache);

$exchange->setDefault('eur');
$exchange->setOutput('usd');

echo $exchange->change(100); // EUR -> USD = 125.0
echo $exchange->change(100, 'czk'); // CZK -> USD = 5.0
echo $exchange->change(100, NULL, 'czk'); // EUR -> CZK = 2500.0
echo $exchange->change(100, 'usd', 'czk'); // USD -> CZK = 2000.0
```

### Change driver and date
Download history exchange rates.
```php
$exchange->setDriver(new Exchange\Driver\Cnb\Day, new \Datetime('2000-12-30'));
```
If we need read data from database, it is not problem write own Driver.
```php
class MyDatabaseDriver extend \h4kuna\Exchange\Driver\ADriver 
{
    /**
     * Load data for iterator.
     * @param DateTime|NULL $date
     * @return array
     */
    protected function loadFromSource(DateTime $date = NULL)
    {
        // your implementation
        $this->setDate('Y-m-d', (string) $database->selectDate());
        return $database->fetchMyCurrencies();
    }

    /**
     * Modify data before save to cache.
     * @return Exchange\Currency\Property|NULL
     */
    protected function createProperty($row) 
    {
        return new \h4kuna\Exchange\Currency\Property([
            'code' => $row['currency'],
            'home' => $row['rate'],
            'foreign' => 1
        ]);
    }
}

$exchange->setDriver(new \MyDatabaseDriver);
```

### Format output
Define output formats, for more information read this documentation [h4kuna/number-format](//github.com/h4kuna/number-format#numberformatstate).
```php
$formats = new Exchange\Currency\Formats(new \h4kuna\Number\NumberFormatFactory());

$formats->addFormat('EUR', ['decimalPoint' => '.', 'unit' => '€']);
```

Create [Filters](src/Filters.php) for format API.
```php
$filters = new Exchange\Filters($exchange, $formats);
```
You can define VAT
```php
// VAT 21%
$filters->setVat(new \h4kuna\Number\Tax(21));
```
Output
```php

// format 100 EUR like default to USD is set above
$filters->format(100); // '125,00 USD'

// count with VAT
$filters->formatVat(100, 'usd', 'eur'); // '96.80 €'
$filters->formatVatTo(100); // '151,25 USD'
$filters->formatTo(100, 'CZK'); // '2 000,00 CZK'

// Other options
$filters->change(100, 'usd', 'eur'); // 80.0
$filters->changeTo(100, 'usd'); // 125.0
$filters->vat(100); // 121.0
```

### Temporary rate

```php
$exchange->addRate('usd', 23.0);
$exchange->change(1, 'usd', 'czk'); // 23.0

$exchange->removeRate('usd');
$exchange->change(1, 'usd', 'czk'); // 20.0
```

### Access and iterator

```php
/* @var $property Exchange\Currenry\Property */
$property = $exchange['eur'];
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

### Save state to cookie
Here is [prepared object](src/Http/CookieManager.php) whose keep selected currency.

Run on startup of you project.
```php
$cookieManager = Exchange\Http\CookieManager($exchange);
```

Set new option
```php
$cookieManager->setCurrency($_GET['currency']);
```

### Cache
Default Cache save data on filesystem you can implement [ICache](src/Caching/ICache.php) interface and change it.
