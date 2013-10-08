<?php
include __DIR__ . "/vendor/autoload.php";



// 2# Create Nette Configurator
$configurator = new Nette\Config\Configurator;
$tmp = __DIR__ . '/tmp';
if (!file_exists($tmp)) {
    throw new \RuntimeException('Create writeable dir: ' . $tmp);
}
$configurator->enableDebugger($tmp);
$configurator->setTempDirectory($tmp);

$configurator->onCompile[] = function ($configurator, $compiler) {
    $compiler->addExtension('exchangeExtension', new h4kuna\Exchange\DI\ExchangeExtension());
};

$container = $configurator->createContainer();

$exchange = Nette\Framework::VERSION == '2.1-dev' ?
        $container->createServiceExchangeExtension__exchange() :
        $container->exchangeExtension->exchange;

$exchange->loadCurrency('usd');
$historyEUR = 20;
$smallVat = 15;
?>
<!DOCTYPE html>
<html>
    <head>
        <meta charset="utf-8">
        <link rel="stylesheet" href="http://netdna.bootstrapcdn.com/bootstrap/3.0.0/css/bootstrap.min.css">
        <title>Exchange example</title>
        <style>
            a.current{
                color: black;
                text-decoration: none;
            }
        </style>
    </head>
    <body>
        <ul class="list-inline">
            <li>VAT: <?php echo $exchange->getVat() ?>%</li>
            <li>Control VAT: <?php echo $exchange->vatLink('On', 'Off'); ?></li>
            <li>Currencies: <?php foreach ($exchange as $v): ?>
                    <?php echo $exchange->currencyLink($v); ?>
                <?php endforeach; ?>
            </li>
        </ul>

        <h3>Accept VAT Control</h3>
        <p><?php echo $exchange->format(100); ?></p>

        <h3>Price with VAT</h3>
        <p><?php echo $exchange->formatVat(); ?></p>


        <h3>VAT for this is: <?php echo $smallVat ?>%</h3>
        <p><?php echo $exchange->format(100, NULL, NULL, $smallVat); ?></p>

        <h3>History for old order in eshop</h3>
        <h4>Before 1:<?php
            echo $historyEUR;
            $exchange->addHistory('eur', $historyEUR);
            ?></h4>
        <p><?php
            echo $exchange->format(10, 'eur', 'czk');
            $exchange->removeHistory('eur');
            ?></p>
        <h4>Actual</h4>
        <p><?php echo $exchange->format(10, 'eur', 'czk'); ?></p>
        <p><small><?php echo Nette\Framework::VERSION ?></small></p>
    </body>
</html>



