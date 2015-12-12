Exchange
-------
[![Build Status](https://travis-ci.org/h4kuna/exchange.svg?branch=master)](https://travis-ci.org/h4kuna/exchange)
[![Latest stable](https://img.shields.io/packagist/v/h4kuna/exchange.svg)](https://packagist.org/packages/h4kuna/exchange)

Is required guzzle/guzzle 6.1+ and php 5.5+. If you have php < 5.5 use older version [v4.1.0] it work but does not use guzzle.

Exchange is PHP script works with currencies. This extension is primary for [Nette framework 2+](http://nette.org/), but you can use without Nette for another framework or [without framework](src/NoFramework).

Dependency on [NumberFormat](//github.com/h4kuna/number-format).

Installation to project
-----------------------
The best way to install h4kuna/exchange is using Composer:
```sh
$ composer require h4kuna/exchange
```

Example NEON config
-------------------
```sh
extensions:
    exchangeExtension: h4kuna\Exchange\Nette\DI\ExchangeExtension

exchangeExtension:
    currencies: {
            czk: [decimal: 0, symbol: 'Kč', point: ',', thousand: ' ', mask: '1 S', flag: 10]
            usd: [symbol: '$']
            gbp: [mask: 'S1', thousand: '.', symbol: '£', decimal: 2] }
    vat: 21
    vatIn: false
    vatOut: false
```

Create dependency on **h4kuna\Exchange\Exchange** in presenter
```php
class HomePresenter extends MyBasePresenter {
    /** @var \h4kuna\Exchange\Exchange @inject */
    public $exchange;
}
```
in model layer:
```php
class MyModel {
    /** @var \h4kuna\Exchange\Exchange */
    private $exchange;

    public function __construct(\h4kuna\Exchange\Exchange $exchange)
    {
        $this->exchange = $exchange;
    }
}
```


Basic usage.
```php
/* @var $exchange h4kuna\Exchange\Exchange */
$exchange->setDate(new DateTime('2000-12-30'));
$exchange->format(10, 'eur', 'czk'); // 351 Kč
```

Method format has parameters:
	- money (int/float)
	- from, this can be global set by method setDefault()
	- to, this can be global set by method setWeb()
	- vat, this can be global set by method setVat()

If you want all currencies. Default is load whose are in config.
```php
$exchange->loadAll(); // array of h4kuna\Exchange\Currency\IProperty
```

Change default currency. Normaly default is first in config.
```php
$exchange->setDefault('czk');
$this->exchange->format(10); // 10 Kč
$exchange->setDefault('gbp');
$exchange->format(10); // 564 Kč, there is czk output
$exchange->format(10, NULL, 'gbp'); // £10.00
// or
$exchange->setWeb('gbp');
$exchange->format(10); // £10.00
```

Change driver on fly.
```php
$exchange->setDate(); // reset history to current
$ecbDriver = $exchange->setDriver(new Driver\Ecb\Day); // Ecb does not support history, yet
$ecbDriverExchange->format(10);
```

