<?php declare(strict_types=1);

namespace h4kuna\Exchange;

use h4kuna\Exchange;
use Tester\Assert;

require_once __DIR__ . '/../bootstrap.php';

$exchangeFactory = createExchangeFactory();
$exchange = $exchangeFactory->create();

// change driver
Assert::same('EUR', $exchange->getFrom()->code);
Assert::same($exchange->getTo(), $exchange->getFrom());

Assert::same(100.0, $exchange->change(100));
Assert::same(25.0, $exchange->change(1, 'EUR', 'CZK'));
Assert::same(80.0, $exchange->change(100, 'USD', 'EUR'));

Assert::same(125.0, $exchange->change(100, null, 'USD'));
Assert::same(80.0, $exchange->change(100, 'USD'));

$result = $exchange->transfer(100, 'USD');
Assert::same(80.0, $result[0]);
Assert::type(Exchange\Driver\Cnb\Property::class, $result[1]);
Assert::same('EUR', $result[1]->code);

foreach ($exchange as $code => $property) {
	Assert::type(Exchange\Currency\Property::class, $property);
}

$exchange2 = $exchange->modify('CZK', 'EUR');
Assert::same(2500.0, $exchange2->change(100));
