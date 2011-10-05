<?php

namespace Exchange;

use Nette;

class File extends Storage {

	public function needUpdate()
	{
		return !$this->offsetExists(self::INFO_CACHE);
	}
}
