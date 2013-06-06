<?php

include __DIR__ . "/vendor/autoload.php";



// 2# Create Nette Configurator
$configurator = new Nette\Config\Configurator;
//$configurator->setDebugMode(array('83.208.9.48'));
$configurator->enableDebugger(__DIR__ . '/tmp');
$configurator->setTempDirectory(__DIR__ . '/tmp');
$configurator->createContainer();



$http = \Nette\Environment::getHttpRequest();
$cache = new \h4kuna\Storage(\Nette\Environment::getContext()->cacheStorage);
$session = \Nette\Environment::getSession('exchange');

$e = new \h4kuna\Exchange($cache, $http, $session);
$e->loadCurrency('czk', array('symbol' => 'Kč', 'decimal' => 0));
$e->loadCurrency('eur', array('mask' => 'S 1', 'symbol' => '€', 'point' => '.', 'decimal' => 1));
dump($e->format(10));
dump($e->format(10, FALSE, 'eur'));

echo $e->vatLink('zapnout dph', 'vypnout dph');

