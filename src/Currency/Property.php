<?php declare(strict_types=1);

namespace h4kuna\Exchange\Currency;

use h4kuna\Exchange\CurrencyInterface;

class Property implements CurrencyInterface
{
	/** @deprecated become private, use getRate() method */
	public float $rate;

	/** @deprecated become private, use getCode() method */
	public string $code;


	public function __construct(
		public int $foreign,
		public float $home,
		string $code,
	)
	{
		$this->code = $code;
		$this->rate = $this->foreign === 0 ? 0.0 : $this->home / $this->foreign;
	}


	public function getRate(): float
	{
		return $this->rate;
	}


	public function getCode(): string
	{
		return $this->code;
	}


	public function __toString(): string
	{
		return $this->getCode();
	}

}
