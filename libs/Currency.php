<?php

namespace h4kuna;

use Nette\Object;

/**
 * Description of Currency
 *
 * @author h4kuna
 */
class Currency extends Object {

    private $code;

    /** @var Transfer */
    private $conversion;
    private $other = array();

    public function __construct($code, $home, $to) {
        $this->code = $code;
        $this->conversion = new Transfer($to, $home);
    }

    public function __set($name, $value) {
        if (!isset($this->{$name})) {
            $this->other[$name] = $value;
            return $value;
        }
        return parent::__set($name, $value);
    }

    public function &__get($name) {
        if (isset($this->other[$name])) {
            return $this->other[$name];
        }
        return parent::__get($name);
    }

    public function __isset($name) {
        return isset($this->other[$name]);
    }

    public function getCode() {
        return $this->code;
    }

    public function getRate() {
        return $this->conversion->getRate();
    }

    public function setTempRate($rate) {
        $this->conversion->push($rate);
        return $this;
    }

    public function removeTempRate() {
        return $this->conversion->pop();
    }

    public function __toString() {
        return $this->getCode();
    }

}

