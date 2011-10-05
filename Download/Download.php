<?php

namespace Exchange;

use Nette\Object;
use Utility\CUrl;

/**
 * Description of Download
 *
 * @author Milan Matějček
 */
abstract class Download extends Object implements IDownload {

	protected $links = array();

	/** @var DateTime */
	protected $date;

	/** @var Storage */
	protected $storage;

	/**
	 * explicit formating number for rating, use UPPERCASE key in array
	 * @var array
	 */
	protected $defaultCurrency = array('CZK' => '1 Kč', // add default format
			'EUR' => array('1€', 2, ',', '.'),
			'USD' => array('$1', 2, '.', ','),
	);

	/**
	 * letter corection by str_replace
	 * @var array
	 */
	protected $rFound = array(1, ' ');
	protected $rChange = array('', "\xc2\xa0");

	/**
	 * setup for proxy
	 * this variable use Cnb::setProxy() for CUrl
	 * http://www.php.net/manual/en/function.curl-setopt.php
	 * @var string
	 */
	protected $proxyName = NULL;
	protected $proxyPort = 0;
	protected $proxyAuth = NULL;

	/**
	 * default number format
	 * @var array
	 */
	protected $defaultFormat = array(2, ',', ' ');

	/**
	 * time for reload cache [s] - default 1 day
	 * @var int
	 */
	protected $refresh = 86400;

	/**
	 * corection for rating, up or down [%], for czech eshop is recommended 1.03 is different beetwen buy and middle rate
	 * @example 5% = 1.05, -5% = 0.95
	 * recommended is by calculate 1.035 rounded to 1.04
	 * correction works well when default is CZK, eur to eur, eur to czk, czk to eur
	 * @var number
	 */
	protected $correction = 1;

	/** @var array */
	private $property = NULL;

	/** @var string */
	protected $default;

	public function __construct(Storage $storage, $defeult, \DateTime $date = NULL) {
		$this->storage = $storage;
		$this->date = $date;
	}

	public function getRchange()
	{
		return $this->rChange;
	}

	public function getRfound()
	{
		return $this->rFound;
	}

	/**
	 * replace stroke to point
	 * @param string $string
	 * @return string
	 */
	static public function stroke2point($string) {
		return str_replace(',', '.', $string);
	}

	protected function & getData() {
		if (extension_loaded('curl')) {
			$data = $this->curl();
		} elseif (ini_get('allow_url_fopen')) {
			$data = $this->fopen();
		}
		else
			throw new ExchangeException('This library need allow_url_fopen -enable or curl extension');
		return $data;
	}

	abstract protected function & fopen();

	abstract protected function & curl();

	abstract protected function save(&$data);

	// <editor-fold defaultstate="collapsed" desc="getter">

	public function getCurrencies() {
		return array_keys($this->defaultCurrency);
	}

	/**
	 * property of exchange
	 * @return array
	 */
	public function getProperty($key=NULL) {
		if ($this->property === NULL) {
			$this->property = \array_flip(\array_merge($this->getStandardProperty(), $this->getUserProperty()));
		}
		return $key === NULL? $this->property: isset($this->property[$key]);
	}

	/**
	 * must be defined everything
	 * @return array
	 */
	final protected function getStandardProperty() {
		return array(IExchange::RATE, IExchange::NUM_FORMAT, IExchange::DECIMAL,
				IExchange::DEC_POINT, IExchange::THOUSANDS, IExchange::SYMBOL);
	}

	/**
	 *
	 * @return array
	 */
	protected function getUserProperty() {
		return array();
	}

	// </editor-fold>

	/**
	 * setup of proxy
	 * @param CUrl $curl
	 * @return void
	 */
	protected function setProxy(CUrl $curl) {
		if (self::$proxyName !== NULL) {
			$curl->setOptions(array(
					CURLOPT_PROXY => self::$proxyName,
					CURLOPT_PROXYPORT => self::$proxyPort,
					CURLOPT_PROXYUSERPWD => self::$proxyAuth,
			));
		}
	}

	/**
	 * setup number formating for later use
	 * @param string $code -UPPERCASE code
	 * @return array
	 */
	protected function createFormat($code) {
		$numFormat = array('1', '1 ' . $code);

		if (!isset($this->defaultCurrency[$code])) {//neexistuje vubec nic
			$numFormat = array_merge($numFormat, $this->defaultFormat);
		} elseif (is_array($this->defaultCurrency[$code]) && isset($this->defaultCurrency[$code][1])) {//formatovani existuje
			$numFormat = $this->defaultCurrency[$code];
			array_unshift($numFormat, 1);
		} else {
			$numFormat[1] = $this->defaultCurrency[$code];
			$numFormat = array_merge($numFormat, $this->defaultFormat);
		}
		$numFormat[5] = $this->setSymbol($numFormat[1]);

		return array_combine($this->getStandardProperty(), $numFormat);
	}

	/**
	 * create symbol of currency
	 * @param string $string
	 * @return string
	 */
	protected function setSymbol($string) {
		return str_replace($this->rFound, '', $string);
	}


}
