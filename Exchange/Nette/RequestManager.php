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

    protected function getSessionVat() {
        return isset($this->session->{$this->getParamVat()}) ? $this->session->{$this->getParamVat()} : NULL;
    }

    protected function getRequestVat() {
        return $this->request->getQuery($this->getParamVat());
    }

    public function setSessionVat($value) {
        $this->session->{$this->getParamVat()} = (bool) $value;
        return $this;
    }

    protected function getRequestCurrency() {
        return $this->request->getQuery($this->getParamCurrency());
    }

    protected function getSessionCurrency() {
        return isset($this->session->{$this->getParamCurrency()}) ? $this->session->{$this->getParamCurrency()} : NULL;
    }

    public function setSessionCurrency($code) {
        $this->session->{$this->getParamCurrency()} = $code;
        return $this;
    }

}
