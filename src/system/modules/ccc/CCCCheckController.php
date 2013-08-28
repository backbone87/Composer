<?php

/**
 *
 * @author Oliver Hoff <oliver@hofff.com>
 */
class CCCCheckController implements CCCController {

	public static function create(CCCContext $ctx) {
		return new self($ctx);
	}

	const DEFAULT_TEMPLATE = 'ccc_check';

	private $ctx;

	protected function __construct(CCCContext $ctx) {
		$this->ctx = $ctx;
		CCCUtil::_loadLanguageFile('ccc_check');
	}

	public function getContext() {
		return $this->ctx;
	}

	public function generate() {
		return $this->createTemplate()->parse();
	}

	public function createTemplate($tpl = self::DEFAULT_TEMPLATE) {
		$tpl = new BackendTemplate($tpl);

		$chk = CCCCheck::create($this->getContext());

		$tpl->action		= Environment::getInstance()->request;
		$tpl->formSubmit	= 'ccc_check';

		$tpl->contao		= $chk->isCompatibleContaoVersion();
		$tpl->contaoVersion	= $chk->getContaoVersion();
		$tpl->php			= $chk->isCompatiblePHPVersion();
		$tpl->phpVersion	= $chk->getPHPVersion();
		$tpl->smh			= $chk->isSafeModeHackDisabled();
		$tpl->fopen			= $chk->isURLFopenAllowed();
		$tpl->phar			= $chk->isPharSupported();
		$tpl->apc			= $chk->isAPCDisabled();
		$tpl->apcCanDisable	= $chk->canDisableAPCCacheByDefault();

		$tpl->composer		= $tpl->contao && $tpl->php && $tpl->smh && $tpl->fopen && $tpl->phar;
		$tpl->warn			= !$tpl->apc;

		return $tpl;
	}

}
