<?php

use Database\Installer;

class CCCUpdateDatabaseController extends CCCController {

	public static function create(CCCContext $ctx) {
		return new self($ctx);
	}

	const DEFAULT_TEMPLATE = 'ccc_update_database';

	const FORM_SUBMIT = 'ccc_update_database';

	private $ctx;

	private $form;

	private $queries;

	public function __construct(CCCContext $ctx) {
		$this->ctx = $ctx;
	}

	public function getContext() {
		return $this->ctx;
	}

	public function generate() {
		CCCUtil::_handleRunOnce();

		if($this->isSubmitted()) {
			$this->executeUpdates($this->getSubmittedUpdateKeys());
			$reload = true;
		}

		if(!$this->getUpdateQueries()) {
			CCCUtil::_addConfirmationMessage($GLOBALS['TL_LANG']['ccc_update_database']['uptodate']); // TODO
			CCCUtil::_redirect('contao/main.php?do=ccc');
		}

		$reload && CCCUtil::_reload();

		return $this->createTemplate()->parse();
	}

	public function createDatabaseInstaller() {
		return version_compare(VERSION, '3', '<') ? new DbInstaller() : new Installer();
	}

	public function getUpdateQueries() {
		// this fills the session with the sql commands
		$this->createDatabaseInstaller()->generateSqlForm();
		$queries = $_SESSION['sql_commands'];
		unset($_SESSION['sql_commands']);
		return $queries;
	}

	public function getFormSubmit() {
		return self::FORM_SUBMIT;
	}

	public function isSubmitted() {
		return Input::getInstance()->post('FORM_SUBMIT') == $this->getFormSubmit();
	}

	public function getSubmittedUpdateKeys() {
		return deserialize(Input::getInstance()->post('sql'), true);
	}

	public function executeUpdates(array $keys) {
		$queries = $this->getUpdateQueries();

		foreach($keys as $key) if(isset($queries[$key])) {
			$query = str_replace(
				'DEFAULT CHARSET=utf8;',
				'DEFAULT CHARSET=utf8 COLLATE ' . $GLOBALS['TL_CONFIG']['dbCollation'] . ';',
				$queries[$key]
			);
			Database::getInstance()->query($query);
			unset($queries[$key]);
			$cnt++;
		}

		CCCUtil::_addConfirmationMessage(sprintf($GLOBALS['TL_LANG']['ccc_update_database']['updated'], $cnt)); // TODO
	}

	public function createTemplate($tpl = self::DEFAULT_TEMPLATE) {
		$tpl = new BackendTemplate($tpl);
		$tpl->action = Environment::getInstance()->request;
		$tpl->formSubmit = $this->getFormSubmit();
		$tpl->form = $this->createDatabaseInstaller()->generateSqlForm();
		return $tpl;
	}

}
