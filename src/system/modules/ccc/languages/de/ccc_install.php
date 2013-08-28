<?php

$GLOBALS['TL_LANG']['ccc_install']['welcome_headline']
	= 'Contao Composer Client – Installation';

$GLOBALS['TL_LANG']['ccc_install']['welcome_intro']
	= 'Dear user, this is the new Contao package manager, based on the PHP
dependency manager <a href="http://getcomposer.org/" target="_blank">Composer</a>.';

$GLOBALS['TL_LANG']['ccc_install']['welcome_beta']
	= 'This is the public beta phase. We need your help to test this client
until the end of this year. It is planned that the Contao Composer Client will
replace the Contao ER 2 Client as the standard mechanism to install and manage
Contao extensions in the new Contao 3.2 LTS release and any following releases.';

$GLOBALS['TL_LANG']['ccc_install']['welcome_faq']
	= 'FAQ';

$GLOBALS['TL_LANG']['ccc_install']['welcome_faq_intro']
	= 'At first we want to answer the some important questions:';

$GLOBALS['TL_LANG']['ccc_install']['welcome_faq_questions'][] = array(
	'Is it necessary to use this client?',
	'No, not at all. The usage of this client is optional, but some developers
may distribute new features or new extensions only via this package manager.
You will miss fancy new stuff, if you do not use it.'
);

$GLOBALS['TL_LANG']['ccc_install']['welcome_faq_questions'][] = array(
	'Can I install packages that are available in the current extension
repository?',
	'Yes you can. All public packages are synchronized into the new repository
(they are prefixed with <code>contao-legacy/</code>).<br />
<em>Please note that existing commercial extensions cannot be installed with
Composer due to license limitations. Please ask the publisher to support
Composer.</em>'
);

$GLOBALS['TL_LANG']['ccc_install']['welcome_faq_questions'][] = array(
	'Will there be a new extension repository?',
	'Yes, a new extension repository exists on
<a href="https://repository.contao.org/" target="_blank">repository.contao.org</a>.
Currently it is a plain packagist installation, but we will improve it
shortly with all our needs.'
);

$GLOBALS['TL_LANG']['ccc_install']['welcome_faq_questions'][] = array(
	'What is Composer and this Composer package manager?',
	'The answer is too long to be answered here. Read the article about the
Composer Client in the
<a href="http://de.contaowiki.org/Composer_Client" target="_blank">Contao Wiki</a>.'
);

$GLOBALS['TL_LANG']['ccc_install']['welcome_faq_questions'][] = array(
	'Can I switch back to the old package manager?',
	'Yes you can. Go to the composer client settings dialog and chose
"switch back to old client".'
);

$GLOBALS['TL_LANG']['ccc_install']['welcome_faq_questions'][] = array(
	'I have problems with the new client, where can I ask for help?',
	'This client is driven by the community. You can ask in the
<a href="https://community.contao.org/de/forumdisplay.php?6-Entwickler-Fragen" target="_blank">community board</a>,
the official irc channel
<a href="irc://chat.freenode.net/%23contao.composer">#contao.composer</a>
or the
<a href="https://github.com/ContaoCommunityAlliance/Composer/issues" target="_blank">ticket system</a>.'
);

$GLOBALS['TL_LANG']['ccc_install']['welcome_install']
	= 'Installation';

$GLOBALS['TL_LANG']['ccc_install']['welcome_install_intro']
	= 'This wizard will help you to install Composer in your Contao system and
migrate existing ER2 extensions, if necessary.';

$GLOBALS['TL_LANG']['ccc_install']['welcome_next']
	= 'Start installation';


$GLOBALS['TL_LANG']['ccc_install']['settings_explanation']
	= 'Please select the initial configuration. A recommended preselection is
done taking an existing <code>composer.json</code> into account, if any.';


$GLOBALS['TL_LANG']['ccc_install']['summerize_headline']
	= 'Contao Composer Client – Installation – Zusammenfassung &amp; Abschluss';

$GLOBALS['TL_LANG']['ccc_install']['summerize_er2Composer']
	= 'Following ER2 extensions will be migrated into Composer:';

$GLOBALS['TL_LANG']['ccc_install']['summerize_settings']
	= 'Following initial settings will be used to configure Composer:';

$GLOBALS['TL_LANG']['ccc_install']['summerize_install']
	= 'Install';
