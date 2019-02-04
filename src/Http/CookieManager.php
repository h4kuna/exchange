<?php declare(strict_types=1);

namespace h4kuna\Exchange\Http;

use h4kuna\Exchange;
use Nette\Http;

class CookieManager
{

	/** @var Exchange\Exchange */
	private $exchange;

	/** @var Http\Response */
	private $response;

	/** @var array */
	private $cookie = ['currency', null, '+14 days'];


	public function __construct(Exchange\Exchange $exchange)
	{
		$this->response = new Http\Response();
		$this->exchange = $exchange;
		$this->initCurrency();
	}


	/**
	 * @see Http\Response::setCookie()
	 */
	public function setCookie($name, $time = '+14 days', $path = null, $domain = null, $secure = null, $httpOnly = null)
	{
		$this->cookie = [
			$name,
			null,
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
		if ($property === null) {
			return;
		}
		$this->exchange->setOutput($property->code);
		$this->cookie[1] = $property->code;
		$this->response->setCookie(...$this->cookie);
	}


	private function initCurrency()
	{
		if (!isset($_COOKIE[$this->cookie[0]])) {
			return null;
		}

		$property = $this->checkCode($_COOKIE[$this->cookie[0]]);
		if ($property === null) {
			$cookie = $this->cookie;
			$cookie[1] = false;
			$cookie[2] = 0;
			$this->response->setCookie(...$cookie);
			return null;
		}

		$this->exchange->setOutput($property->code);
	}


	private function checkCode($code)
	{
		try {
			return $this->exchange->offsetGet($code);
		} catch (Exchange\Exceptions\UnknownCurrency $e) {
			return null;
		}
	}

}
