<?php

final class CCCUtil extends Backend {

	public static function skipDots($name) {
		return $name != '.' && $name != '..';
	}

	public static function testProc($cmd) {
		$proc = proc_open(
			$cmd,
			array(
				array('pipe', 'r'),
				array('pipe', 'w'),
				array('pipe', 'w')
			),
			$pipes
		);
		return is_resource($proc) && !proc_close($proc);
	}

	/**
	 * Based on:
	 * Copyright (c) 2011 Nils Adermann, Jordi Boggiano
	 * @see https://github.com/composer/composer/blob/master/bin/composer
	 *
	 * @return void
	 */
	public static function increaseMemoryLimit() {
		if(!CCCCheck::isIniSetAvailable()) {
			return false;
		}

		ini_set('display_errors', 1);

		$memoryLimit = trim(ini_get('memory_limit'));
		if($memoryLimit != -1 && self::parseIniByteValue($memoryLimit) < 512 * 1024 * 1024) {
			ini_set('memory_limit', '512M');
		}
	}

	/**
	 * Based on:
	 * Copyright (c) 2011 Nils Adermann, Jordi Boggiano
	 * @see https://github.com/composer/composer/blob/master/bin/composer
	 *
	 * @param string
	 * @return number
	 */
	public static function parseIniByteValue($value) {
		$unit = strtolower(substr($value, -1, 1));
		$value = (int) $value;
		switch($unit) {
			case 'g': $value *= 1024; // no break (cumulative multiplier)
			case 'm': $value *= 1024; // no break (cumulative multiplier)
			case 'k': $value *= 1024;
		}
		return $value;
	}

	public static function _loadLanguageFile() {
		return call_user_func_array(array(self::getSubject(), substr(__METHOD__, 1)), func_get_args());
	}

	public static function _reload() {
		return call_user_func_array(array(self::getSubject(), substr(__METHOD__, 1)), func_get_args());
	}

	public static function _redirect() {
		return call_user_func_array(array(self::getSubject(), substr(__METHOD__, 1)), func_get_args());
	}

	public static function _addErrorMessage() {
		return call_user_func_array(array(self::getSubject(), substr(__METHOD__, 1)), func_get_args());
	}

	public static function _addConfirmationMessage() {
		return call_user_func_array(array(self::getSubject(), substr(__METHOD__, 1)), func_get_args());
	}

	public static function _handleRunOnce() {
		return call_user_func_array(array(self::getSubject(), substr(__METHOD__, 1)), func_get_args());
	}

	private static function getSubject() {
		if(version_compare(VERSION, '3', '>=')) {
			return 'Controller';
		} else {
			return self::getInstance();
		}
	}

	protected function __construct() {
		parent::__construct();
	}

	private static $instance;

	private static function getInstance() {
		isset(self::$instance) || self::$instance = new self();
		return self::$instance;
	}

}
