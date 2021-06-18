<?php declare(strict_types=1);

namespace h4kuna\Exchange\Driver\Cnb;

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
	}


	public function testDownloadHistory(): void
	{
		$day = new Day();
		$allowed = ['CZK', 'EUR', 'USD'];
		$list = $day->download(new \DateTime('2017-08-10'), $allowed);
		Assert::same('2017-08-10', $list->getDate()->format('Y-m-d'));
		\assert(isset($list['EUR']));
		$currency = $list['EUR'];
		Assert::same(1, $currency->foreign);
		Assert::same(26.16, $currency->home);
		Assert::same(26.16, $currency->rate);
		Assert::equal($allowed, array_keys($list->getCurrencies()));
	}

}

(new DayTest())->run();
