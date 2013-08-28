<?php

use Composer\Util\Filesystem;

use Composer\Json\JsonFile;

/**
 *
 * @author Oliver Hoff <oliver@hofff.com>
 */
class CCCSetup {

	public static function install(CCCContext $ctx, array $config = null) {
		$installer = self::create($ctx);
		$installer->createComposerDirectory();
		$installer->createArtifactDirectory();
		$installer->writeComposerPhar();
		$installer->writeRootComposerJSON($config);
		$installer->writeRuntimeArtefact();
	}

	public static function create(CCCContext $ctx) {
		return new self($ctx);
	}

	private $ctx;

	public function __construct(CCCContext $ctx) {
		$this->ctx = $ctx;
	}

	public function getContext() {
		return $this->ctx;
	}

	public function createComposerDirectory() {
		$path = $this->getContext()->getComposerHomePath();
		$created = is_dir($path) || mkdir($path, 0777, true);
		if(!$created) {
			throw new Exception('', 1); // TODO
		}
	}

	public function createArtifactDirectory() {
		$path = $this->getContext()->getArtifactRepositoryPath();
		$created = is_dir($path) || mkdir($path, 0777, true);
		if(!$created) {
			throw new Exception('', 2); // TODO
		}
	}

	public function writeComposerPhar() {
		$bytes = file_get_contents($this->getContext()->getComposerDownloadURL());
		$written = file_put_contents($this->getContext()->getComposerPharPath(), $bytes);
		if(!$written || $written != strlen($bytes)) {
			throw new Exception('', 3); // TODO
		}
	}

	public function writeRootComposerJSON(array $config = null) {
		$file = new JsonFile($this->getContext()->getComposerJSONPath());
		$json = $file->read();

		$json['name']				= 'contao/core';
		$json['description']		= 'Contao Open Source CMS';
		$json['license']			= 'LGPL-3.0+';
		$json['version']			= VERSION . (is_numeric(BUILD) ? '.' : '-') . BUILD;
		$json['type']				= 'metapackage';

		$json['require']['contao-community-alliance/composer'] = 'dev-master@dev';

		$json['scripts']['pre-update-cmd']		= 'ContaoCommunityAlliance\\ComposerInstaller\\ModuleInstaller::preUpdate';
		$json['scripts']['post-update-cmd']		= 'ContaoCommunityAlliance\\ComposerInstaller\\ModuleInstaller::postUpdate';
		$json['scripts']['post-autoload-dump']	= 'ContaoCommunityAlliance\\ComposerInstaller\\ModuleInstaller::postAutoloadDump';

		$json['config']['cache-dir']			= $this->getContext()->getComposerCachePath();

		$json['name']['repositories'][0]['type']= 'composer';
		$json['name']['repositories'][0]['url']	= $this->getContext()->getLegacyRepositoryURL();
		$json['name']['repositories'][1]['type']= 'artifact';
		$json['name']['repositories'][1]['url']	= $this->getContext()->getArtifactRepositoryPath();

		$file->write($json);
	}

	public function writeRuntimeArtefact() {
		$path = TL_ROOT . '/system/modules/!ccc/config';
		mkdir($path, 0777, true);
		file_put_contents($path + '/config.php', '<?php CCCLauncher::launch();');
	}

	/**
	 * Clear composer cache.
	 *
	 * @param \Input $input
	 */
	public function clearComposerCache() {
		$path = $this->getContext()->getComposerCachePath();
		if(!is_dir($path)) {
			return;
		}

		$fs = new Filesystem();
		$fs->removeDirectory($path);

		CCCUtil::_addConfirmationMessage($GLOBALS['TL_LANG']['ccc']['cacheCleared']); // TODO
	}

}
