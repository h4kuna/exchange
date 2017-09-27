<?php

ini_set('date.timezone', 'Europe/Prague');

include __DIR__ . '/../vendor/autoload.php';

include __DIR__ . '/lib/Driver.php';

define('TEMP_DIR', __DIR__ . '/temp/' . getmypid());

Tester\Environment::setup();

Tracy\Debugger::enable(false);



