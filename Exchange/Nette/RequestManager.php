<?php

namespace h4kuna\Exchange\Nette;

use h4kuna\Exchange\Storage\RequestManager AS hESR;
use Nette\Http\Request;
use Nette\Http\SessionSection;

/**
 *
 * @author Milan Matějček
 */
final class RequestManager extends hESR {

    /** @var SessionSection */
    private $session;

    /** @var Request */
    private $request;

    public function __construct(Request $request, SessionSection $session) {
        $this->request = $request;
        $this->session = $session;
    }

    public function setSessionVat($value) {
        $this->session->{$this->getParamVat()} = $value = (bool) $value;
        return $value;
    }

    public function setSessionCurrency($code) {
        $this->session->{$this->getParamCurrency()} = $code;
        return $code;
    }

    public function loadParamCurrency($code) {
        $paramGet = $this->request->getQuery($this->getParamCurrency());
        if ($paramGet) {
            return $this->setSessionCurrency($paramGet);
        }

        if (isset($this->session->{$this->getParamCurrency()})) {
            return $this->session->{$this->getParamCurrency()};
        }
        return $this->setSessionCurrency($code);
    }

    public function loadParamVat($default) {
        $paramGet = $this->request->getQuery($this->getParamVat());
        if ($paramGet !== NULL) {
            return $this->setSessionVat($paramGet);
        }

        if (isset($this->session->{$this->getParamVat()})) {
            return $this->session->{$this->getParamVat()};
        }
        return $this->setSessionVat($default);
    }

}
