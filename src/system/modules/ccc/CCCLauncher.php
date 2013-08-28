<?php

/**
 * This will be called during Contao config load time and registers the Composer
 * ClassLoader, if the runtime executes in a compatible PHP version.
 *
 * Additionally it will add a Contao hook when in BE env to disable the ER2 Client.
 *
 * PHP version 5.2
 *
 * @copyright	ContaoCommunityAlliance 2013
 * @author		Oliver Hoff <oliver@hofff.com>
 * @author		Dominik Zogg <dominik.zogg@gmail.com>
 * @author		Tristan Lins <tristan.lins@bit3.de>
 * @license		LGPLv3
 */

final class CCCLauncher {

	public static function launch() {
		static $launched;
		if($launched) {
			return;
		}
		$launched = true;

		$ctx = CCCDefaultContext::getInstance();

		if(CCCCheck::create($ctx)->isCompatiblePHPVersion()) {
			$ctx->getRuntime()->registerComposerClassLoader();
		} elseif(TL_MODE == 'BE') {
			CCCUtil::_addErrorMessage('Composer ClassLoader skipped due to incompatible PHP version.');
		}

		if(TL_MODE == 'BE') {
			$GLOBALS['TL_HOOKS']['loadLanguageFile']['ccc'] = array('CCCIntegration', 'hookLoadLanguageFile');
		}
	}

}
