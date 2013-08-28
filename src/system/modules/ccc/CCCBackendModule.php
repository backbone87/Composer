<?php

/**
 *
 * @author Oliver Hoff <oliver@hofff.com>
 */
class CCCBackendModule extends BackendModule {

	public function __construct() {
		parent::__construct();
	}

	public function getContext() {
		return CCCDefaultContext::getInstance();
	}

	public function generate() {
		$ctrl = Input::getInstance()->get('c');
		strlen($ctrl) || $ctrl = 'overview';
		$ctrl = 'install';
		return $this->getController($ctrl)->generate();
	}

	public function getController($ctrl) {
		try {
			$class = new ReflectionClass('CCC' . ucfirst($ctrl) . 'Controller');
		} catch(Exception $e) {
			throw new Exception(); // TODO
		}
		if(!$class->implementsInterface('CCCController')) {
			throw new Exception(); // TODO
		}
		return $class->newInstance($this->getContext());
	}

	protected function compile() {
	}

}
