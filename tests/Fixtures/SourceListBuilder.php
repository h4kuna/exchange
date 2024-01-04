<?php declare(strict_types=1);

namespace h4kuna\Exchange\Fixtures;

use DateTimeInterface;
use GuzzleHttp\Client;
use GuzzleHttp\Psr7\HttpFactory;
use h4kuna\Exchange\Currency\Property;
use h4kuna\Exchange\Driver\Driver;
use h4kuna\Exchange\Utils;

final class SourceListBuilder
{
	/**
	 * @param class-string<Driver> $driver
	 * @return array<string, Property>
	 */
	public static function make(string $driver, DateTimeInterface $date = null): array
	{
		$client = new HttpFactory();
		$day = new $driver(new Client(), $client);
		$sourceList = $day->initRequest($date, Utils::transformCurrencies([
			'EUR',
			'JPY',
			'CZK',
		]));
		$list = [];
		foreach ($sourceList as $item) {
			$list[$item->code] = $item;
		}

		return $list;
	}

}
