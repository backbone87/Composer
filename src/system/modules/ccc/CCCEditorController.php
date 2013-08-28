<?php

use Composer\Json\JsonFile;

use Composer\IO\NullIO;
use Composer\Util\ConfigValidator;

class CCCEditorController implements CCCController {

	public static function create(CCCContext $ctx) {
		return new self($ctx);
	}

	const DEFAULT_TEMPLATE = 'ccc_editor';

	const FORM_SUBMIT = 'ccc_editor';

	private $ctx;

	public function __construct(CCCContext $ctx) {
		$this->ctx = $ctx;
	}

	public function getContext() {
		return $this->ctx;
	}

	public function generate() {
		if($this->isSubmitted()) {
			$path = $this->writeSubmittedComposerJSON();
			$this->validateComposerJSON($path)
				? $this->moveTemporaryComposerJSON()
				: $this->removeTemporaryComposerJSON();
			CCCUtil::_reload();
		}
		return $this->createTemplate()->parse();
	}

	public function isSubmitted() {
		return Input::getInstance()->post('FORM_SUBMIT') == self::FORM_SUBMIT;
	}

	public function getSubmittedComposerJSON() {
		$json = Input::getInstance()->postRaw('ctrl_json');
		$json = html_entity_decode($json, ENT_QUOTES, 'UTF-8'); // TODO OH: y?
		return $json;
	}

	public function getTemporaryComposerJSONPath($relative = false) {
		return $this->getContext()->getComposerJSONPath($relative) . '~';
	}

	public function writeSubmittedComposerJSON() {
		$json = $this->getSubmittedComposerJSON();
		$path = $this->getTemporaryComposerJSONPath();
		file_put_contents($path, $json);
		return $path;
	}

	public function moveTemporaryComposerJSON() {
		rename($this->getTemporaryComposerJSONPath(), $this->getContext()->getComposerJSONPath());
		CCCUtil::_addConfirmationMessage($GLOBALS['TL_LANG']['ccc_editor']['updated']);
	}

	public function removeTemporaryComposerJSON() {
		unlink($this->getTemporaryComposerJSONPath());
	}

	public function validateComposerJSON($path, $silent = false) {
		$validator = new ConfigValidator(new NullIO());
		$problems = array_combine(array('error', 'publishError', 'warning'), $validator->validate($path));

		if($silent) {
			return !$problems;
		}

		if($problems) {
			foreach($problems as $severity => $msgs) {
				foreach((array) $msgs as $msg) {
					CCCUtil::_addErrorMessage(sprintf($GLOBALS['TL_LANG']['ccc_editor'][$severity], $msg));
				}
			}
			return false;
		} else {
			CCCUtil::_addConfirmationMessage($GLOBALS['TL_LANG']['ccc_editor']['validated']);
			return true;
		}
	}

	public function createTemplate($tpl = self::DEFAULT_TEMPLATE) {
		$tpl = new BackendTemplate($tpl);

		$tpl->json = $this->isSubmitted()
			? $this->getSubmittedComposerJSON()
			: file_get_contents($this->getContext()->getComposerJSONPath());

		return $tpl;
	}

}
