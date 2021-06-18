<?php declare(strict_types=1);

ini_set('date.timezone', 'Europe/Prague');

require_once __DIR__ . '/../vendor/autoload.php';

define('TEMP_DIR', __DIR__ . '/temp');

if (defined('__PHPSTAN_RUNNING__')) {
	return;
}

Nette\Utils\FileSystem::createDir(TEMP_DIR);

Tester\Environment::setup();

Tracy\Debugger::enable(false, TEMP_DIR);
