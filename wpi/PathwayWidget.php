<?php
/**
Entry point for a pathway viewer widget that can be included in other pages.

This page will display the interactive pathway viewer for a given pathway. It takes the following parameters:
- id: the pathway id (e.g. WP4)
- rev: the revision number of a specific version of the pathway (optional, leave out to display the newest version)

You can include a pathway viewer in another website using an iframe:

<iframe src ="http://www.wikipathways.org/wpi/PathwayWidget.php?id=WP4" width="500" height="500" style="overflow:hidden;"></iframe>

 */
	require_once('wpi.php');
	require_once('extensions/PathwayViewer/PathwayViewer.php');
?>
<!DOCTYPE HTML>
<html>
<head>
<style  type="text/css">
a#wplink {
text-decoration:none;
font-family:serif;
color:black;
font-size:12px;
}
#logolink {
	float:right;
	top:-20px;
	left: -10px;
	position:relative;
	z-index:2;
	opacity: 0.8;
}
html, body {
	width:100%;
	height:100%;
}
#pathwayImage {
	position:fixed;
	top:0;
	left:0;
	font-size:12px;
	width:100%;
	height:100%;
}
</style>
<meta name="svg.render.forceflash" content="true">
<?php
	  echo '<link rel="stylesheet" href="' . $cssJQueryUI . '" type="text/css" />' . "\n";

$jsSnippets = XrefPanel::getJsSnippets();
foreach($jsSnippets as $js) {
	echo "<script type=\"text/javascript\">$js</script>\n";
}

$imgPath = "$wgServer/$wgScriptPath/skins/common/images/";
echo "<script type=\"text/javascript\">XrefPanel_imgPath = '$imgPath';</script>";

$jsSrc = PathwayViewer::getJsDependencies();
$jsSrc = array_merge($jsSrc, XrefPanel::getJsDependencies());
foreach($jsSrc as $js) {
	echo '<script type="text/javascript" src="' . $js . '"></script>' . "\n";
}

$id = null;
$rev = null;
if ( isset( $_REQUEST['id'] ) ) {
	$id = $_REQUEST['id'];
}
if ( isset( $_REQUEST['rev'] ) ) {
	$rev = $_REQUEST['rev'];
}

if ( $id == null ) {
	throw new MWException( "No ID given!" );
}

$pathway = Pathway::newFromTitle($id);
if($rev) {
	$pathway->setActiveRevision($rev);
}

$svg = $pathway->getFileURL(FILETYPE_IMG);
$gpml = $pathway->getFileURL(FILETYPE_GPML);

/**
 * Maybe use getJsSnippets() from Xref panel?  '$search' and '$bridge'
 * were just before the end tag here.
 */
echo <<<SCRIPT
<script type="text/javascript">
	$(document).ready( function() {
			PathwayViewer_basePath = '$wfPathwayViewerPath/';
			PathwayViewer_viewers.push(new PathwayViewer(
					{ imageId: "pathwayImage",
					  svgUrl: "$svg",
					  gpmlUrl: "$gpml",
					  start: true,
					  width: '100%',
					  height: '100%'
					}))});
</script>
SCRIPT;
?>
<title>WikiPathways Pathway Viewer</title>
</head>
<body>
<div id="pathwayImage"><img src="" /></div>
<div style="position:absolute;height:0px;overflow:visible;bottom:0;right:0;">
	<div id="logolink">
		<?php
			echo "<a id='wplink' target='top' href='{$pathway->getFullUrl()}'>View at ";
			echo "<img style='border:none' src='$wgScriptPath/skins/common/images/wikipathways_name.png' /></a>";
		?>
	</div>
</div>
</body>
</html>
