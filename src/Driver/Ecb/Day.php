<?php declare(strict_types=1);

namespace h4kuna\Exchange\Driver\Ecb;

use DateTimeInterface;
use DateTimeZone;
use h4kuna\Exchange;
use h4kuna\Exchange\Download\SourceData;
use Psr\Http\Message\ResponseInterface;
use SimpleXMLElement;

class Day implements Exchange\Driver\Source
{
	public static string $url = 'https://www.ecb.europa.eu/stats/eurofxref/eurofxref-daily.xml';

	private DateTimeZone $timeZone;


	public function __construct(
		string|DateTimeZone $timeZone = 'Europe/Berlin',
		private string $refresh = 'midnight',
	)
	{
		$this->timeZone = Exchange\Utils::createTimeZone($timeZone);
	}


	public function makeUrl(?DateTimeInterface $date): string
	{
		if ($date !== null) {
			throw new Exchange\Exceptions\InvalidStateException('Ecb does not support history.');
		}

		return self::$url;
	}


	public function getTimeZone(): DateTimeZone
	{
		return $this->timeZone;
	}


	public function createSourceData(ResponseInterface $response): SourceData
	{
		$xml = Exchange\Utils::createSimpleXMLElement($response);

		// including EUR
		$eur = $xml->Cube->Cube->addChild('Cube');
		$eur->addAttribute('currency', 'EUR');
		$eur->addAttribute('rate', '1');
		assert(isset($xml->Cube->Cube) && $xml->Cube->Cube->attributes() !== null);
		$date = Exchange\Utils::createFromFormat('!Y-m-d', (string) $xml->Cube->Cube->attributes()['time'], $this->timeZone);

		return new SourceData($date, $this->refresh, $xml->Cube->Cube->Cube);
	}


	public function createProperty(mixed $row): Exchange\Currency\Property
	{
		assert($row instanceof SimpleXMLElement);

		return new Exchange\Currency\Property(
			1,
			1 / floatval(strval($row->xpath('@rate')[0])),
			(string) $row->xpath('@currency')[0],
		);
	}

}
