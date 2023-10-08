<?php declare(strict_types=1);

namespace h4kuna\Exchange\Driver;

use h4kuna\DataType\Collection\LazyBuilder;

/**
 * @extends LazyBuilder<Driver>
 */
final class DriverBuilder extends LazyBuilder implements DriverAccessor
{

	public function get(string|int $key): Driver
	{
		return parent::get($key);
	}

}
