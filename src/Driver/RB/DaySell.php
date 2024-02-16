<?php declare(strict_types=1);

namespace h4kuna\Exchange\Driver\RB;

use SimpleXMLElement;

final class DaySell extends Day
{

	protected function rate(SimpleXMLElement $element): SimpleXMLElement
	{
		return $element->exchangeRateSell;
	}

}
