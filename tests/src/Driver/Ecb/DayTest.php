<?php declare(strict_types=1);

namespace h4kuna\Exchange\Tests\Driver\Ecb;

use DateTime;
use h4kuna\Exchange;
use h4kuna\Exchange\Currency\Property;
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
		$list = Exchange\Fixtures\SourceListBuilder::make(Exchange\Driver\Ecb\Day::class);

		Assert::equal(new Property(1, 1, 'EUR'), $list['EUR']);
		Assert::same(['JPY', 'CZK', 'EUR'], array_keys($list));
	}


	public function testDownloadHistory(): void
	{
		Assert::exception(fn (
		) => Exchange\Fixtures\SourceListBuilder::make(Exchange\Driver\Ecb\Day::class, new DateTime('2022-12-15')), Exchange\Exceptions\InvalidStateException::class, 'Ecb does not support history.');;
	}

}

(new DayTest())->run();
