<?php declare(strict_types = 1);

use h4kuna\CriticalCache\CacheFactory;
use h4kuna\DriverBuilderFactory;
use h4kuna\Exchange\Driver\Cnb\Day as CnbDay;
use h4kuna\ExchangeFactory;
use h4kuna\HttpFactory;
use Tester\Environment;
use Tracy\Debugger;

ini_set('date.timezone', 'Europe/Prague');

require_once __DIR__ . '/../vendor/autoload.php';

define('TEMP_DIR', __DIR__ . '/temp');

if (defined('__PHPSTAN_RUNNING__')) {
	return;
}

function createExchangeFactory(string $driver = CnbDay::class): ExchangeFactory
{
	$httpFactory = new HttpFactory($driver);
	$driverBuilderFactory = new DriverBuilderFactory($httpFactory, $httpFactory);
	$allowed = [
		'CZK',
		'USD',
		'EUR',
	];

	return new ExchangeFactory(
		'EUR',
		null,
		$allowed,
		$driverBuilderFactory,
		new CacheFactory(TEMP_DIR . '/exchange'),
		$driver,
	);
}


// Tester\Helpers::purge(TEMP_DIR . '/exchange');
Environment::setup();

Debugger::enable(false, TEMP_DIR);
