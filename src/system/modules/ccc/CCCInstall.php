<?php

/**
 * Class ComposerClientBackend
 *
 * Composer client interface.
 */
class CCCInstall
{

	const COMPOSER_DOWNLOAD = 'https://getcomposer.org/composer.phar';

	public function createComposerDirectory() {
		$created = is_dir(COMPOSER_DIR_ABSOLUTE);
		if(!$created) {
			$created = mkdir(COMPOSER_DIR_ABSOLUTE);
		}
		if(!$created) {
			throw new Exception('', 1); // TODO
		}
	}

	public function createArtifactDirectory() {
		$created = is_dir(COMPOSER_ARTIFACT_DIR_ABSOULTE);
		if(!$created) {
			$created = mkdir(COMPOSER_ARTIFACT_DIR_ABSOULTE);
		}
		if(!$created) {
			throw new Exception('', 2); // TODO
		}
	}

	public function writeComposerPhar() {
		$file = COMPOSER_DIR_ABSOULTE . '/composer.phar';
		$bytes = file_put_contents($file, file_get_contents(self::COMPOSER_DOWNLOAD));
		if(!$bytes) {
			throw new Exception('', 3); // TODO
		}
	}

	public function writeRootComposerJSON() {
		$contaoVersion = VERSION . (is_numeric(BUILD) ? '.' . BUILD : '-' . BUILD);
		$json = <<<EOT
{
    "name": "contao/core",
    "description": "Contao Open Source CMS",
    "license": "LGPL-3.0+",
    "version": "$contaoVersion",
    "type": "metapackage",
    "require": {
        "contao-community-alliance/composer": "dev-master@dev"
    },
    "scripts": {
        "pre-update-cmd": "ContaoCommunityAlliance\\\\ComposerInstaller\\\\ModuleInstaller::preUpdate",
        "post-update-cmd": "ContaoCommunityAlliance\\\\ComposerInstaller\\\\ModuleInstaller::postUpdate",
        "post-autoload-dump": "ContaoCommunityAlliance\\\\ComposerInstaller\\\\ModuleInstaller::postAutoloadDump"
    },
    "config": {
        "preferred-install": "dist",
        "cache-dir": "cache"
    },
    "repositories": [
        {
            "type": "composer",
            "url": "http://legacy-packages-via.contao-community-alliance.org/"
        },
        {
            "type": "artifact",
            "url": "packages/"
        }
    ]
}
EOT;
		file_put_contents(COMPOSER_DIR_ABSOULTE . '/composer.json', $json);
	}

}
