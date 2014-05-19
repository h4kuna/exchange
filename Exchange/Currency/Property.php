<?php

namespace h4kuna\Exchange\Currency;

use h4kuna\INumberFormat;

/**
 * @author Milan Matějček
 */
class Property implements IProperty {

    /** @var float */
    private $foreing;

    /** @var strning */
    private $code;

    /** @var int */
    private $home;

    /** @var INumberFormat */
    private $format;

    /** @var array */
    private $stack = array();

    /** @var Property */
    public $default;

    public function __construct($home, $code, $foreing) {
        $this->home = floatval($home);
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
        return ($this->foreing / $this->home) / ($this->default->getForeing() / $this->default->getHome());
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
     * @return Property
     */
    public function popRate() {
        if ($this->stack) {
            $this->home = array_pop($this->stack);
        }
        return $this;
    }

    /**
     * Add new rate
     *
     * @param type $number
     * @return Property
     */
    public function pushRate($number) {
        array_push($this->stack, $this->home);
        $this->home = $number;
        return $this;
    }

    /**
     * Set last rate in stack and clear
     *
     * @return Property
     */
    public function revertRate() {
        if ($this->stack) {
            $this->home = end($this->stack);
            $this->stack = array();
        }
        return $this;
    }

// </editor-fold>

    public function __toString() {
        return $this->getCode();
    }

}
