<?php declare(strict_types=1);

namespace h4kuna\Exchange;

use h4kuna\Exchange;
use Tester\Assert;

require_once __DIR__ . '/../bootstrap.php';

$exchangeFactory = createExchangeFactory();
$exchange = $exchangeFactory->create();

// change driver
Assert::same('EUR', $exchange->getDefault()->code);
Assert::same($exchange->getOutput(), $exchange->getDefault());

Assert::same(100.0, $exchange->change(100));
Assert::same(25.0, $exchange->change(1, 'eur', 'czk'));
Assert::same(80.0, $exchange->change(100, 'usd', 'eur'));

Assert::same(125.0, $exchange->change(100, null, 'usd'));
Assert::same(80.0, $exchange->change(100, 'usd'));

$result = $exchange->transfer(100, 'usd');
Assert::same(80.0, $result[0]);
Assert::type(Exchange\Driver\Cnb\Property::class, $result[1]);
Assert::same('EUR', $result[1]->code);

foreach ($exchange as $code => $property) {
	Assert::type(Exchange\Currency\Property::class, $property);
}
