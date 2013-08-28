<?php

/**
 * PHP Version 5.2
 *
 * @author Oliver Hoff <oliver@hofff.com>
 */
class CCCDefaultContext implements CCCContext {

	private $runtime;

	protected function __construct() {
	}

	public function getPHPMinVersion() {
		return '5.3.4';
	}

	public function getContaoMinVersion() {
		return '2.11.11';
	}

	public function getComposerHomePath($relative = false) {
		return $relative ? 'composer' : TL_ROOT . '/composer';
	}

	public function getArtifactRepositoryPath($relative = false) {
		return $this->getComposerHomePath($relative) . '/packages';
	}

	public function getComposerCachePath($relative = false) {
		return $this->getComposerHomePath($relative) . '/cache';
	}

	public function getLegacyRepositoryURL() {
		return 'http://legacy-packages-via.contao-community-alliance.org/';
	}

	public function getComposerPharPath($relative = false) {
		return $this->getComposerHomePath($relative) . '/composer.phar';
	}

	public function getComposerDownloadURL() {
		return 'https://getcomposer.org/composer.phar';
	}

	public function getComposerJSONPath($relative = false) {
		return $this->getComposerHomePath($relative) . '/' . $this->getRuntime()->getComposerFile();
	}

	public function getComposerLockPath($relative = false) {
		return $this->getComposerHomePath($relative) . '/' . preg_replace('@\.json$@', '.lock', $this->getRuntime()->getComposerFile()); // TODO better way than preg_replace?
	}

	public function getRuntime() {
		isset($this->runtime) || $this->runtime = CCCRuntime::create($this);
		return $this->runtime;
	}

	private static $instance;

	public static function getInstance() {
		isset(self::$instance) || self::$instance = new self();
		return self::$instance;
	}

}
