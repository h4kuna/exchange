<?php declare(strict_types=1);

namespace h4kuna\Exchange\Driver\Cnb;

use h4kuna\Exchange\Currency;

final class Property extends Currency\Property
{

	public function __construct(
		int $foreign,
		float $home,
		string $code,
		public string $country,
		public string $name,
	)
	{
		parent::__construct($foreign, $home, $code);
	}

}
