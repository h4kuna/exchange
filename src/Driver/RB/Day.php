<?php declare(strict_types=1);

namespace h4kuna\Exchange\Driver\RB;

use h4kuna\Exchange\Currency\Property;
use h4kuna\Exchange\Driver\Driver;
use h4kuna\Exchange\Exceptions\InvalidStateException;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use SimpleXMLElement;

/**
 * @extends Driver<SimpleXMLElement, Property>
 */
abstract class Day extends Driver
{
	public static string $url = 'https://www.rb.cz/frontend-controller/backend-data/currency/listDataXml';


	public function __construct(
		ClientInterface $client,
		RequestFactoryInterface $requestFactory,
		string $timeZone = 'Europe/Prague',
		string $refresh = 'midnight, +15 minute',
	)
	{
		parent::__construct($client, $requestFactory, $timeZone, $refresh);
	}


	protected function createList(ResponseInterface $response): iterable
	{
		$data = $response->getBody()->getContents();
		$xml = simplexml_load_string($data);

		if ($xml === false) {
			throw new InvalidStateException('Invalid source xml.');
		}

		// add CZK
		$czk = $xml->exchangeRateList->exchangeRates->addChild('exchangeRate');
		$czk->addChild('unitsFrom', '1');
		$czk->addChild('exchangeRateCenter', '1');
		$czk->addChild('currencyFrom', 'CZK');
		$czk->addChild('exchangeRateSellCash', '1');
		$czk->addChild('exchangeRateBuyCash', '1');

		$this->setDate(DATE_RFC3339_EXTENDED, (string) $xml->exchangeRateList->effectiveDateFrom);

		return $xml->exchangeRateList->exchangeRates->exchangeRate;
	}


	protected function createProperty($row)
	{
		return new Property(
			intval($row->unitsFrom),
			$this->rate($row),
			strval($row->currencyFrom),
		);
	}


	abstract protected function rate(SimpleXMLElement $element): float;


	protected function prepareUrl(?\DateTimeInterface $date): string
	{
		$url = self::$url;

		return $date === null ? $url : "$url?filtered=true&date=" . urlencode($date->format('Y-m-d'));
	}
}
