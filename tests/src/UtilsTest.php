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
		DateTime $from
	): void
	{
		Assert::same($expectedTime, Utils::countTTL($from));
	}
}

(new UtilsTest())->run();
