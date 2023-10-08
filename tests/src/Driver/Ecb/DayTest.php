<?php declare(strict_types=1);

namespace h4kuna\Exchange\Tests\Driver\Ecb;

use Tester\Assert;
use h4kuna\Exchange;
use Tester\TestCase;

require_once __DIR__ . '/../../../bootstrap.php';

/**
 * @testCase
 */
final class DayTest extends TestCase
{

	public function testDownload(): void
	{
		$exchangeFactory = createExchangeFactory(Exchange\Driver\Ecb\Day::class);
		$exchange = $exchangeFactory->create();
		Assert::same('2022-12-21', $exchange->getDate()->format('Y-m-d'));
	}


	public function testDownloadHistory(): void
	{
		$exchangeFactory = createExchangeFactory(Exchange\Driver\Ecb\Day::class);

		Assert::exception(fn (
		) => $exchangeFactory->create(new \DateTime('2022-12-15')), Exchange\Exceptions\InvalidStateException::class, 'Ecb does not support history.');;
	}

}

(new DayTest())->run();
