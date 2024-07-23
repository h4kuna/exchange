<?php declare(strict_types=1);

namespace h4kuna\Exchange\Driver\RB;

use DateTimeInterface;
use DateTimeZone;
use h4kuna\Exchange\Currency\Property;
use h4kuna\Exchange\Download\SourceData;
use h4kuna\Exchange\Driver\Source;
use h4kuna\Exchange\Utils;
use Psr\Http\Message\ResponseInterface;
use SimpleXMLElement;

abstract class Day implements Source
{

	public static string $url = 'https://www.rb.cz/frontend-controller/backend-data/currency/listDataXml';

	private DateTimeZone $timeZone;


	public function __construct(
		string|DateTimeZone $timeZone = 'Europe/Prague',
		private string $refresh = 'midnight',
	)
	{
		$this->timeZone = Utils::createTimeZone($timeZone);
	}


	public function makeUrl(?DateTimeInterface $date): string
	{
		$url = self::$url;

		if ($date === null) {
			return $url;
		}

		return "$url?" . http_build_query([
				'filtered' => 'true',
				'date' => $date->format('Y-m-d'),
			]);
	}


	public function getTimeZone(): DateTimeZone
	{
		return $this->timeZone;
	}


	public function createSourceData(ResponseInterface $response): SourceData
	{
		$xml = Utils::createSimpleXMLElement($response);

		// add CZK
		$czk = $xml->exchangeRateList->exchangeRates->addChild('exchangeRate');
		$czk->addChild('unitsFrom', '1');
		$czk->addChild('exchangeRateCenter', '1');
		$czk->addChild('currencyFrom', 'CZK');
		$czk->addChild('exchangeRateSellCash', '1');
		$czk->addChild('exchangeRateSellCenter', '1');
		$czk->addChild('exchangeRateSell', '1');
		$czk->addChild('exchangeRateBuyCash', '1');
		$czk->addChild('exchangeRateCenter', '1');
		$czk->addChild('exchangeRateBuy', '1');

		$date = Utils::createFromFormat(DATE_RFC3339_EXTENDED, (string) $xml->exchangeRateList->effectiveDateFrom, $this->timeZone);

		return new SourceData($date, $this->refresh, $xml->exchangeRateList->exchangeRates->exchangeRate);
	}


	public function createProperty(mixed $row): Property
	{
		assert($row instanceof SimpleXMLElement);

		return new Property(
			intval($row->unitsFrom),
			floatval((string) $this->rate($row)),
			strval($row->currencyFrom),
		);
	}


	abstract protected function rate(SimpleXMLElement $element): SimpleXMLElement;

}
