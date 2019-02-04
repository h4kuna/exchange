<?php declare(strict_types=1);

namespace h4kuna\Exchange\Driver\Cnb;

use DateTime;
use GuzzleHttp;
use h4kuna\Exchange;

class Day extends Exchange\Driver\Driver
{

	/**
	 * Url where download rating
	 */
	private const URL_DAY = 'http://www.cnb.cz/cs/financni_trhy/devizovy_trh/kurzy_devizoveho_trhu/denni_kurz.txt';
	// private const URL_DAY_OTHER = 'http://www.cnb.cz/cs/financni_trhy/devizovy_trh/kurzy_ostatnich_men/kurzy.txt';


	/**
	 * Load data from remote source.
	 * @param DateTime $date
	 * @return array
	 */
	protected function loadFromSource(?\DateTimeInterface $date): iterable
	{
		$data = $this->downloadContent($date);
		$list = explode("\n", Exchange\Utils::stroke2point($data));
		$list[1] = 'Česká Republika|koruna|1|CZK|1';

		$this->setDate('d.m.Y', explode(' ', $list[0])[0]);
		unset($list[0]);

		return $list;
	}


	protected function createProperty($row): Property
	{
		$currency = explode('|', $row);
		return new Property([
			'country' => $currency[0],
			'currency' => $currency[1],
			'foreign' => $currency[2],
			'code' => $currency[3],
			'home' => $currency[4],
		]);
	}


	private function createUrl($url, ?\DateTimeInterface $date): string
	{
		if ($date === null) {
			return $url;
		}
		return $url . '?date=' . urlencode($date->format('d.m.Y'));
	}


	protected function downloadContent(?\DateTimeInterface $date): string
	{
		$request = new GuzzleHttp\Client();
		$data = $request->request('GET', $this->createUrl(self::URL_DAY, $date))->getBody()->getContents();
		return $data;
	}

}
