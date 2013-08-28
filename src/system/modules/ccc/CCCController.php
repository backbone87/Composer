<?php

interface CCCController {

	public static function create(CCCContext $ctx);

	public function getContext();

	public function generate();

}
