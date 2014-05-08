<?php

namespace h4kuna\Exchange;

use h4kuna\INumberFormat;

/**
 * @author Milan Matějček
 */
class CurrencyProperty implements ICurrencyProperty {

    /** @var int */
    private $home;

    /** @var strning */
    private $code;

    /** @var float */
    private $foreing;

    /** @var INumberFormat */
    private $format;

    /** @var array */
    private $stack = array();

    /** @var CurrencyProperty fill by reference */
    public $default;

    public function __construct($home, $code, $foreing) {
        $this->home = intval($home);
        $this->code = strtoupper($code);
        $this->foreing = floatval($foreing);
    }

    public function getCode() {
        return $this->code;
    }

    public function getForeing() {
        return $this->foreing;
    }

    public function getHome() {
        return $this->home;
    }

    public function getRate() {
        return ($this->default->foreing / $this->default->home) / ($this->foreing / $this->home);
    }

// <editor-fold defaultstate="collapsed" desc="Number will format for render">
    public function getFormat() {
        return $this->format;
    }

    public function setFormat(INumberFormat $nf) {
        $this->format = $nf;
        return $this;
    }

// </editor-fold>
// <editor-fold defaultstate="collapsed" desc="History">
    /**
     * Remove first in stack and set up
     *
     * @return CurrencyProperty
     */
    public function popRate() {
        if ($this->stack) {
            $this->foreing = array_pop($this->stack);
        }
        return $this;
    }

    /**
     * Add new rate
     *
     * @param type $number
     * @return CurrencyProperty
     */
    public function pushRate($number) {
        array_push($this->stack, $this->foreing);
        $this->foreing = $number;
        return $this;
    }

    /**
     * Set last rate in stack and clear
     *
     * @return CurrencyProperty
     */
    public function revertRate() {
        if ($this->stack) {
            $this->foreing = end($this->stack);
            $this->stack = array();
        }
        return $this;
    }

// </editor-fold>

    public function __toString() {
        return $this->getCode();
    }

}
