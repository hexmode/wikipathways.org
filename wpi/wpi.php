<?php

try {
	//Initialize MediaWiki
	$wpiDir = dirname( realpath(__FILE__) );
	$dir = getcwd();
	if( !isset( $IP ) ) $IP = dirname( $wpiDir );

	set_include_path( get_include_path() . PATH_SEPARATOR .
		implode( PATH_SEPARATOR, array_map( 'realpath', array( $wpiDir, "$wpiDir/includes",
					"$wpiDir/../includes", "$dir/../" ) ) ) );
	putenv( "MW_INSTALL_PATH=$IP" );
	if ( !defined( 'MEDIAWIKI' ) ) {
		require_once( "WebStart.php" );
		require_once( "Wiki.php" );
		require_once( 'globals.php' );
	}
	//Parse HTTP request (only if script is directly called!)
	if(realpath($_SERVER['SCRIPT_FILENAME']) == realpath(__FILE__)) {
		if( !isset( $_GET['action'] ) ) {
			throw new Exception("No action given!");
		}
		$action = $_GET['action'];
		if( !isset( $_GET['pwTitle'] ) ) {
			throw new Exception("No pwTitle given!");
		}
		$pwTitle = $_GET['pwTitle'];
		if( !isset( $_GET['oldId'] ) && $action !== "downloadFile" && $action !== "delete" && $action !== "display" ) {
			throw new Exception("No oldId given!");
		}
		$oldId = $_GET['oldid'];

		switch($action) {
			case 'launchCytoscape':
				launchCytoscape(createPathwayObject($pwTitle, $oldId));
				break;
			case 'launchGenMappConverter':
				launchGenMappConverter(createPathwayObject($pwTitle, $oldId));
				break;
			case 'downloadFile':
				if( !isset( $_GET['type'] ) ) {
					throw new Exception("No type given!");
				}
				downloadFile($_GET['type'], $pwTitle);
				break;
			case 'display':
				if( !isset( $_GET['type'] ) ) {
					throw new Exception("No type given!");
				}
				displayFile($_GET['type'], $pwTitle);
				break;
			case 'revert':
				revert($pwTitle, $oldId);
				break;
			case 'delete':
				delete($pwTitle);
				break;
			default:
				throw new Exception("'$action' isn't implemented");
		}
	}
} catch(Exception $e) {
	//Redirect to special page that reports the error
	ob_clean();
	echo "<pre>";
	echo $e->getMessage();
	#header("Location: " . SITE_URL . "/index.php?title=Special:ShowError&error=" . urlencode($e->getMessage()));
	exit;
}

/**
 * Utility function to import the required javascript for the xref panel
 */
function wpiAddXrefPanelScripts() {
	XrefPanel::addXrefPanelScripts();
}

function createPathwayObject($pwTitle, $oldid) {
	$pathway = Pathway::newFromTitle($pwTitle);
	if($oldId) {
		$pathway->setActiveRevision($oldId);
	}
	return $pathway;
}

function delete($title) {
	global $wgUser, $wgOut;
	$pathway = Pathway::newFromTitle($_GET['pwTitle']);
	if($wgUser->isAllowed('delete')) {
		$pathway = Pathway::newFromTitle($_GET['pwTitle']);
		$pathway->delete();
		echo "<h1>Deleted</h1>";
		echo "<p>Pathway $title was deleted, return to <a href='" . SITE_URL . "'>wikipathways</a>";
	} else {
		echo "<h1>Error</h1>";
		echo "<p>Pathway $title is not deleted, you have no delete permissions</a>";
		$wgOut->permissionRequired( 'delete' );
	}
	exit;
}

function revert($pwTitle, $oldId) {
	$pathway = Pathway::newFromTitle($pwTitle);
	$pathway->revert($oldId);
	//Redirect to old page
	$url = $pathway->getTitleObject()->getFullURL();
	header("Location: $url");
	exit;
}

function launchGenMappConverter($pathway) {
	global $wgUser;

	$webstart = file_get_contents(WPI_SCRIPT_PATH . "/applet/genmapp.jnlp");
	$pwUrl = $pathway->getFileURL(FILETYPE_GPML);
	$pwName = substr($pathway->getFileName(''), 0, -1);
	$arg = "<argument>" . htmlspecialchars($pwUrl) . "</argument>";
	$arg .= "<argument>" . htmlspecialchars($pwName) . "</argument>";
	$webstart = str_replace("<!--ARG-->", $arg, $webstart);
	$webstart = str_replace("CODE_BASE", WPI_URL . "/applet/", $webstart);
	sendWebstart($webstart, $pathway->name(), "genmapp.jnlp");//This exits script
}

function launchCytoscape($pathway) {
	global $wgUser;

	$webstart = file_get_contents(WPI_SCRIPT_PATH . "/bin/cytoscape/cy1.jnlp");
	$arg = createJnlpArg("-N", $pathway->getFileURL(FILETYPE_GPML));
	$webstart = str_replace(" <!--ARG-->", $arg, $webstart);
	$webstart = str_replace("CODE_BASE", WPI_URL . "/bin/cytoscape/", $webstart);
	sendWebstart($webstart, $pathway->name(), "cytoscape.jnlp");//This exits script
}

function sendWebstart($webstart, $tmpname, $filename = "wikipathways.jnlp") {
	ob_start();
	ob_clean();
	//return webstart file directly
	header("Content-type: application/x-java-jnlp-file");
	header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
	header("Content-Disposition: attachment; filename=\"{$filename}\"");
	echo $webstart;
	exit;
}

function getJnlpURL($webstart, $tmpname) {
	$wsFile = tempnam(getcwd() . "/tmp",$tmpname);
	writeFile($wsFile, $webstart);
	return 'http://' . $_SERVER['HTTP_HOST'] . '/wpi/tmp/' . basename($wsFile);
}

function createJnlpArg($flag, $value) {
	//return "<argument>" . $flag . ' "' . $value . '"' . "</argument>\n";
	if(!$flag || !$value) return '';
	return "<argument>" . htmlspecialchars($flag) . "</argument>\n<argument>" . htmlspecialchars($value) . "</argument>\n";
}

function getFilename( $fileType, $pwTitle ) {
	$pathway = Pathway::newFromTitle($pwTitle);
	if(!$pathway->getTitleObject()->userCan('read')) {
		throw new Exception("You don't have permissions to view this pathway");
	}

	if($fileType === 'mapp') {
		launchGenMappConverter($pathway);
	}
	ob_start();
	if($oldid = $_REQUEST['oldid']) {
		$pathway->setActiveRevision($oldid);
	}
	//Register file type for caching
	Pathway::registerFileType($fileType);

	$file = $pathway->getFileLocation($fileType);
	$fn = basename( $file );

	$mime = MimeTypes::getMimeType($fileType);
	if(!$mime) $mime = "text/plain";

	ob_clean();
	return array($file, $fn, $mime);
}

function downloadFile($fileType, $pwTitle) {
	list($file, $fn, $mime) = getFilename( $fileType, $pwTitle );
	header("Content-type: $mime");
	header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
	header("Content-Disposition: attachment; filename=\"$fn\"");
	//header("Content-Length: " . filesize($file));
	set_time_limit(0);
	@readfile($file);
	exit();
}

function displayFile($fileType, $pwTitle) {
	list($file, $fn, $mime) = getFilename( $fileType, $pwTitle );
	header("Content-type: $mime");
	header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
	//header("Content-Length: " . filesize($file));
	set_time_limit(0);
	@readfile($file);
	exit();
}

function getClientOs() {
	$regex = array(
		'windows' => '([^dar]win[dows]*)[\s]?([0-9a-z]*)[\w\s]?([a-z0-9.]*)',
		'mac' => '(68[k0]{1,3})|(ppc mac os x)|([p\S]{1,5}pc)|(darwin)',
		'linux' => 'x11|inux');
	$ua = $_SERVER['HTTP_USER_AGENT'];
	foreach (array_keys($regex) as $os) {
		if(eregi($regex[$os], $ua)) return $os;
	}
}

$spName2Code = array('Human' => 'Hs', 'Rat' => 'Rn', 'Mouse' => 'Mm');//TODO: complete

function toGlobalLink($localLink) {
	if($wgScriptPath && $wgScriptPath != '') {
		$wgScriptPath = "$wgScriptPath/";
	}
	return urlencode("http://" . $_SERVER['HTTP_HOST'] . "$wgScriptPath$localLink");
}

function writeFile($filename, $data) {
	if( $filename === null ) {
		throw new MWException( "Need a filename, got null!" );
	}
	if( is_object( $filename ) ) {
		throw new MWException( "Don't know what to do with objects!" );
	}
	if( substr( $filename, 0, 10 ) === "mwstore://" ) {
		throw new MWException( "Don't know what to do with mwstore paths!" );
	}

	$dir = dirname( $filename );
	if( !is_dir( $dir ) && file_exists( $dir ) ) {
		mkdir( $dir, 0777, true );
	}

	$handle = fopen($filename, 'w');
	if(!$handle) {
		throw new MWException ("Couldn't open file $filename");
	}
	if(fwrite($handle, $data) === false) {
		throw new MWException ("Couldn't write file $filename");
	}
	if(fclose($handle) === false) {
		throw new MWException ("Couldn't close file $filename");
	}
}

function tag($name, $text, $attributes = array()) {
	$attr = "";
	foreach(array_keys($attributes) as $key) {
		if($value = $attributes[$key])$attr .= $key . '="' . $value . '" ';
	}
	return "<$name $attr>$text</$name>";
}

/**
 * Modified wfShellExec for java calls. This does not include the memory limit
 * on the ulimit call, since this doesn't work well with java.
 * @param $cmd Command line, properly escaped for shell.
 * @param &$retval optional, will receive the program's exit code.
 *                 (non-zero is usually failure)
 * @return collected stdout as a string (trailing newlines stripped)
 */
function wfJavaExec( $cmd, &$retval=null ) {
	global $IP, $wgMaxShellMemory, $wgMaxShellFileSize;

	if( wfIniGetBool( 'safe_mode' ) ) {
		wfDebug( "wfShellExec can't run in safe_mode, PHP's exec functions are too broken.\n" );
		$retval = 1;
		return "Unable to run external programs in safe mode.";
	}

	if ( php_uname( 's' ) == 'Linux' ) {
		$time = intval( ini_get( 'max_execution_time' ) );
		$filesize = intval( $wgMaxShellFileSize );

		if ( $time > 0) {
			$script = "$IP/bin/ulimit4-nomemory.sh";
			if ( is_executable( $script ) ) {
				$cmd = escapeshellarg( $script ) . " $time $filesize " . escapeshellarg( $cmd );
			}
		}
	} elseif ( php_uname( 's' ) == 'Windows NT' ) {
		# This is a hack to work around PHP's flawed invocation of cmd.exe
		# http://news.php.net/php.internals/21796
		$cmd = '"' . $cmd . '"';
	}
	wfDebug( "wfJavaExec: $cmd\n" );

	$retval = 1; // error by default?
	ob_start();
	passthru( $cmd, $retval );
	$output = ob_get_contents();
	ob_end_clean();
	return $output;
}

function wpiGetThumb( $img, $w, $h = false ) {
	if( !$h && $h !== 0 ) {
		$h = -1;
	}

	$thumb = $img->transform
		( array( 'width' => $w, 'height' => $h ) );
	if( is_null( $thumb ) ) {
		throw new MWException( "Unknown failure in thumbnail" );
	} elseif( $thumb->isError() ) {
		throw new MWException( $thumb->getHtmlMsg() );
	}
	return array( $thumb, $thumb->getUrl(), $thumb->width, $thumb->height );
}
