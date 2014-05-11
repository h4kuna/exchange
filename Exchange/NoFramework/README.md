Example how to use without framework
-------

```php
// $tempPath is writeable
$builder = new h4kuna\Exchange\NoFramework\Builder($tempPath);
$exchange = $builder->create();
$exchange->loadCurrency('CZK');
$exchange->loadCurrency('EUR');
```

