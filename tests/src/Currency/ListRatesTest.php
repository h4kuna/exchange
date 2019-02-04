<?php declare(strict_types=1);

namespace h4kuna\Exchange\Currency;

use Tester\Assert;

require_once __DIR__ . '/../../bootstrap.php';

$listRates = new ListRates(new \DateTime());;

Assert::exception(function () use ($listRates) {
	$listRates->getFirst();
}, \h4kuna\Exchange\Exceptions\EmptyExchangeRate::class);

Assert::exception(function () use ($listRates) {
	$listRates->offsetSet('xxx', 'value');
}, \h4kuna\Exchange\Exceptions\FrozenMethod::class);

Assert::exception(function () use ($listRates) {
	$listRates->offsetUnset('xxx');
}, \h4kuna\Exchange\Exceptions\FrozenMethod::class);
