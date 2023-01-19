<?php declare(strict_types=1);

namespace h4kuna\Exchange;

use h4kuna\Exchange\Driver\Cnb\Day;

final class Configuration
{

	public function __construct(public string $from, public string $to)
	{
	}

}
