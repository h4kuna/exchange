<?php declare(strict_types=1);

namespace h4kuna\Exchange\Currency;

use Stringable;

class Property implements Stringable
{
	public float $rate;


	public function __construct(
		public int $foreign,
		public float $home,
		public string $code,
	)
	{
		$this->rate = $this->foreign === 0 ? 0.0 : $this->home / $this->foreign;
	}


	public function __toString()
	{
		return $this->code;
	}

}
