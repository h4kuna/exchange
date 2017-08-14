<?php

namespace h4kuna\Exchange;

use h4kuna\Number;

class Filters
{

	/** @var Exchange */
	private $exchange;

	/** @var Currency\Formats */
	private $formats;

	/** @var Number\Tax */
	private $vat;

	public function __construct(Exchange $exchange, Currency\Formats $formats)
	{
		$this->exchange = $exchange;
		$this->formats = $formats;
	}

	/**
	 * @param Number\Tax $vat
	 */
	public function setVat(Number\Tax $vat)
	{
		$this->vat = $vat;
	}

	public function change($number, $from = NULL, $to = NULL)
	{
		return $this->exchange->change($number, $from, $to);
	}

	public function changeTo($number, $to = NULL)
	{
		return $this->change($number, NULL, $to);
	}

	/**
	 * Count and format number.
	 * @param number $number
	 * @param string|NULL
	 * @param string $to output currency, NULL set actual
	 * @return string
	 */
	public function format($number, $from = NULL, $to = NULL)
	{
		$data = $this->exchange->transfer($number, $from, $to);
		return $this->formats->getFormat($data[1]->code)->format($data[0], $data[1]->code);
	}

	/**
	 * @param float $number
	 * @param string $to
	 * @return string
	 */
	public function formatTo($number, $to)
	{
		return $this->format($number, NULL, $to);
	}

	/**
	 * @param float|int $number
	 * @return float
	 */
	public function vat($number)
	{
		return $this->vat->add($number);
	}

	/**
	 * @param float|int $number
	 * @param string|NULL $from
	 * @param string|NULL $to
	 * @return string
	 */
	public function formatVat($number, $from = NULL, $to = NULL)
	{
		return $this->format($this->vat($number), $from, $to);
	}

	/**
	 * @param float|int $number
	 * @param string|NULL $to
	 * @return string
	 */
	public function formatVatTo($number, $to)
	{
		return $this->formatVat($number, NULL, $to);
	}
}