<?php declare(strict_types=1);

namespace h4kuna\Exchange\Driver;

final class DriverCollection implements DriverAccessor
{

	/**
	 * @param array<class-string, \Closure(): Driver|Driver> $drivers
	 */
	public function __construct(private array $drivers)
	{
	}


	public function get(string $name): Driver
	{
		if ($this->drivers[$name] instanceof \Closure) {
			$this->drivers[$name] = ($this->drivers[$name])();
		}

		return $this->drivers[$name];
	}

}
