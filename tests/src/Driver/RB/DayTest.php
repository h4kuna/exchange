<?php declare(strict_types=1);

namespace h4kuna\Exchange\Tests\Driver\RB;

use DateTime;
use h4kuna\Exchange\Currency\Property;
use h4kuna\Exchange\Driver\RB\DayBuy;
use h4kuna\Exchange\Driver\RB\DayCenter;
use h4kuna\Exchange\Driver\RB\DaySell;
use h4kuna\Exchange\Fixtures\SourceListBuilder;
use Tester\Assert;
use Tester\TestCase;

require_once __DIR__ . '/../../../bootstrap.php';

final class DayTest extends TestCase
{

	public function testDownload(): void
	{
		$list = SourceListBuilder::make(DayCenter::class);

		Assert::equal(new Property(1, 1, 'CZK'), $list['CZK']);
		Assert::same(['EUR', 'JPY', 'CZK'], array_keys($list));
	}


	public function testHistory(): void
	{
		$list = SourceListBuilder::make(DayCenter::class, new DateTime('2024-01-04'));

		$expected = [
			'EUR' => new Property(1, 24.67105, 'EUR'),
			'JPY' => new Property(100, 15.7468, 'JPY'),
			'CZK' => new Property(1, 1, 'CZK'),
		];
		Assert::equal($expected, $list);
	}


	public function testBuy(): void
	{
		$list = SourceListBuilder::make(DayBuy::class, new DateTime('2024-01-04'));

		$expected = [
			'EUR' => new Property(1, 23.3659515, 'EUR'),
			'JPY' => new Property(100, 14.9137943, 'JPY'),
			'CZK' => new Property(1, 1, 'CZK'),
		];
		Assert::equal($expected, $list);
	}


	public function testSell(): void
	{
		$list = SourceListBuilder::make(DaySell::class, new DateTime('2024-01-04'));

		$expected = [
			'EUR' => new Property(1, 25.9761485, 'EUR'),
			'JPY' => new Property(100, 16.5798057, 'JPY'),
			'CZK' => new Property(1, 1, 'CZK'),
		];
		Assert::equal($expected, $list);
	}

}

(new DayTest())->run();
