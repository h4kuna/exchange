<?php declare(strict_types=1);

namespace h4kuna\Exchange\Tests\Driver\Cnb;

use h4kuna\Exchange\Driver\Cnb\Day;
use h4kuna\Exchange\Driver\Cnb\Property;
use h4kuna\Exchange\Fixtures\SourceListBuilder;
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
		$list = SourceListBuilder::make(Day::class, new \DateTime('2024-01-03'));

		$expected = [
			'CZK' => new Property(1, 1, 'CZK', 'Česká Republika', 'koruna'),
			'EUR' => new Property(1, 24.675, 'EUR', 'EMU', 'euro'),
			'JPY' => new Property(100, 15.809, 'JPY', 'Japonsko', 'jen'),
		];
		Assert::equal($expected, $list);
	}

}

(new DayTest())->run();
