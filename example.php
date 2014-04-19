<?php
$container = require __DIR__ . '/tests/bootstrap.php';
\Nette\Diagnostics\Debugger::timer();
$exchange = $container->getService('exchangeExtension.exchange');
$exchange->loadCurrency('usd');
$historyEUR = 20;
$smallVat = 15;

if (false) {
    $exchange = new \h4kuna\Exchange\Exchange;
}

$rbDriver = $exchange->setDriver(new \h4kuna\Exchange\RB\Day);


dump($rbDriver->store);
die();
$date = new DateTime('2013-12-30');
$history = $exchange->setDate($date);
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

        <h3>History rate for old order in eshop</h3>
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

        <h3>History date <?php echo $date->format('Y-m-d'); ?> </h3>
        <p>Today: <?php echo $exchange->format(10, 'eur'); ?></p>
        <p>History: <?php echo $history->format(10, 'eur'); ?></p>

        <p><small><?php echo Nette\Framework::VERSION ?></small></p>
    </body>
</html>



