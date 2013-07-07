<?php

/**
 *
 * @author Oliver Hoff <oliver@hofff.com>
 */
class CCCBackendModule extends BackendModule
{

	public function __construct() {
		parent::__construct();
	}

	public function generate() {
		$command = Input::getInstance()->get('command');
		$command = 'CCC' . ucfirst($command);
		if(!class_exists($command)) {
			throw new Exception(); // TODO
		}
		$command = new $command();
		return $command->generate();
	}

}
