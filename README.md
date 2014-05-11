Exchange
-------
[![Build Status](https://travis-ci.org/h4kuna/exchange.png)](https://travis-ci.org/h4kuna/exchange)

Exchange is PHP script works with currencies. This extension is primary for [Nette framework 2+](http://nette.org/), but you can use without Nette for another framework or [without framework](https://github.com/h4kuna/exchange/tree/master/NoFramework).

Installation to project
-----------------------
The best way to install h4kuna/exchange is using Composer:
```sh
$ composer require h4kuna/exchange:4.0.*
```

Example NEON config
-------------------
<pre>
extensions:
    exchangeExtension: h4kuna\Exchange\DI\ExchangeExtension

exchangeExtension:
    currencies: {
            czk: [decimal: 5, symbol: 'Kč', point: ',', thousand: ' ', mask: 'S 1', zeroClear: true]
            usd: [symbol: '$']
            gbp: [mask: 'S1', thousand: '.', symbol: '£', decimal: 0] }
    vat: 20.5
    vatIn: false
    vatOut: false
</pre>

Run example
-----------
```sh
$ cd to/your/web/document/root
$ git clone git@github.com:h4kuna/exchange.git
$ cd exchange
$ mkdir tmp
$ chmod 777 tmp
$ composer install
```
Run in browser example.php and you can see how it is work.
