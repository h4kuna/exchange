<?php

declare(strict_types=1);

namespace h4kuna\Exchange\Tests\Driver\RB;

use DateTimeImmutable;
use GuzzleHttp\Client;
use GuzzleHttp\Psr7\HttpFactory;
use h4kuna\Exchange\Currency\Property;
use h4kuna\Exchange\Driver\RB\DayCenter;
use h4kuna\Exchange\Utils;
use Tester\Assert;
use Tester\TestCase;

require_once __DIR__ . '/../../../bootstrap.php';

final class DayTest extends TestCase
{

	public function testBasic(): void
	{
		$client = new HttpFactory();
		$day = new DayCenter(new Client(), $client);
		$sourceList = $day->initRequest(new DateTimeImmutable('2024-01-04'), Utils::transformCurrencies([
			'EUR',
			'JPY',
			'CZK',
		]));
		$list = [];
		foreach ($sourceList as $item) {
			$list[$item->code] = $item;
		}

		$expected = [
			'EUR' => new Property(1, 24.67105, 'EUR', 25.9761485, 23.3659515),
			'JPY' => new Property(100, 15.7468, 'JPY', 16.5798057, 14.9137943),
			'CZK' => new Property(1, 1, 'CZK', 1, 1),
		];
		Assert::equal($expected, $list);
	}

}

(new DayTest())->run();
