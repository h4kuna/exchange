<?php

namespace h4kuna\Exchange;

use ArrayIterator;
use DateTime;
use h4kuna\Exchange\Currency\IProperty;
use h4kuna\Exchange\Driver\Download;
use h4kuna\Exchange\Storage\IWarehouse;
use h4kuna\INumberFormat;
use h4kuna\NumberFormat;
use h4kuna\Tax;
use h4kuna\Vat;
use Nette\Http\Request;
use Nette\Http\SessionSection;
use Nette\Reflection\Property;
use Nette\Templating\Template;
use Nette\Utils\Html;

/**
 *
 * @author Milan Matějček
 * @since 2009-06-22 - version 0.5
 * @property string $default
 * @property string $web
 */
class Exchange extends ArrayIterator {

    /**
     * Czech currency code
     */
    const CZK = 'CZK';

    /**
     * Param in url for change value
     *
     * @var string
     */
    private static $paramCurrency = 'currency';

    /** @var string */
    private static $paramVat = 'vat';

    /** @var Html */
    private static $href;

    /**
     * History instances
     *
     * @var array
     */
    private static $history = array();

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

    /** @var Tax */
    protected $tax;

    /**
     * Last changed value
     *
     * @var INumberFormat
     */
    private $lastChange;

    /** @var IWarehouse */
    private $warehouse;

    /** @var NumberFormat */
    private $number;

    /** @var SessionSection */
    private $session;

    /** @var Request */
    private $request;

// </editor-fold>

    public function __construct(IWarehouse $warehouse, Request $request, SessionSection $session) {
        parent::__construct();
        $this->warehouse = $warehouse;
        $this->request = $request;
        $this->session = $session;
        self::$history[$warehouse->getName()] = $this;
    }

// <editor-fold defaultstate="collapsed" desc="Setters">

    /**
     * Set default "from" currency
     *
     * @param string|Property $code
     * @return Exchange
     */
    public function setDefault($code) {
        if (is_string($code)) {
            $code = $this->offsetGet($code);
        } elseif (!($code instanceof IProperty)) {
            throw new ExchangeException('Bad declaration of default currency. Use object or currency code.');
        }
        $this->default = $code;
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
     *
     * @param DateTime $date
     * @return Exchange
     */
    public function setDate(DateTime $date) {
        $warehouse = $this->warehouse->setDate($date);
        return $this->bindMe($warehouse);
    }

    /**
     *
     * @param Download $driver
     * @return Exchange
     */
    public function setDriver(Download $driver) {
        $warehouse = $this->warehouse->setDriver($driver);
        return $this->bindMe($warehouse);
    }

    /**
     * Currency param in url
     *
     * @param string $str
     * @return Exchange
     */
    public function setParamCurrency($str) {
        self::$paramCurrency = $str;
        return $this;
    }

    /**
     * VAT param in url
     *
     * @param string $str
     * @return Exchange
     */
    public function setParamVat($str) {
        self::$paramVat = $str;
        return $this;
    }

    /**
     * Set global VAT
     *
     * @param type $v
     * @param bool $in
     * @param bool $out
     * @return Exchange
     */
    public function setVat($v, $in, $out) {
        $this->tax = new Tax($v);
        $this->tax->setVatIO($in, $out);
        $this->loadParamVat();
        return $this;
    }

    /**
     * Set currency "to"
     *
     * @param string $code
     * @param bool $session
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
     * @return IProperty
     * @throws ExchangeException
     */
    public function offsetGet($index) {
        if ($index instanceof IProperty) {
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
     * @param IProperty $currency
     * @param bool $symbol
     * @return Html
     */
    public function currencyLink(IProperty $currency, $symbol = TRUE) {
        $code = $currency->getCode();
        $a = self::getHref();
        $a->setText($symbol ? $currency->getFormat()->getSymbol() : $code);

        if ($this->getWeb() === $code) {
            $a->class = 'current';
        }
        return $a->href(NULL, array(self::$paramCurrency => $code));
    }

    /**
     * Create link for vat
     * 
     * @param string $textOn
     * @param string $textOff
     * @return Html
     */
    public function vatLink($textOn, $textOff) {
        $a = self::getHref();
        $isVatOn = $this->tax->isVatOn();
        $a->href(NULL, array(self::$paramVat => !$isVatOn));
        if ($isVatOn) {
            $a->setText($textOff);
        } else {
            $a->setText($textOn);
        }
        return $a;
    }

    /**
     * Create helper to template
     * @todo predelat callback
     * @param Template $tpl
     */
    public function registerAsHelper(Template $tpl) {
        $tpl->registerHelper('formatVat', callback($this, 'formatVat'));
        $tpl->registerHelper('currency', callback($this, 'format'));
        $tpl->exchange = $this;
    }

    /** @return Html */
    private static function getHref() {
        if (!self::$href) {
            self::$href = Html::el('a');
        }
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
     * @param int|float|Vat $vat
     * @return float|NULL
     */
    public function change($price, $from = NULL, $to = NULL, $round = NULL, $vat = NULL) {
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

        if ($this->tax) {
            $price = $this->tax->taxation($price, $vat);
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
     * @param int|float|Vat $vat
     * @return string
     */
    public function format($number, $from = NULL, $to = NULL, $vat = NULL) {
        $to = $this->offsetGet($to ? $to : $this->getWeb());
        $number = $this->change($number, $from, $to, NULL, $vat);
        $this->lastChange = $to->getFormat();
        return $this->lastChange->render($number);
    }

    /**
     *
     * @param float $number
     * @param string|FALSE $to
     * @param int|float|Vat $vat
     * @return string
     */
    public function formatTo($number, $to, $vat = NULL) {
        return $this->format($number, NULL, $to, $vat);
    }

    /**
     * Price with VAT every time
     *
     * @return string
     */
    public function formatVat() {
        $number = $this->lastChange->getNumber();
        if ($this->tax->isVatOn()) {
            return $this->lastChange->render($number);
        }
        $this->tax->vatOn();
        $number = $this->lastChange->render($this->tax->taxation($number));
        $this->tax->vatOff();
        return $number;
    }

    /**
     * LoadAll currencies in storage
     *
     * @return Exchange
     */
    public function loadAll() {
        foreach ($this->warehouse->getListCurrencies() as $code) {
            if (!$this->offsetExists($code)) {
                $this->loadCurrency($code);
            }
        }

        return $this;
    }

    /**
     * Load currency by code
     *
     * @param string $code
     * @return IProperty
     */
    public function loadCurrency($code, $property = array()) {
        try {
            $currency = $this->warehouse->loadCurrency($code);
            if (!$this->default) {
                $this->setDefault($currency);
            }
        } catch (ExchangeException $e) {
            if (!$this->default) {
                throw new ExchangeException('Let\'s define possible currency code. Not this: ' . $code);
            }
            $currency = $this->default;
        }

        $code = $currency->getCode();

        if ($property || !isset($this[$code])) {
            if (!$property) {
                $profil = $this->getDefaultFormat();
                $profil->setSymbol($code);
            } elseif (is_array($property)) {
                $profil = $this->getDefaultFormat();
                $profil->setSymbol($code);
                foreach ($property as $k => $v) {
                    $k = 'set' . ucfirst($k);
                    $profil->$k($v);
                }
            } else {
                $profil = $property;
            }

            if (!($profil instanceof INumberFormat)) {
                throw new ExchangeException('Property of currency must be array or instance of INumberFormat');
            }

            $this[$code] = $currency->setFormat($profil);
            $this->loadParamCurrency($code);
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
        return $this;
    }

// </editor-fold>
// <editor-fold defaultstate="collapsed" desc="Getters">

    /** @var Property */
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
        if (!$this->tax) {
            $this->setVat();
        }
        return $this->tax->getVat()->getPercent();
    }

    /** @return string */
    public function getWeb() {
        if (!$this->web) {
            $this->web = $this->getDefault();
        }
        return $this->web;
    }

// </editor-fold>
// <editor-fold defaultstate="collapsed" desc="Session setup">
    /**
     * Set currency from query and set local property and save to session
     */
    protected function loadParamCurrency($code) {
        try {
            $currency = $this->offsetGet($this->request->getQuery(self::$paramCurrency, $this->session->currency))->getCode();
            if ($code == $currency) {
                $this->setWeb($currency, TRUE);
            }
        } catch (ExchangeException $e) {
            
        }
    }

    /**
     * Set VAT from query and set local property and save to session
     */
    protected function loadParamVat() {
        $session = isset($this->session->vat) ? $this->session->vat : $this->tax->isVatOn();
        $this->session->vat = $vat = (bool) $this->request->getQuery(self::$paramVat, $session);
        if ($vat) {
            $this->tax->vatOn();
        } else {
            $this->tax->vatOff();
        }
    }

    /**
     *
     * @param IWarehouse $warehouse
     * @return Exchange
     */
    private function bindMe(IWarehouse $warehouse) {
        $key = $warehouse->getName();
        if (isset(self::$history[$key])) {
            return self::$history[$key];
        }

        $exchange = new static($warehouse, $this->request, $this->session);
        $exchange->setDefaulFormat($this->getDefaultFormat());
        foreach ($this as $key => $v) {
            $exchange->loadCurrency($key, $v->getFormat());
        }

        $exchange->tax = $this->tax;

        return self::$history[$key] = $exchange;
    }

// </editor-fold>
}
