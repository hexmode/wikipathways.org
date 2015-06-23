<?php
# Not a valid entry point, skip unless MEDIAWIKI is defined
if (!defined('MEDIAWIKI')) {
		echo <<<EOT
To install MostEditedPathways, put the following line in LocalSettings.php:
require_once( "$IP/extensions/MostEditedPathways/MostEditedPathways.php" );
EOT;
		exit( 1 );
}

$c = "MostEditedPathways";

$wgExtensionMessagesFiles[$c] = __DIR__ . "/$c.i18n.php";
$wgAutoloadClasses[$c] = __DIR__ . "/{$c}_body.php";
$wgSpecialPages[$c] = $c;

$wgSpecialPages["{$c}Page"] = "Legacy{$c}";
$wgAutoloadClasses["Legacy{$c}"] = __DIR__ . "/{$c}_body.php";
