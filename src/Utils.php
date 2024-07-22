<?php declare(strict_types=1);

namespace h4kuna\Exchange;

use DateTime;
use DateTimeImmutable;
use DateTimeInterface;
use DateTimeZone;
use h4kuna\Exchange\Exceptions\InvalidStateException;
use h4kuna\Exchange\Exceptions\XmlResponseFailedException;
use Nette\StaticClass;
use Nette\Utils\DateTime as NetteDateTime;
use Psr\Http\Message\ResponseInterface;
use SimpleXMLElement;

final class Utils
{

	use StaticClass;

	public const DateFormat = 'Y-m-d';

	public const CacheMinutes = NetteDateTime::MINUTE * 30;


	/**
	 * Stroke replace by point
	 */
	public static function stroke2point(string $str): string
	{
		return trim(strtr($str, [',' => '.']));
	}


	public static function createSimpleXMLElement(ResponseInterface $response): SimpleXMLElement
	{
		$xml = @simplexml_load_string($response->getBody()->getContents());

		if ($xml === false) {
			throw new XmlResponseFailedException((string) $response->getStatusCode());
		}

		return $xml;
	}


	public static function createTimeZone(string|DateTimeZone $timeZone): DateTimeZone
	{
		return is_string($timeZone) ? new DateTimeZone($timeZone) : $timeZone;
	}


	public static function toImmutable(?DateTimeInterface $date, DateTimeZone $timeZone): ?DateTimeImmutable
	{
		if ($date === null) {
			return null;
		}

		if (self::isSameTimeOffsetTimeZone($date, $timeZone) === false || ($date instanceof DateTimeImmutable) === false) {
			$date = (new DateTimeImmutable('now', $timeZone))
				->setTimestamp($date->getTimestamp());
		}

		if (self::isTodayAndFuture($date, $timeZone)) {
			return null;
		}

		return $date;
	}


	private static function isSameTimeOffsetTimeZone(DateTimeInterface $date, DateTimeZone $timeZone): bool
	{
		$now = new DateTimeImmutable();
		return $date->getTimezone()->getOffset($now) === $timeZone->getOffset($now);
	}


	public static function isTodayAndFuture(DateTimeInterface $date, DateTimeZone $timeZone): bool
	{
		return $date->format(self::DateFormat) >= (self::now($timeZone))->format(self::DateFormat);
	}


	public static function now(DateTimeZone $timeZone): DateTimeImmutable
	{
		return new DateTimeImmutable('now', $timeZone);
	}


	public static function createFromFormat(string $format, string $value, DateTimeZone $timezone): DateTimeImmutable
	{
		$date = DateTimeImmutable::createFromFormat($format, $value, $timezone);
		if ($date === false) {
			throw new InvalidStateException(sprintf('Can not create DateTime object from source "%s" with format "%s".', $value, $format));
		}

		return $date;
	}


	/**
	 * ['czk', 'eur'] => ['CZK' => 0, 'EUR' => 1]
	 *
	 * @param array<string> $currencies
	 * @return array<string, int>
	 */
	public static function transformCurrencies(array $currencies): array
	{
		return array_flip(array_map(fn (string $v) => strtoupper($v), $currencies));
	}


	/**
	 * @param int $beforeExpiration // 900 seconds -> 15 minutes
	 */
	public static function countTTL(DateTime $dateTime, int $beforeExpiration = 900, int $time = null): int
	{
		$time ??= time();
		if (($dateTime->getTimestamp() - $beforeExpiration) <= $time) {
			$dateTime->modify('+1 day');
		}

		return $dateTime->getTimestamp() - $time;
	}

}
