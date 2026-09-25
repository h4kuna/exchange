<?php declare(strict_types = 1);

namespace h4kuna\Exchange\Tests\E2E;

require_once __DIR__ . '/../../bootstrap.php';

use DateTimeImmutable;
use DateTimeInterface;
use DateTimeZone;
use GuzzleHttp\Client;
use GuzzleHttp\Psr7\HttpFactory;
use h4kuna\Exchange\Currency\Property;
use h4kuna\Exchange\Download\SourceDownload;
use h4kuna\Exchange\Driver\Cnb\Day as CnbDay;
use h4kuna\Exchange\Driver\Cnb\Property as CnbProperty;
use h4kuna\Exchange\Driver\Ecb\Day as EcbDay;
use h4kuna\Exchange\Driver\RB\DayBuy;
use h4kuna\Exchange\Driver\RB\DayCenter;
use h4kuna\Exchange\Driver\RB\DaySell;
use h4kuna\Exchange\Exceptions\InvalidStateException;
use h4kuna\Exchange\Utils;
use Tester\Assert;
use Tester\TestCase;
use function array_keys;
use function assert;

/**
 * @testCase
 */
final class SourceDownloadTest extends TestCase
{

	public function testRbToday(): void
	{
		$source = self::createSourceDownload();

		$rateList = $source->execute(new DayCenter(), null);

		$actual = new DateTimeImmutable('today 00:30', new DateTimeZone('Europe/Prague'));
		Assert::same(self::format($actual), self::format($rateList->getExpire()));
		Assert::null($rateList->getRequest());
		Assert::same(['EUR', 'USD', 'CZK'], array_keys((array) $rateList->getIterator()));
	}

	public function testRbPast(): void
	{
		$source = self::createSourceDownload();
		$date = self::rbPastDate();

		$center = $source->execute(new DayCenter(), $date);
		$sell = $source->execute(new DaySell(), $date);
		$buy = $source->execute(new DayBuy(), $date);

		foreach ([$center, $sell, $buy] as $rateList) {
			Assert::null($rateList->getExpire());
			Assert::same(self::format($date), self::format($rateList->getRequest()));
			// saturday, the rate list is from friday or earlier when friday is a holiday
			Assert::true($rateList->getDate() < $date);
			Assert::true($rateList->getDate() >= $date->modify('-4 days'));
			Assert::same(['EUR', 'USD', 'CZK'], array_keys((array) $rateList->getIterator()));
		}

		$centerRates = (array) $center->getIterator();
		$sellRates = (array) $sell->getIterator();
		$buyRates = (array) $buy->getIterator();
		foreach (['EUR', 'USD'] as $code) {
			$centerRate = $centerRates[$code];
			$sellRate = $sellRates[$code];
			$buyRate = $buyRates[$code];
			assert($centerRate instanceof Property && $sellRate instanceof Property && $buyRate instanceof Property);
			Assert::same(1, $centerRate->foreign);
			Assert::same($code, $centerRate->code);
			Assert::true($buyRate->home < $centerRate->home);
			Assert::true($centerRate->home < $sellRate->home);
		}

		Assert::equal(new Property(foreign: 1, home: 1.0, code: 'CZK'), $centerRates['CZK']);
	}

	public function testCnbToday(): void
	{
		$source = self::createSourceDownload([]);

		$rateList = $source->execute(new CnbDay(), null);

		$actual = new DateTimeImmutable('today 15:00', new DateTimeZone('Europe/Prague'));
		Assert::same(self::format($actual), self::format($rateList->getExpire()));
		Assert::null($rateList->getRequest());
		Assert::same([
			'CZK',
			'AUD',
			'BRL',
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

		$rateList = $source->execute(new CnbDay(), $request);

		$properties = [
			'CZK' => new CnbProperty(
				foreign: 1,
				home: 1.0,
				code: 'CZK',
				country: 'Česká Republika',
				name: 'koruna',
			),
			'EUR' => new CnbProperty(
				foreign: 1,
				home: 24.875,
				code: 'EUR',
				country: 'EMU',
				name: 'euro',
			),
			'USD' => new CnbProperty(
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

		$rateList = $source->execute(new EcbDay(), null);

		$actual = new DateTimeImmutable('today 00:30', new DateTimeZone('Europe/Berlin'));
		Assert::same(self::format($actual), self::format($rateList->getExpire()));
		Assert::null($rateList->getRequest());
		Assert::same(['USD', 'CZK', 'EUR'], array_keys((array) $rateList->getIterator()));
	}

	public function testEcbPast(): void
	{
		Assert::exception(static function (): void {
			$source = self::createSourceDownload();

			$source->execute(new EcbDay(), self::pastDate());
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
	 * RB keeps history only about two years back, use a saturday in the last month.
	 */
	private static function rbPastDate(): DateTimeImmutable
	{
		return new DateTimeImmutable('last saturday -2 weeks', new DateTimeZone('Europe/Prague'));
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
