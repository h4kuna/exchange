<?php

namespace h4kuna\Exchange\Nette;

use h4kuna\Exchange\Storage,
	Nette\Http;

/**
 *
 * @author Milan Matějček
 */
final class RequestManager extends Storage\RequestManager
{

	/** @var Http\SessionSection */
	private $session;

	/** @var Http\Request */
	private $request;

	function __construct(Http\Request $request, Http\Session $session)
	{
		$this->session = $session->getSection('exchange-2015-06-12');
		$this->request = $request;
	}

	public function setSessionVat($value)
	{
		$this->session->{$this->getParamVat()} = $value = (bool) $value;
		return $value;
	}

	public function setSessionCurrency($code)
	{
		$this->session->{$this->getParamCurrency()} = $code;
		return $code;
	}

	public function loadParamCurrency($code)
	{
		$paramGet = $this->request->getQuery($this->getParamCurrency());
		if ($paramGet) {
			return $this->setSessionCurrency($paramGet);
		}

		if (isset($this->session->{$this->getParamCurrency()})) {
			return $this->session->{$this->getParamCurrency()};
		}
		return $this->setSessionCurrency($code);
	}

	public function loadParamVat($default)
	{
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
