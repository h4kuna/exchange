<?php
namespace Exchange;


interface IStorage {
	
	/** cache use for check */
	const INFO_CACHE = 'info';
	const ALL_CODE = 'all';
	
	/**
	 * @return bool
	 */
	function needUpdate();
	
	function getAll();
}

