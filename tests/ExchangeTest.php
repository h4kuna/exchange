<?php

namespace Tests;

require_once __DIR__ . '/bootstrap.php';

use h4kuna\Exchange;
use Nette\Environment;
use PHPUnit_Framework_TestCase;
use h4kuna\NumberFormat;

/**
 * @author Milan Matějček
 */
class ExchangeTest extends PHPUnit_Framework_TestCase {

    /** @var Exchange\Exchange */
    private $object;

    protected function setUp() {
        $this->initExchange();
    }

    private function initExchange(Exchange\Download $driver = NULL) {
        /* @var $exchange \h4kuna\Exchange\Exchange */
        $exchange = Environment::getService('exchangeExtension.exchange');
        if ($driver !== NULL) {
            $exchange = $exchange->setDriver($driver);
        }
        $this->object = $exchange->setDate(new \DateTime('2000-12-30'));
        $this->object->loadCurrency('czk');
        $this->object->loadCurrency('eur');
        $this->object->loadCurrency('usd');
        $this->object->setWeb('CZK', TRUE);
    }

    public function testChange() {
        $this->assertSame(10, $this->object->change(10));
        $this->assertSame(10, $this->object->change(10, FALSE));
        $this->assertSame(350.9, $this->object->change(10, 'eur'));
        $this->assertSame(350.9, $this->object->change(10, 'eur', 'czk'));
        $this->assertSame(9.28, $this->object->change(10, 'eur', 'usd', 2));
        $this->assertSame(10.78, $this->object->change(10, 'usd', 'eur', 2));
    }

    public function testFormat() {
        $n = NumberFormat::NBSP;
        $this->assertSame('10' . $n . 'Kč', $this->object->format(10));
        $this->assertSame('10' . $n . 'Kč', $this->object->format(10, FALSE));
        $this->assertSame('351' . $n . 'Kč', $this->object->format(10, 'eur'));

        $this->assertSame('351' . $n . 'Kč', $this->object->format(10, 'eur', 'czk'));
        $this->assertSame('9,28' . $n . 'USD', $this->object->format(10, 'eur', 'usd', 2));
        $this->assertSame('10,78' . $n . 'EUR', $this->object->format(10, 'usd', 'eur', 2));
    }

    public function testLoadAll() {
        $this->object->loadAll();
    }

    public function testHistory() {
        $code = 'eur';
        $this->object->addHistory($code, 26)->setWeb($code);
        $this->assertSame(1.0, $this->object->change(26));
        $this->object->removeHistory($code);
        $this->assertSame(0.74, $this->object->change(26, NULL, NULL, 2));
    }

    public function testSetDefault() {
        $this->object->setDefault('eur');
        $this->object->loadCurrency('byr');
        $this->assertSame(35.09, $this->object->change(1, NULL, 'czk'));
        $this->assertSame(17371.287, $this->object->change(1, NULL, 'byr', 3));
    }

    public function testRbDriver() {
        $this->initExchange(new Exchange\RB\Day);
        $this->assertSame(10, $this->object->change(10));
        $this->assertSame(9.1575, $this->object->change(10, 'eur', 'usd', 4));
    }

}
