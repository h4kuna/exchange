<?php declare(strict_types=1);

namespace h4kuna\Exchange\Driver\Ecb;

use Tester\Assert;
use Tester\TestCase;

require_once __DIR__ . '/../../../bootstrap.php';

/**
 * @testCase
 */
final class DayTest extends TestCase
{

	public function testDownload(): void
	{
		$day = new Day();
		$list = $day->download();
		\assert(isset($list['EUR']));
		$currency = $list['EUR'];
		Assert::same('EUR', $currency->code);
		Assert::same('EUR', (string) $currency);
	}


	/**
	 * @throws \h4kuna\Exchange\Exceptions\DriverDoesNotSupport
	 */
	public function testDownloadHistory(): void
	{
		$day = new Day();
		$day->download(new \DateTime('2010-12-30'));
	}

}

(new DayTest())->run();
