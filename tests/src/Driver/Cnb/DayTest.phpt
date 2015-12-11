<?php

namespace h4kuna\Exchange\Driver\Cnb;

use Tester\Assert;

$container = require_once __DIR__ . '/../../../bootstrap.php';

class DayTest extends \Tester\TestCase
{

	public function testDownload()
	{
		$day = new Day();
		Assert::same('Cnb\Day', $day->getName());
		$list = $day->loadCurrencies();
		$currency = $list['EUR'];
		Assert::same('EUR', $currency->getCode());
	}

	public function testDownloadHistory()
	{
		$day = new Day();
		Assert::same('Cnb\Day', $day->getName());
		$list = $day->loadCurrencies(new \DateTime('2010-12-30'));
		$currency = $list['EUR'];
		Assert::same(25.225, $currency->getHome());
	}

}

(new DayTest())->run();
