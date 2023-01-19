<?php declare(strict_types=1);

namespace h4kuna\Exchange\Driver;

interface DriverAccessor
{

	function get(string $name): Driver;

}
