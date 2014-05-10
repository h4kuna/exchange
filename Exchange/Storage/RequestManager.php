<?php

namespace h4kuna\Exchange\Storage;

use Nette\Object;

/**
 * @author Milan Matejcek
 */
abstract class RequestManager extends Object implements IRequestManager {

    /** @return string */
    public function getParamCurrency() {
        return 'currency';
    }

    /** @return string */
    public function getParamVat() {
        return 'vat';
    }

    public function loadParamCurrency($default) {
        $paramGet = $this->getRequestCurrency();
        if ($paramGet !== NULL) {
            $paramGet = strtoupper($paramGet);
            $this->setSessionCurrency($paramGet);
            return $paramGet;
        }

        $session = $this->getSessionCurrency();
        if ($session !== NULL) {
            return $session;
        }

        $this->setSessionCurrency($default);
        return $default;
    }

    /**
     * Set VAT from query and set local property and save to session
     */
    public function loadParamVat($default) {
        $paramGet = $this->getRequestVat();
        if ($paramGet !== NULL) {
            $this->setSessionVat($paramGet);
            return (bool) $paramGet;
        }

        $session = $this->getSessionVat();
        if ($session !== NULL) {
            return (bool) $session;
        }

        $this->setSessionVat($default);
        return (bool) $default;
    }

    /** @return bool|NULL */
    abstract protected function getSessionVat();

    /** @return bool|NULL */
    abstract protected function getRequestVat();

    /** @return string|NULL */
    abstract protected function getSessionCurrency();

    /** @return string|NULL */
    abstract protected function getRequestCurrency();
}
