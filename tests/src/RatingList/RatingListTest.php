<?php declare(strict_types=1);

namespace h4kuna\Exchange\Tests\RatingList;

use h4kuna\Exchange;
use h4kuna\Exchange\Exceptions;
use Tester\Assert;

require_once __DIR__ . '/../../bootstrap.php';

$exchange = createExchangeFactory()->create();
$ratingList = $exchange->getIterator();

Assert::same(20.0, $ratingList['usd']->rate);
Assert::same(25.0, $ratingList['eur']->rate);

Assert::exception(function () use ($ratingList) {
	$ratingList->offsetSet('xxx', new Exchange\Currency\Property(
		1,
		1,
		'XXX',
	));
}, Exceptions\FrozenMethodException::class);

Assert::exception(function () use ($ratingList) {
	$ratingList->offsetUnset('xxx');
}, Exceptions\FrozenMethodException::class);

Assert::exception(function () use ($ratingList) {
	$ratingList['qwe'];
}, Exchange\Exceptions\UnknownCurrencyException::class);

Assert::type(Exchange\Currency\Property::class, $ratingList->offsetGet('czk'));

Assert::exception(function () use ($ratingList) {
	$ratingList['czk'] = new Exchange\Currency\Property(
		1,
		1,
		'XXX',
	);
}, Exceptions\FrozenMethodException::class);

Assert::exception(function () use ($ratingList) {
	unset($ratingList['czk']);
}, Exceptions\FrozenMethodException::class);
