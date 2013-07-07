<?php

/**
 *
 * @author Oliver Hoff <oliver@hofff.com>
 */
class CCCInstaller
{

	public function __construct() {
	}

	public function generate() {
		$GLOBALS['TL_CSS'][] = 'system/modules/ccc/assets/css/installer.css';

		switch(Input::getInstance()->post('FORM_SUBMIT')) {
			case 'ccc_installer_welcome':
				$check = new CCCCheck();
				$commercial = Database::getInstance()
					->execute('SELECT extension FROM tl_repository_installs WHERE lickey != \'\'')
					->fetchEach('extension');
				return $check->generate('contao/main.php?do=ccc&command=install', $commercial);
				break;

			case 'ccc_check':
				break;

			case 'ccc_migrate':
				break;
		}

		$tpl = new BackendTemplate('ccc_installer_welcome');
		return $tpl->parse();
	}

}
