<?php

declare(strict_types=1);

namespace h4kuna\Exchange\Tests\E2E;

require_once __DIR__ . '/../../bootstrap.php';

use DateTimeImmutable;
use DateTimeInterface;
use DateTimeZone;
use GuzzleHttp\Client;
use GuzzleHttp\Psr7\HttpFactory;
use h4kuna\Exchange\Currency\Property;
use h4kuna\Exchange\Download\SourceDownload;
use h4kuna\Exchange\Driver;
use h4kuna\Exchange\Exceptions\InvalidStateException;
use h4kuna\Exchange\Utils;
use Tester\Assert;
use Tester\TestCase;

/**
 * @testCase
 */
final class SourceDownloadTest extends TestCase
{
	public function testRbToday(): void
	{
		$source = self::createSourceDownload();

		$rateList = $source->execute(new Driver\RB\DayCenter(), null);

		$actual = new DateTimeImmutable('today 00:30', new DateTimeZone('Europe/Prague'));
		Assert::same(self::format($actual), self::format($rateList->getExpire()));
		Assert::null($rateList->getRequest());
		Assert::same(['EUR', 'USD', 'CZK'], array_keys((array) $rateList->getIterator()));
	}


	public function testRbPast(): void
	{
		$source = self::createSourceDownload();
		$date = self::pastDate();

		// center
		$rateList = $source->execute(new Driver\RB\DayCenter(), $date);

		$properties = [
			'CZK' => new Property(
				foreign: 1,
				home: 1.0,
				code: 'CZK',
			),
			'EUR' => new Property(
				foreign: 1,
				home: 24.82309913635254,
				code: 'EUR',
			),
			'USD' => new Property(
				foreign: 1,
				home: 22.939350128173828,
				code: 'USD',
			),
		];

		Assert::null($rateList->getExpire());
		Assert::same(self::format($date), self::format($rateList->getRequest()));
		Assert::same(self::format($date->modify('-1 day')), self::format($rateList->getDate()));
		Assert::equal($properties, (array) $rateList->getIterator());

		// sell
		$rateList = $source->execute(new Driver\RB\DaySell(), $date);
		$properties = [
			'CZK' => new Property(
				foreign: 1,
				home: 1.0,
				code: 'CZK',
			),
			'EUR' => new Property(
				foreign: 1,
				home: 25.68942642211914,
				code: 'EUR',
			),
			'USD' => new Property(
				foreign: 1,
				home: 23.739933013916016,
				code: 'USD',
			),
		];

		Assert::null($rateList->getExpire());
		Assert::same(self::format($date), self::format($rateList->getRequest()));
		Assert::same(self::format($date->modify('-1 day')), self::format($rateList->getDate()));
		Assert::equal($properties, (array) $rateList->getIterator());

		// buy
		$rateList = $source->execute(new Driver\RB\DayBuy(), $date);
		$properties = [
			'CZK' => new Property(
				foreign: 1,
				home: 1.0,
				code: 'CZK',
			),
			'EUR' => new Property(
				foreign: 1,
				home: 23.95677375793457,
				code: 'EUR',
			),
			'USD' => new Property(
				foreign: 1,
				home: 22.13876724243164,
				code: 'USD',
			),
		];

		Assert::null($rateList->getExpire());
		Assert::same(self::format($date), self::format($rateList->getRequest()));
		Assert::same(self::format($date->modify('-1 day')), self::format($rateList->getDate()));
		Assert::equal($properties, (array) $rateList->getIterator());
	}


	public function testCnbToday(): void
	{
		$source = self::createSourceDownload([]);

		$rateList = $source->execute(new Driver\Cnb\Day(), null);

		$actual = new DateTimeImmutable('today 15:00', new DateTimeZone('Europe/Prague'));
		Assert::same(self::format($actual), self::format($rateList->getExpire()));
		Assert::null($rateList->getRequest());
		Assert::same([
			'CZK',
			'AUD',
			'BRL',
			'BGN',
			'CNY',
			'DKK',
			'EUR',
			'PHP',
			'HKD',
			'INR',
			'IDR',
			'ISK',
			'ILS',
			'JPY',
			'ZAR',
			'CAD',
			'KRW',
			'HUF',
			'MYR',
			'MXN',
			'XDR',
			'NOK',
			'NZD',
			'PLN',
			'RON',
			'SGD',
			'SEK',
			'CHF',
			'THB',
			'TRY',
			'USD',
			'GBP',
		], array_keys((array) $rateList->getIterator()));
	}


	public function testCnbPast(): void
	{
		$source = self::createSourceDownload();
		$request = self::pastDate();

		$rateList = $source->execute(new Driver\Cnb\Day(), $request);

		$properties = [
			'CZK' => new Driver\Cnb\Property(
				foreign: 1,
				home: 1.0,
				code: 'CZK',
				country: 'Česká Republika',
				name: 'koruna',
			),
			'EUR' => new Driver\Cnb\Property(
				foreign: 1,
				home: 24.875,
				code: 'EUR',
				country: 'EMU',
				name: 'euro',
			),
			'USD' => new Driver\Cnb\Property(
				foreign: 1,
				home: 22.853,
				code: 'USD',
				country: 'USA',
				name: 'dolar',
			),
		];

		Assert::null($rateList->getExpire());
		Assert::same(self::format($request), self::format($rateList->getRequest()));
		Assert::same(self::format($request->modify('-1 day')), self::format($rateList->getDate()));
		Assert::equal($properties, (array) $rateList->getIterator());
	}


	public function testEcbToday(): void
	{
		$source = self::createSourceDownload();

		$rateList = $source->execute(new Driver\Ecb\Day(), null);

		$actual = new DateTimeImmutable('today 00:30', new DateTimeZone('Europe/Berlin'));
		Assert::same(self::format($actual), self::format($rateList->getExpire()));
		Assert::null($rateList->getRequest());
		Assert::same(['USD', 'CZK', 'EUR'], array_keys((array) $rateList->getIterator()));
	}


	public function testEcbPast(): void
	{
		Assert::exception(function () {
			$source = self::createSourceDownload();

			$source->execute(new Driver\Ecb\Day(), self::pastDate());
		}, InvalidStateException::class, 'Ecb does not support history.');
	}


	private static function format(?DateTimeInterface $dateTime): string
	{
		return $dateTime === null ? '' : $dateTime->format(DateTimeInterface::RFC3339);
	}


	private static function pastDate(): DateTimeImmutable
	{
		return new DateTimeImmutable('2024-02-03', new DateTimeZone('Europe/Prague'));
	}


	/**
	 * @param array<string>|null $allowedCurrencies
	 */
	private static function createSourceDownload(?array $allowedCurrencies = null): SourceDownload
	{
		return new SourceDownload(new Client(), new HttpFactory(), Utils::transformCurrencies($allowedCurrencies ?? [
			'CZK',
			'EUR',
			'USD',
		]));
	}
}

(new SourceDownloadTest())->run();
