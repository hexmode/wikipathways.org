<?php
# Not a valid entry point, skip unless MEDIAWIKI is defined
if (!defined('MEDIAWIKI')) {
	echo <<<EOT
		To install DiffAppletPage, put the following line in LocalSettings.php:
	require_once( "$IP/extensions/DiffAppletPage/DiffAppletPage.php" );
EOT;
	exit( 1 );
}

$c = 'DiffAppletPage';
$wgAutoloadClasses[$c] = dirname(__FILE__) . "/{$c}_body.php";
$wgSpecialPages[$c] = $c;
$wgExtensionMessagesFiles[$c] = dirname( __FILE__ ) . "/{$c}.i18n.php";
