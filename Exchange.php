<?php

namespace h4kuna;

use Nette,
    Nette\Http\SessionSection,
    Nette\Http\Request,
    Nette\Utils\Html;

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

    const WITH_CURRENCY = 2;
    const SWAP_RATE = 1;

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

    /** @var Storage */
    private $storage;

    /** @var Download */
    private $download;

    /** @var NumberFormat */
    private $number;

    /** @var SessionSection */
    private $session;

    /** @var Request */
    private $request;

    /** @var Tax */
    private $vat;

    public function __construct(Storage $storage, Request $request, SessionSection $session) {
        parent::__construct();
        $this->storage = $storage;
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
        return ($name) ? $this[$code]->$name : $this[$code];
    }

    /**
     * 1.2 or 20 or 0.2
     * @param number $vat
     * @param bool $in
     * @param bool $out
     * @return \h4kuna\Exchange
     */
    public function setVat($vat = 21, $in = TRUE, $out = TRUE) {
        $this->vat = new Tax($vat);
        $this->vat->setVatIO($in, $out);
        return $this;
    }

    /**
     * @param \h4kuna\Download $obj
     * @return \h4kuna\Exchange
     */
    public function setDownload(Download $obj) {
        $this->download = $obj;
        return $this;
    }

    /**
     * @param \h4kuna\NumberFormat $obj
     * @return \h4kuna\Exchange
     */
    public function setDefaulFormat(NumberFormat $obj) {
        $this->number = $obj;
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
        $a->setText(($symbol) ? $this[$code]->profil->symbol : $code);

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
     * @param string|FALSE $from default currency, FALSE no transfer
     * @param string $to output currency
     * @param int $round number round
     * @return double
     */
    public function change($price, $from = NULL, $to = NULL, $round = NULL, $vat = NULL) {
        $_price = new Float($price);
        $price = $_price->getValue();
        $to = $to ? $this->loadCurrency($to) : $this->getWeb();

        if ($from !== FALSE) {
            $from = $from ? $this->loadCurrency($from) : $this->getDefault();
            if ($to != $from) {
                $price *= $this[$to]->getRate() / $this[$from]->getRate();
            }
        }

        $this->lastChange[0] = $vat;
        $this->lastChange[1] = $this[$to]->profil->setNumber($price);

        $price = $this->getVat()->taxation($price, $vat);

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
        $to = $to ? $this->loadCurrency($to) : $this->getWeb();
        $number = $this->change($number, $from, $to, NULL, $vat);
        return $this[$to]->profil->render($number);
    }

    /**
     * before call this method MUST call method format()
     * formating price only with vat
     * @return string
     */
    public function formatVat() {
        return $this->lastChange[1]->setNumber($this->vat->withVat($this->lastChange[1]->getNumber(), $this->lastChange[0]))->render();
    }

    /**
     * auto upper
     * @param string $code
     * @return Currency
     */
    public function offsetGet($code) {
        return parent::offsetGet(strtoupper($code));
    }

    /**
     * load currency by code
     * @param string $code
     * @return string
     */
    public function loadCurrency($code, $property = array()) {
        $code = ($code) ? strtoupper($code) : $this->getWeb();

        if (!$this->offsetExists($code)) {
            if (!$this->default) {
                $this->default = $code;
                $this->init();
            }
            if ($this->storage[$code]) {
                $this->offsetSet($code, $this->storage[$code]);
            } else {
                return $this->getDefault();
            }
        }

        if ($property || !isset($this[$code]->profil)) {
            if (!$property) {
                $profil = $this->getDefaultFormat();
                $profil->setSymbol($code);
            } elseif (is_array($property)) {
                $profil = $this->getDefaultFormat();
                foreach ($property as $k => $v) {
                    $k = 'set' . ucfirst($k);
                    $profil->$k($v);
                }
            } elseif ($property instanceof NumberFormat) {
                $profil = $property;
            }

            $this[$code]->profil = $profil;
        }

        return $code;
    }

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

// <editor-fold defaultstate="collapsed" desc="getter">
    /** @return Download */
    public function getDownload() {
        if (!$this->download) {
            $this->download = new CnbDay;
        }
        return $this->download;
    }

    /** @var NumberFormat */
    public function getDefaultFormat() {
        if (!$this->number) {
            $this->number = new NumberFormat;
        }

        return clone $this->number;
    }

    /** @return string */
    public function getWeb() {
        if (!$this->web) {
            $this->web = $this->getDefault();
        }
        return $this->web;
    }

    /** @return Tax */
    public function getVat() {
        if (!$this->vat) {
            $this->setVat();
        }
        return $this->vat;
    }

    /** @var string */
    public function getDefault() {
        if (!$this->default) {
            return $this->loadCurrency(self::CZK); // nenastavovat default!!
        }
        return $this->default;
    }

// </editor-fold>
//-----------------protected
    protected function init() {
        $this->getDownload()->setDefault($this->getDefault());
        $this->download();
        $this->session();
    }

    /**
     * start download the source
     * @return void
     */
    protected function download() {
        if (!isset($this->storage[$this->getDefault()])) {
            $this->storage->import($this->getDownload()->downloading(), $this->getDefault());
        }
    }

    /**
     *
     * @param code $code
     * @param bool $session
     * @return \h4kuna\Exchange
     */
    public function setWeb($code, $session = FALSE) {
        $this->web = $this->loadCurrency($code);
        if ($session) {
            $this->session->currency = $this->web;
        }
        return $this;
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
            $this->session->currency = $this->getWeb();
        }
        $this->web = $this->session->currency;

//-------------vat
        $qVal = $this->request->getQuery(self::PARAM_VAT);
        if ($qVal !== NULL) {
            $this->session->vat = (bool) $qVal;
            if ($qVal) {
                $this->getVat()->vatOn();
            } else {
                $this->getVat()->vatOff();
            }
        } elseif (!isset($this->session->vat)) {
            $this->session->vat = $this->getVat()->isVatOn();
        }
    }

    /** @return Nette\Utils\Html */
    protected static function getHref() {
        if (!self::$href)
            self::$href = Html::el('a');
        return clone self::$href;
    }

    public function addTempRate($rate, $code = NULL) {
        $code = $this->loadCurrency($code);
        $this[$code]->setTempRate($rate);
        return $this;
    }

    public function removeTempRate($code) {
        return $this[$code]->removeTempRate();
    }

}

