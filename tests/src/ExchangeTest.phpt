<?php

namespace h4kuna\Exchange;

$container = require_once __DIR__ . '/../bootstrap.php';

use DateTime,
    h4kuna\Exchange\Driver\Rb\Day,
    h4kuna\NumberFormat,
    Nette\DI\Container,
    Tester\Assert,
    Tester\TestCase;

/**
 * @author Milan Matějček
 */
class ExchangeTest extends TestCase
{

    /** @var Exchange */
    private $exchange;

    /** @var Container */
    private $container;

    function __construct(Container $container)
    {
        $this->container = $container;
    }

    protected function setUp()
    {
        $this->exchange = $this->container->createService('exchangeExtension.exchange')
                ->setDate(new DateTime('2000-12-30'));
        $this->exchange->loadCurrency('czk');
        $this->exchange->loadCurrency('eur');
        $this->exchange->loadCurrency('usd');
        $this->exchange->setWeb('czk', TRUE);
    }

    public function testChange()
    {
        Assert::same(10, $this->exchange->change(10));
        Assert::same(10, $this->exchange->change(10, FALSE));
        Assert::same(350.9, $this->exchange->change(10, 'eur'));
        Assert::same(350.9, $this->exchange->change(10, 'eur', 'czk'));
        Assert::same(9.28, $this->exchange->change(10, 'eur', 'usd', 2));
        Assert::same(10.78, $this->exchange->change(10, 'usd', 'eur', 2));
    }

    public function testFormat()
    {
        $n = NumberFormat::NBSP;
        Assert::same('10' . $n . 'Kč', $this->exchange->format(10));
        Assert::same('10' . $n . 'Kč', $this->exchange->format(10, FALSE));
        Assert::same('351' . $n . 'Kč', $this->exchange->format(10, 'eur'));

        Assert::same('351' . $n . 'Kč', $this->exchange->format(10, 'eur', 'czk'));
        Assert::same('9,28' . $n . 'USD', $this->exchange->format(10, 'eur', 'usd', 2));
        Assert::same('10,78' . $n . 'EUR', $this->exchange->format(10, 'usd', 'eur', 2));
    }

    public function testChangeRate()
    {
        $code = 'eur';
        $this->exchange->addRate($code, 26)->setWeb($code);
        Assert::same(1.0, $this->exchange->change(26));
        $this->exchange->removeRate($code);
        Assert::same(0.74, $this->exchange->change(26, NULL, NULL, 2));
    }

    public function testSetDefault()
    {
        $this->exchange->setDefault('eur');
        $this->exchange->loadCurrency('sit');
        // kurz z 30.12.2000
        Assert::same(35.09, $this->exchange->change(1, NULL, 'czk'));
        Assert::same(164.35, $this->exchange->change(1000, 'sit', 'czk'));
        Assert::same(213.508, $this->exchange->change(1, NULL, 'sit', 3));
    }

    public function testRbDriver()
    {
        $rb = $this->exchange->setDriver(new Day);
        Assert::same(10, $rb->change(10));
        Assert::same(9.1575, $rb->change(10, 'eur', 'usd', 4));
    }

    public function testLoadAll()
    {
        $this->exchange->loadAll();
        Assert::same(152, $this->exchange->count());
    }

    public function testHetory()
    {
        $ex2010 = $this->exchange->setDate(new \DateTime('2010-12-30'));
        Assert::same('2000-12-30\Cnb\Day', $this->exchange->getName());
        Assert::same('2010-12-30\Cnb\Day', $ex2010->getName());
    }

}

$test = new ExchangeTest($container);
