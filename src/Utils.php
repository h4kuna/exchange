<?php

namespace h4kuna\Exchange;

/**
 * Static helpers
 * @author Milan Matějček
 */
final class Utils
{

	/**
	 * Czech currency code
	 */
	const CZK = 'CZK';

	private function __construct() {}

	/**
	 * Stroke replace by point
	 *
	 * @param string $str
	 * @return string
	 */
	public static function stroke2point($str)
	{
		return trim(str_replace(',', '.', $str));
	}

}
