<?php declare(strict_types=1);

namespace h4kuna\Exchange;

use h4kuna\Number;

class Filters
{

	public function __construct(
		private Exchange $exchange,
		private Currency\Formats $formats,
		private Number\Tax $vat,
	)
	{
	}


	public function change(float $number, ?string $from = null, ?string $to = null): float
	{
		return $this->exchange->change($number, $from, $to);
	}


	public function changeTo(float $number, ?string $to = null): float
	{
		return $this->change($number, null, $to);
	}


	/**
	 * Count and format number.
	 */
	public function format(float $number, ?string $from = null, ?string $to = null): string
	{
		$data = $this->exchange->transfer($number, $from, $to);

		return $this->formats->getFormat($data[1]->code)->format($data[0], $data[1]->code);
	}


	public function formatTo(float $number, ?string $to): string
	{
		return $this->format($number, null, $to);
	}


	public function vat(float $number): float
	{
		return $this->vat->add($number);
	}


	public function formatVat(float $number, ?string $from = null, ?string $to = null): string
	{
		return $this->format($this->vat($number), $from, $to);
	}


	public function formatVatTo(float $number, ?string $to): string
	{
		return $this->formatVat($number, null, $to);
	}

}
