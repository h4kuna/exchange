<?php declare(strict_types=1);

namespace h4kuna\Exchange\Driver\Ecb;

use GuzzleHttp;
use h4kuna\Exchange;
use h4kuna\Exchange\Exceptions\DriverDoesNotSupport;

/**
 * @author Petr Poupě <pupe.dupe@gmail.com>
 */
class Day extends Exchange\Driver\Driver
{

	/**
	 * Url where download rating
	 * @var string
	 */
	private const URL_DAY = 'http://www.ecb.europa.eu/stats/eurofxref/eurofxref-daily.xml';


	/**
	 * Load data from remote source
	 * @param \DateTimeInterface $date
	 * @return array
	 */
	protected function loadFromSource(?\DateTimeInterface $date): iterable
	{
		$data = $this->downloadContent($date);

		$xml = simplexml_load_string($data);

		if ($xml === false) {
			throw new Exchange\Exceptions\InvalidState('Invalid source xml.');
		}

		// including EUR
		$eur = $xml->Cube->Cube->addChild("Cube");
		$eur->addAttribute('currency', 'EUR');
		$eur->addAttribute('rate', '1');
		$this->setDate('Y-m-d', (string) $xml->Cube->Cube->attributes()['time']);
		return $xml->Cube->Cube->Cube;
	}


	protected function createProperty($row): Exchange\Currency\Property
	{
		return new Exchange\Currency\Property([
			'code' => $row['currency'],
			'home' => $row['rate'],
			'foreign' => 1
		]);
	}


	private function createUrlDay(string $url, ?\DateTimeInterface $date): string
	{
		if ($date) {
			throw new DriverDoesNotSupport('Driver does not support history.');
		}
		return $url;
	}


	protected function downloadContent(?\DateTimeInterface $date): string
	{
		$request = new GuzzleHttp\Client;
		$data = $request->request('GET', $this->createUrlDay(self::URL_DAY, $date))->getBody()->getContents();
		return $data;
	}

}
