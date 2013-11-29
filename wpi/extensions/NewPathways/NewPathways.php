<?php
# Not a valid entry point, skip unless MEDIAWIKI is defined
if (!defined('MEDIAWIKI')) {
		echo <<<EOT
To install NewPathwaysPage, put the following line in LocalSettings.php:
require_once( "$IP/extensions/NewPathwaysPage/NewPathwaysPage.php" );
EOT;
		exit( 1 );
}

$c = "NewPathways";

$wgSpecialPages[$c] = $c;
$wgAutoloadClasses[$c] = __DIR__ . "/{$c}_body.php";
$wgExtensionMessagesFiles[$c] = __DIR__ . "/{$c}.i18n.php";

$wgSpecialPages["{$c}Page"] = "Legacy{$c}";
$wgAutoloadClasses["Legacy{$c}"] = __DIR__ . "/{$c}_body.php";
