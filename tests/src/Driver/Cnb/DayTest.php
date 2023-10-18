<?php declare(strict_types=1);

namespace h4kuna\Exchange\Tests\Driver\Cnb;

use GuzzleHttp\Client;
use GuzzleHttp\Psr7\HttpFactory;
use h4kuna\Exchange\Driver\Cnb\Day;
use h4kuna\Exchange\Utils;
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
		$exchange = createExchangeFactory()->create(new \DateTime('2022-12-01'));
		Assert::same(24.0, $exchange['EUR']->rate);
		Assert::same('2022-12-01', $exchange->getDate()->format('Y-m-d'));
	}


	public function testRefresh(): void
	{
		$client = new HttpFactory();
		$day = new Day(new Client(), $client);

		Assert::same((new \DateTime('now', new \DateTimeZone('Europe/Prague')))->format('Y-m-d'), $day->getRefresh()->format('Y-m-d'));

		$prevTtl = 900;
		$refresh = new \DateTime('today 15:00:00', new \DateTimeZone('Europe/Prague'));
		if ($refresh->getTimestamp() < (time() - $prevTtl)) {
			$refresh->modify('+1 day');
		}

		Assert::same($refresh->getTimestamp() - time(), Utils::countTTL($day->getRefresh(), $prevTtl));
	}

}

(new DayTest())->run();
