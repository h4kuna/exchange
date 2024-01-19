<?php

declare(strict_types=1);

namespace h4kuna\Exchange\Tests;

use Closure;
use DateTime;
use h4kuna\Exchange\Utils;
use Tester\Assert;
use Tester\TestCase;

require __DIR__ . '/../bootstrap.php';

final class UtilsTest extends TestCase
{
	/**
	 * @return array<string|int, array{0: Closure(static):void}>
	 */
	public function data(): array
	{
		return [
			[
				function (self $self) {
					$self->assert(
						901,
						new DateTime('+901 seconds'),
					);
				},
			],
			[
				function (self $self) {
					$self->assert(
						87300,
						new DateTime('+900 seconds'),
					);
				},
			],
			[
				function (self $self) {
					$self->assert(
						87300,
						new DateTime('2023-01-01 14:45:00'),
						(new DateTime('2023-01-01 14:45:00, -900 seconds'))->getTimestamp(),
					);
				},
			],
			[
				function (self $self) {
					$self->assert(
						901,
						new DateTime('2023-01-01 14:45:00'),
						(new DateTime('2023-01-01 14:45:00, -901 seconds'))->getTimestamp(),
					);
				},
			],
		];
	}


	/**
	 * @param Closure(static):void $assert
	 * @dataProvider data
	 */
	public function testCountTTL(Closure $assert): void
	{
		$assert($this);
	}


	public function assert(
		int $expectedTime,
		DateTime $from,
		int $time = 0
	): void
	{
		if ($time === 0) {
			Assert::same($expectedTime, Utils::countTTL($from, 900));
		} else {
			Assert::same($expectedTime, Utils::countTTL($from, 900, $time));
		}
	}
}

(new UtilsTest())->run();
