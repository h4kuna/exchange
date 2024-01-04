<?php declare(strict_types=1);

namespace h4kuna\Exchange\Driver;

use GuzzleHttp\Client;
use GuzzleHttp\Psr7\HttpFactory;
use h4kuna\Exchange\Driver\RB\Day;
use h4kuna\Exchange\Exceptions\MissingDependencyException;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestFactoryInterface;

class DriverBuilderFactory
{
	public function __construct(
		private ?ClientInterface $client = null,
		private ?RequestFactoryInterface $requestFactory = null
	)
	{
	}


	public function create(): DriverBuilder
	{
		return new DriverBuilder([
			Cnb\Day::class => fn () => $this->createCnb(),
			Ecb\Day::class => fn () => $this->createEcb(),
			RB\DayCenter::class => fn () => $this->createRB(RB\DayCenter::class),
			RB\DayBuy::class => fn () => $this->createRB(RB\DayBuy::class),
			RB\DaySell::class => fn () => $this->createRB(RB\DaySell::class),
		]);
	}


	protected function createCnb(): Driver
	{
		return new Cnb\Day($this->getClient(), $this->getRequestFactory());
	}


	protected function createEcb(): Driver
	{
		return new Ecb\Day($this->getClient(), $this->getRequestFactory());
	}


	/**
	 * @param class-string<Day> $class
	 */
	protected function createRB(string $class): Driver
	{
		return new $class($this->getClient(), $this->getRequestFactory());
	}


	protected function getClient(): ClientInterface
	{
		if ($this->client === null) {
			MissingDependencyException::guzzleClient();
			$this->client = new Client();
		}

		return $this->client;
	}


	protected function getRequestFactory(): RequestFactoryInterface
	{
		if ($this->requestFactory === null) {
			MissingDependencyException::guzzleFactory();
			$this->requestFactory = new HttpFactory();
		}

		return $this->requestFactory;
	}

}
