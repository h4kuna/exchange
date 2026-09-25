<?php declare(strict_types = 1);

namespace h4kuna\Exchange\Fixtures;

use Exception;
use GuzzleHttp\Psr7\HttpFactory as Psr7HttpFactory;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use h4kuna\Exchange\Driver\Cnb\Day as CnbDay;
use h4kuna\Exchange\Driver\Ecb\Day as EcbDay;
use h4kuna\Exchange\Exceptions\InvalidStateException;
use Psr\Http\Client\ClientExceptionInterface;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\UriInterface;
use function array_splice;
use function explode;
use function parse_str;
use function sprintf;
use function strtolower;
use function strval;

final class HttpFactory implements RequestFactoryInterface, ClientInterface
{

	public static bool $exception = false;

	public function __construct(
		private string $filePath,
	)
	{
	}

	public function sendRequest(RequestInterface $request): ResponseInterface
	{
		$date = '';
		$filePath = $this->filePath;
		if ($filePath === CnbDay::class) {
			$filePath = self::driverName($filePath) . '.txt';
			parse_str($request->getUri()->getQuery(), $params);
			$date = isset($params['date']) ? strval($params['date']) . '.' : '';
		} elseif ($filePath === EcbDay::class) {
			$filePath = self::driverName($filePath) . '.xml';
		} else {
			throw new InvalidStateException(sprintf('Driver name is not defined "%s".', $this->filePath));
		}

		if ($date === '02.12.2022.' || self::$exception) {
			self::$exception = false;
			throw new class extends Exception implements ClientExceptionInterface {

			};
		}

		$stream = (new Psr7HttpFactory())->createStreamFromFile(__DIR__ . "/../Fixtures/$date" . $filePath);

		return new Response(200, [], $stream, '1.1');
	}

	/**
	 * @param UriInterface|string $uri
	 */
	public function createRequest(
		string $method,
		$uri,
	): RequestInterface
	{
		return new Request($method, $uri);
	}

	private static function driverName(string $class): string
	{
		$names = explode('\\', $class);

		return strtolower(array_splice($names, -2)[0]);
	}

}
