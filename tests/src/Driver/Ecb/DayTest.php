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
		$exchangeFactory = createExchangeFactory('ecb');

		$exchange = $exchangeFactory->create();
		$ratingList = $exchange->getIterator();
		Assert::same('2022-12-21', $ratingList->getDate()->format('Y-m-d'));
	}


	public function testDownloadHistory(): void
	{
		$exchangeFactory = createExchangeFactory();
		$exchangeFactory->setDriver(new Exchange\Driver\Ecb\Day($exchangeFactory->getClient(), $exchangeFactory->getRequestFactory()));

		Assert::exception(fn (
		) => $exchangeFactory->create(new \DateTime('2022-12-15')), Exchange\Exceptions\InvalidStateException::class, 'Ecb does not support history.');;
	}

}

(new DayTest())->run();
