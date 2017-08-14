<?php

namespace h4kuna\Exchange\Http;

use h4kuna\Exchange,
	Nette\Http;

class CookieManager
{
	/** @var Exchange\Exchange */
	private $exchange;

	/** @var Http\Response */
	private $response;

	/** @var string */
	private $cookie = ['currency', NULL, '+14 days'];

	public function __construct(Exchange\Exchange $exchange)
	{
		$this->response = new Http\Response();
		$this->exchange = $exchange;
		$this->initCurrency();
	}

	/**
	 * @see Http\Response::setCookie()
	 */
	public function setCookie($name, $time = '+14 days', $path = NULL, $domain = NULL, $secure = NULL, $httpOnly = NULL)
	{
		$this->cookie = [
			$name,
			NULL,
			$time,
			$path,
			$domain,
			$secure,
			$httpOnly
		];
	}

	public function setCurrency($code)
	{
		$property = $this->checkCode($code);
		if ($property === NULL) {
			return;
		}
		$this->exchange->setOutput($property->code);
		$this->cookie[1] = $property->code;
		$this->response->setCookie(...$this->cookie);
	}

	private function initCurrency()
	{
		if (!isset($_COOKIE[$this->cookie[0]])) {
			return NULL;
		}

		$property = $this->checkCode($_COOKIE[$this->cookie]);
		if ($property === NULL) {
			$cookie = $this->cookie;
			$cookie[1] = FALSE;
			$cookie[2] = 0;
			$this->response->setCookie(...$cookie);
			return NULL;
		}

		$this->exchange->setOutput($property->code);
	}

	private function checkCode($code)
	{
		try {
			return $this->exchange->offsetGet($code);
		} catch (Exchange\UnknownCurrencyException $e) {
			return NULL;
		}
	}

}
