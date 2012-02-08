<?php

namespace Exchange;

/**
 * ČNB obnovuje svuj soubor vždy po 14:00 - 14:30
 */
class CnbStorage extends Storage
{
	protected $hourRefresh = '15:00';
}
