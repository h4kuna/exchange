For dependency look at to composer.json
- h4kuna/number-format
- h4kuna/static
- h4kuna/data-type
- h4kuna/unit-conversion

Exchange
-------
Exchange is PHP script works with currencies.

I will use \Nette\Environment in example, but you look at to [autowire in Nette](http://doc.nette.org/en/configuring#toc-setup).

Example rate:
- 1 EUR -> 25 CZK
- 1 USD -> 20 CZK

This is default setup where is not defined custom format and vat:
```php
$storage = new Storage(Environment::getContext()->cacheStorage);
$ex = new \h4kuna\Exchange($storage, Environment::getHttpRequest(), Environment::getSession('exchange'));
$ex->format(10); // 10,00 CZK
// this setup you can use for working with vat only
```

Custom format and vat use [API Money](https://github.com/h4kuna/number-format/blob/master/Money.php) and [NumberFormat](https://github.com/h4kuna/number-format/blob/master/NumberFormat.php):
```php
$money = new Money(NULL, 15);// default vat is 15%, you can write as 1.15, 15, 0.15 recomend as percent 15
$money->setDecimal(1);

$ex = new \h4kuna\Exchange($storage, Environment::getHttpRequest(), Environment::getSession('exchange'), $money);

// keys in array call same as property of NumberFormat
$ex->loadCurrency('czk', array('symbol' => 'Kč', 'decimal' => 0, 'mask' => '1 S')); // first is default
$ex->loadCurrency('eur', array('symbol' => '€', 'decimal' => 2, 'mask' => 'S1'));
$ex->loadCurrency('usd', array('symbol' => '$', 'decimal' => 1, 'mask' => 'S 1', 'point' => '.'));
```

Easy formating, output is controled via url param **currency**=>CODE(CZK|EUR|USD etc.)
```php
$ex->format(10); // 10 Kč
$ex->format(10, 'usd'); // usd to default 200 Kč
$ex->format(10, 'usd', 'eur'); // €8,00
$ex->format(10, FALSE, 'usd'); // 10.0 $
```

Example with vat and this is controlled via query param **vat**=(1|0)
```php
$ex->setVatIO(21, FALSE, TRUE); // vat percent, input number are without vat, output number are with vat
$ex->format(10); // 10 * 1.21 => 12.1 => 12 Kč
$ex->format(10, null, null, 15) => 10 * 1.15 => 8.85 => 9 Kč

$ex->formatVat() // show everytime money with vat, but you can use after format
```

Use in NetteFramework
---------------------
Config
look at to (config)[https://github.com/h4kuna/exchange/blob/master/_plan/config.neon]

Presenter
```php
$this->context->exchange->registerAsHelper($this->template);
```

Template Latte
```html
{$exchange->vatLink('VAT on', 'VAT off')}

<ul>
{foreach $exchange as $code => $v}
    <li>{$exchange->currencyLink($code)}</li>
{/foreach}
</ul>

{!=10|currency}
```



--------------------------------HISTORY-----------------------------------------
3.2)
- lepší práce s DPH a rozděleno do více repozitářů

3.1)
- upraveno pro Nette 2.0.3

3.0)
- přepsáno pro Nette 2.0 stable
- možnost DI
- nový objekt na formátování čísel NumberFormat

2.5)
-při CnbDb probíhaly zbytečné dotazy
-při CnbDb nefunchovala korekce měn
-nyní pevně fixováno na SqLite3, kvuli registrované metodě CnbDb::correction
-je možné udělat stahování z jakékoliv banky a přepočty budou fungovat

2.2)
-přidáno připojení přes proxy viz Cnb::$proxyName
-drobné úpravy v přehlednosti kódu
-změněn namespace z Cnb na Exchange

2.1)
-fix: chyba s duplicitami
-upravena CnbFile
-upraveny namespace

2.0)
-přepis pro PHP 5.3 využití sqLite3

--------------------------------------------------------------------------------

1.8.4)
-opraveno stahování duplicitních měn
-tímto vývoj pro PHP 5.2 končí

1.8.3)
-byla odstraněna metoda getUsingRate() byla totožná s metodou Rate() v šablone se $useRate nezměnila
-možnost zakázat stahování Cnb::CNB_LIST2 pomoci Cnb::$loadBoth = false;
-Cnb::getAllCode() možnost sežadit podle kódu měny
-Cnb::getRating() vložíte-li false načte všechny stažené měny
-kód měny se měnil podle velikosti písma nyní je UPPER, pokud chcete aby se nadále měníl použijte $cnb->getSymbol();

1.8.2)
-vylepšena metoda getElement2Cache()
-vylepšena třída CnbNette její registrace
-instance template už není potřeba vkládat při registraci
-přidána statická metoda getVersion()

1.8.1)
-upravena metoda na tvorbu cache souboru

1.8)
-fix: nastavení dph zlobilo, špatně se ukldalo do session
-fix: CnbHistory
-CnbNette do template přibyli dvě nové proměnné, $useRate, $globalVat
-nová metoda Cnb::getUsingRating()
-cache soubor vytvářen jako statický
-defaultně vyplněný parametr pro měny v CnbNette

1.7.2)
-rychlejší výpočet ratingu
-odstraněna metoda divisionRate()

1.7.1)
-kontrola proti znovunačteni třídy v cache

1.7)
-možnost přetěžování parametrů ukládaných do cache souboru
-změna tříd, nyní **Cnb** použití bez Nette a **NCnb** s Nette, pro šablony viz ukázka výše

1.6.1)
-ošetření zda je načtená curl extension

1.6)
-kosmetické úpravy kodu
-špatně se načítala turecká měna
-zmenšení cache souboru na disku o více jak 50%

1.5.3)
-podstatné zrychlení metod format() a change()
-aj. kosmetické úpravy

1.5.2)
-statická třída určená jen pro Nette, **CnbNette**

1.5.1)
-popisky v angličtině
-příklady

1.5)
-podpora zobrazování cen s dph i bez dph


1.4)
-trošku pozměněná struktura souborů na disku
-nové stahovaní historie s ukládaním do databáze

1.3)
-nová metoda __call, pro rychlejši přístup k informacím
-odstraněna metoda defineMoney() nahrazena getRating()
-možnost stažení staršího kurzu, použijte třídu CnbHistory
-nově se ukláda id kurzu vydaneho za danný rok a datum
-historie má jedno omezení, tj nestahuje Cnb::CNB_LIST2 těchto měn se nevede historie

1.2)
-metoda format(), kdyz druhý parametr je TRUE, měna se nepřepočítá jen zformátuje
-fix: opravena možnost korekce měn
-vlastnosti static převedeny na dynamicke aby šli děděním měnit
-funkčnost i bez Nette avšak přijdete o ukládani do cookie, třída **Cnb** s Nette **NCnb**
-fix: opraveno načítaní přes CUrl

1.1)
-stahuje kompletně celý kurzovní lístek a načte se online pouze přepočet který bude potřeba
-tzn nemusíte přemýšlet nad tím jaké všechny měny budete potřebovat
-všechny dostupné měny získáte pomocí metody getAllCode()
-vylepšená metoda format(), nyní si lze nastavit z čeho na co přepocítat a zformatuje podle nastavení

1.0)
-nová metoda format(), která formátuje každou měnu samostatně, tzn máte czk → 15 365 Kč, usd → $1,955.35, gbp → £1 568.45

0.9.1)
-vnitřní úpravy bez vlivu na funkčnost

0.9)
-přidán helper pro šablony na úpravu formátu čísla, vycházel jsem z [Helper currency | helper-currency]
