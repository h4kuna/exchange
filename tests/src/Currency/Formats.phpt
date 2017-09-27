<?php

namespace h4kuna\Exchange\Currency;

use h4kuna\Number,
	Tester\Assert;

require_once __DIR__ . '/../../bootstrap.php';

$numberFormatFactory = new Number\NumberFormatFactory();

$formats = new \h4kuna\Exchange\Currency\Formats($numberFormatFactory);

$formats->addFormat('CZK', ['decimals' => 3, 'nbsp' => false]);
$formats->addFormat('USD', ['decimals' => 2, 'unit' => '$', 'nbsp' => false]);
$formats->setDefaultFormat(['decimals' => 1]);
$formats->setDefaultFormat($numberFormatFactory->createUnit(['decimals' => 0, 'nbsp' => false]));

Assert::exception(function () use ($formats) {
	$formats->setDefaultFormat(new \ArrayObject([]));
}, \h4kuna\Exchange\InvalidArgumentException::class);

Assert::same('100 EUR', $formats->getFormat('EUR')->format('100', 'EUR'));
Assert::same($formats->getFormat('EUR'), $formats->getFormat('EUR'));
Assert::same('100,00 $', $formats->getFormat('USD')->format('100'));
Assert::same('100,000 CZK', $formats->getFormat('CZK')->format('100', 'CZK'));