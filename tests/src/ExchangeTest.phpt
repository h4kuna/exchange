<?php

namespace h4kuna\Exchange;

use h4kuna\Exchange,
	Tester\Assert;

require_once __DIR__ . '/../bootstrap.php';

$cache = new Exchange\Caching\Cache(TEMP_DIR);
$exchange = new Exchange\Exchange($cache);
$exchange->setDriver(new Exchange\Test\Driver());

Assert::same('EUR', $exchange->getDefault()->code);
Assert::same($exchange->getOutput(), $exchange->getDefault());

Assert::same(20.0, $exchange['usd']->rate);
Assert::same(25.0, $exchange['eur']->rate);

Assert::same(100, $exchange->change(100));
Assert::same(25.0, $exchange->change(1, 'eur', 'czk'));
Assert::same(80.0, $exchange->change(100, 'usd', 'eur'));

Assert::same(125.0, $exchange->change(100, NULL, 'usd'));
Assert::same(80.0, $exchange->change(100, 'usd'));

$exchange->setDefault('usd');
Assert::same(80.0, $exchange->change(100, NULL, 'eur'));

$exchange->setOutput('czk');
Assert::same(2000.0, $exchange->change(100));
Assert::same(0, $exchange->change(0));

$exchange->addRate('usd', 23.0);
Assert::same(23.0, $exchange->change(1, 'usd', 'czk'));
Assert::same(1.0, $exchange->change(23.0, 'czk', 'usd'));

$exchange->removeRate('usd');
Assert::same(20.0, $exchange->change(1, 'usd', 'czk'));

Assert::exception(function () use ($exchange) {
	$exchange['qwe'];
}, UnknownCurrencyException::class);

foreach ($exchange as $code => $property) {
	Assert::type(Exchange\Currency\Property::class, $property);
}

Assert::type(Exchange\Currency\Property::class, $exchange->offsetGet('czk'));

Assert::exception(function () use ($exchange) {
	$exchange['czk'] = 5;
}, FrozenMethodException::class);

Assert::exception(function () use ($exchange) {
	unset($exchange['czk']);
}, FrozenMethodException::class);

$exchange->setDriver(NULL, new \DateTime('yesterday'));
Assert::same(2223.2, $exchange->change(100));