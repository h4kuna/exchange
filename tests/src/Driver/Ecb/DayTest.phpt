<?php

namespace h4kuna\Exchange\Driver\Ecb;

use Tester\Assert;

require_once __DIR__ . '/../../../bootstrap.php';

class DayTest extends \Tester\TestCase
{

	public function testDownload()
	{
		$day = new Day();
		$list = $day->download();
		$currency = $list['EUR'];
		Assert::same('EUR', $currency->code);
		Assert::same('EUR', (string) $currency);
	}

	/**
	 * @throws \h4kuna\Exchange\DriverDoesNotSupport
	 */
	public function testDownloadHistory()
	{
		$day = new Day();
		$day->download(new \DateTime('2010-12-30'));
	}

}

(new DayTest())->run();
