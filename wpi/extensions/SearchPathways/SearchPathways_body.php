<?php
class SearchPathways extends SpecialSearch
{
	private $this_url;

	function __construct( $empty = null ) {
		parent::__construct("SearchPathways");
	}

	function execute( $par ) {
		global $wgRequest, $wgOut, $wpiScriptURL, $wgUser, $wgSearchPagePath;

		$this->setHeaders();
		$this->this_url = SITE_URL . '/index.php';
		$wgOut->setPagetitle( wfMessage( "searchpathways" )->text() );

		$query   = isset( $_GET['query'] ) ?   $_GET['query']   : null;
		$species = isset( $_GET['species'] ) ? $_GET['species'] : null;
		$ids     = isset( $_GET['ids'] ) ?     $_GET['ids']     : null;
		$codes   = isset( $_GET['codes'] ) ?   $_GET['codes']   : null;
		$type    = isset( $_GET['type'] ) ?    $_GET['type']    : null;

		// SET DEFAULTS
		if (!$type || $type == '') $type = 'query';
		if ((!$query || $query =='') && $type == 'query') $query = 'glucose';
		if ($species == 'ALL SPECIES') $species = '';

		$wgOut->addStyle("$wgSearchPagePath/SearchPathways.css");

		if($_GET['doSearch'] == '1') { //Submit button pressed
			$this->showForm($query, $species, $ids, $codes, $type);
			try {
				$this->showResults();
			} catch(Exception $e) {
				$wgOut->addHTML("<b>Error: {$e->getMessage()}</b>");
				$wgOut->addHTML("<pre>$e</pre>");
			}
		} else {
			$this->showForm($query, $species, $ids, $codes, $type);
		}
	}


	function showForm($query, $species = '', $ids = '', $codes = '', $type) {
		global $wgRequest, $wgOut, $wpiScriptURL, $wgJsMimeType, $wgSearchPagePath, $wgScriptPath;
		#For now, hide the form when id search is done (no gui for that yet)
		$hide = "";
		$xrefInfo = "";
		if($type != 'query') {
			$hide = "style='display:none'";
			$xrefs = SearchPathwaysAjax::parToXref($ids, $codes);
			$xrefInfo = "Pathways by idenifier: ";
			$xstr = array();
			foreach($xrefs as $x)	$xstr[] = "{$x->getId()} ({$x->getSystem()})";
			$xrefInfo .= implode(", ", $xstr);
			$xrefInfo = "<P>$xrefInfo</P>";
		}

		$form_method = "get";
		$form_extra = "";
		$search_form ="$xrefInfo<FORM $hide id='searchForm' action='$this->this_url' method='get'>
				<table cellspacing='7'><tr valign='middle'><td>"
		//<input type='radio' name='type' value='query' CHECKED>Keywords
		//<input type='radio' name='type' value='xref'>Identifiers
		//<tr><td>
		."Search for:
								<input type='text' name='query' value='$query' size='25'>
				</td><td><select name='species'>";
		$allSpecies = Pathway::getAvailableSpecies();
		$search_form .= "<option value='ALL SPECIES'" . ($species == '' ? ' SELECTED' : ''). ">ALL SPECIES";
		foreach($allSpecies as $sp) {
			$search_form .= "<option value='$sp'" . ($sp == $species ? ' SELECTED' : '') . ">$sp";
		}
		$search_form .= '</select>';
		$search_form .= "<input type='hidden' name='title' value='Special:SearchPathways'>
				<input type='hidden' name='doSearch' value='1'>
				</td><td><input type='submit' value='Search'></td></tr>
				<tr valign='top'><td colspan='3'><font size='-3'><i>&nbsp;&nbsp;&nbsp;Tip: use AND, OR, *, ?, parentheses or quotes</i></font></td></tr>
				</table>";

		$search_form .= "<input type='hidden' name='ids' value='$ids'/>";
		$search_form .= "<input type='hidden' name='codes' value='$codes'/>";
		$search_form .= "<input type='hidden' name='type' value='$type'/>";

		$search_form .= "</FORM><BR>";

		$wgOut->addHTML("
						<DIV id='search' >
			$search_form
			</DIV>
						");
		$wgOut->addScript("<script type=\"{$wgJsMimeType}\" src=\"$wgSearchPagePath/SearchPathways.js\"></script>\n");
		$wgOut->addHTML("<DIV id='searchResults'></DIV>");
		$wgOut->addHTML(
			"<DIV id='loading'><IMG src='$wgScriptPath/skins/common/images/progress.gif'/> Loading...</DIV>"
		);
		$wgOut->addHTML("<DIV id='more'></DIV>");
		$wgOut->addHTML("<DIV id='error'></DIV>");
	}

	static function makeThumbNail( $pathway, $label = '', $href = '', $alt, $align = 'right', $id = 'thumb', $boxwidth = 300, $boxheight=false, $framed=false ) {
		global $wgStylePath, $wgContLang;

		try {
			$pathway->getImage();
			$imgURL = $img->getURL();
		} catch (Exception $e) {
			$blank = "<div id=\"{$id}\" class=\"thumb t{$align}\"><div class=\"thumbinner\" style=\"width:200px;\">";
			$blank .= "Image does not exist";
			$blank .= '  <div class="thumbcaption" style="text-align:right">'.$label."</div></div></div>";
			return str_replace("\n", ' ', $blank);
		}

		$thumbUrl = '';
		$error = '';

		$width = $height = 0;
		if ( $img->exists() ) {
			$width  = $img->getWidth();
			$height = $img->getHeight();
		}
		if ( 0 == $width || 0 == $height ) {
			$width = $height = 220;
		}
		if ( $boxwidth == 0 ) {
			$boxwidth = 230;
		}
		if ( $framed ) {
			// Use image dimensions, don't scale
			$boxwidth  = $width;
			$boxheight = $height;
			$thumbUrl  = $img->getViewURL();
		} else {
			if ( $boxheight === false ) $boxheight = -1;
			$thumb = $img->getThumbnail( $boxwidth, $boxheight );
			if ( $thumb ) {
				$thumbUrl = $thumb->getUrl();
				$boxwidth = $thumb->width;
				$boxheight = $thumb->height;
			} else {
				$error = $img->getLastError();
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
		return str_replace("\n", ' ', $s);
	}
}
