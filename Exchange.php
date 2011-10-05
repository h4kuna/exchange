<?php

namespace Exchange;

use Nette;

/**
 * PHP > 5.3
 *
 * @author Milan Matějček
 * @since 2009-06-22 - version 0.5
 * @version 2.5
 * @property-read $context
 * @property-read $date
 */
class Exchange extends \ArrayIterator implements IExchange {

	/**
	 * number of version
	 * @var string
	 */
	private static $version = FALSE;

	/** @var Nette\Utils\Html */
	private static $href;

	/**
	 * param in url for change value
	 */
	const PARAM_CURRENCY = 'currency';
	const PARAM_VAT = 'vat';


//-----------------config section-----------------------------------------------
	/**
	 * default money on web, must be UPPER
	 * @var string
	 */
	protected $default = self::CZK;

	/** @var Storage */
	protected $storage;

	/** @var Download */
	protected $download;

	/**
	 * show actual money for web and is first in array, must be UPPER
	 * @var string
	 */
	protected $web;

	/** @var bool */
	protected $globalVat = FALSE;

	/**
	 * vat, only prefered value [%]
	 * @example 20% = 1.20
	 * @var real
	 */
	protected $vat = 1.2;

//------------------------------------------------------------------------------

	/**
	 * last working value
	 * @var array
	 */
	protected $lastChange = array(NULL, NULL);

	/** @var DateTime */
	private $date;

	/** @var Nette\DI\IContainer */
	private $context;

	public function __construct(Nette\DI\IContainer $context) {
		parent::__construct();
		$this->context = $context;
		$this->download();
		$this->session();
		$this->loadCurrencies();
	}

	/**
	 * return property of currency
	 * @example ->getSymbol(); ->getSymbol('usd');
	 * @param string $name
	 * @param strong $args
	 * @return mixed
	 */
	public function __call($name, $args) {
		$n = \strtolower(\substr($name, 3));
		if ($this->download->getProperty($n)) {
			return $this->getCurrency(!isset($args[0]) ? $this->key : $args[0], $n);
		}
		throw new ExchangeException('Call undefined method ' . $name);
	}

	/** set date for download */
	public function setDate($date = NULL) {
		$this->date = $date instanceof \DateTime ? $date : new \DateTime($date);
		$this->download($this->date);
	}

	/**
	 * @param string $code
	 * @param key property of currency
	 * @return Nette\Utils\Html
	 */
	public function currencyLink($code, $key = self::SYMBOL) {
		$code = $this->loadCurrency($code);
		$a = self::getHref();
		if (isset($this[$code][$key])) {
			$a->setText($this[$code][$key]);
		} else {
			$a->setText($code);
		}

		if ($this->web === $code) {
			$a->class = 'current';
		}
		return $a->href(NULL, array(self::PARAM_CURRENCY => $code));
	}

	/**
	 * create link for vat
	 * @param string $textOn
	 * @param string $textOff
	 * @return Nette\Utils\Html
	 */
	public function vatLink($textOn, $textOff) {
		$a = self::getHref();
		$a->href(NULL, array(self::PARAM_VAT => !$this->globalVat));
		if ($this->globalVat) {
			$a->setText($textOff);
		} else {
			$a->setText($textOn);
		}
		return $a;
	}

	/**
	 * array for form to addSelect
	 * @param string $key
	 * @return array
	 */
	public function selectInput($key = self::SYMBOL) {
		$out = array();
		foreach ($this as $k => $v) {
			$out[$k] = isset($v[$key])? $v[$key]: $k;
		}
		return $out;
	}

	/**
	 * create helper to template
	 */
	public function registerAsHelper() {
		$tpl = $this->context->application->template;
		$tpl->registerHelper('formatVat', callback($this, 'formatVat'));
		$tpl->registerHelper('format', callback($this, 'format'));
		$tpl->exchange = $this;
	}

	/**
	 * transfer number by exchange rate
	 * @param double|int|string $price number
	 * @param string $from default currency
	 * @param string $to output currency
	 * @param int $round number round
	 * @return double
	 */
	public function change($price, $from=FALSE, $to=FALSE, $round=FALSE) {
		if (is_string($price)) {
			$price = (double) Download::stroke2point($price);
		}

		$from = (!$from) ? $this->default : $this->loadCurrency($from);
		$to = (!$to) ? $this->web : $this->loadCurrency($to);
		$price = $this[$to][self::RATE] / $this[$from][self::RATE] * $price;

		if ($round !== FALSE) {
			$price = round($price, $round);
		}

		return $price;
	}

	/**
	 * count, format price and set vat
	 * @param number $number price
	 * @param string|bool $from TRUE currency doesn't counting, FALSE set actual
	 * @param string $to output currency, FALSE set actual
	 * @param bool|real $vat use vat, but get vat by method $this->formatVat(), look at to globatVat upper
	 * @return number string
	 */
	public function format($number, $from=NULL, $to=NULL, $vat=FALSE) {
		if ($to != FALSE) {
			$old = $this->web;
			$to = $this->loadCurrency($to);
			$this->web = $to;
		}

		if ($from !== TRUE) {
			$number = $this->change($number, $from, $to);
		}

		$getVat = FALSE;
		if ($vat === FALSE) {
			$vat = $this->vat;
		} elseif ($vat === TRUE) {
			$getVat = TRUE;
			$vat = $this->vat;
		} else {
			$vat = (double) $vat;
		}

		$withVat = $number * $vat;

		if ($this->globalVat || $getVat) {
			$number = $withVat;
		}

		$number = $this->numberFormating($number, $this->web);

		if ($to != FALSE) {
			$this->web = $old;
		} else {
			$to = $this->web;
		}

		$this->lastChange = array($withVat, $to);

		return $number;
	}

	/**
	 * before call this method MUST call method format()
	 * formating price only with vat
	 * @return string
	 */
	public function formatVat() {
		return $this->numberFormating($this->lastChange[0], $this->lastChange[1]);
	}

	/**
	 * load currency by code
	 * @param string $code
	 * @return string
	 */
	public function loadCurrency($code) {
		$code = \strtoupper($code);
		if (!$this->offsetExists($code)) {
			$this->offsetSet($code, $this->storage[$code]);
		}
		return $code;
	}

	/**
	 * @param code1, code2, ...
	 * @return array
	 */
	public function loadCurrencies(/* ... */) {
		$codes = \func_get_args();
		if (empty($codes)) {
			$codes = $this->download->getCurrencies();
		} elseif ($codes[0] === TRUE) {
			$codes = $this->getAllCode();
		}

		foreach ($codes as $code) {
			$this->loadCurrency($code);
		}
	}

	// <editor-fold defaultstate="collapsed" desc="getter">
	/**
	 * @param bool $codeSort
	 * @return array
	 */
	public function & getAllCode() {
		return $this->storage->getAllCode();
	}

	/**
	 * return value of array, you can see __call()
	 * @param string $code currency
	 * @param string $key use value of constant upper, rate, code
	 * @return mix
	 */
	public function getCurrency($code=FALSE, $key=FALSE) {
		$code = $code ? $this->loadCurrency($code) : $this->web;
		return (!$key) ? $this[$code] : $this[$code][$key];
	}

	/** @return float */
	public function getVat() {
		return $this->vat;
	}

	/** @return number */
	public function getPercentVat()
	{
		return round(($this->vat - 1) * 100, 2);
	}

	/** @return string */
	public function getWeb() {
		return $this->web;
	}

	/** @return Nette\DI\IContainer */
	public function getContext() {
		return $this->context;
	}

	public function getDefault() {
		return $this->default;
	}

	public function getDate() {
		if (!$this->date)
			$this->setDate();
		return $this->date;
	}

	/**
	 * version of this class
	 * @return string
	 */
	static public function getVersion() {
		if (self::$version === FALSE) {
			$rc = new \ReflectionClass(__CLASS__);
			$found = array();
			preg_match('~@version (.*)~', $rc->getDocComment(), $array);
			self::$version = $array[1];
		}
		return self::$version;
	}

	// </editor-fold>

//-----------------protected
	/**
	 * formating number
	 * @param number $number
	 * @return string
	 */
	protected function numberFormating($number, $to) {
		$change = $this->download->getRchange();
		$change[0] = number_format($number, $this[$to][self::DECIMAL], $this[$to][self::DEC_POINT], $this[$to][self::THOUSANDS]);
		return str_replace($this->download->getRfound(), $change, $this[$to][self::NUM_FORMAT]);
	}

	/**
	 * start download the source
	 * @return void
	 */
	protected function download(\DateTime $date = NULL) {
		if ($this->storage === NULL) {
			$this->storage = new File($this->context->cacheStorage, $date);
		} elseif (!is_object($this->storage)) {
			$this->storage = new $this->storage($this->context->cacheStorage, $date);
		}

		if ($this->download === NULL) {
			$this->download = new CnbDay($this->storage, $this->default, $date);
		} elseif (!is_object($this->download)) {
			$this->download = new $this->download($this->storage, $this->default, $date);
		}

		if (!($this->download instanceof IDownload)) {
			throw new ExchangeException('Class for download must be instance of ' . __NAMESPACE__ . '\IDownload.');
		}

		if (!$this->storage->needUpdate())
			return;

		$this->download->downloading();
	}

	/**
	 * setup session
	 */
	protected function session($expiration = '+30 days') {
		$session = $this->context->session->getSection(__NAMESPACE__);
		$session->setExpiration($expiration);
		$request = $this->context->httpRequest;

		//-------------crrency------------------------------------------------------
		$qVal = strtoupper($request->getQuery(self::PARAM_CURRENCY));
		$currency = $this->storage[$qVal];
		if (empty($currency)) {
			$qVal = NULL;
		} else {
			$this->offsetSet($qVal, $currency);
		}

		if ($qVal) {
			$session->currency = $qVal;
		} elseif (!isset($session->currency)) {
			$session->currency = is_null($this->web) ? $this->default : $this->web;
		}
		$this->web = $session->currency;

		//-------------vat----------------------------------------------------------
		$qVal = $request->getQuery(self::PARAM_VAT);
		if ($qVal !== NULL) {
			$this->globalVat = $session->vat = (bool) $qVal;
		} elseif (!isset($session->vat)) {
			$session->vat = $this->globalVat;
		}
	}

	/** @return Nette\Utils\Html */
	protected static function getHref() {
		if (!self::$href)
			self::$href = Nette\Utils\Html::el('a');
		return clone self::$href;
	}

}
