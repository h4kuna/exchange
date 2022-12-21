<?php declare(strict_types=1);

namespace h4kuna\Exchange\Fixtures;

use GuzzleHttp\Psr7;
use h4kuna\Exchange\Exceptions\InvalidStateException;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

final class HttpFactory implements RequestFactoryInterface, ClientInterface
{

	public function __construct(
		private string $filePath,
	)
	{
	}


	public function sendRequest(RequestInterface $request): ResponseInterface
	{
		$date = '';
		$filePath = $this->filePath;
		if ($filePath === 'cnb') {
			$filePath .= '.txt';
			parse_str($request->getUri()->getQuery(), $params);
			$date = isset($params['date']) ? strval($params['date']) . '.' : '';
		} elseif ($filePath === 'ecb') {
			$filePath .= '.xml';
		} else {
			throw new InvalidStateException(sprintf('Driver name is not defined "%s".', $this->filePath));
		}

		$stream = (new Psr7\HttpFactory())->createStreamFromFile(__DIR__ . "/../Fixtures/$date" . $filePath);

		return new Psr7\Response(200, [], $stream, '1.1');
	}


	public function createRequest(string $method, $uri): RequestInterface
	{
		return new Psr7\Request($method, $uri);
	}

}
