<?php

/**
 * Class CCCHooks
 *
 * Composer client interface.
 */
class CCCHooks extends System
{

	public function disableOldClientHook()
	{
		// disable the repo client
		$reset           = false;
		$activeModules   = $this->Config->getActiveModules();
		$inactiveModules = deserialize($GLOBALS['TL_CONFIG']['inactiveModules']);

		if (in_array('rep_base', $activeModules)) {
			$inactiveModules[] = 'rep_base';
			$reset             = true;
		}
		if (in_array('rep_client', $activeModules)) {
			$inactiveModules[] = 'rep_client';
			$reset             = true;
		}
		if (in_array('repository', $activeModules)) {
			$inactiveModules[] = 'repository';
			$skipFile          = new File('system/modules/repository/.skip');
			$skipFile->write('Remove this file to enable the module');
			$skipFile->close();
			$reset = true;
		}
		if ($reset) {
			$this->Config->update("\$GLOBALS['TL_CONFIG']['inactiveModules']", serialize($inactiveModules));
			$this->reload();
		}
		unset($GLOBALS['TL_HOOK']['loadLanguageFile']['composer']);
	}

	/** @var self */
	protected static $instance;

	/**
	 * @return self
	 */
	static public function getInstance()
	{
		isset(self::$instance) || self::$instance = new self();
		return self::$instance;
	}

}
