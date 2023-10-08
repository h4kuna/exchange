<?php declare(strict_types=1);

use h4kuna\CriticalCache\CacheFactory;
use h4kuna\Exchange;

ini_set('date.timezone', 'Europe/Prague');

require_once __DIR__ . '/../vendor/autoload.php';

define('TEMP_DIR', __DIR__ . '/temp');

if (defined('__PHPSTAN_RUNNING__')) {
	return;
}

function createExchangeFactory(string $driver = Exchange\Driver\Cnb\Day::class): Exchange\ExchangeFactory
{
	$httpFactory = new Exchange\Fixtures\HttpFactory($driver);
	$driverBuilderFactory = new Exchange\Driver\DriverBuilderFactory($httpFactory, $httpFactory);
	$allowed = [
		'CZK',
		'USD',
		'EUR',
	];

	return new Exchange\ExchangeFactory(
		'EUR',
		null,
		$allowed,
		$driverBuilderFactory,
		new CacheFactory(TEMP_DIR . '/exchange'),
		$driver
	);
}


// Tester\Helpers::purge(TEMP_DIR . '/exchange');
Tester\Environment::setup();

Tracy\Debugger::enable(false, TEMP_DIR);
