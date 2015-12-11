<?php

namespace h4kuna\Exchange\Currency;

use h4kuna\Number,
	Tester\Assert;

$container = require_once __DIR__ . '/../../bootstrap.php';

class PropertyTest extends \Tester\TestCase
{

	/** @var Property */
	private $home;

	protected function setUp()
	{
		$this->home = new Property(1, 'czk', 1);
		$this->home->default = $this->home;
		$number = new Number\NumberFormat();
		$this->home->setFormat($number); // test namespce
	}

	public function testInitial()
	{
		$property = new Property('7.54', 'rub', '100');
		$property->default = $this->home;
		Assert::same(7.54, $property->getHome());
		Assert::same('RUB', $property->getCode());
		Assert::same('RUB', (string) $property);
		Assert::same(100.0, $property->getForeing());
		Assert::same(13.262599469496, round($property->getRate(), 12));

		$property->pushRate(8.35);
		Assert::same(11.976047904192, round($property->getRate(), 12));
		$property->revertRate();
		Assert::same(13.262599469496, round($property->getRate(), 12));
	}

}

(new PropertyTest())->run();
