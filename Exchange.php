<?php

namespace h4kuna;

use Nette,
    Nette\Http\SessionSection,
    Nette\Http\Request,
    Nette\Utils\Html;

require_once 'libs/Download.php';
require_once 'libs/Storage.php';
require_once 'libs/ExchangeException.php';
require_once 'libs/IExchange.php';

/**
 * PHP > 5.3
 *
 * @author Milan Matějček
 * @since 2009-06-22 - version 0.5
 * @property-read $default
 * @property $date
 * @property $vat
 */
class Exchange extends \ArrayIterator implements IExchange {

    /** @var Html */
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
    private $default;

    /**
     * show actual money for web and is first in array, must be UPPER
     * @var string
     */
    private $web;

//------------------------------------------------------------------------------

    /**
     * last working value
     * @var array
     */
    protected $lastChange = array(NULL, NULL);

    /** @var DateTime */
    private $date;

    /** @var Storage */
    private $storage;

    /** @var Download */
    private $download;

    /** @var Money */
    private $number;

    /** @var SessionSection */
    private $session;

    /** @var Request */
    private $request;

    /** @var array */
    private $tempRate = array('enable' => FALSE);

    public function __construct(Storage $storage, Request $request, SessionSection $session, Money $number = NULL, Download $download = NULL) {
        parent::__construct();
        $this->storage = $storage;
        $this->download = $download ? $download : new CnbDay;
        if ($this->number) {
            $this->number = $number;
        } else {
            $this->number = new Money();
            $this->setVat();
        }
        $this->session = $session;
        $this->request = $request;
    }

    /**
     * return property of currency
     * @example usdProfil
     * @param string $name
     * @param strong $args
     * @return mixed
     */
    public function __get($name) {
        $code = $this->loadCurrency(substr($name, 0, 3));
        $name = strtolower(substr($name, 3));
        return ($name) ? $this[$code][$name] : $this[$code];
    }

    /**
     *
     * @param string $code
     * @param float $rate
     * @return \h4kuna\Exchange
     * @throws ExchangeException
     */
    public function setTempRate($code, $rate) {
        $code = $this->loadCurrency($code);
        if ($code == $this->default) {
            throw new ExchangeException('You can\'t setup temp rate for default currency.');
        }

        $this->tempRate = array(
            'code' => $code,
            'rate' => $rate,
            'enable' => FALSE
        );
        return $this;
    }

    /**
     * enable temp value
     * @param string $code
     * @param number $rate
     * @throws ExchangeException
     */
    public function onTempRate($code = NULL, $rate = NULL) {
        if ($code && $rate) {
            $this->setTempRate($code, $rate);
        } elseif (empty($this->tempRate['code'])) {
            throw new ExchangeException('You forgot set up code and rate.');
        }

        $this->_setTempRate(TRUE);
    }

    /**
     * disable temp value
     * @return \h4kuna\Exchange
     */
    public function offTempRate() {
        if ($this->tempRate['enable'] === TRUE) {
            $this->_setTempRate(FALSE);
        }
        return $this;
    }

    private function _setTempRate($bool) {
        $this->tempRate['enable'] = $bool;
        self::swap($this->tempRate['rate'], $this[$this->tempRate['code']][self::RATE]);
        self::swap($this->tempRate['code'], $this->web);
    }

    /**
     * 1.2 or 20 or 0.2
     * @param number $vat
     * @param bool $in
     * @param bool $out
     * @return \h4kuna\Exchange
     */
    public function setVat($vat = 21, $in = TRUE, $out = TRUE) {
        $this->number->setVat($vat)->setVatIO($in, $out);
        return $this;
    }

//-----------------methods for template
    /**
     * @param string $code
     * @param bool $symbol
     * @return Nette\Utils\Html
     */
    public function currencyLink($code, $symbol = TRUE) {
        $code = $this->loadCurrency($code);
        $a = self::getHref();
        $a->setText(($symbol) ? $this[$code]['profil']->symbol : $code);

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
        $isVatOn = $this->number->isVatOn();
        $a->href(NULL, array(self::PARAM_VAT => !$isVatOn));
        if ($isVatOn) {
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
            $out[$k] = isset($v[$key]) ? $v[$key] : $k;
        }
        return $out;
    }

    /**
     * create helper to template
     */
    public function registerAsHelper(Nette\Templating\Template $tpl) {
        $tpl->registerHelper('formatVat', callback($this, 'formatVat'));
        $tpl->registerHelper('currency', callback($this, 'format'));
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
    public function change($price, $from = NULL, $to = NULL, $round = NULL) {
        return $this->_change($price, $from, $to, $round);
    }

    /**
     * transfer number by exchange rate
     * @param double|int|string $price number
     * @param string $from default currency
     * @param string $to output currency
     * @param int $round number round
     * @return double
     */
    private function _change($price, &$from = NULL, &$to = NULL, $round = NULL) {
        $_price = new Float($price);

        $from = (!$from) ? $this->getDefault() : $this->loadCurrency($from);
        $to = (!$to) ? $this->getWeb() : $this->loadCurrency($to);
        $price = $this[$to][self::RATE] / $this[$from][self::RATE] * $_price->getValue();

        if ($round !== NULL) {
            $price = round($price, $round);
        }

        return $price;
    }

    /**
     * count, format price and set vat
     * @param number $number price
     * @param string|bool $from FALSE currency doesn't counting, NULL set actual
     * @param string $to output currency, NULL set actual
     * @param bool|real $vat use vat, but get vat by method $this->formatVat(), look at to globatVat upper
     * @return number string
     */
    public function format($number, $from = NULL, $to = NULL, $vat = NULL) {
        $old = $this->getWeb();
        if ($to) {
            $this->web = $to = $this->loadCurrency($to);
        }

        if ($from !== FALSE) {
            $number = $this->_change($number, $from, $to);
        }

        $vat = $this->lastChange[0] = ($vat === NULL) ? $this->number->getVat() : Vat::create($vat);
        $out = $this->lastChange[1] = $this[$to]['profil'];

        $out->setNumber($number);

        if ($to !== FALSE) {
            $this->web = $old;
        } else {
            $to = $this->getWeb();
        }

        return $this->number->render($out, $vat);
    }

    /**
     * before call this method MUST call method format()
     * formating price only with vat
     * @return string
     */
    public function formatVat() {
        return $this->number->withVat($this->lastChange[1], $this->lastChange[0]);
    }

    /**
     * load currency by code
     * @param string $code
     * @return string
     */
    public function loadCurrency($code, $property = array()) {
        $code = strtoupper($code);

        if (!$this->offsetExists($code)) {
            if (!$this->default) {
                $this->default = $code;
                $this->init();
            }
            $this->offsetSet($code, $this->storage[$code]);
        }

        if ($property || !isset($this[$code]['profil'])) {
            if (!$property) {
                $profil = $this->getDefaultProfile();
                $profil->setSymbol($code);
            } elseif (is_array($property)) {
                $profil = $this->getDefaultProfile();
                foreach ($property as $k => $v) {
                    $profil->{$k} = $v;
                }
            } elseif ($property instanceof NumberFormat) {
                $profil = $property;
            }

            $this[$code]['profil'] = $profil;
        }

        return $code;
    }

// <editor-fold defaultstate="collapsed" desc="getter">
    /**
     * @return Exchange
     */
    public function loadAll() {
        $code = $this->getDefault();
        do {
            $this->loadCurrency($code);
            $code = $this[$code]['next'];
        } while ($code);
        return $this;
    }

    /** @return string */
    public function getWeb() {
        if ($this->web === NULL) {
            return $this->getDefault();
        }
        return $this->web;
    }

    public function getDefault() {
        if ($this->default === NULL) {
            return $this->loadCurrency(self::CZK);
        }
        return $this->default;
    }

    public function getDate() {
        if (!$this->date) {
            $this->setDate();
        }
        return $this->date;
    }

// </editor-fold>
//-----------------protected
    protected function init() {
        $this->download->setDefault($this->getDefault());
        $this->download();
        $this->session();
    }

    /**
     * start download the source
     * @return void
     */
    protected function download() {
        if (!isset($this->storage[$this->getDefault()])) {
            $this->storage->import($this->download->downloading(), $this->getDefault());
        }
    }

    /**
     * setup session
     */
    protected function session() {
//-------------crrency
        $qVal = strtoupper($this->request->getQuery(self::PARAM_CURRENCY));
        if (!isset($this->storage[$qVal])) {
            $qVal = NULL;
        }

        if ($qVal) {
            $this->session->currency = $qVal;
        } elseif (!isset($this->session->currency)) {
            $this->session->currency = is_null($this->web) ? $this->getDefault() : $this->getWeb();
        }
        $this->web = $this->session->currency;

//-------------vat
        $qVal = $this->request->getQuery(self::PARAM_VAT);
        if ($qVal !== NULL) {
            $this->session->vat = (bool) $qVal;
            if ($qVal) {
                $this->number->vatOn();
            } else {
                $this->number->vatOff();
            }
        } elseif (!isset($this->session->vat)) {
            $this->session->vat = $this->number->isVatOn();
        }
    }

    /** @return Nette\Utils\Html */
    protected static function getHref() {
        if (!self::$href)
            self::$href = Html::el('a');
        return clone self::$href;
    }

    protected function getDefaultProfile() {
        return clone $this->number;
    }

    private static function swap(&$a, &$b) {
        $c = $a;
        $a = $b;
        $b = $c;
    }

}

