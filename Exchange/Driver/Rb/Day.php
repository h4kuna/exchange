<?php

namespace h4kuna\Exchange\Driver\Rb;

use DateTime;
use DOMDocument;
use DOMElement;
use h4kuna\CUrl;
use h4kuna\Exchange\Currency\Property;
use h4kuna\Exchange\Driver\Download;
use h4kuna\Exchange\Utils;
use h4kuna\Exchange\ExchangeException;

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

    protected function createProperty($row) {
        return new Property($row->getAttribute('rate'), $row->getAttribute('name'), $row->getAttribute('quota'));
    }

    /**
     * Load data from RB
     *
     * @param DateTime $date
     * @return array
     * @throws ExchangeException
     */
    protected function loadFromSource(DateTime $date = NULL) {
        $data = CUrl\CurlBuilder::download($this->createUrl(self::URL, $date));
        $doc = new DOMDocument;
        $doc->loadXML($data);
        foreach ($doc->getElementsByTagName('exchange_rate') as $v) {
            if ($v->getAttribute('type') == $this->namespace) {
                $v->appendChild($doc->importNode($this->czk()));
                return $v->getElementsByTagName(self::NODE);
            }
        }

        throw new ExchangeException('Namespace not found: ' . $this->namespace);
    }

    /**
     *
     * @return DOMElement
     */
    private function czk() {
        $doc = new DOMDocument;
        $doc->loadXML('<' . self::NODE . ' name="' . Utils::CZK . '" quota="1" rate="1"/>');
        return $doc->getElementsByTagName(self::NODE)->item(0);
    }

    /**
     *
     * @param string $url
     * @param DateTime $date
     */
    protected function createUrlDay($url, DateTime $date) {
        return $url . '?' . $date->format('\d\a\y=j&\m\o\n\t\h=n&\y\e\a\r=Y');
    }

}
