<?php

/**
 * Composer integration for Contao.
 *
 * PHP version 5
 *
 * @copyright  ContaoCommunityAlliance 2013
 * @author     Dominik Zogg <dominik.zogg at gmail.com>
 * @author     Tristan Lins <tristan.lins@bit3.de>
 * @package    Composer
 * @license    LGPLv3
 * @filesource
 */

define('COMPOSER_MIN_PHPVERSION', '5.3.4'); // TODO remove
define('COMPOSER_DIR_RELATIVE', 'composer');
define('COMPOSER_DIR_ABSOULTE', TL_ROOT . '/' . COMPOSER_DIR_RELATIVE);
define('COMPOSER_ARTIFACT_DIR_RELATIVE', COMPOSER_DIR_RELATIVE . '/packages');
define('COMPOSER_ARTIFACT_DIR_ABSOULTE', TL_ROOT . '/' . COMPOSER_ARTIFACT_DIR_RELATIVE);

$GLOBALS['BE_MOD']['system']['ccc']['callback']	= 'CCCBackendModule';
$GLOBALS['BE_MOD']['system']['ccc']['icon']		= 'system/modules/ccc/assets/images/icon.png';
