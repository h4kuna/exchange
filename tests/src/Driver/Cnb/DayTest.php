<?php declare(strict_types=1);

namespace h4kuna\Exchange\Tests\Driver\Cnb;

use Tester\Assert;
use Tester\TestCase;

require_once __DIR__ . '/../../../bootstrap.php';

/**
 * @testCase
 */
final class DayTest extends TestCase
{

	public function testDownloadHistory(): void
	{
		$exchange = createExchangeFactory()->create(new \DateTime('2022-12-01'));
		Assert::same(24.0, $exchange['EUR']->rate);
		Assert::same('2022-12-01', $exchange->getDate()->format('Y-m-d'));
	}

}

(new DayTest())->run();
