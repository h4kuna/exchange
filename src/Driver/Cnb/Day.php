<?php declare(strict_types=1);

namespace h4kuna\Exchange\Driver\Cnb;

use DateTimeInterface;
use DateTimeZone;
use h4kuna\Exchange;
use h4kuna\Exchange\Download\SourceData;
use h4kuna\Exchange\Utils;
use Psr\Http\Message\ResponseInterface;

class Day implements Exchange\Driver\Source
{
	public static string $url = 'https://www.cnb.cz/cs/financni_trhy/devizovy_trh/kurzy_devizoveho_trhu/denni_kurz.txt';

	private DateTimeZone $timeZone;


	public function __construct(
		string|DateTimeZone $timeZone = 'Europe/Prague',
		private string $refresh = 'today 14:30:00',
	)
	{
		$this->timeZone = Utils::createTimeZone($timeZone);
	}


	public function getTimeZone(): DateTimeZone
	{
		return $this->timeZone;
	}


	public function makeUrl(?DateTimeInterface $date): string
	{
		$url = self::$url;

		if ($date === null) {
			return $url;
		}

		return "$url?" . http_build_query([
				'date' => $date->format('d.m.Y'),
			]);
	}


	public function createSourceData(ResponseInterface $response): SourceData
	{
		$data = $response->getBody()->getContents();
		$list = explode("\n", Utils::stroke2point($data));
		$list[1] = 'Česká Republika|koruna|1|CZK|1';

		$date = Utils::createFromFormat('!d.m.Y', explode(' ', $list[0])[0], $this->timeZone);
		unset($list[0]);

		return new SourceData($date, $this->refresh, $list);
	}


	public function createProperty(mixed $row): Property
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

}
