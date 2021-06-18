<?php declare(strict_types=1);

namespace h4kuna\Exchange\Currency;

use h4kuna\Exchange\Exceptions;
use Tester\Assert;

require_once __DIR__ . '/../../bootstrap.php';

$listRates = new ListRates(new \DateTime());;

Assert::exception(function () use ($listRates) {
	$listRates->getFirst();
}, \h4kuna\Exchange\Exceptions\EmptyExchangeRate::class);

Assert::exception(function () use ($listRates) {
	$listRates->offsetSet('xxx', new Property([
		'foreign' => 1,
		'home' => 1,
		'code' => 'XXX',
		'rate' => 1.0,
	]));
}, Exceptions\FrozenMethod::class);

Assert::exception(function () use ($listRates) {
	$listRates->offsetUnset('xxx');
}, Exceptions\FrozenMethod::class);
