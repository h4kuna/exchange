<?php

declare(strict_types=1);

namespace h4kuna\Exchange\Tests\RatingList;

require_once __DIR__ . '/../../bootstrap.php';

use h4kuna\Exchange\Currency\Property;
use h4kuna\Exchange\Exceptions\UnknownCurrencyException;
use h4kuna\Exchange\RatingList\RatingList;
use Tester\Assert;
use Tester\TestCase;

final class RatingListTest extends TestCase
{
	public function testBasic(): void
	{
		$ratingList = new RatingList(new \DateTimeImmutable(), null, null, [
			'CZK' => new Property(1, 1, 'CZK'),
			'EUR' => new Property(1, 26, 'EUR'),
			'USD' => new Property(10, 130, 'USD'),
		]);

		Assert::same(26.0, $ratingList['EUR']->rate);

		Assert::exception(fn () => $ratingList->getSafe(''), UnknownCurrencyException::class, '[empty string]');
		Assert::exception(fn () => $ratingList->getSafe('AAA'), UnknownCurrencyException::class, 'AAA');
	}
}

(new RatingListTest())->run();
