<?php declare(strict_types=1);

namespace h4kuna\Exchange\Tests;

use DateTime;
use DateTimeZone;
use Tester\Assert;
use Tester\TestCase;

require __DIR__ . '/../bootstrap.php';

/**
 * @testCase
 */
final class TimestampTimeZoneTest extends TestCase
{
	public function testDefault(): void
	{
		$adak = new DateTimeZone('America/Adak');
		$prague = new DateTimeZone('Europe/Prague');
		$date = new DateTime('1986-12-30 5:30:57', $adak);

		$newDate = new DateTime('now', $prague);
		$newDate->setTimestamp($date->getTimestamp());

		Assert::same('1986-12-30 05:30:57', $date->format('Y-m-d H:i:s'));
		Assert::same('1986-12-30 16:30:57', $newDate->format('Y-m-d H:i:s'));

		Assert::notSame((new DateTime('midnight', $prague))->getTimestamp(), (new DateTime('midnight', $adak))->getTimestamp());
	}
}

(new TimestampTimeZoneTest)->run();
