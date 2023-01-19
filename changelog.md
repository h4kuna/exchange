# Changelog

## v7.0

- your temporary rate implement by own RatingList
- custom driver must be to use in Builder, see to ExchangeFactory
- cache is implemented by PSR-6
- remove dependency on Guzzle, PSR-7, PRS-17 and PSR-18 ready
- remove dependency on h4kuna/data-type, nette/safe-stream, nette/utils
- access for currency use RatingList instead of Exchange, Exchange::getRatingList()['EUR']
- support php 8.0+
- the methods that are preserved are the same prototype and behavior
- CookieManager moved [this extension](//github.com/h4kuna/exchange-nette)
- remove dependency on h4kuna/number-format
- Formats.php and Filters.php moved [this extension](//github.com/h4kuna/exchange-nette)
- add lazy mode for create Driver, Client, HttpFactory

## v6.0

- support php 7.1+
- use type hints
- api is same like v5.0


## v5.0

Dependency on Nette framework was removed, If you want, follow [this extension](//github.com/h4kuna/exchange-nette). Minimal php is 5.5+. Api is changed, not compatible with older version.


## v4.0

Here is [older version](//github.com/h4kuna/exchange/tree/v4.2.2).
