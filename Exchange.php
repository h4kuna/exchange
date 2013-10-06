<?php

namespace h4kuna\Exchange;

use Nette;
use Nette\Http\SessionSection;
use Nette\Http\Request;
use Nette\Utils\Html;
use h4kuna\INumberFormat;
use h4kuna\NumberFormat;

/**
 * PHP > 5.3
 *
 * @author Milan Matějček
 * @since 2009-06-22 - version 0.5
 * @property-read $default
 * @property $date
 * @property $vat
 */
class Exchange extends \ArrayIterator {

    /**
     * Czech currency code
     */
    const CZK = 'CZK';

    /**
     * Param in url for change value
     */
    const PARAM_CURRENCY = 'currency';
    const PARAM_VAT = 'vat';

    /**
     * Default currency "from" input
     *
     * @var string
     */
    private $default;

    /**
     * Display currency "to" output
     *
     * @var string
     */
    private $web;

// <editor-fold defaultstate="collapsed" desc="Private dependencies">

    /**
     * Last changed value
     *
     * @var INumberFormat
     */
    private $lastChange;

    /** @var Store */
    private $store;

    /** @var NumberFormat */
    private $number;

    /** @var SessionSection */
    private $session;

    /** @var Request */
    private $request;

// </editor-fold>

    public function __construct(Store $store, Request $request, SessionSection $session) {
        parent::__construct();
        $this->store = $store;
        $this->request = $request;
        $this->session = $session;
    }

// <editor-fold defaultstate="collapsed" desc="Setters">

    /**
     * Set default "from" currency
     *
     * @param string $code
     * @return Exchange
     */
    public function setDefault($code) {
        $currency = $this->offsetGet($code);
        $this->default = $currency->getCode();
        return $this;
    }

    /**
     * Set default custom render number
     *
     * @param NumberFormat $nf
     * @return Exchange
     */
    public function setDefaulFormat(INumberFormat $nf) {
        $this->number = $nf;
        return $this;
    }

    /**
     * Set currency "to"
     *
     * @param string $str
     * @return Exchange
     */
    public function setWeb($code, $session = FALSE) {
        $this->web = $this->offsetGet($code)->getCode();
        if ($session) {
            $this->session->currency = $this->web;
        }
        return $this;
    }

// </editor-fold>
// <editor-fold defaultstate="collapsed" desc="ArrayIterator API">
    /**
     * Load currency property
     *
     * @param string $index
     * @return ICurrencyProperty
     * @throws ExchangeException
     */
    public function offsetGet($index) {
        if ($index instanceof ICurrencyProperty) {
            return $index;
        }
        $index = strtoupper($index);
        if ($this->offsetExists($index)) {
            return parent::offsetGet($index);
        }
        throw new ExchangeException('Undefined currency code: ' . $index . ', you must call loadCurrency before.');
    }

// </editor-fold>
// <editor-fold defaultstate="collapsed" desc="Method for template">
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
        $isVatOn = $this->vat->isVatOn();
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

    /** @return Nette\Utils\Html */
    protected static function getHref() {
        if (!self::$href)
            self::$href = Html::el('a');
        return clone self::$href;
    }

// </editor-fold>
// <editor-fold defaultstate="collapsed" desc="Main API">
    /**
     * Transfer number by exchange rate
     *
     * @param float|int|string $price number
     * @param string|FALSE $from default currency, FALSE no transfer
     * @param string $to output currency
     * @param int $round
     * @return float|NULL
     */
    public function change($price, $from = NULL, $to = NULL, $round = NULL) {
        if (!is_numeric($price)) {
            return NULL;
        }

        $to = $this->offsetGet($to ? $to : $this->getWeb());

        if ($from === NULL || $from) {
            $from = $this->offsetGet($from ? $from : $this->getDefault());

            if ($to != $from) {
                $price *= $to->getRate() / $from->getRate();
            }
        }

        if ($round !== NULL) {
            $price = round($price, $round);
        }

        return $price;
    }

    /**
     * Count, format price
     *
     * @param number $number
     * @param string|bool $from FALSE currency doesn't counting, NULL set actual
     * @param string $to output currency, NULL set actual
     * @return string
     */
    public function format($number, $from = NULL, $to = NULL) {
        $to = $this->offsetGet($to ? $to : $this->getWeb());
        $number = $this->change($number, $from, $to);
        $this->lastChange = $to->getFormat();
        return $this->lastChange->render($number);
    }

    /**
     * LoadAll currencies in storage
     *
     * @return Exchange
     */
    public function loadAll() {
        $code = $this->store->loadCode();
        do {
            $currency = $this->loadCurrency($code);
            $code = $currency->getNext();
        } while ($code);
        return $this;
    }

    /**
     * Load currency by code
     *
     * @param string $code
     * @return ICurrencyProperty
     */
    public function loadCurrency($code, $property = array()) {
        try {
            $currency = $this->store->loadCurrency($code);
            if (!$this->default) {
                $this->default = $code;
                $this->loadSession();
            }
        } catch (ExchangeException $e) {
            if (!$this->default) {
                throw new ExchangeException('Let\'s define possible currency code. Not this: ' . $code);
            }
            $currency = $this->store->loadCurrency($this->default);
        }

        $code = $currency->getCode();

        if ($property || !isset($this[$code])) {
            if (!$property) {
                $profil = $this->getDefaultFormat();
                $profil->setSymbol($code);
            } elseif (is_array($property)) {
                $profil = $this->getDefaultFormat();
                foreach ($property as $k => $v) {
                    $k = 'set' . ucfirst($k);
                    $profil->$k($v);
                }
            }

            if (!($profil instanceof INumberFormat)) {
                throw new ExchangeException('Property of currency must be array or instance of INumberFormat');
            }

            $this[$code] = $currency->setFormat($profil);
        }

        return $currency;
    }

    /**
     * Add history rate for rating
     *
     * @param string $code
     * @param float $rate
     * @return Exchange
     */
    public function addHistory($code, $rate) {
        $this->offsetGet($code)->pushRate($rate);
        return $this;
    }

    /**
     * Rwmove history rating
     *
     * @param string $code
     * @return Exchange
     */
    public function removeHistory($code) {
        $this->offsetGet($code)->popRate();
        return $code;
    }

// </editor-fold>
// <editor-fold defaultstate="collapsed" desc="Getters">

    /** @var string */
    public function getDefault() {
        if (!$this->default) {
            throw new ExchangeException('Let\'s define currency by method loadCurrency() and first is default.');
        }
        return $this->default;
    }

    /**
     * Prototype INumberFormat
     *
     * @return INumberFormat
     */
    public function getDefaultFormat() {
        if (!$this->number) {
            $this->number = new NumberFormat;
        }

        return clone $this->number;
    }

    /** @return INumberFormat */
    public function getLastChange() {
        return $this->getLastChange;
    }

    /** @return Tax */
    public function getVat() {
        if (!$this->vat) {
            $this->setVat();
        }
        return $this->vat;
    }

    /** @return string */
    public function getWeb() {
        if (!$this->web) {
            $this->web = $this->getDefault();
        }
        return $this->web;
    }

// </editor-fold>

    /**
     * Setup session
     */
    protected function loadSession() {
//-------------crrency
        $qVal = strtoupper($this->request->getQuery(self::PARAM_CURRENCY));
        if (!isset($this[$qVal])) {
            $qVal = NULL;
        }

        if ($qVal) {
            $this->session->currency = $qVal;
        } elseif (!isset($this->session->currency)) {
            $this->session->currency = $this->getWeb();
        }
        $this->web = $this->session->currency;

//-------------vat
//        $qVal = $this->request->getQuery(self::PARAM_VAT);
//        if ($qVal !== NULL) {
//            $this->session->vat = (bool) $qVal;
//            if ($qVal) {
//                $this->getVat()->vatOn();
//            } else {
//                $this->getVat()->vatOff();
//            }
//        } elseif (!isset($this->session->vat)) {
//            $this->session->vat = $this->getVat();
//        }
    }

}
