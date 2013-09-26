<?php
require_once( "$IP/wpi/wpi.php" );
require_once( "$IP/wpi/extensions/Pathways/Organism.php" );
require_once( "$IP/wpi/extensions/Pathways/PathwayData.php" );
require_once( "$IP/wpi/extensions/Pathways/MetaDataCache.php" );
require_once( "$IP/wpi/extensions/PathwayOfTheDay/PathwayOfTheDay.php" );
require_once( "$IP/wpi/extensions/siteStats.php" );
require_once( "$IP/wpi/extensions/pathwayInfo.php" );
require_once( "$IP/wpi/extensions/imageSize.php" );
require_once( "$IP/wpi/extensions/magicWords.php" );
require_once( "$IP/wpi/extensions/PopularPathwaysPage2/PopularPathwaysPage.php" );
require_once( "$IP/wpi/extensions/MostEditedPathwaysPage/MostEditedPathwaysPage.php" );
require_once( "$IP/wpi/extensions/NewPathwaysPage/NewPathwaysPage.php" );
require_once( "$IP/wpi/extensions/CreatePathwayPage/CreatePathwayPage.php" );
require_once( "$IP/wpi/extensions/LabeledSectionTransclusion/compat.php" );
require_once( "$IP/wpi/extensions/LabeledSectionTransclusion/lst.php" );
require_once( "$IP/wpi/extensions/LabeledSectionTransclusion/lsth.php" );
require_once( "$IP/wpi/extensions/SearchPathways/SearchPathways.php" );
require_once( "$IP/wpi/extensions/SearchPathways/searchPathwaysBox.php" );
require_once( "$IP/wpi/extensions/button.php" );
require_once( "$IP/wpi/extensions/imageLink.php" );
require_once( "$IP/wpi/extensions/BrowsePathways/BrowsePathways.php" );
require_once( "$IP/wpi/extensions/editApplet.php" );
require_once( "$IP/wpi/extensions/listPathways.php" );
require_once( "$IP/wpi/extensions/movePathway.php" );
require_once( "$IP/wpi/extensions/deletePathway.php" );
require_once( "$IP/wpi/batchDownload.php" );
require_once( "$IP/wpi/extensions/Pathways/PathwayPage.php" );
require_once( "$IP/wpi/extensions/SpecialWishList/PathwayWishList.php");
require_once( "$IP/wpi/extensions/SpecialWishList/SpecialWishList.php" );
require_once( "$IP/wpi/extensions/SpecialWishList/TopWishes.php" );
require_once( "$IP/wpi/extensions/DiffAppletPage/DiffAppletPage.php" );
require_once( "$IP/wpi/extensions/RecentPathwayChanges/RecentPathwayChanges.php" );
require_once( "$IP/wpi/extensions/ParserFunctions/ParserFunctions.php" );
require_once( "$IP/wpi/extensions/CheckGpmlOnSave.php" );
require_once( "$IP/wpi/extensions/CreateUserPage.php" );
require_once( "$IP/wpi/extensions/CurationTags/CurationTags.php" );
#require_once( "$IP/wpi/extensions/UserSnoop.php" );
require_once( "$IP/wpi/extensions/AuthorInfo/AuthorInfo.php" );
require_once( "$IP/wpi/extensions/CurationTags/SpecialCurationTags/SpecialCurationTags.php" );
require_once( "$IP/wpi/extensions/UserLoginLog/UserLoginLog.php" );
require_once( "$IP/wpi/extensions/ShowError/ShowError.php" );
require_once( "$IP/wpi/extensions/pathwayParserFunctions.php" );
require_once( "$IP/wpi/extensions/PrivatePathways/PrivatePathways.php" );
require_once( "$IP/wpi/extensions/PrivatePathways/ListPrivatePathways.php" );
require_once( "$IP/wpi/extensions/PrivatePathways/PrivateContributions.php" );
require_once( "$IP/wpi/extensions/recentChangesBox.php" );
require_once( "$IP/wpi/extensions/otag/otags_main.php" );
require_once( "$IP/wpi/extensions/ontologyindex/ontologyindex.php" );
require_once( "$IP/wpi/extensions/StubManager/StubManager.php" );
require_once( "$IP/wpi/extensions/ParserFunctionsHelper/ParserFunctionsHelper.php" );
require_once( "$IP/wpi/extensions/SecureHTML/SecureHTML.php" );
require_once( "$IP/wpi/extensions/RSS/rss.php" );
require_once( "$IP/wpi/extensions/XrefPanel.php" );
require_once( "$IP/wpi/statistics/StatisticsHook.php" );
require_once( "$IP/wpi/extensions/PageEditor/PageEditor.php" );
require_once( "$IP/wpi/extensions/ContributionScores/ContributionScores.php" );
require_once( "$IP/wpi/extensions/PullPages/PullPages.php" );
require_once( "$IP/wpi/DetectBrowserOS.php");

$wgExtensionFunctions[] = "wfPathwayBibliography";
$wgAutoloadClasses['PathwayBibliography'] = "$IP/wpi/extensions/pathwayBibliography.php";
function wfPathwayBibliography() {
	global $wgParser;
	$wgParser->setHook( "pathwayBibliography", "PathwayBibliography::output" );
}

$wgExtensionFunctions[] = "wfPathwayHistory";
$wgAutoloadClasses['GpmlHistoryPager'] = "$IP/wpi/extensions/pathwayHistory.php";
function wfPathwayHistory() {
	global $wgParser;
	$wgParser->setHook( "pathwayHistory", "GpmlHistoryPager::history" );
}

$wfPathwayViewerPath = WPI_URL . "/extensions/PathwayViewer";
$wgExtensionFunctions[] = 'wfPathwayViewer';
$wgAutoloadClasses['PathwayViewer'] = "$IP/wpi/extensions/PathwayViewer/PathwayViewer.php";
$wgHooks['LanguageGetMagic'][]  = 'wfPathwayViewer_Magic';
function wfPathwayViewer() {
	global $wgParser;
	$wgParser->setFunctionHook( "PathwayViewer", "PathwayViewer::display" );
}
function wfPathwayViewer_Magic( &$magicWords, $langCode ) {
	$magicWords['PathwayViewer'] = array( 0, 'PathwayViewer' );
	return true;
}


$wgAutoloadClasses['PathwayThumb'] = "$IP/wpi/extensions/pathwayThumb.php";
$wgExtensionFunctions[] = 'wfPathwayThumb';
$wgHooks['LanguageGetMagic'][]  = 'wfPathwayThumb_Magic';

function wfPathwayThumb() {
	global $wgParser;
	$wgParser->setFunctionHook( "pwImage", "PathwayThumb::render" );
}

function wfPathwayThumb_Magic( &$magicWords, $langCode ) {
	$magicWords['pwImage'] = array( 0, 'pwImage' );
	return true;
}

$wgAutoloadClasses['PathwayPage'] = "$IP/wpi/extensions/Pathways/PathwayPage.php";
$wgExtensionMessagesFiles['PathwayPage'] = "$IP/wpi/extensions/Pathways/PathwayPage.i18n.php";