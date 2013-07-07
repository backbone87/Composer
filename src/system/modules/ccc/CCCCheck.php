<?php

/**
 *
 * @author Oliver Hoff <oliver@hofff.com>
 */
class CCCCheck
{

	public static function isIniSetAvailable() {
		return !in_array('ini_set', explode(',', ini_get('disable_functions')));
	}

	public static function isSafeModeHackDisabled() {
		return !$GLOBALS['TL_CONFIG']['useFTP'];
	}

	public static function isURLFopenAllowed() {
		return (bool) ini_get('allow_url_fopen');
	}

	public static function isPharSupported() {
		try {
			if (class_exists('Phar', false)) {
				new Phar(TL_ROOT . '/system/modules/ccc/config/test.phar');
				return true;
			}
		}
		catch (Exception $e) {
		}
		return false;
	}

	public static function isAPCDisabled() {
		return !ini_get('apc.enabled') || !ini_get('apc.cache_by_default');
	}

	public static function canDisableAPCCacheByDefault() {
		$previous = ini_get('apc.cache_by_default');
		$can = ini_set('apc.cache_by_default', 0);
		ini_set('apc.cache_by_default', $previous);
		return $can;
	}

	public static function isGitAvailable() {
		return self::testProc('git --version');
	}

	public static function isHGAvailable() {
		return self::testProc('hg --version');
	}

	public static function isSVNAvailable() {
		return self::testProc('svn --version');
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

	const PHP_MIN_VERSION = '5.3.4';

	public static function getPHPVersion() {
		return PHP_VERSION;
	}

	public static function isCompatiblePHPVersion() {
		return version_compare(self::getPHPVersion(), self::PHP_MIN_VERSION, '>=');
	}

	const CONTAO_MIN_VERSION = '2.11.11';

	public static function getContaoVersion() {
		return VERSION . '.' . BUILD;
	}

	public static function isCompatibleContaoVersion() {
		return version_compare(self::getContaoVersion(), self::CONTAO_MIN_VERSION, '>=');
	}

	public function __construct() {
	}

	public function generate($action = null, array $commercial = null) {
		$tpl = new BackendTemplate('ccc_check');

		$tpl->action		= $action;

		$tpl->contao		= self::isCompatibleContaoVersion();
		$tpl->contaoVersion	= self::getContaoVersion();
		$tpl->php			= self::isCompatiblePHPVersion();
		$tpl->phpVersion	= self::getPHPVersion();
		$tpl->smh			= self::isSafeModeHackDisabled();
		$tpl->fopen			= self::isURLFopenAllowed();
		$tpl->phar			= self::isPharSupported();
		$tpl->apc			= self::isAPCDisabled();
		$tpl->apcCanDisable	= self::canDisableAPCCacheByDefault();
		$tpl->commercial	= $commercial;

		$tpl->composer		= $tpl->contao && $tpl->php && $tpl->smh && $tpl->fopen && $tpl->phar;
		$tpl->warn			= !$tpl->apc || $commercial;

		return $tpl->parse();
	}

}
