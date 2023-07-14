<?php declare(strict_types=1);

namespace h4kuna\Exchange\Exceptions;

use GuzzleHttp\Client;
use GuzzleHttp\Psr7\HttpFactory;

final class MissingDependencyException extends \RuntimeException
{

	public static function guzzleFactory(): void
	{
		self::check(HttpFactory::class, 'guzzlehttp/guzzle');
	}


	public static function guzzleClient(): void
	{
		self::check(Client::class, 'guzzlehttp/guzzle');
	}


	private static function check(string $class, string $package): void
	{
		if (class_exists($class) === false) {
			throw new self("Missing class \"$class\", you can install by: composer require $package");
		}
	}

}
