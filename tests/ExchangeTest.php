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
        if ($driver === NULL) {
            $driver = new Exchange\Cnb\Day;
        }
        $storage = new Exchange\Storage(Environment::getContext()->cacheStorage, $driver);
        $store = new Exchange\Store($storage, $driver);
        $exchange = new Exchange\Exchange($store, Environment::getHttpRequest(), Environment::getSession('exchange'));
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
        $this->assertSame('10,00' . $n . 'CZK', $this->object->format(10));
        $this->assertSame('10,00' . $n . 'CZK', $this->object->format(10, FALSE));
        $this->assertSame('350,90' . $n . 'CZK', $this->object->format(10, 'eur'));
        $this->assertSame('350,90' . $n . 'CZK', $this->object->format(10, 'eur', 'czk'));
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

    public function testRbDriver() {
        $this->initExchange(new Exchange\RB\Day);
        $this->assertSame(10, $this->object->change(10));
        $this->assertSame(9.2799, $this->object->change(10, 'eur', 'usd', 4));
}

}
