<?php declare(strict_types = 1);

namespace h4kuna\Exchange\Driver\Ecb;

use DateTimeInterface;
use DateTimeZone;
use h4kuna\Exchange\Currency\Property;
use h4kuna\Exchange\Download\SourceData;
use h4kuna\Exchange\Driver\Source;
use h4kuna\Exchange\Exceptions\InvalidStateException;
use h4kuna\Exchange\Utils;
use Psr\Http\Message\ResponseInterface;
use SimpleXMLElement;
use function assert;
use function floatval;
use function strval;

class Day implements Source
{

	public static string $url = 'https://www.ecb.europa.eu/stats/eurofxref/eurofxref-daily.xml';

	private DateTimeZone $timeZone;


	public function __construct(
		string|DateTimeZone $timeZone = 'Europe/Berlin',
		private string $refresh = 'midnight',
	)
	{
		$this->timeZone = Utils::createTimeZone($timeZone);
	}

	public function makeUrl(?DateTimeInterface $date): string
	{
		if ($date !== null) {
			throw new InvalidStateException('Ecb does not support history.');
		}

		return self::$url;
	}

	public function getTimeZone(): DateTimeZone
	{
		return $this->timeZone;
	}

	public function createSourceData(ResponseInterface $response): SourceData
	{
		$xml = Utils::createSimpleXMLElement($response);

		// including EUR
		$eur = $xml->Cube->Cube->addChild('Cube');
		$eur->addAttribute('currency', 'EUR');
		$eur->addAttribute('rate', '1');
		assert(isset($xml->Cube->Cube) && $xml->Cube->Cube->attributes() !== null);
		$date = Utils::createFromFormat('!Y-m-d', (string) $xml->Cube->Cube->attributes()['time'], $this->timeZone);

		return new SourceData($date, $this->refresh, $xml->Cube->Cube->Cube);
	}

	public function createProperty(mixed $row): Property
	{
		assert($row instanceof SimpleXMLElement);

		return new Property(
			1,
			1 / floatval(strval($row->xpath('@rate')[0])), // @phpstan-ignore offsetAccess.notFound
			(string) $row->xpath('@currency')[0], // @phpstan-ignore offsetAccess.notFound
		);
	}

}
