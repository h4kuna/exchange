<?php declare(strict_types=1);

namespace h4kuna\Exchange;

final class Utils
{

	private function __construct() { }


	/**
	 * Stroke replace by point
	 * @param string $str
	 * @return string
	 */
	public static function stroke2point($str)
	{
		return trim(str_replace(',', '.', $str));
	}

}
