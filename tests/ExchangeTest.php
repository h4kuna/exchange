<?php

namespace Tests;

require_once __DIR__ . '/bootstrap.php';

use DateTime;
use h4kuna\Exchange;
use h4kuna\NumberFormat;
use Nette\Environment;
use PHPUnit_Framework_TestCase;

/**
 * @author Milan Matějček
 */
class ExchangeTest extends PHPUnit_Framework_TestCase {

    /** @var Exchange\Exchange */
    private $object;

    protected function setUp() {
        $this->object = Environment::getContext()->createService('exchangeExtension.exchange')
                ->setDate(new DateTime('2000-12-30'));
        $this->object->setDefault('czk');
        $this->object->loadCurrency('czk');
        $this->object->loadCurrency('eur');
        $this->object->loadCurrency('usd');
        $this->object->setWeb('czk', TRUE);
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

    public function testChangeRate() {
        $code = 'eur';
        $this->object->addRate($code, 26)->setWeb($code);
        $this->assertSame(1.0, $this->object->change(26));
        $this->object->removeRate($code);
        $this->assertSame(0.74, $this->object->change(26, NULL, NULL, 2));
    }

    public function testSetDefault() {
        $this->object->setDefault('eur');
        $this->object->loadCurrency('byr');
        $this->assertSame(35.09, $this->object->change(1, NULL, 'czk'));
        $this->assertSame(17633.166, $this->object->change(1, NULL, 'byr', 3));
    }

    public function testRbDriver() {
        $rb = $this->object->setDriver(new Exchange\Driver\Rb\Day);
        $this->assertSame(10, $rb->change(10));
        $this->assertSame(9.1575, $rb->change(10, 'eur', 'usd', 4));
    }

    public function testLoadAll() {
        $this->object->loadAll();
        $this->assertSame(152, $this->object->count());
    }
    
    public function testHetory() {
        $ex2010 = $this->object->setDate(new \DateTime('2010-12-30'));
        $this->assertSame('2000-12-30\Cnb\Day', $this->object->getName());
        $this->assertSame('2010-12-30\Cnb\Day', $ex2010->getName());
    }

    private function d($v) {
        \Nette\Diagnostics\Debugger::enable(FALSE);
        dump($v);
    }

}
