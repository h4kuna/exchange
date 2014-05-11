<?php

namespace h4kuna\Exchange\NoFramework;

use h4kuna\Exchange\Storage\RequestManager AS hESR;

/**
 *
 * @author Milan Matejcek
 */
class RequestManager extends hESR {

    public function loadParamCurrency($code) {
        if (!empty($_GET[$this->getParamCurrency()])) {
            return $this->setSessionCurrency($_GET[$this->getParamCurrency()]);
        }

        if (!empty($_SESSION[$this->getParamCurrency()])) {
            return $_SESSION[$this->getParamCurrency()];
        }

        return $this->setSessionCurrency($code);
    }

    public function loadParamVat($default) {
        if (isset($_GET[$this->getParamVat()])) {
            return $this->setSessionVat($_GET[$this->getParamVat()]);
        }

        if (isset($_SESSION[$this->getParamVat()])) {
            return $_SESSION[$this->getParamVat()];
        }

        return $this->setSessionCurrency($default);
    }

    public function setSessionCurrency($code) {
        return $_SESSION[$this->getParamCurrency()] = $code;
    }

    public function setSessionVat($value) {
        return $_SESSION[$this->getParamVat()] = (bool) $value;
    }

}
