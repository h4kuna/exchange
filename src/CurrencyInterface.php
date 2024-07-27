<?php declare(strict_types=1);

namespace h4kuna\Exchange;

interface CurrencyInterface
{
	function getRate(): float;


	/**
	 * Recommend to use ISO 4217
	 * @see https://en.wikipedia.org/wiki/ISO_4217
	 */
	function getCode(): string;
}
