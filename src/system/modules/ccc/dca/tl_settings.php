<?php

$callback = &$GLOBALS['TL_DCA']['tl_settings']['fields']['inactiveModules']['options_callback'];
CCCIntegration::getInstance()->setLegacyModuleOptionsCallback($callback);
$callback = array('CCCIntegration', 'callbackModuleOptions');
unset($callback);
