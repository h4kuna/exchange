<?php

namespace Exchange;

use Nette\Object,
    Nette\Localization\ITranslator;

/**
 * @property-write $number
 * @property-write $thousand
 * @property-write $decimal
 * @property-write $point
 * @property-write $nbsp
 * @property-write $zeroClear
 * @property-write $mask
 * @property-write $symbol
 */
class NumberFormat extends Object {

    /** @var string */
    private $thousand = ' ';

    /** @var int */
    private $decimal = 2;

    /** @var string */
    private $point = ',';

    /** @var bool */
    private $nbsp = TRUE;

    /** @var bool */
    private $zeroClear = FALSE;

    /** @var number */
    private $number = 0;

    /** @var string */
    private $mask = '1 S';

    /** @var ITranslator */
    private $translator;

    /**
     * internal helper
     * @var array
     */
    private $workMask = array('', '');

    /** @var string */
    private $symbol;

    public function __construct($symbol = NULL, ITranslator $translator = NULL) {
        $this->translator = $translator;
        $this->setSymbol($symbol);
    }

    public function getSymbol() {
        return $this->symbol;
    }

    public function setDecimal($val) {
        $this->decimal = $val;
        return $this;
    }

    /**
     * @example '1 S', 'S 1'
     * S = symbol
     * @param string $mask
     * @return Format
     */
    public function setMask($mask) {
        if (strpos($mask, '1') === FALSE || strpos($mask, 'S') === FALSE) {
            throw new ExchangeException('The mask consists of 1 and S.');
        }

        $this->mask = $mask;
        $this->workMask = explode('1', str_replace('S', $this->symbol, $mask));
        return $this;
    }

    public function setNumber($number) {
        $this->number = $number;
        return $this;
    }

    /**
     * @param bool $val
     * @return Format
     */
    public function setNbsp($val) {
        $this->nbsp = (bool) $val;
        return $this;
    }

    public function setPoint($val) {
        $this->point = $val;
        return $this;
    }

    public function setSymbol($symbol) {
        $this->symbol = $symbol;
        if ($this->translator) {
            $this->symbol = $this->translator->translate($this->symbol);
        }

        if ($symbol !== NULL) {
            $this->setMask($this->mask);
        }
        return $this;
    }

    public function setThousand($val) {
        $this->thousand = $val;
        return $this;
    }

    public function setZeroClear($val) {
        $this->zeroClear = (bool) $val;
        return $this;
    }

    public function toggleNbsp() {
        return $this->setNbsp(!$this->nbsp);
    }

    public function toggleZeroClear() {
        return $this->setZeroClear(!$this->zeroClear);
    }

    public function render($number = NULL) {
        if ($number) {
            $this->setNumber($number);
        } elseif ($this->number === NULL) {
            return NULL;
        }

        $num = number_format($this->number, $this->decimal, $this->point, $this->thousand);

        if ($this->decimal > 0 && $this->zeroClear) {
            $num = rtrim(rtrim($num, '0'), $this->point);
        }

        if ($this->symbol) {
            $num = implode($num, $this->workMask);
        }

        if ($this->nbsp) {
            $num = str_replace(' ', "\xc2\xa0", $num);
        }

        return $num;
    }

    public function __toString() {
        return $this->render();
    }

}