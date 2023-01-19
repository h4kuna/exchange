<?php declare(strict_types=1);

namespace h4kuna\Exchange\Driver\Cnb;

use h4kuna\Exchange;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

/**
 * @extends Exchange\Driver\Driver<Property>
 */
class Day extends Exchange\Driver\Driver
{
	// private const URL_DAY_OTHER = 'http://www.cnb.cz/cs/financni_trhy/devizovy_trh/kurzy_ostatnich_men/kurzy.txt';

	protected string $refresh = 'today 15:30';

	protected string $timeZone = 'Europe/Prague';


	protected function createList(ResponseInterface $response): iterable
	{
		$data = $response->getBody()->getContents();
		$list = explode("\n", Exchange\Utils::stroke2point($data));
		$list[1] = 'Česká Republika|koruna|1|CZK|1';

		$this->setDate('!d.m.Y', explode(' ', $list[0])[0]);
		unset($list[0]);

		return $list;
	}


	/**
	 * @return Property
	 */
	protected function createProperty($row): Property
	{
		assert(is_string($row));
		$currency = explode('|', $row);

		return new Property(
			intval($currency[2]),
			floatval($currency[4]),
			$currency[3],
			$currency[0],
			$currency[1],
		);
	}


	protected function prepareUrl(?\DateTimeInterface $date): string
	{
		$url = 'https://www.cnb.cz/cs/financni_trhy/devizovy_trh/kurzy_devizoveho_trhu/denni_kurz.txt';

		if ($date === null) {
			return $url;
		}

		return "$url?date=" . urlencode($date->format('d.m.Y'));
	}

}
