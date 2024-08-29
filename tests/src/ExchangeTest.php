<?php

declare(strict_types=1);

namespace h4kuna\Exchange\Tests;

require_once __DIR__ . '/../bootstrap.php';

use h4kuna\CriticalCache\PSR16\CacheLocking;
use h4kuna\Exchange\Currency\Property;
use h4kuna\Exchange\Download\SourceDownloadInterface;
use h4kuna\Exchange\Exceptions\FrozenMethodException;
use h4kuna\Exchange\Exceptions\UnknownCurrencyException;
use h4kuna\Exchange\Exchange;
use h4kuna\Exchange\RatingList\CacheEntity;
use h4kuna\Exchange\RatingList\RatingList;
use h4kuna\Exchange\RatingList\RatingListCache;
use Mockery\MockInterface;
use Tester\Assert;
use Tester\TestCase;

final class ExchangeTest extends TestCase
{
	public function testGetRatingList(): void
	{
		$exchange = self::createExchange();
		Assert::same($exchange->ratingList, $exchange->getIterator());
	}


	public function testChange(): void
	{
		$exchange = self::createExchange();

		Assert::same(0.0, $exchange->change(0));
		Assert::same(100.0, $exchange->change(100));
		Assert::same(50.0, $exchange->change(100, 'USD'));
		Assert::same(200.0, $exchange->change(100, null, 'USD'));
		Assert::same(26.0, $exchange->change(1, 'EUR', 'CZK'));
		Assert::same(50.0, $exchange->change(100, 'USD', 'EUR'));

		Assert::exception(function () use ($exchange) {
			Assert::error(fn () => $exchange->change(100, 'BBB', 'EUR'), E_WARNING);
		}, \TypeError::class);

		Assert::exception(function () use ($exchange) {
			Assert::error(fn () => $exchange->change(100, 'USD', ''), E_WARNING);
		}, \TypeError::class);
	}


	public function testIteratorAggregate(): void
	{
		$exchange = self::createExchange();

		$codes = [];
		foreach ($exchange as $k => $v) {
			$codes[] = $k;
		}

		Assert::same(['CZK', 'EUR', 'USD'], $codes);
	}


	public function testArrayAccess(): void
	{
		$exchange = self::createExchange();
		Assert::same('EUR', $exchange['EUR']->getCode());
		Assert::true(isset($exchange['EUR']));
		Assert::false(isset($exchange['CCC']));

		Assert::exception(function () use ($exchange) {
			unset($exchange['EUR']);
		}, FrozenMethodException::class);

		Assert::exception(function () use ($exchange) {
			$exchange['EUR'] = 'foo'; // @phpstan-ignore-line
		}, FrozenMethodException::class);

		Assert::exception(fn () => $exchange['AAA'], UnknownCurrencyException::class);

		Assert::exception(fn () => $exchange->get('AAA'), UnknownCurrencyException::class);
	}


	private static function createExchange(): Exchange
	{
		$ratingList = new RatingList(new \DateTimeImmutable(), null, null, [
			'CZK' => new Property(1, 1, 'CZK'),
			'EUR' => new Property(1, 26, 'EUR'),
			'USD' => new Property(10, 130, 'USD'),
		]);

		$ratingList2 = new RatingList(new \DateTimeImmutable(), null, null, [
			'CZK' => new Property(1, 1, 'CZK'),
			'EUR' => new Property(1, 28, 'EUR'),
			'USD' => new Property(10, 135, 'USD'),
		]);

		/** @var CacheLocking&MockInterface $ratingListCache */
		$ratingListCache = mock(CacheLocking::class);
		$ratingListCache->makePartial();
		$ratingListCache->shouldReceive('load')
			->andReturn(null);
		$ratingListCache->shouldReceive('get')
			->andReturn($ratingList, $ratingList2);

		/** @var SourceDownloadInterface&MockInterface $sourceDownload */
		$sourceDownload = mock(SourceDownloadInterface::class);

		$ratingListCache = new RatingListCache($ratingListCache, $sourceDownload);

		return new Exchange('EUR', $ratingListCache->build(new CacheEntity()));
	}
}

(new ExchangeTest())->run();
