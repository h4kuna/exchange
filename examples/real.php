<?php declare(strict_types=1);

use h4kuna\Exchange\ExchangeFactory;

require_once __DIR__ . '/../vendor/autoload.php';

$factory = new ExchangeFactory(to: 'EUR', tempDir: __DIR__ . '/../tests/temp/example');

$exchange = $factory->create();

var_dump($exchange->change(100));
