<?php

if ( !defined( 'MEDIAWIKI' ) ) {
	echo "This file is part of MediaWiki, it is not a valid entry point.\n";
	exit( 1 );
}
$wgOpath = WPI_URL . "/extensions/otag" ;
$wgExtensionFunctions[] = "wfotag";

function wfotag() {
	global $wgHooks;
	global $wgParser;
	$wgHooks['ParserAfterTidy'][]='oheader';
	$wgParser->setHook( "OntologyTags", "ofunction" );
}

function oheader(&$parser, &$text) {
	$text = preg_replace(
		'/<!-- ENCODED_CONTENT ([0-9a-zA-Z\\+]+=*) -->/e',
		'base64_decode("$1")',
		$text

	);
	return true;
}

function ofunction( $input, $argv, $parser ) {
	global $wgTitle, $wgOut,  $wgOpath, $wgOntologiesJSON, $wgStylePath, $wgJsMimeType;
	$title = $parser->getTitle();
	$loggedIn = $title->userCan('edit') ? 1 : 0;

	if($loggedIn) {
		$wgOut->addScript('<script type="text/javascript" src="' . $wgOpath . '/js/yui2.7.0.allcomponents.js"></script>');
		$wgOut->addStyle("$wgOpath/css/yui2.7.0.css");
	} else {
		$wgOut->addScript('<script type="text/javascript" src="' . $wgOpath . '/js/yui2.7.0.mincomponents.js"></script>');
	}

	$wgOut->addStyle("$wgOpath/css/otag.css");

	$wgOut->addScript(
		"<script type=\"{$wgJsMimeType}\">" .
		"var opath=\"$wgOpath\";" .
		"var otagloggedIn = \"$loggedIn\";" .
		"var ontologiesJSON = '$wgOntologiesJSON';" .
		"</script>\n"
	);

	if($loggedIn) {
		$output = <<<HTML
<div id="otagprogress" style="display:none" align='center'><span><img src='$wgStylePath/common/images/progress.gif'> Saving...</span></div>
<div id="ontologyContainer" class="yui-skin-sam">
	<div id="ontologyMessage" style="display:none;">No Tags!</div>
	<div id="ontologyTags" style="display:none;"></div>
	<div id="ontologyTagDisplay">&nbsp;</div>
	<a href="javascript:toggleOntologyControls();" id="ontologyEditLabel">Add Ontology tags</a><br /><br />
	<div id="ontologyEdit" style="display:none;">
		<div id="myAutoComplete">
			<input id="ontologyACInput" type="text" onfocus="clearBox();" value="Type Ontology term.."/>
			<div id="myContainer"></div>
		</div>
		<div id="otaghelp" class="otaghelp">To add a tag, either select from the available ontology trees below or type a search term in the search box.</div>
		<div style="clear:both;"></div>
		<div id="ontologyTrees"></div>
	</div>
</div>
<div style="clear:both;"></div>
<script type="text/javascript" src="$wgOpath/js/script.js"></script>
<script type="text/javascript">
$(document).ready(
	function() { YAHOO.util.Event.onDOMReady(ontologytree.init, ontologytree,true); }
)
</script>
HTML;
	} else {
		$output = <<<HTML
<div id="otagprogress" style="display:none" align='center'><span><img src='$wgStylePath/common/images/progress.gif'> Saving...</span></div>
<div id="ontologyContainer" class="yui-skin-sam">
<div id="ontologyMessage" style="display:none;">No Tags!</div>
<div id="ontologyTags" style="display:none;"> </div>
<div id="ontologyTagDisplay">&nbsp;</div>
</div>
<script type="text/javascript" src="$wgOpath/js/script.js"></script>
HTML;
	}
	return   '<!-- ENCODED_CONTENT '.base64_encode($output).' -->' ;
}
