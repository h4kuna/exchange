<?php declare(strict_types=1);

namespace h4kuna\Exchange;

final class Utils
{

	private function __construct()
	{
	}


	/**
	 * Stroke replace by point
	 * @param string $str
	 * @return string
	 */
	public static function stroke2point($str)
	{
		return trim(str_replace(',', '.', $str));
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

}
