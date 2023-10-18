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
		$date = new DateTime('1986-12-30 5:30:57', new DateTimeZone('America/Adak'));

		$newDate = new DateTime('now', new DateTimeZone('Europe/Prague'));
		$newDate->setTimestamp($date->getTimestamp());

		Assert::same('1986-12-30 05:30:57', $date->format('Y-m-d H:i:s'));
		Assert::same('1986-12-30 16:30:57', $newDate->format('Y-m-d H:i:s'));
	}
}

(new TimestampTimeZoneTest)->run();
