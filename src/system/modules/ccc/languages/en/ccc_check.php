<?php

$GLOBALS['TL_LANG']['ccc_check']['check_headline']
	= 'Contao Composer Client – System check';

$GLOBALS['TL_LANG']['ccc_check']['check_enabled']
	= 'is enabled';
$GLOBALS['TL_LANG']['ccc_check']['check_disabled']
	= 'is disabled';
$GLOBALS['TL_LANG']['ccc_check']['check_confirm']
	= 'I understand this.';

$GLOBALS['TL_LANG']['ccc_check']['check_contao']
	= 'Contao 2.11.11 or later';
$GLOBALS['TL_LANG']['ccc_check']['check_contaoExplain']
	= 'You are using Contao %s currently. You need to update Contao.';

$GLOBALS['TL_LANG']['ccc_check']['check_php']
	= 'PHP ' . CCCCheck::PHP_MIN_VERSION . ' or later';
$GLOBALS['TL_LANG']['ccc_check']['check_phpExplain']
	= 'You are using PHP %s currently. You need to update PHP.';

$GLOBALS['TL_LANG']['ccc_check']['check_smh']
	= 'Contao safe mode hack';
$GLOBALS['TL_LANG']['ccc_check']['check_smhExplain']
	= 'The Contao safe mode hack is a workaround for the limitations of the PHP
safe mode. This is a deprecated technology and will be removed in future PHP
versions, because it caused a lot of problems when creating innovative and
feature-rich software. Ask your hoster for a proper setup PHP environment that
does not rely on the PHP safe mode.';

$GLOBALS['TL_LANG']['ccc_check']['check_fopen']
	= 'Open URLs via <code>fopen</code>';
$GLOBALS['TL_LANG']['ccc_check']['check_fopenExplain']
	= '<code>allow_url_fopen</code> is deactivated by some hosters under the
assumption that is rises the system\'s security. This does not apply to well
written software like Contao. Although poor PHP scripts can cause the same
problems through a lot of other ways, thought to be prevented by disabling
<code>allow_url_fopen</code>. Ask your hoster to allow opening of URLs via
<code>fopen</code>.';

$GLOBALS['TL_LANG']['ccc_check']['check_phar']
	= 'Phar support';
$GLOBALS['TL_LANG']['ccc_check']['check_pharExplain']
	= 'Phar support is not properly configured. Ask your hoster for a solution
of this problem.';

$GLOBALS['TL_LANG']['ccc_check']['check_apcDisabled']
	= 'The APC opcode cache is disabled';
$GLOBALS['TL_LANG']['ccc_check']['check_apcCanDisable']
	= 'The APC opcode cache can be disabled at runtime';
$GLOBALS['TL_LANG']['ccc_check']['check_apcCanDisableExplain']
	= 'The APC opcode cache will be temporarily disabled whenever you use the
Contao Composer Client.';
$GLOBALS['TL_LANG']['ccc_check']['check_apcEnabled']
	= 'The APC opcode cache is enabled';
$GLOBALS['TL_LANG']['ccc_check']['check_apcEnabledExplain']
	= 'This may produce unexpected exceptions. If you have unexpected "cannot
redeclare class" errors, try to disable APC or upgrade APC to 3.0.13 or later,
so it can be disabled at runtime.';
$GLOBALS['TL_LANG']['ccc_check']['check_apcHelp']
	= 'In general it is recommended that you use another PHP cache.';

$GLOBALS['TL_LANG']['ccc_check']['check_commercial']
	= 'Contao ER2 commercial extensions';
$GLOBALS['TL_LANG']['ccc_check']['check_commercialExplain']
	= 'The commercial extensions installed via the Contao ER2 repository will
not be migrated to Composer by this wizard.';
$GLOBALS['TL_LANG']['ccc_check']['check_commercialHelp']
	= 'Please consult the publishers of these extensions, if they will support
Composer and for further instructions.';

$GLOBALS['TL_LANG']['ccc_check']['check_result']
	= 'Result';
$GLOBALS['TL_LANG']['ccc_check']['check_pass']
	= 'You can use the Contao Composer Client.';
$GLOBALS['TL_LANG']['ccc_check']['check_warn']
	= 'You can use the Contao Composer Client, but further actions may have to be taken to
ensure a correct functionality of Composer.';
$GLOBALS['TL_LANG']['ccc_check']['check_fail']
	= 'You can not use the Contao Composer Client. Please consult your hoster and / or system
developers to resolve outstanding incompatibilities.';

$GLOBALS['TL_LANG']['ccc_check']['check_next']
	= 'Next';





