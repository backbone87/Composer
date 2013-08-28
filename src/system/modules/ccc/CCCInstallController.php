<?php

/**
 *
 * @author Oliver Hoff <oliver@hofff.com>
 */
class CCCInstallController implements CCCController {

	public static function create(CCCContext $ctx) {
		return new self($ctx);
	}

	const STEP_WELCOME			= 'ccc_install_welcome';
	const STEP_CHECK			= 'ccc_check';
	const STEP_ER2_TO_COMPOSER	= 'ccc_er2_to_composer';
	const STEP_SETTINGS			= 'ccc_settings';
	const STEP_SUMMARY			= 'ccc_install_summary';
	const STEP_FINISHED			= 'ccc_install_finished';

	const DEFAULT_WELCOME_TEMPLATE = 'ccc_install_welcome';
	const DEFAULT_SUMMARY_TEMPLATE = 'ccc_install_summary';

	private $ctx;
	private $data;

	private $check;
	private $er2Migration;
	private $settings;

	public function __construct(CCCContext $ctx) {
		$this->ctx = $ctx;
		$this->data = &$_SESSION['ccc']['install'];

		CCCUtil::_loadLanguageFile('ccc');
		CCCUtil::_loadLanguageFile('ccc_install');

		$this->check = CCCCheck::create($ctx);
		$this->er2ToComposer = CCCER2ToComposer::create($ctx);
		$this->settings = CCCSettings::create($ctx);
	}

	public function getContext() {
		return $this->ctx;
	}

	public function generate() {
		$step = $this->handleSubmit();

		if($step == self::STEP_FINISHED) {
			CCCInstaller::install($this->ctx, $this->data);
			$this->cleanSession();
			CCCUtil::_redirect('contao/main.php?do=ccc');
			return '';
		}

		if($step == self::STEP_WELCOME) {
			$tpl = $this->createWelcomeTemplate();
		}

		if($step == self::STEP_CHECK) {
			$commercial = CCCER2Migration::create($this->getContext())->getCommericalOrPrivateExtensionNames();
			$tpl = $this->getCheckController()->createTemplate();
			$tpl->commercial = $commercial;
			$tpl->warn |= $commercial;
		}

		if($step == self::STEP_ER2_TO_COMPOSER) {
			if(CCCER2Migration::create($this->getContext())->getInstalledPortableExtensions()) {
				$tpl = $this->getER2MigrationController()->createTemplate();
			} else {
				$step = self::STEP_SETTINGS;
			}
		}

		if($step == self::STEP_SETTINGS) {
			$tpl = $this->getSettingsController()->createTemplate();
			$tpl->explanation = $GLOBALS['TL_LANG']['ccc_install']['settings_explanation'];
			$tpl->submitLabel = $GLOBALS['TL_LANG']['ccc_install']['settings_next'];
		}

		if($step == self::STEP_SUMMARY) {
			$tpl = $this->createSummaryTemplate();
		}

		if(!$tpl) {
			throw new Exception('should never occur', 1); // TODO
		}

		$tpl->formSubmit = $step;
		$tpl->action = 'contao/main.php?do=ccc&command=install';
		return $tpl->parse();
	}

	public function getCheckController() {
		isset($this->check) || $this->check = CCCCheckController::create($this->getContext());
		return $this->check;
	}

	public function getER2MigrationController() {
		isset($this->er2Migration) || $this->er2Migration = CCCER2ToComposerController::create($this->getContext());
		return $this->er2Migration;
	}

	public function getSettingsController() {
		isset($this->settings) || $this->settings = CCCSettingsController::create($this->getContext());
		return $this->settings;
	}

	protected function handleSubmit() {
		switch(Input::getInstance()->post('FORM_SUBMIT')) {
			default:
				$nextStep = self::STEP_WELCOME;
				break;

			case self::STEP_WELCOME:
				$nextStep = self::STEP_CHECK;
				break;

			case self::STEP_CHECK:
				$nextStep = self::STEP_ER2_TO_COMPOSER;
				break;

			case self::STEP_ER2_TO_COMPOSER:
				$this->data['extensions'] = $this->getER2MigrationController()->getSubmittedExtensionNames();
				$nextStep = self::STEP_SETTINGS;
				break;

			case self::STEP_SETTINGS:
				if($this->settings->validateWidgets()) {
					$this->data['settings'] = $this->getSettingsController()->getDataFromWidgets();
					$nextStep = self::STEP_SUMMARY;

				} else {
					$nextStep = self::STEP_SETTINGS;
				}
				break;

			case self::STEP_SUMMARY:
				$nextStep = self::STEP_FINISHED;
				break;
		}
		return $nextStep;
	}

	public function cleanSession() {
		unset($_SESSION['ccc']['install']);
		unset($this->data);
		$this->data = &$_SESSION['ccc']['install'];
	}

	public function createWelcomeTemplate($tpl = self::DEFAULT_WELCOME_TEMPLATE) {
		$tpl = new BackendTemplate($tpl);
		return $tpl;
	}

	public function createSummaryTemplate($tpl = self::DEFAULT_SUMMARY_TEMPLATE) {
		$tpl = new BackendTemplate($tpl);

		$tpl->extensions = $this->data['extensions'];
		$tpl->settings = $this->data['settings'];

		return $tpl;
	}

}
