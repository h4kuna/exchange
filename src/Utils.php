<?php declare(strict_types=1);

namespace h4kuna\Exchange;

use DateTime;
use h4kuna\DataType\Basic\Strings;
use Nette\StaticClass;

final class Utils
{

	use StaticClass;

	/**
	 * Stroke replace by point
	 */
	public static function stroke2point(string $str): string
	{
		return trim(Strings::strokeToPoint($str));
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
	public static function countTTL(DateTime $dateTime, int $beforeExpiration = 900): int
	{
		$time = time();
		if (($dateTime->getTimestamp() - $beforeExpiration) <= $time) {
			$dateTime->modify('+1 day');
		}

		return $dateTime->getTimestamp() - time();
	}

}
