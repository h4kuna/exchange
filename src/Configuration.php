<?php declare(strict_types=1);

namespace h4kuna\Exchange;

final class Configuration
{
	public function __construct(public string $from, public string $to)
	{
	}

}
