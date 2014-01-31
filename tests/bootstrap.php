<?php

include __DIR__ . "/../vendor/autoload.php";

// 2# Create Nette Configurator
$configurator = new Nette\Config\Configurator;
$tmp = __DIR__ . '/temp';
$configurator->enableDebugger($tmp);
$configurator->setTempDirectory($tmp);
$configurator->setDebugMode();

$container = $configurator->createContainer();

$container->getService('session')->start();



