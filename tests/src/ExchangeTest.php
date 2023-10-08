<?php declare(strict_types=1);

namespace h4kuna\Exchange;

use h4kuna\Exchange;
use Psr\Http\Client\ClientExceptionInterface;
use Tester\Assert;

require_once __DIR__ . '/../bootstrap.php';

$exchangeFactory = createExchangeFactory();
$exchange = $exchangeFactory->create(new \DateTime('2022-12-20'));

// change driver
Assert::same('EUR', $exchange->getFrom()->code);
Assert::same($exchange->getTo(), $exchange->getFrom());
Assert::same($exchange->getOutput(), $exchange->getDefault());
Assert::true(isset($exchange['EUR']));

Assert::same(100.0, $exchange->change(100));
Assert::same(26.0, $exchange->change(1, 'EUR', 'CZK'));
Assert::same(50.0, $exchange->change(100, 'USD', 'EUR'));

Assert::same(200.0, $exchange->change(100, null, 'USD'));
Assert::same(50.0, $exchange->change(100, 'USD'));

$result = $exchange->transfer(100, 'USD');
Assert::same(50.0, $result[0]);
Assert::type(Exchange\Driver\Cnb\Property::class, $result[1]);
Assert::same('EUR', $result[1]->code);
Assert::same('EUR', (string) $result[1]);

foreach ($exchange as $code => $property) {
	Assert::type(Exchange\Currency\Property::class, $property);
}

$exchange2 = $exchange->modify('CZK', 'EUR');
Assert::same(2600.0, $exchange2->change(100));
Assert::same(0.0, $exchange2->change(0));

Assert::exception(function () use ($exchange) {
	unset($exchange['EUR']);
}, Exchange\Exceptions\FrozenMethodException::class);

Assert::exception(function () use ($exchange) {
	$exchange['EUR'] = 1; // @phpstan-ignore-line
}, Exchange\Exceptions\FrozenMethodException::class);

$exchange2 = $exchange->modify(null, null, new Exchange\RatingList\CacheEntity(new \DateTime('2022-12-01'), Driver\Cnb\Day::class));
Assert::same(24.0, $exchange2['EUR']->rate);

Assert::exception(static function() use ($exchange) {
	$exchange->modify(cacheEntity: new Exchange\RatingList\CacheEntity(new \DateTime('2022-12-02'), Driver\Cnb\Day::class));
}, ClientExceptionInterface::class);

$exchange3 = $exchange->modify(cacheEntity: new Exchange\RatingList\CacheEntity(new \DateTime(), Driver\Cnb\Day::class));
Assert::same(25.0, $exchange3['EUR']->rate);
unlink(TEMP_DIR . '/exchange/h4kuna/cache/_.h4kuna.Exchange.Driver.Cnb.Day.ttl');
Exchange\Fixtures\HttpFactory::$exception = true;
$exchange4 = $exchange->modify(cacheEntity: new Exchange\RatingList\CacheEntity(null, Driver\Cnb\Day::class));
Assert::same(25.0, $exchange4['EUR']->rate);
