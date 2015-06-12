<?php

namespace h4kuna\Exchange\Driver\Ecb;

use DateTime,
	Kdyby\Curl,
	h4kuna\Exchange;

/**
 * @author Petr Poupě <pupe.dupe@gmail.com>
 */
class Day extends Exchange\Driver\Download
{

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
	protected function loadFromSource(DateTime $date = NULL)
	{
		$request = new Curl\Request(self::URL_DAY);
		$response = $request->get();
		$data = $response->getResponse();

		$xml = simplexml_load_string($data);

		// including EUR
		$eur = $xml->Cube->Cube->addChild("Cube");
		$eur->addAttribute('currency', 'EUR');
		$eur->addAttribute('rate', '1');
		return $xml->Cube->Cube->Cube;
	}

	/**
	 * @param string $row
	 * @return Property|NULL
	 */
	protected function createProperty($row)
	{
		return new Exchange\Currency\Property(1, $row['currency'], $row['rate']);
	}

	protected function createUrlDay($url, DateTime $date)
	{
		throw new Exchange\ExchangeException('This driver does not support history.');
	}

}
