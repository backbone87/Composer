<?php

use Composer\IO\IOInterface;

use Composer\Installer;

use Composer\Console\HtmlOutputFormatter;
use Composer\Factory;
use Composer\IO\BufferIO;

/**
 * PHP Version 5.3
 *
 * @author Oliver Hoff <oliver@hofff.com>
 */
class CCCRuntime {

	/**
	 * @param CCCContext $ctx
	 * @return CCCRuntime
	 */
	public static function create(CCCContext $ctx) {
		return new self($ctx);
	}

	/** @var CCCContext */
	private $ctx;

	/** @var \Composer\Composer */
	private $composer;

	/** @var \Composer\IO\IOInterface */
	private $io;

	/**
	 * @param CCCContext $ctx
	 */
	protected function __construct(CCCContext $ctx) {
		$this->ctx = $ctx;
	}

	/**
	 * @return CCCContext
	 */
	public function getContext() {
		return $this->ctx;
	}

	/**
	 * @return string
	 */
	public function getComposerFile() {
		return Factory::getComposerFile();
	}

	/**
	 * @return \Composer\Composer
	 */
	public function getComposer() {
		if(!isset($this->composer)) {
			$this->prepareEnv();
			$this->registerComposerPharClassLoader();
			$this->composer = $this->createComposer();
		}
		return $this->composer;
	}

	/**
	 * @return \Composer\IO\IOInterface
	 */
	public function getIO() {
		if(!isset($this->io)) {
			$this->prepareEnv();
			$this->registerComposerPharClassLoader();
			$this->io = $this->createIO();
		}
		return $this->io;
	}

	public function update(IOInterface $io = null) {
		$this->unregisterContao2ClassLoader();

		$io === null && $io = $this->getIO();
		$composer = $this->getComposer();

		$composer->getDownloadManager()->setOutputProgress(false);

		$installer = Installer::create($io, $composer);
		switch($composer->getConfig()->get('preferred-install')) {
			case 'source': $installer->setPreferSource(true); break;
			case 'dist': $installer->setPreferDist(true); break;
		}
		is_file($this->getContext()->getComposerLockPath()) && $installer->setUpdate(true);

		$result = $installer->run();

		$this->registerContao2ClassLoader();

		return $result;
	}

	/**
	 * @return void
	 */
	public function prepareEnv() {
		static $prepared;

		if($prepared) {
			return;
		}

		// TODO: OH: we should always set the COMPOSER_HOME to the current composer home
		getenv('COMPOSER_HOME') || putenv('COMPOSER_HOME=' . $this->getContext()->getComposerHomePath());

		// see #54
		if(!getenv('PATH')) {
			if(defined('PHP_WINDOWS_VERSION_BUILD')) {
				putenv('PATH=%SystemRoot%\system32;%SystemRoot%;%SystemRoot%\System32\Wbem');
			} else {
				putenv('PATH=/opt/local/bin:/usr/local/sbin:/usr/local/bin:/usr/sbin:/usr/bin:/sbin:/bin');
			}
		}

		$prepared = true;
	}

	/**
	 * @throws Exception
	 * @return void
	 */
	public function registerComposerPharClassLoader() {
		static $registered;

		if($registered) {
			return;
		}

		if(!is_file($this->getContext()->getComposerPharPath())) {
			throw new Exception('', 1); // TODO
		}

		$this->unregisterContao2ClassLoader();

		$phar = new Phar($this->getContext()->getComposerPharPath());
		require_once $phar['vendor/autoload.php']->getPathname();

		$this->registerContao2ClassLoader();

		$registered = true;
	}

	/**
	 * @return \Composer\Composer
	 */
	public function createComposer() {
		/** @var \Composer\Factory $factory */
		$factory = new Factory();
		return $factory->createComposer($this->getIO());
	}

	/**
	 * @return \Composer\IO\BufferIO
	 */
	public function createIO() {
		return new BufferIO('', null, new HtmlOutputFormatter());
	}

	/**
	 * @throws Exception
	 * @return void
	 */
	public function registerComposerClassLoader() {
		static $registered;

		if($registered) {
			return;
		}

		$path = $this->getContext()->getComposerHome() . '/vendor/autoload.php';
		if(!is_file($path)) {
			throw new Exception('', 1); // TODO
		}

		$this->unregisterContao2ClassLoader();

		require_once $path;

		$this->registerContao2ClassLoader();

		$registered = true;
	}

	/**
	 * TODO implement
	 * @return string
	 */
	public function getComposerVersion() {
		return '0.0';
	}

	/**
	 * Read the stub from the composer.phar and return the warning timestamp.
	 *
	 * @return integer
	 */
	public function readComposerDevWarningTime() {
		$fp = fopen($this->getContext()->getComposerPharPath());
		while(false !== $line = fgets($fp)) {
			if(preg_match('#define\(\'COMPOSER_DEV_WARNING_TIME\',\s*(\d+)\);#', $line, $matches)) {
				fclose($fp);
				return intval($matches[1]);
			}
		}
		fclose($fp);
		return PHP_INT_MAX;
	}

	/**
	 * @return boolean
	 */
	public function isComposerDevWarning() {
		return time() > $this->readComposerDevWarningTime();
	}

	/**
	 * @return boolean
	 */
	public function disableAPCCache() {
		$chk = CCCCheck::create($this->getContext());
		if($chk->isAPCDisabled()) {
			return true;
		}
		if(!$chk->canDisableAPCCacheByDefault()) {
			return false;
		}
		apc_clear_cache();
		ini_set('apc.cache_by_default', 0);
		return true;
	}

	public function unregisterContao2ClassLoader() {
		version_compare(VERSION, '3', '<') && spl_autoload_unregister('__autoload');
	}

	public function registerContao2ClassLoader() {
		version_compare(VERSION, '3', '<') && spl_autoload_register('__autoload');
	}

}
