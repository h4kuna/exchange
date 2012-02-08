<?php

namespace Exchange;

use Nette\Object;

/**
 * @author Milan Matějček
 */
abstract class Download extends Object implements IDownload
{
	protected $links = array();

	/** @var DateTime */
	protected $date;

	/**
	 * corection for rating, up or down [%], for czech eshop is recommended 1.03 is different beetwen buy and middle rate
	 * @example 5% = 1.05, -5% = 0.95
	 * recommended is by calculate 1.035 rounded to 1.04
	 * correction works well when default is CZK, eur to eur, eur to czk, czk to eur
	 * @var number
	 */
	protected $correction = 1;

	/** @var string */
	protected $default;
	private $curl;

	public function __construct($default, \DateTime $date = NULL)
	{
		$this->date = $date;
		$this->default = $default;
	}

	/**
	 * @param float $num
	 */
	public function setCorrection($num)
	{
		$this->correction = $num;
	}

	public function setDate(\DateTime $date = NULL)
	{
		$this->date = $date;
		return $this;
	}

	/**
	 * replace stroke to point
	 * @param string $string
	 * @return string
	 */
	static public function stroke2point($string)
	{
		return str_replace(',', '.', $string);
	}

	protected function getData()
	{
		if ($this->getCurl()) {
			$data = $this->curl();
		} elseif (ini_get('allow_url_fopen')) {
			$data = $this->fopen();
		}
		else
			throw new ExchangeException('This library need allow_url_fopen -enable or curl extension');
		return $data;
	}

	abstract protected function fopen();

	abstract protected function curl();

	/**
	 * @example
	 * array(
	 * 0 => additional information
	 * 'CZK' => array(...)
	 * )
	 * @return array
	 */
	abstract protected function save($data);

	/**
	 * setup of proxy
	 * @param CUrl $curl
	 * @return void
	 */
	public function setProxy($proxy, $port, $pass)
	{
		$curl = $this->getCurl();
		if ($curl) {
			$curl->setOptions(array(
					CURLOPT_PROXY => $proxy,
					CURLOPT_PROXYPORT => $port,
					CURLOPT_PROXYUSERPWD => $pass,
			));
		}
		return $curl;
	}

	protected function getCurl()
	{
		if (!$this->curl && extension_loaded('curl')) {
			$this->curl = new CUrl;
		}
		return $this->curl;
	}

}
