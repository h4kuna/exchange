<?php declare(strict_types=1);

use h4kuna\Exchange;

ini_set('date.timezone', 'Europe/Prague');

require_once __DIR__ . '/../vendor/autoload.php';

define('TEMP_DIR', __DIR__ . '/temp');

if (defined('__PHPSTAN_RUNNING__')) {
	return;
}

function createExchangeFactory(string $driver = 'cnb'): Exchange\ExchangeFactory
{
	$httpFactory = new Exchange\Fixtures\HttpFactory($driver);

	$exchangeFactory = new Exchange\ExchangeFactory('eur', null, __DIR__ . '/temp/exchange', [
		'czk',
		'usd',
		'eur',
	]);
	$exchangeFactory->setClient($httpFactory);
	$exchangeFactory->setRequestFactory($httpFactory);
	if ($driver === 'ecb') {
		$exchangeFactory->setDriver(new Exchange\Driver\Ecb\Day($exchangeFactory->getClient(), $exchangeFactory->getRequestFactory()));
	}

	return $exchangeFactory;
}


Tester\Helpers::purge(TEMP_DIR . '/exchange');
Tester\Environment::setup();

Tracy\Debugger::enable(false, TEMP_DIR);
