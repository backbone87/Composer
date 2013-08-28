<?php

/**
 * Contao integration components for Contao Composer Client
 *
 * @author Oliver Hoff <oliver@hofff.com>
 */
class CCCIntegration extends System {

	/** @var array */
	private $legacyModuleOptionsCallback;

	/**
	 * @param array $callback
	 * @return void
	 */
	public function setLegacyModuleOptionsCallback(array $callback) {
		$this->legacyModuleOptionsCallback = $callback;
	}

	/**
	 * @return array<string, string>
	 */
	public function callbackModuleOptions() {
		$callback = $this->legacyModuleOptionsCallback;
		$this->import($callback[0]);
		$callback[0] = $this->{$callback[0]};
		$args = func_get_args();
		$modules = call_user_func_array($callback, $args);

		if(!in_array('!ccc', Config::getInstance()->getActiveModules())) {
			return $modules;
		}

		foreach(array('repository', 'rep_base', 'rep_client') as $module) if(isset($modules[$module])) {
			$modules[$module] = sprintf(
				'<span style="text-decoration:line-through;">%s</span> <span style="color:#f00;">%s</span>',
				$modules[$module],
				$GLOBALS['TL_LANG']['tl_settings']['ccc_disabledBy']
			);
		}

		return $modules;
	}

	/**
	 * @return void
	 */
	public function hookLoadLanguageFile() {
		if(!in_array('!ccc', Config::getInstance()->getActiveModules())) {
			return;
		}

		// disable the repo client
		$cfg		= Config::getInstance();
		$deactivate	= array_intersect(
			array('rep_base', 'rep_client', 'repository'),
			$cfg->getActiveModules()
		);

		if(!$deactivate) {
			unset($GLOBALS['TL_HOOK']['loadLanguageFile']['ccc']);
			return;
		}

		in_array('repository', $deactivate) && file_put_contents(TL_ROOT . '/system/modules/repository/.skip', 'Remove this file to enable the module');
		$inactive = array_merge(deserialize($GLOBALS['TL_CONFIG']['inactiveModules'], true), $deactivate);
		$cfg->update('$GLOBALS[\'TL_CONFIG\'][\'inactiveModules\']', serialize($inactive));
		CCCUtil::_reload();
	}

	/** @var self */
	protected static $instance;

	/**
	 * @return self
	 */
	static public function getInstance() {
		isset(self::$instance) || self::$instance = new self();
		return self::$instance;
	}

}
