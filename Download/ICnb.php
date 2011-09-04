<?php

namespace Exchange;

interface ICnb
{
    /**
     * url where download rating
     * @var const
     */
    const CNB_DAY = 'http://www.cnb.cz/cs/financni_trhy/devizovy_trh/kurzy_devizoveho_trhu/denni_kurz.txt';
    const CNB_DAY2 = 'http://www.cnb.cz/cs/financni_trhy/devizovy_trh/kurzy_ostatnich_men/kurzy.txt';
    /**
     * @deprecated only for develop
     */
    //const CNB_DAY  ='D:\denni_kurz.txt';
    //const CNB_DAY2 ='D:\kurzy.txt';

    /**
     * include czech rating !important
     * @var const
     */
    const CNB_CZK = 'Česká Republika|koruna|1|CZK|1';

    /**
     * param for another day DD.MM.YYYY
     */
    const CNB_PARAM = '?date=';

    /**
     * @var const delimiter in self::CNB_CZK
     */
    const PIPE = '|';

    const CODE = 'code';
    const COUNTRY = 'country';//only czech
    const NAME = 'name';//only czech
    const FROM1 = 'from';
    const TO = 'to';
}
