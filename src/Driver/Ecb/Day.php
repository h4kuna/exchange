<?php

namespace h4kuna\Exchange\Driver\Ecb;

use DateTime,
	GuzzleHttp,
	h4kuna\Exchange;

/**
 * @author Petr Poupě <pupe.dupe@gmail.com>
 */
class Day extends Exchange\Driver\ADriver
{

	private $source;

	/**
	 * Url where download rating
	 * @var const
	 */
	const URL_DAY = 'http://www.ecb.europa.eu/stats/eurofxref/eurofxref-daily.xml';


	/**
	 * Load data from remote source
	 * @param DateTime $date
	 * @return array
	 */
	protected function loadFromSource(DateTime $date = null)
	{
		$request = new GuzzleHttp\Client;
		$data = $request->request('GET', $this->createUrlDay(self::URL_DAY, $date))->getBody();

		$xml = simplexml_load_string($data);

		// including EUR
		$eur = $xml->Cube->Cube->addChild("Cube");
		$eur->addAttribute('currency', 'EUR');
		$eur->addAttribute('rate', '1');
		$this->setDate('Y-m-d', (string) $xml->Cube->Cube->attributes()['time']);
		return $xml->Cube->Cube->Cube;
	}


	/**
	 * @param string $row
	 * @return Property|NULL
	 */
	protected function createProperty($row)
	{
		return new Exchange\Currency\Property([
			'code' => $row['currency'],
			'home' => $row['rate'],
			'foreign' => 1
		]);
	}


	/**
	 * @param string $url
	 * @param DateTime $date
	 * @return string
	 * @throws Exchange\DriverDoesNotSupport
	 */
	private function createUrlDay($url, DateTime $date = null)
	{
		if ($date) {
			throw new Exchange\DriverDoesNotSupport('Driver does not support history.');
		}
		return $url;
	}

}
