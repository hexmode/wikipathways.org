<?php
# Not a valid entry point, skip unless MEDIAWIKI is defined
if (!defined('MEDIAWIKI')) {
	echo <<<EOT
To install DiffApplet, put the following line in LocalSettings.php:
	require_once( "$IP/extensions/DiffApplet/DiffApplet.php" );
EOT;
	exit( 1 );
}

$c = 'DiffApplet';
$wgAutoloadClasses[$c] = dirname(__FILE__) . "/{$c}_body.php";
$wgSpecialPages[$c] = $c;
$wgExtensionMessagesFiles[$c] = dirname( __FILE__ ) . "/{$c}.i18n.php";

$wgSpecialPages["{$c}Page"] = "Legacy{$c}";
$wgAutoloadClasses["Legacy{$c}"] = __DIR__ . "/{$c}_body.php";
