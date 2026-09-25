<?php declare(strict_types = 1);

namespace h4kuna\Exchange\Tests;

use Closure;
use DateTime;
use DateTimeImmutable;
use DateTimeInterface;
use DateTimeZone;
use GuzzleHttp\Psr7\Response;
use h4kuna\Exchange\Exceptions\XmlResponseFailedException;
use h4kuna\Exchange\Utils;
use Tester\Assert;
use Tester\TestCase;
use function file_get_contents;

require __DIR__ . '/../bootstrap.php';

final class UtilsTest extends TestCase
{

	/**
	 * @return array<string|int, array{0: Closure(static):void}>
	 */
	public function dataCountTTL(): array
	{
		return [
			[
				static function (self $self): void {
					$self->assertCountTTL(
						901,
						new DateTime('+901 seconds'),
					);
				},
			],
			[
				static function (self $self): void {
					$self->assertCountTTL(
						87_300,
						new DateTime('+900 seconds'),
					);
				},
			],
			[
				static function (self $self): void {
					$self->assertCountTTL(
						87_300,
						new DateTime('2023-01-01 14:45:00'),
						(new DateTime('2023-01-01 14:45:00, -900 seconds'))->getTimestamp(),
					);
				},
			],
			[
				static function (self $self): void {
					$self->assertCountTTL(
						901,
						new DateTime('2023-01-01 14:45:00'),
						(new DateTime('2023-01-01 14:45:00, -901 seconds'))->getTimestamp(),
					);
				},
			],
		];
	}

	/**
	 * @param Closure(static):void $assert
	 *
	 * @dataProvider dataCountTTL
	 */
	public function testCountTTL(Closure $assert): void
	{
		$assert($this);
	}

	public function assertCountTTL(
		int $expectedTime,
		DateTime $from,
		int $time = 0,
	): void
	{
		if ($time === 0) {
			Assert::same($expectedTime, Utils::countTTL($from, 900));
		} else {
			Assert::same($expectedTime, Utils::countTTL($from, 900, $time));
		}
	}

	/**
	 * @return array<string|int, array{0: Closure(static):void}>
	 */
	public function dataToImmutable(): array
	{
		return [
			[
				static function (self $self): void {
					$self->assertToImmutable(
						null,
						null,
					);
				},
			],
			[
				static function (self $self): void {
					$self->assertToImmutable(
						null,
						new DateTime(),
					);
				},
			],
			[
				static function (self $self): void {
					$self->assertToImmutable(
						null,
						new DateTimeImmutable(),
					);
				},
			],
			[
				static function (self $self): void {
					$self->assertToImmutable(
						null,
						new DateTimeImmutable('now', new DateTimeZone('America/Adak')),
					);
				},
			],
			[
				static function (self $self): void {
					$self->assertToImmutable(
						'1986-12-30T15:16:17+01:00',
						new DateTimeImmutable('1986-12-30 15:16:17'),
					);
				},
			],
			[
				static function (self $self): void {
					$self->assertToImmutable(
						'1986-12-30T15:16:17+01:00',
						new DateTime('1986-12-30 15:16:17', new DateTimeZone('Europe/Berlin')),
					);
				},
			],
			[
				static function (self $self): void {
					$self->assertToImmutable(
						'1986-12-30T15:16:17+01:00',
						new DateTimeImmutable('1986-12-30 15:16:17', new DateTimeZone('Europe/Berlin')),
					);
				},
			],
			[
				static function (self $self): void {
					$self->assertToImmutable(
						'1986-12-31T02:16:17+01:00',
						new DateTime('1986-12-30 15:16:17', new DateTimeZone('America/Adak')),
					);
				},
			],
		];
	}

	/**
	 * @param Closure(static):void $assert
	 *
	 * @dataProvider dataToImmutable
	 */
	public function testToImmutable(Closure $assert): void
	{
		$assert($this);
	}

	public function assertToImmutable(
		?string $expected,
		?DateTimeInterface $date,
	): void
	{
		Assert::same($expected, Utils::toImmutable($date, new DateTimeZone('Europe/Prague'))?->format(DateTimeInterface::RFC3339));
	}

	public function testCreateSimpleXMLElement(): void
	{
		$response = new Response(403, body: (string) file_get_contents(__DIR__ . '/../Fixtures/rb.failed.no.xml.html'));

		Assert::exception(static fn () => Utils::createSimpleXMLElement($response), XmlResponseFailedException::class, '403');
	}

}

(new UtilsTest())->run();
