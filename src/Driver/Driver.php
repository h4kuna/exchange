<?php declare(strict_types=1);

namespace h4kuna\Exchange\Driver;

use DateTime;
use h4kuna\Exchange;
use Psr\Http\Client\ClientExceptionInterface;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

/**
 * Download currency from server.
 * @phpstan-type Source object|string
 * @template T of Exchange\Currency\Property
 */
abstract class Driver
{

	private \DateTimeImmutable $date;

	protected string $refresh = 'tomorrow midnight';

	/**
	 * @var iterable<Source>
	 */
	private iterable $list;


	public function __construct(
		private ClientInterface $client,
		protected RequestFactoryInterface $requestFactory,
	)
	{
	}


	/**
	 * @throws ClientExceptionInterface
	 */
	public function initRequest(?\DateTimeInterface $date): void
	{
		$content = $this->client->sendRequest($this->createRequest($date));
		$this->list = $this->createList($content);
	}


	public function getDate(): \DateTimeImmutable
	{
		return $this->date;
	}


	/**
	 * @param array<string, int> $allowedCurrencies
	 * @return \Generator<T>
	 */
	public function properties(array $allowedCurrencies): \Generator
	{
		foreach ($this->list as $data) {
			$property = $this->createProperty($data);

			if ($property->rate === 0.0 || ($allowedCurrencies !== [] && !isset($allowedCurrencies[$property->code]))) {
				continue;
			}

			yield $property;
		}

		return [];
	}


	public function getRefresh(): string
	{
		return $this->refresh;
	}


	protected function setDate(string $format, string $value): void
	{
		$date = \DateTime::createFromFormat($format, $value);
		if ($date === false) {
			throw new Exchange\Exceptions\InvalidStateException(sprintf('Can not create DateTime object from source "%s" with format "%s".', $value, $format));
		}
		$this->date = \DateTimeImmutable::createFromMutable($date->setTime(0, 0));
	}


	/**
	 * @return iterable<Source>
	 */
	abstract protected function createList(ResponseInterface $response): iterable;


	/**
	 * @param Source $data
	 * @return T
	 */
	abstract protected function createProperty($data);


	abstract protected function prepareUrl(?\DateTimeInterface $date): string;


	protected function createRequest(?\DateTimeInterface $date): RequestInterface
	{
		return $this->requestFactory->createRequest('GET', $this->prepareUrl($date));
	}

}
