<?php

define( 'CONTENT_MODEL_PATHWAY', 'pathway' );
$wgContentHandlerTextFallback = 'serialize';
$wgExtraNamespaces[NS_PATHWAY]            = 'Pathway';
$wgNamespaceContentModels[NS_PATHWAY]     = CONTENT_MODEL_PATHWAY;
$wgContentHandlers[CONTENT_MODEL_PATHWAY] = 'PathwayHandler';

$wgAutoloadClasses['PathwayCache']      = "$IP/wpi/extensions/Pathways/PathwayCache.php";
$wgAutoloadClasses['PathwayContent']      = "$IP/wpi/extensions/Pathways/PathwayContent.php";
$wgAutoloadClasses['PathwayHandler']      = "$IP/wpi/extensions/Pathways/PathwayHandler.php";

$wgAutoloadClasses['FeaturedPathway']     = "$IP/wpi/extensions/PathwayOfTheDay/FeaturedPathway.php";
$wgAutoloadClasses['GpmlHistoryPager']    = "$IP/wpi/extensions/pathwayHistory.php";
$wgAutoloadClasses['Interaction']         = "$IP/wpi/extensions/Pathways/PathwayData.php";
$wgAutoloadClasses['MetaDataCache']       = "$IP/wpi/extensions/Pathways/MetaDataCache.php";
$wgAutoloadClasses['MimeTypes']           = "$IP/wpi/MimeTypes.php";
$wgAutoloadClasses['MwUtils']             = "$IP/wpi/MwUtils.php";
$wgAutoloadClasses['Organism']            = "$IP/wpi/extensions/Pathways/Organism.php";
$wgAutoloadClasses['Pathway']             = "$IP/wpi/extensions/Pathways/Pathway.php";
$wgAutoloadClasses['PathwayBibliography'] = "$IP/wpi/extensions/pathwayBibliography.php";
$wgAutoloadClasses['PathwayData']         = "$IP/wpi/extensions/Pathways/PathwayData.php";
$wgAutoloadClasses['PathwayInfo']         = "$IP/wpi/extensions/pathwayInfo.php";
$wgAutoloadClasses['PathwayOfTheDay']     = "$IP/wpi/extensions/PathwayOfTheDay/PathwayOfTheDay_class.php";
$wgAutoloadClasses['PathwayPage']         = "$IP/wpi/extensions/Pathways/PathwayPage.php";
$wgAutoloadClasses['PathwayThumb']        = "$IP/wpi/extensions/pathwayThumb.php";
$wgAutoloadClasses['PathwayViewer']       = "$IP/wpi/extensions/PathwayViewer/PathwayViewer.php";
$wgAutoloadClasses['TaggedPathway']       = "$IP/wpi/extensions/PathwayOfTheDay/TaggedPathway.php";
$wgAutoloadClasses['Xref']                = "$IP/wpi/extensions/Pathways/PathwayData.php";

$wgExtensionMessagesFiles['PathwayPage'] = "$IP/wpi/extensions/Pathways/PathwayPage.i18n.php";

$wgExtensionFunctions[] = "LocalHooks::extensionFunctions";

$wgHooks['LanguageGetMagic'][]  = 'LocalHooks::getMagic';

$wfPathwayViewerPath = WPI_URL . "/extensions/PathwayViewer";

/* Need autoloader here */
require_once( "$IP/wpi/wpi.php" );
require_once( "$IP/wpi/extensions/siteStats.php" );
require_once( "$IP/wpi/extensions/pathwayInfo.php" );
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
require_once( "$IP/wpi/search.php");
