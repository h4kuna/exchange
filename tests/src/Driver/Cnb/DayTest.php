<?php declare(strict_types=1);

namespace h4kuna\Exchange\Driver\Cnb;

use Tester\Assert;

require_once __DIR__ . '/../../../bootstrap.php';

/**
 * @testCase
 */
class DayTest extends \Tester\TestCase
{

	public function testDownload()
	{
		$day = new Day();
		$list = $day->download();
		$currency = $list['EUR'];
		Assert::same('EUR', $currency->code);
	}


	public function testDownloadHistory()
	{
		$day = new Day();
		$allowed = ['CZK', 'EUR', 'USD'];
		$list = $day->download(new \DateTime('2017-08-10'), $allowed);
		Assert::same('2017-08-10', $list->getDate()->format('Y-m-d'));
		$currency = $list['EUR'];
		Assert::same(1, $currency->foreign);
		Assert::same(26.16, $currency->home);
		Assert::same(26.16, $currency->rate);
		Assert::equal($allowed, array_keys($list->getCurrencies()));
	}

}

(new DayTest())->run();
