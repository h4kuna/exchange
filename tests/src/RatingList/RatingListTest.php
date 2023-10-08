<?php declare(strict_types=1);

namespace h4kuna\Exchange\Tests\RatingList;

use h4kuna\Exchange;
use h4kuna\Exchange\Exceptions;
use Tester\Assert;

require_once __DIR__ . '/../../bootstrap.php';

/**
 * @deprecated test back compatibility
 */

$exchange = createExchangeFactory()->create();
$ratingList = $exchange->getRatingList();

Assert::same(20.0, $ratingList['USD']->rate);
Assert::same(25.0, $ratingList['EUR']->rate);
Assert::true(isset($ratingList['EUR']));

Assert::exception(function () use ($ratingList) {
	$ratingList->offsetSet('XXX', new Exchange\Currency\Property(
		1,
		1,
		'XXX',
	));
}, Exceptions\FrozenMethodException::class);

Assert::exception(function () use ($ratingList) {
	$ratingList->offsetUnset('XXX');
}, Exceptions\FrozenMethodException::class);

Assert::exception(function () use ($ratingList) {
	$ratingList['QWE'];
}, Exchange\Exceptions\UnknownCurrencyException::class);

Assert::type(Exchange\Currency\Property::class, $ratingList->offsetGet('CZK'));

Assert::exception(function () use ($ratingList) {
	$ratingList['CZK'] = new Exchange\Currency\Property(
		1,
		1,
		'XXX',
	);
}, Exceptions\FrozenMethodException::class);

Assert::exception(function () use ($ratingList) {
	unset($ratingList['CZK']);
}, Exceptions\FrozenMethodException::class);

$list = [];
foreach ($ratingList as $item) {
	$list[] = $item;
}

Assert::count(3, $list);
