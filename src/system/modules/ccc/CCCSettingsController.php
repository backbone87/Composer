<?php

/**
 *
 * @author Oliver Hoff <oliver@hofff.com>
 */
class CCCSettingsController implements CCCController {


	public static function setSettingsToComposerJSON(array $config, array $json = null) {
		$json['minimum-stability']			= $config['minimumStability'];
		$json['prefer-stable']				= $config['preferStable'];
		$json['config']['preferred-install']= $config['preferredInstall'];
		return $json;

// 		$json['minimum-stability']	= in_array($config['minimumStability'], array('stable', 'rc', 'beta', 'alpha', 'dev')) ? $config['minimumStability'] : 'rc';
// 		$json['prefer-stable']		= (bool) $config['preferStable'];
// 		$json['config']['preferred-install']	= in_array($config['preferredInstall'], array('auto', 'dist', 'source')) ? $config['preferredInstall'] : 'auto';

	}

	public static function create() {
		return new self();
	}

	const DEFAULT_TEMPLATE = 'ccc_settings';

	private $widgets;

	private $validate;

	public function __construct() {
		CCCUtil::_loadLanguageFile('ccc');
		CCCUtil::_loadLanguageFile('ccc_settings');
		$this->widgets['expertMode']		= self::createExportModeWidget();
		$this->widgets['minimumStability']	= self::createMinimumStabilityWidget();
		$this->widgets['preferStable']		= self::createPreferStableWidget();
		$this->widgets['preferredInstall']	= self::createPreferredInstallWidget();
	}

	public function validateWidgets($revalidate = false) {
		if(!$revalidate && isset($this->validate)) {
			return $this->validate;
		}
		foreach($this->widgets as $widget) {
			$widget->validate();
			if($widget->hasErrors) {
				$error = true;
			}
		}
		return $this->validate = !$error;
	}

	public function getDataFromWidgets() {
		foreach($this->widgets as $widget) {
			$data[$widget->name] = $widget->value;
		}
		return (array) $data;
	}

	public function createTemplate($tpl = self::DEFAULT_TEMPLATE) {
		$tpl = new BackendTemplate($tpl);

		$tpl->action		= Environment::getInstance()->request;
		$tpl->formSubmit	= 'ccc_settings';
		$tpl->widgets		= $this->widgets;
		$tpl->submitLabel	= $GLOBALS['TL_LANG']['ccc_settings']['save'];

		return $tpl;
	}

	public static function createPreferStableWidget($value = false) {
		return new CheckBox(
			array(
				'id'			=> 'expertMode',
				'name'			=> 'expertMode',
				'label'			=> $GLOBALS['TL_LANG']['ccc_settings']['expertMode']['label'],
				'description'	=> $GLOBALS['TL_LANG']['ccc_settings']['expertMode']['description'],
				'options'		=> array(
					array('value' => '1',		'label' => $GLOBALS['TL_LANG']['ccc_settings']['expertMode']['label']),
				),
				'value'			=> $value,
				'class'			=> 'expert-mode',
				'tl_class'		=> 'm12 cbx w50',
				'required'		=> true,
			)
		);
	}

	public static function createMinimumStabilityWidget($value = 'RC') {
		return new SelectMenu(
			array(
				'id'			=> 'minimumStability',
				'name'			=> 'minimumStability',
				'label'			=> $GLOBALS['TL_LANG']['ccc_settings']['minimumStability']['label'],
				'description'	=> $GLOBALS['TL_LANG']['ccc_settings']['minimumStability']['description'],
				'options'		=> array(
					array('value' => 'stable',	'label' => $GLOBALS['TL_LANG']['ccc_settings']['minimumStability']['stable']),
					array('value' => 'RC',		'label' => $GLOBALS['TL_LANG']['ccc_settings']['minimumStability']['rc']),
					array('value' => 'beta',	'label' => $GLOBALS['TL_LANG']['ccc_settings']['minimumStability']['beta']),
					array('value' => 'alpha',	'label' => $GLOBALS['TL_LANG']['ccc_settings']['minimumStability']['alpha']),
					array('value' => 'dev',		'label' => $GLOBALS['TL_LANG']['ccc_settings']['minimumStability']['dev']),
				),
				'value'			=> $value,
				'class'			=> 'minimum-stability',
				'tl_class'		=> 'w50',
				'required'		=> true,
			)
		);
	}

	public static function createPreferStableWidget($value = true) {
		return new CheckBox(
			array(
				'id'			=> 'preferStable',
				'name'			=> 'preferStable',
				'label'			=> $GLOBALS['TL_LANG']['ccc_settings']['preferStable']['label'],
				'description'	=> $GLOBALS['TL_LANG']['ccc_settings']['preferStable']['description'],
				'options'		=> array(
					array('value' => '1',		'label' => $GLOBALS['TL_LANG']['ccc_settings']['preferStable']['label']),
				),
				'value'			=> $value,
				'class'			=> 'prefer-stable',
				'tl_class'		=> 'm12 cbx w50',
				'required'		=> true,
			)
		);
	}

	public static function createPreferredInstallWidget($value = 'auto') {
		return new SelectMenu(
			array(
				'id'			=> 'preferredInstall',
				'name'			=> 'preferredInstall',
				'label'			=> $GLOBALS['TL_LANG']['ccc_settings']['preferredInstall']['label'],
				'description'	=> $GLOBALS['TL_LANG']['ccc_settings']['preferredInstall']['description'],
				'options'		=> array(
					array('value' => 'auto',	'label' => $GLOBALS['TL_LANG']['ccc_settings']['preferredInstall']['auto']),
					array('value' => 'dist',	'label' => $GLOBALS['TL_LANG']['ccc_settings']['preferredInstall']['dist']),
					array('value' => 'source',	'label' => $GLOBALS['TL_LANG']['ccc_settings']['preferredInstall']['source']),
				),
				'value'			=> $value,
				'class'			=> 'preferred-install',
				'tl_class'		=> 'w50',
				'required'		=> true,
			)
		);
	}

}
