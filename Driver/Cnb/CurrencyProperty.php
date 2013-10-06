<?php

namespace h4kuna\Exchange\Cnb;

/**
 * Description of CurrencyProperty
 *
 * @author Milan Matějček
 */
class CurrencyProperty extends \h4kuna\Exchange\CurrencyProperty {

    /** @var strning */
    private $country;

    /** @var strning */
    private $name;

    public function __construct($home, $code, $foreing, $country, $name) {
        parent::__construct($home, $code, $foreing);
        $this->country = $country;
        $this->name = $name;
    }

    /**
     * Country in czech language
     *
     * @return string
     */
    public function getCountry() {
        return $this->country;
    }

    /**
     * Name in czech language
     *
     * @return string
     */
    public function getName() {
        return $this->name;
    }

}
