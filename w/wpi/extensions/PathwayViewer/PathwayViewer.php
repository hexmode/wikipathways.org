<?php
require_once('DetectBrowserOS.php');

/*
 * Loads an interactive pathway viewer using svgweb.
 */

$wgExtensionFunctions[] = 'wfPathwayViewer';
$wgHooks['LanguageGetMagic'][]  = 'wfPathwayViewer_Magic';

function wfPathwayViewer() {
	global $wgParser;
	$wgParser->setFunctionHook( "PathwayViewer", "displayPathwayViewer" );
}

function wfPathwayViewer_Magic( &$magicWords, $langCode ) {
	$magicWords['PathwayViewer'] = array( 0, 'PathwayViewer' );
	return true;
}

function displayPathwayViewer(&$parser, $pwId, $imgId) {
	global $wgOut, $wgStylePath, $wpiJavascriptSources, $wgScriptPath,
		$wpiJavascriptSnippets, $jsRequireJQuery, $wgRequest, $wgJsMimeType;

	$jsRequireJQuery = true;

	try {
		$parser->disableCache();

		//Add javascript dependencies
		// TODO it appears the following line only adds JS dependencies for the XrefPanel, which
		// we are no longer using, so I think the XrefPanel code should be removed. --AR
		XrefPanel::addXrefPanelScripts();
		$wpiJavascriptSources = array_merge($wpiJavascriptSources, PathwayViewer::getJsDependencies());

		$revision = $wgRequest->getval('oldid');
		$pvPwAdded[] = $pwId . '@' . $revision;

		$pathway = Pathway::newFromTitle($pwId);
		if($revision) {
			$pathway->setActiveRevision($revision);
		}
		$png = $pathway->getFileURL(FILETYPE_PNG);
				$gpml = $pathway->getFileURL(FILETYPE_GPML);

               $script = "<script>var gpmlFilePath = \"$gpml\"; var pngFilePath = \"$png\";</script>";
		return array($script, 'isHTML'=>1, 'noparse'=>1);
	} catch(Exception $e) {
		return "invalid pathway title: $e";
	}
	return true;
}

class PathwayViewer {
	static function getJsDependencies() {
		global $wgScriptPath;

		if(preg_match('/(?i)msie [6-8]/',$_SERVER['HTTP_USER_AGENT'])) {
			// if IE<=8
			$scripts = array(
			);
		}
		else {
			// if IE>8 or any other browser
			$scripts = array(
				"$wgScriptPath/wpi/js/querystring-parameters.js",
				"$wgScriptPath/wpi/extensions/PathwayViewer/pathwayviewer.js",
				"$wgScriptPath/wpi/js/jquery/plugins/jquery.mousewheel.js",
				"$wgScriptPath/wpi/js/jquery/plugins/jquery.layout.min-1.3.0.js",
				// libs required by pathwayviewer.js
				"//cdnjs.cloudflare.com/ajax/libs/lodash.js/2.4.1/lodash.min.js",
				"//cdnjs.cloudflare.com/ajax/libs/modernizr/2.7.1/modernizr.min.js",
				// pvjs
				"$wgScriptPath/wpi/lib/pathvisiojs/js/pathvisiojs-2.2.0.bundle.min.js",
			);
		}

		return $scripts;
	}
}
