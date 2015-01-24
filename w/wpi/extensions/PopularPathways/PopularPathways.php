<?php
# Not a valid entry point, skip unless MEDIAWIKI is defined
if (!defined('MEDIAWIKI')) {
		echo <<<EOT
To install PopularPathways, put the following line in LocalSettings.php:
require_once( "$IP/extensions/PopularPathway/PopularPathway.php" );
EOT;
		exit( 1 );
}


$c = "PopularPathways";


$wgExtensionMessagesFiles[$c] = __DIR__ . "/$c.i18n.php";
$wgAutoloadClasses[$c] = __DIR__ . "/{$c}_body.php";
$wgSpecialPages[$c] = $c;

$wgSpecialPages["{$c}Page"] = "Legacy{$c}";
$wgAutoloadClasses["Legacy{$c}"] = __DIR__ . "/{$c}_body.php";
