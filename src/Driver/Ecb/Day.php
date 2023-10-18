<?php declare(strict_types=1);

namespace h4kuna\Exchange\Driver\Ecb;

use h4kuna\Exchange;
use Psr\Http\Message\ResponseInterface;

/**
 * @extends Exchange\Driver\Driver<Exchange\Currency\Property>
 */
class Day extends Exchange\Driver\Driver
{
	protected string $timeZone = 'Europe/Berlin';

	public static string $url = 'http://www.ecb.europa.eu/stats/eurofxref/eurofxref-daily.xml';


	/**
	 * @return iterable<\SimpleXMLElement>
	 */
	protected function createList(ResponseInterface $response): iterable
	{
		$data = $response->getBody()->getContents();

		$xml = simplexml_load_string($data);

		if ($xml === false) {
			throw new Exchange\Exceptions\InvalidStateException('Invalid source xml.');
		}

		// including EUR
		$eur = $xml->Cube->Cube->addChild("Cube");
		$eur->addAttribute('currency', 'EUR');
		$eur->addAttribute('rate', '1');
		assert(isset($xml->Cube->Cube) && $xml->Cube->Cube->attributes() !== null);
		$this->setDate('!Y-m-d', (string) $xml->Cube->Cube->attributes()['time']);

		return $xml->Cube->Cube->Cube;
	}


	protected function createProperty($row): Exchange\Currency\Property
	{
		assert($row instanceof \SimpleXMLElement);

		return new Exchange\Currency\Property(
			1,
			floatval(strval($row->xpath('@rate')[0])),
			(string) $row->xpath('@currency')[0],
		);
	}


	protected function prepareUrl(?\DateTimeInterface $date): string
	{
		if ($date !== null) {
			throw new Exchange\Exceptions\InvalidStateException('Ecb does not support history.');
		}

		return self::$url;
	}

}
