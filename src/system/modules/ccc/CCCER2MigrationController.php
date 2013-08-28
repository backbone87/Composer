<?php

/**
 *
 * @author Oliver Hoff <oliver@hofff.com>
 */
class CCCER2MigrationController implements CCCController {

	public static function create(CCCContext $ctx) {
		return new self($ctx);
	}

	const DEFAULT_TEMPLATE = 'ccc_er2_migration';

	private $ctx;

	public function __construct(CCCContext $ctx) {
		$this->ctx = $ctx;
		CCCUtil::_loadLanguageFile('ccc_er2_migration');
	}

	public function getContext() {
		return $this->ctx;
	}

	public function generate() {
		return $this->createTemplate()->parse();
	}

	public function getSubmittedExtensionNames() {
		return (array) Input::getInstance()->post('extensions');
	}

	public function createTemplate($tpl = self::DEFAULT_TEMPLATE) {
		$er2Migration = CCCER2Migration::create($this->getContext());

		foreach($er2Migration->getInstalledPortableExtensions() as $extension) {
			$extensions[] = array(
				'er2Name'			=> $extension['extension'],
				'er2Version'		=> $er2Migration->formatER2Version($extension['version']),
				'er2Build'			=> $extension['build'],
				'composerName'		=> $er2Migration->convertER2ExtensionNameToComposerPackageName($extension['extension']),
				'composerConstraint'=> $er2Migration->convertER2VersionToComposerConstraint($extension['version'], $extension['build']),
			);
		}

		$tpl = new BackendTemplate($tpl);

		$tpl->action		= Environment::getInstance()->request;
		$tpl->formSubmit	= 'ccc_er2_migration';
		$tpl->extensions	= (array) $extensions;

		return $tpl;
	}

}
