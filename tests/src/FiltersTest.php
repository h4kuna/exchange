<?php declare(strict_types=1);

namespace h4kuna\Exchange;

use h4kuna;
use h4kuna\Exchange;
use Tester\Assert;

require __DIR__ . '/../bootstrap.php';

$exchange = createExchangeFactory()->create();

$formats = new Exchange\Currency\Formats(new h4kuna\Number\NumberFormatFactory());

$formats->addFormat('EUR', ['decimalPoint' => '.', 'unit' => '€']);

$filters = new Filters($exchange, $formats, new h4kuna\Number\Tax(21));

Assert::same('EUR', $exchange->getDefault()->code);

Assert::same(80.0, $filters->change(100, 'usd', 'eur'));

Assert::same(125.0, $filters->changeTo(100, 'usd'));

Assert::same(121.0, $filters->vat(100));
Assert::same('96.80 €', $filters->formatVat(100, 'usd', 'eur'));
Assert::same('151,25 USD', $filters->formatVatTo(100, 'usd'));
Assert::same('125,00 USD', $filters->formatTo(100, 'usd'));
