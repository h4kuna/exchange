<?php

namespace h4kuna\Exchange\DI;

use Nette\Configurator;
use Nette\DI\Compiler;
use Nette\DI\CompilerExtension;

/**
 * Do not forget set alias for nette < 2.1
 * 
 * Nette\Config\CompilerExtension, Nette\DI\CompilerExtension
 * Nette\Config\Compiler, Nette\DI\Compiler
 * Nette\Utils\PhpGenerator\ClassType, Nette\PhpGenerator\ClassType
 */
class ExchangeExtension extends CompilerExtension {

    public $defaults = array(
        'vat' => 21,
        'vatIn' => FALSE,
        'vatOut' => FALSE,
        'currencies' => array(
            'czk' => array('decimal' => 0, 'symbol' => 'Kč'),
            'eur'
        ),
        'driver' => 'h4kuna\Exchange\Driver\Cnb\Day',
        'storage' => 'h4kuna\Exchange\Storage\Stock'
    );

    public function loadConfiguration() {
        $builder = $this->getContainerBuilder();
        $currencies = $this->defaults['currencies'];
        unset($this->defaults['currencies']);
        $config = $this->getConfig($this->defaults);
        if (!isset($config['currencies'])) {
            $config['currencies'] = $currencies;
        }

        // driver
        $builder->addDefinition($this->prefix('driver'))
                ->setClass($config['driver']);

        // session section
        $builder->addDefinition($this->prefix('sessionSection'))
                ->setClass('Nette\Http\SessionSection')
                ->setArguments(array('@session', $this->name))
                ->setShared(FALSE)->setAutowired(FALSE);

        // storage factory
        $builder->addDefinition($this->prefix('stockFactory'))
                ->setClass('h4kuna\Exchange\Storage\Factory')
                ->setArguments(array('@cacheStorage', $config['storage']))
                ->setShared(FALSE)->setAutowired(FALSE);

        // warehouse
        $builder->addDefinition($this->prefix('warehouse'))
                ->setClass('h4kuna\Exchange\Storage\Warehouse')
                ->setArguments(array($this->prefix('@stockFactory'), $this->prefix('@driver')))
                ->setShared(FALSE)->setAutowired(FALSE);

        // main class Exchange
        $exchange = $builder->addDefinition($this->prefix('exchange'))
                ->setClass('h4kuna\Exchange\Exchange')
                ->setArguments(array($this->prefix('@warehouse'), '@httpRequest', $this->prefix('@sessionSection')));

        if ($config['vat']) {
            $exchange->addSetup('setVat', array($config['vat'], $config['vatIn'], $config['vatOut']));
        }

        foreach ($config['currencies'] as $code => $currency) {
            if (is_array($currency)) {
                $exchange->addSetup('loadCurrency', array($code, $currency));
            } else {
                $exchange->addSetup('loadCurrency', array($currency));
            }
        }
        return $builder;
    }

}
