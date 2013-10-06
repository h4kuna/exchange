<?php

namespace h4kuna\Exchange\Cnb;

interface ICnb {

    /**
     * Url where download rating
     * 
     * @var const
     */
    const CNB_DAY = 'http://www.cnb.cz/cs/financni_trhy/devizovy_trh/kurzy_devizoveho_trhu/denni_kurz.txt';
    const CNB_DAY2 = 'http://www.cnb.cz/cs/financni_trhy/devizovy_trh/kurzy_ostatnich_men/kurzy.txt';

    /**
     * Include czech rating !important
     *
     * @var const
     */
    const CNB_CZK = 'Česká Republika|koruna|1|CZK|1';

}
