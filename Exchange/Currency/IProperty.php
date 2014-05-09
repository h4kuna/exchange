<?php

namespace h4kuna\Exchange\Currency;

use h4kuna\INumberFormat;

/**
 *
 * @author Milan Matějček
 */
interface IProperty {

    /** @return int */
    public function getHome();

    /** @return string */
    public function getCode();

    /** @return float */
    public function getForeing();

    /** @return float */
    public function getRate();

    /**
     * Set how render currency
     *
     * @param INumberFormat $nf
     * @return IProperty
     */
    public function setFormat(INumberFormat $nf);

    /** @return INumberFormat */
    public function getFormat();

    /**
     * Set history rate
     *
     * @param float $number
     * @return IProperty
     */
    public function pushRate($number);

    /**
     * @return IProperty
     */
    public function popRate();

    /**
     * Set last value in stack and clear stack
     *
     * @return IProperty
     */
    public function revertRate();

    /**
     * Default currency for count rate
     *
     * @return IProperty
     */
    // public function setDefault($property);
}
