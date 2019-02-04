<?php declare(strict_types=1);

namespace h4kuna\Exchange\Test;

class Driver extends \h4kuna\Exchange\Driver\Driver
{

	protected function loadFromSource(?\DateTimeInterface $date): iterable
	{
		$myDate = $date;
		if ($myDate === null) {
			$myDate = new \DateTime;
		}
		$this->setDate('Y-m-d', $myDate->format('Y-m-d'));

		return [
			[
				'home' => $date ? 26 : 25,
				'foreign' => 1,
				'code' => 'EUR'
			],
			[
				'home' => $date ? 19 : 20,
				'foreign' => 1,
				'code' => 'USD'
			],
			[
				'home' => 1,
				'foreign' => 1,
				'code' => 'CZK'
			],
		];
	}


	protected function createProperty($row)
	{
		return new \h4kuna\Exchange\Currency\Property($row);
	}

}
