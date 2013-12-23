<?php

class PathwayThumb {

	static function render( &$parser, $pwTitleEncoded, $width = 0, $align = '',
		$caption = '', $href = '', $tooltip = '', $id='pwthumb') {
		wfProfileIn( __METHOD__ );
		global $wgUser, $wgRequest;

		$pwTitle = Title::newFromText( urldecode( $pwTitleEncoded ) );
		if( $pwTitle->getNamespace() !== NS_PATHWAY ) {
			return "";
		}
		try {
			$pathway = Pathway::newFromTitle($pwTitle);

			if ( $pathway === null ) {
				return null;
			}
			$revision = $wgRequest->getVal('oldid');
			if($revision) {
				$pathway->setActiveRevision($revision);
			}
			switch($href) {
				case 'svg':
					$href = wfLocalFile($pathway->getFileTitle(FILETYPE_IMG)->getPartialURL())->getURL();
					break;
				case 'pathway':
					$href = $pathway->getFullURL();
					break;
				default:
					if(!$href) $href = $pathway->getFullURL();
			}

			switch($caption) {
				case 'edit':
					$caption = self::createEditCaption($pathway);
					break;
				case 'view':
					$caption = $pathway->name() . " (" . $pathway->species() . ")";
					break;
				default:
					$caption = html_entity_decode($caption);
					//This can be quite dangerous (injection),
					//we would rather parse wikitext, let me know if
					//you know a way to do that (TK)
			}

			$output = self::makeThumbLinkObj($pathway, $caption, $href,
				$tooltip, $align, $id, $width);
		} catch(Exception $e) {
			wfProfileOut( __METHOD__ );
			return "invalid pathway title: $e";
		}
		wfProfileOut( __METHOD__ );
		return array($output, 'isHTML'=>1, 'noparse'=>1);
	}

	static function createEditCaption(Pathway $pathway) {
		global $wgUser;

		//Create edit button
		$pathwayURL = $pathway->getTitleObject()->getPrefixedURL();
		//AP20070918
		if (!$wgUser->isLoggedIn()){
			$hrefbtn = SITE_URL . "/index.php?title=Special:Userlogin&returnto=$pathwayURL";
			$label = "Log in to edit pathway";
		} else {
			if(wfReadOnly()) {
				$hrefbtn = "";
				$label = "Database locked";
			} else if(!$pathway->getTitleObject()->userCan('edit')) {
				$hrefbtn = "";
				$label = "Editing is disabled";
			} else {
				$hrefbtn = "javascript:;";
				$label = "Edit pathway";
			}
		}
		$helpUrl = Title::newFromText("Help:Known_problems")->getFullUrl();
		$caption = "<a href='$hrefbtn' title='$label' id='edit' ".
			"class='button'><span>$label</span></a>" .
			"<div style='float:left;'><a href='$helpUrl'> not working?</a></div>";

		//Create dropdown action menu
		$pwTitle = $pathway->getTitleObject()->getFullText();
		//disable dropdown for now
		$drop = self::editDropDown($pathway);
		$drop = '<div style="float:right;">' . $drop . '</div>';
		return $caption . $drop;
	}

	static function getDownloadURL($pathway, $type) {
		if($pathway->getActiveRevision()) {
			$oldid = "&oldid={$pathway->getActiveRevision()}";
		}
		return WPI_SCRIPT_URL . "?action=downloadFile&type=$type&pwTitle={$pathway->getTitleObject()->getFullText()}{$oldid}";
	}

	static function editDropDown($pathway) {
		global $wgOut;
		wfProfileIn( __METHOD__ );

		//AP20081218: Operating System Detection
		require_once 'DetectBrowserOS.php';
		//echo (browser_detection( 'os' ));
		$download = array(
			'PathVisio (.gpml)' => self::getDownloadURL($pathway, 'gpml'),
			'Scalable Vector Graphics (.svg)' => self::getDownloadURL($pathway, 'svg'),
			'Gene list (.txt)' => self::getDownloadURL($pathway, 'txt'),
			'Biopax level 3 (.owl)' => self::getDownloadURL($pathway, 'owl'),
			'Eu.Gene (.pwf)' => self::getDownloadURL($pathway, 'pwf'),
			'Png image (.png)' => self::getDownloadURL($pathway, 'png'),
			'Acrobat (.pdf)' => self::getDownloadURL($pathway, 'pdf'),
		);
		$downloadlist = '';
		foreach(array_keys($download) as $key) {
			$downloadlist .= "<li><a href='{$download[$key]}'>$key</a></li>";
		}

		$dropdown = <<<DROPDOWN
<ul id="nav" name="nav">
<li><a href="#nogo2" class="button buttondown"><span>Download</span></a>
		<ul>
			$downloadlist
		</ul>
</li>
</ul>

DROPDOWN;

		$script = <<<SCRIPT
<script type="text/javascript">

sfHover = function() {
	var sfEls = document.getElementById("nav").getElementsByTagName("LI");
	for (var i=0; i<sfEls.length; i++) {
		sfEls[i].onmouseover=function() {
			this.className+=" sfhover";
		}
		sfEls[i].onmouseout=function() {
			this.className=this.className.replace(" sfhover", "");
		}
	}
}
if (window.attachEvent) window.attachEvent("onload", sfHover);

</script>
SCRIPT;
		$wgOut->addScript($script);
		wfProfileOut( __METHOD__ );
		return $dropdown;
	}

	/** MODIFIED FROM Linker.php
	 * Make HTML for a thumbnail including image, border and caption
	 * $img is an Image object
	 */
	static function makeThumbLinkObj( $pathway, $label = '', $href = '', $alt, $align = 'right', $id = 'thumb',
		$boxwidth = 180, $boxheight=false, $framed=false ) {
		wfProfileIn( __METHOD__ );
		global $wgStylePath, $wgContLang;

		$pathway->updateCache();
		$img = $pathway->getImage();
		$imgURL = $img->getURL();

		$thumbUrl = '';
		$error = '';

		$width = $height = 0;
		if ( $img->exists() ) {
			$width  = $img->getWidth();
			$height = $img->getHeight();
		}
		if ( 0 == $width || 0 == $height ) {
			$width = $height = 180;
		}
		if ( $boxwidth == 0 ) {
			$boxwidth = 180;
		}
		if ( $framed ) {
			// Use image dimensions, don't scale
			$boxwidth  = $width;
			$boxheight = $height;
			$thumbUrl  = $img->getViewURL();
		} else {
			try {
				list($thumb, $thumbUrl, $boxwidth, $boxheight) = wpiGetThumb( $img, $boxwidth, $boxheight );
			} catch (MWException $e) {
				$error = $e->getMessage();

			}
		}
		$oboxwidth = $boxwidth + 2;

		$more = htmlspecialchars( wfMessage( 'thumbnail-more' )->text() );
		$magnifyalign = $wgContLang->isRTL() ? 'left' : 'right';
		$textalign = $wgContLang->isRTL() ? ' style="text-align:right"' : '';

		$s = "<div id=\"{$id}\" class=\"thumb t{$align}\"><div class=\"thumbinner\" style=\"width:{$oboxwidth}px;\">";
		if( $thumbUrl == '' ) {
			// Couldn't generate thumbnail? Scale the image client-side.
			$thumbUrl = $img->getViewURL();
			if( $boxheight == -1 ) {
				// Approximate...
				$boxheight = intval( $height * $boxwidth / $width );
			}
		}
		wfDebug("Got thumbUrl: $thumbUrl\n");
		if ( $error ) {
			$s .= htmlspecialchars( $error );
		} elseif( !$img->exists() ) {
			$s .= "Image does not exist";
		} else {
			$s .= '<a href="'.$href.'" class="internal" title="'.$alt.'">'.
				'<img src="'.$thumbUrl.'" alt="'.$alt.'" ' .
				'width="'.$boxwidth.'" height="'.$boxheight.'" ' .
				'longdesc="'.$href.'" class="thumbimage" /></a>';
		}
		$s .= '  <div class="thumbcaption"'.$textalign.'>'.$label."</div></div></div>";
		wfProfileOut( __METHOD__ );
		return str_replace("\n", ' ', $s);
		//return $s;
	}
}