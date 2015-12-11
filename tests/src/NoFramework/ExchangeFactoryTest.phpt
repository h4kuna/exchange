<?php

namespace h4kuna\Exchange\NoFramework;

use h4kuna\Exchange,
	Tester\Assert;

$container = require_once __DIR__ . '/../../bootstrap.php';

class ExchangeFactoryTest extends \Tester\TestCase
{

	public function testInitial()
	{
		$builder = new ExchangeFactory(__DIR__ . '/../temp');
		$exchange = $builder->create();
		Assert::true($exchange instanceof Exchange\Exchange);
	}

}

(new ExchangeFactoryTest())->run();
