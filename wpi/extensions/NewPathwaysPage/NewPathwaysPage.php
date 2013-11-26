<?php
# Not a valid entry point, skip unless MEDIAWIKI is defined
if (!defined('MEDIAWIKI')) {
		echo <<<EOT
To install NewPathwaysPage, put the following line in LocalSettings.php:
require_once( "$IP/extensions/NewPathwaysPage/NewPathwaysPage.php" );
EOT;
		exit( 1 );
}

$c = "NewPathwaysPage";
$wgAutoloadClasses[$c] = dirname(__FILE__) . "/{$c}_body.php";
$wgSpecialPages[$c] = $c;
$wgExtensionMessagesFiles[$c] = dirname( __FILE__ ) . "/{$c}.i18n.php";
