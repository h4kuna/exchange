<?php

namespace h4kuna\Exchange\RB;

use h4kuna\Exchange\Download;
use h4kuna\CUrl;
use h4kuna\Exchange\CurrencyProperty;

/**
 * Raiffeisenbank
 *
 * @author Milan Matějček
 */
class Day extends Download {

    const URL = 'http://www.rb.cz/views/components/rates/ratesXML.jsp';
    const SALE = 'XML_RATE_TYPE_EBNK_SALE_VALUTA';
    const PURCHASE = 'XML_RATE_TYPE_EBNK_PURCHASE_VALUTA';
    const SALE_DEVIZA = 'XML_RATE_TYPE_EBNK_SALE_DEVIZA';
    const PURCHASE_DEVIZA = 'XML_RATE_TYPE_EBNK_PURCHASE_DEVIZA';
    const MIDDLE = 'XML_RATE_TYPE_EBNK_MIDDLE';

    /** @var string */
    private $namespace;

    const NODE = 'currency';

    public function __construct($xmlNS = self::MIDDLE) {
        $this->namespace = $xmlNS;
    }

    protected function createCurrencyProperty($row) {
        $foreing = $row->getAttribute('rate');
        if (!$foreing) {
            return NULL;
        }
        $quota = $row->getAttribute('quota');

        return new CurrencyProperty($quota, $row->getAttribute('name'), $this->makeCorrection($quota, $foreing));
    }

    /**
     * Load data from RB
     *
     * @return array
     * @throws \h4kuna\Exchange\ExchangeException
     */
    protected function loadData() {
        $data = CUrl::download(self::URL);
        $doc = new \DOMDocument;
        $doc->loadXML($data);
        foreach ($doc->getElementsByTagName('exchange_rate') as $v) {
            if ($v->getAttribute('type') == $this->namespace) {
                $v->appendChild($doc->importNode($this->czk()));
                return $v->getElementsByTagName(self::NODE);
            }
        }

        throw new \h4kuna\Exchange\ExchangeException('Namespace not found: ' . $this->namespace);
    }

    /**
     *
     * @return \DOMElement
     */
    private function czk() {
        $doc = new \DOMDocument;
        $doc->loadXML('<' . self::NODE . ' name="' . \h4kuna\Exchange\Exchange::CZK . '" quota="1" rate="1"/>');
        return $doc->getElementsByTagName(self::NODE)->item(0);
    }

    public function getPrefix() {
        return NULL;
    }

}
