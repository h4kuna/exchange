<?php

namespace h4kuna\Exchange;

use h4kuna\INumberFormat;

/**
 * Description of CurrencyProperty
 *
 * @author Milan Matějček
 */
class CurrencyProperty implements ICurrencyProperty {

    /** @var int */
    private $home;

    /** @var strning */
    private $code;

    /** @var float */
    private $foreing;

    /** @var float */
    private $rate;

    /** @var INumberFormat */
    private $format;

    /** @var string */
    private $next;

    /** @var array */
    private $stack = array();

    public function __construct($home, $code, $foreing) {
        $this->home = intval($home);
        $this->code = strtoupper($code);
        $this->foreing = floatval($foreing);
        $this->rate = $this->home / $this->foreing;
    }

    public function getCode() {
        return $this->code;
    }

    public function getForeing() {
        return $this->foreing;
    }

    public function getHome() {
        return $this->rate;
    }

    public function getRate() {
        return $this->rate;
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
// <editor-fold defaultstate="collapsed" desc="Reference to next currency">
    public function getNext() {
        return $this->next;
    }

    public function setNext($code) {
        $this->next = $code;
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
            $this->rate = array_pop($this->stack);
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
        array_push($this->stack, $this->rate);
        $this->rate = $this->home / $number;
        return $this;
    }

    /**
     * Set last rate in stack and clear
     *
     * @return CurrencyProperty
     */
    public function revertRate() {
        if ($this->stack) {
            $this->rate = end($this->stack);
            $this->stack = array();
        }
        return $this;
    }

// </editor-fold>
}
