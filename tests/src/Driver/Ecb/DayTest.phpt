<?php

namespace h4kuna\Exchange\Driver\Ecb;

use Tester\Assert;

$container = require_once __DIR__ . '/../../../bootstrap.php';

class DayTest extends \Tester\TestCase
{

	public function testDownload()
	{
		$day = new Day();
		Assert::same('Ecb\Day', $day->getName());
		$list = $day->loadCurrencies();
		$currency = $list['EUR'];
		Assert::same('EUR', $currency->getCode());
	}

	/**
	 * @throws \h4kuna\Exchange\DriverDoesNotSupport
	 */
	public function testDownloadHistory()
	{
		$day = new Day();
		$day->loadCurrencies(new \DateTime('2010-12-30'));
	}

}

$test = new DayTest();
$test->run();
