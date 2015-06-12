<?php

include __DIR__ . "/../vendor/autoload.php";

function dd($var /* ... */)
{
	foreach (func_get_args() as $arg) {
		Tracy\Debugger::dump($arg);
	}
	exit;
}

Tester\Environment::setup();


// 2# Create Nette Configurator
$configurator = new Nette\Configurator;

$tmp = __DIR__ . '/temp';
$configurator->enableDebugger($tmp);
$configurator->setTempDirectory($tmp);
$configurator->setDebugMode(TRUE);
$configurator->addConfig(__DIR__ . '/test.neon');
$container = $configurator->createContainer();

Tracy\Debugger::enable(FALSE);

return $container;



