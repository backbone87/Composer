<?php

/**
 * PHP Version 5.2
 *
 * @author Oliver Hoff <oliver@hofff.com>
 */
class CCCCheck {

	public static function create(CCCContext $ctx) {
		return new self($ctx);
	}

	private $ctx;

	protected function __construct(CCCContext $ctx) {
		$this->ctx = $ctx;
	}

	public function getContext() {
		return $this->ctx;
	}

	public function isIniSetAvailable() {
		return !in_array('ini_set', explode(',', ini_get('disable_functions')));
	}

	public function isSafeModeHackDisabled() {
		return !$GLOBALS['TL_CONFIG']['useFTP'];
	}

	public function isURLFopenAllowed() {
		return (bool) ini_get('allow_url_fopen');
	}

	const TEST_PHAR_PATH = 'system/modules/ccc/config/test.phar';

	public function isPharSupported() {
		try {
			if(class_exists('Phar', false)) {
				new Phar(TL_ROOT . '/' . self::TEST_PHAR_PATH);
				return true;
			}
		} catch(Exception $e) {
		}
		return false;
	}

	public function isAPCDisabled() {
		return !extension_loaded('apc') || !ini_get('apc.enabled') || !ini_get('apc.cache_by_default');
	}

	const APC_MIN_VERSION_RUNTIME_CACHE_BY_DEFAULT = '3.0.13';

	public function canDisableAPCCacheByDefault() {
		if(!extension_loaded('apc')) {
			return false;
		}
		$apc = new ReflectionExtension('apc');
		if(version_compare($apc->getVersion(), self::APC_MIN_VERSION_RUNTIME_CACHE_BY_DEFAULT, '<')) {
			return false;
		}
		return $this->isIniSetAvailable();
	}

	public function isGitAvailable() {
		return CCCUtil::testProc('git --version');
	}

	public function isHGAvailable() {
		return CCCUtil::testProc('hg --version');
	}

	public function isSVNAvailable() {
		return CCCUtil::testProc('svn --version');
	}

	public function getPHPVersion() {
		return PHP_VERSION;
	}

	public function isCompatiblePHPVersion() {
		return version_compare($this->getPHPVersion(), $this->getContext()->getPHPMinVersion(), '>=');
	}

	public function getContaoVersion() {
		return VERSION . '.' . BUILD;
	}

	public function isCompatibleContaoVersion() {
		return version_compare($this->getContaoVersion(), $this->getContext()->getContaoMinVersion(), '>=');
	}

	public function hasErrors() {
		return !$this->isCompatibleContaoVersion()
			|| !$this->isCompatiblePHPVersion()
			|| !$this->isSafeModeHackDisabled()
			|| !$this->isURLFopenAllowed()
			|| !$this->isPharSupported();
	}

	public function hasWarnings() {
		return !$this->isAPCDisabled() && !$this->canDisableAPCCacheByDefault();
	}

}
