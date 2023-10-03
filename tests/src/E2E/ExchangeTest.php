<?php declare(strict_types=1);

namespace h4kuna\Exchange\Tests\RatingList;

use h4kuna\Exchange\Driver\Cnb\Day;
use h4kuna\Exchange\ExchangeFactory;
use Tester\Assert;

require_once __DIR__ . '/../../bootstrap.php';

$exchangeFactory = new ExchangeFactory('czk', 'EUR', __DIR__ . '/../../temp');

$exchange = $exchangeFactory->create(new \DateTimeImmutable('2021-06-18'));

$exchangeFactory->createRatingListCache()->rebuild(Day::class, new \DateTimeImmutable('2021-06-18'));

Assert::same(3.918495297805643, $exchange->change(100.0));
Assert::type('float', $exchange->change(100.0));