<?php
class SpecialWishList extends SpecialPage {
	private $wishlist;

	private $thisUrl;

	function __construct() {
		parent::__construct( "SpecialWishList" );
	}

	// FIXME: This shouldn't use $_REQUEST
	function execute( $par ) {
		global $wgOut;
		$this->setHeaders();

		$this->thisUrl = SITE_URL . '/index.php?title=Special:SpecialWishList';

		// Create the wishlist table
		$this->wishlist = new PathwayWishList();
		$checkwishes = array();
		foreach ( array_keys( $_REQUEST ) as $key ) {
			if ( 'check_' == substr( $key, 0, 6 ) ) {
				$wid = substr( $key, 6 );
				$checkwishes[$wid] = $_REQUEST[$key];
			}
		}

		$req = array();
		foreach ( array( "wishaction", "name", "comments", "pathway", "id" ) as $key ) {
			$req[$key] = isset( $_REQUEST[$key] ) ? $_REQUEST[$key] : null;
		}
		try {
			switch ( $req['wishaction'] ) {
				case 'add':
					$done = $this->add( $req['name'], $req['comments'] );
					break;
				case 'resolved':
					$done = $this->markResolved( $req['id'], $req['pathway'] );
					break;
				case 'watch':
					$done = $this->setWatch( $checkwishes );
					break;
				case 'remove':
					$done = $this->remove( $req['id'] );
					break;
				case 'vote':
					$done = $this->vote( $req['id'] );
					break;
				case 'unvote':
					$done = $this->unvote( $req['id'] );
					break;
				default:
					$this->showlist();
			}
		} catch ( Exception $e ) {
			global $wgOut;
			$wgOut->showErrorPage( "Error", $e->getMessage() );
		}
	}

	function reload() {
		global $wgOut;
		$wgOut->redirect( $this->thisUrl );
	}

	function add( $name, $comments ) {
		$this->wishlist->addWish( $name, $comments );
		$this->reload();
	}

	function remove( $wishId ) {

		$wish = new Wish( $wishId );
		$wish->remove();

		$this->reload();
	}

	function vote( $wishId ) {
		global $wgUser;
		$wish = new Wish( $wishId );
		$wish->vote( $wgUser->getId() );
		$this->reload();
	}

	function unvote( $wishId ) {
		global $wgUser;
		$wish = new Wish( $wishId );
		$wish->unvote( $wgUser->getId() );
		$this->reload();
	}

	function setWatch( $wishes ) {
		// Unwatch all unchecked items
		foreach ( $this->wishlist->getWishlist() as $wish ) {
			if ( $wishes[$wish->getId()] ) {
				$wish->watch();
			} else {
				$wish->unwatch();
			}
		}
		$this->reload();
	}

	static function comparePathways( $path1, $path2 ) {
		$out1 = $path1->getSpecies() . $path1->getName();
		$out2 = $path2->getSpecies() . $path2->getName();
		return strcmp( $out1, $out2 );
	}

	function markResolved( $wishId, $pathwayTitle = '' ) {
		global $wgOut;
		if ( $pathwayTitle ) {
			$this->doMarkResolved( $wishId, $pathwayTitle );
			$this->reload();
		}

		// FIXME: this leftover variable pwSelect is meaningless
		// $select = "<select name='pathway'>$pwSelect";
		$select = "<select name='pathway'>";
		// First show a form to fill in the pathway names
		$pathways = Pathway::getAllPathways();
		usort( $pathways, 'SpecialWishList::comparePathways' );
		foreach ( $pathways as $pathway ) {
			$name = $pathway->name();
			$species = $pathway->species();
			$title = $pathway->getTitleObject()->getFullText();
			$select .= "<option value='$title'>$name ($species)</option>";
		}
		$select .= '</select>';
		// resolved wish info
		$wish = new Wish( $wishId );
		$title = $wish->getTitle()->getText();
		$user = $wish->getRequestUser();
		$user = RequestContext::getMain()->getSkin()->userLink( $user->getId(), $user->getName() );
		$date = self::getSortTimeDate( $wish->getRequestDate() );
		$comment = $this->truncateComment( $wish, 75 ); // Cutoff comment at 20 chars
		// displayed html
		$this->addJavaScript();
		$html = <<<HTML
<H2>Resolve wishlist item</H2>
<table class="prettytable"><tbody>
<tr class='table-blue-tableheadings'>
<td class='table-blue-headercell'>Suggested pathway name</td>
<td class='table-blue-headercell'>Suggested by user</td>
<td class='table-blue-headercell'>Suggested on date</td>
<td class='table-blue-headercell'>Comments</td>
<tr class=''><td class='table-blue-contentcell'>{$title}</td>
<td class='table-blue-contentcell'>{$user}</td>
<td class='table-blue-contentcell'>{$date}</td><td class='table-blue-contentcell'>
HTML;
		$wgOut->addHTML( $html );
		$wgOut->addWikiText( $comment );
		$href = SITE_URL . "/index.php?title=Special:SpecialWishList";
		$html2 = <<<HTML
</td></tbody></table>
<BR>Please specify the pathway that resolves this wishlist item:
<P>
<FORM acion="{$this->thisUrl}" method="post">
$select
<INPUT type="hidden" name="wishaction" value="resolved">
<INPUT type="submit" value="Resolve item"> <a href="{$href}">Cancel</a>
</FORM>
HTML;
		$wgOut->addHTML( $html2 );
		return true;
	}

	function doMarkResolved( $wishId, $pathwayTitle ) {
		$wish = new Wish( $wishId );
		$pathway = Pathway::newFromTitle( $pathwayTitle );
		$wish->markResolved( $pathway );
	}

	function showlist() {
		global $wgOut, $wgUser;
		$this->addJavaScript();

		// Create a small toolbar with 'new' and 'help' actions
		// TODO: pretty style
		$wgOut->addHTML( "<ul><li>" );
		$elm = $this->getNewFormElements();
		$newdiv = $elm['div'];
		$newbutton = $elm['button'];
		$wgOut->addHTML( "$newbutton<li>" );
		$elm = $this->getHelpElements();
		$helpbutton = $elm['button'];
		$helpdiv = $elm['div'];
		$wgOut->addHTML( "$helpbutton</ul>" );
		$wgOut->addHTML( $newdiv . $helpdiv );

		// Create the actual wishlist
		$wishes = $this->wishlist->getWishlist();
		$html = <<<HTML
<form action='{$this->thisUrl}' method='post'>
<table class="prettytable sortable"><tbody>
<tr class='table-blue-tableheadings'>
<td class='table-blue-headercell'>Suggested pathway name</td>
<td class='table-blue-headercell'>Suggested by user</td>
<td class='table-blue-headercell'>Suggested on date</td>
<td class='table-blue-headercell'>Comments</td>
<td class='table-blue-headercell'>Votes
HTML;
		if ( $wgUser->isLoggedIn() ) {
			$html .= "<td class='table-blue-headercell' class='unsortable'>Watch</td>" .
				"<td class='table-blue-headercell' class='unsortable'>Resolve";
		}
		$wgOut->addHTML( $html );
		$altrow = '';
		foreach ( $wishes as $wish ) {
			if ( !$wish->isResolved() ) {
				$this->createUnresolvedRow( $wish, $altrow );
				if ( $altrow === '' ) {
					$altrow = 'table-blue-altrow';
				} else {
					$altrow = '';
				}
			}
		}
		$html = "";
		if ( $wgUser->isLoggedIn() ) {
			$html = <<<HTML
<tr class="{$altrow} sortbottom"><td class='table-blue-contentcell'>
<td class='table-blue-contentcell'><td class='table-blue-contentcell'>
<td class='table-blue-contentcell'><td class='table-blue-contentcell'>
<td class='table-blue-contentcell' align="center">
	<input type="hidden" name="wishaction" value="watch">
	<input type="submit" value="Apply"><td class='table-blue-contentcell'></tr>
HTML;
		}
		$html .= "</tbody></table></form>";
		$wgOut->addHTML( $html );

		$wgOut->addWikiText( "== Resolved items ==" );
		$wgOut->addHTML( "<table class='prettytable sortable'><tbody>
				<tr class='table-green-tableheadings'>
				<td class='table-green-headercell'>Suggested pathway name
<td class='table-green-headercell'>Suggested by user
<td class='table-green-headercell'>Suggested on date
<td class='table-green-headercell'>Resolved by pathway name
<td class='table-green-headercell'>Resolved by user
<td class='table-green-headercell'>Resolved on date" );
			$altrow2 = '';
			foreach ( $wishes as $wish ) {
				if ( $wish->isResolved() ) {
					$this->createResolvedRow( $wish, $altrow2 );
					if ( $altrow2 === '' ) {
						$altrow2 = 'table-green-altrow';
					} else {
						$altrow2 = '';
					}
				}
			}
			$wgOut->addHTML( "</tbody></table>" );
	}

	function addJavaScript() {
		global $wgOut, $wgScriptPath;
		$jsToggle = <<<JS
<script type="text/javascript">
	function showhide(id, toggle, hidelabel, showlabel) {
		elm = document.getElementById(id);
		if(toggle.innerHTML == hidelabel) {
			elm.style.display = "none";
			toggle.innerHTML = showlabel;
		} else {
			elm.style.display = "";
			toggle.innerHTML = hidelabel;
		}
	}
</script>
<link rel="stylesheet" type="text/css"
      href="$wgScriptPath/skins/wikipathways/TableColor.css" />
JS;
		$wgOut->addScript( $jsToggle );
	}

	function getNewFormElements() {
		global $wgUser;

		$div = <<<DIV
<div id="new" style="display:none">
Fill in the form below to add a new item to the wishlist.
<FORM action="{$this->thisUrl}" method="post">
	<P>
	<LABEL for="name">Name of pathway: </LABEL>
			  <INPUT type="text" id="name" name="name"><BR>
	<LABEL for="comments">Description, example urls or comments: </LABEL>
			  <TEXTAREA id="comments" name="comments" rows="10" cols="30"></TEXTAREA><BR>
	<INPUT type="hidden" name="wishaction" value="add">
	<INPUT type="submit" value="Add">
	</P>
</FORM>
</div>
DIV;

		if ( wfReadOnly() || !$wgUser->isAllowed( 'edit' ) ) {
			$href = SITE_URL . "/index.php?title=Special:Userlogin&returnto=Special:SpecialWishList";
			$button = "<a href=$href>Log in</a> to add pathways to the wishlist";
		} else {
			$button = "<a href=\"javascript:showhide('new', this, " .
				"'Add new wishlist item', '');\">Add new wishlist item</a>";
		}
		return array( 'button' => $button, 'div' => $div );
	}

	function getHelpElements() {
		global $wgScriptPath;

		$button = "<a href=\"javascript:showhide('help', this, 'Help', 'Hide help');\">Help</a>";
		$div = <<<HELP
<div id="help" style="display:none">
<p>This page shows a list of pathways that users would like to see added
to WikiPahtways. You can add a pathway request to the list,
by clicking 'Add new wishlist item'.
<p>
<table frame="box"><head><i>Legend for the pathway wishlist table:</i><tbody>
<td colspan="2"><b>Watch:</b>
<tr>
<td colspan="2">
You can watch items in the wishlist by checking the checkbox in the 'Watch'
column and clicking the 'Apply' button.
When you watch an item from the wishlist, you will recieve an email notification
 when somebody modifies or resolves the item.
<tr>
<td colspan="2"><b>Resolve:</b>
<tr>
<td><img align='right' style='border:1' src='$wgScriptPath/skins/common/images/apply.gif'/>
<td>Use this button to resolve an item when the requested pathway is created. The item will
be transfered to the 'resolved items' list.
<tr>
<td><img align='right' style='border:1' src='$wgScriptPath/skins/common/images/cancel.gif'/>
<td>Removes the item from the list, it will not show up in the 'resolved items' list.
You can only remove items that you created yourself.
<tr>
<td colspan="2"><b>Votes:</b>
<tr>
<td><img align='right' style='border:1' src='$wgScriptPath/skins/common/images/plus.png'/>
<td>Vote for the pathway</td>
<tr>
<td><img align='right' style='border:1' src='$wgScriptPath/skins/common/images/minus.png'/>
<td>Remove your vote</td>
</tbody></table></div>
HELP;
		return array( 'button' => $button, 'div' => $div );
	}

	function createResolvedRow( $wish, $altrow2 ) {
		global $wgOut;
		$title = $wish->getTitle()->getText();
		$user = $wish->getRequestUser();
		$user = RequestContext::getMain()->getSkin()->userLink( $user->getId(), $user->getName() );
		$pathway = $wish->getResolvedPathway();
		if ( $pathway->exists() ) {
			$rev = $pathway->getFirstRevision();
			$pwDate = self::getSortTimeDate( $rev->getTimestamp() );
			$resUser = RequestContext::getMain()->getSkin()->userLink( $rev->getUser(),
				$rev->getUserText() );
		}
		$resDate = self::getSortTimeDate( $wish->getResolvedDate() );
		if ( $wish->isResolved() ) {
			$wgOut->addHTML( "<tr class='{$altrow2}'><td class='table-green-contentcell'>
$title<td class='table-green-contentcell'>$user<td class='table-green-contentcell'>" );
			if( isset( $pwDate ) ) {
				$wgOut->addHTML( $pwDate );
			}
			$wgOut->addHTML( "</td>" );
			$wgOut->addWikiText( "[[{$pathway->getTitleObject()->getFullText()} |" .
				"{$pathway->name()} ({$pathway->species()})]]" );
			$wgOut->addHTML( "<td class='table-green-contentcell'>" );
			if ( isset( $resUser ) ) {
				$wgOut->addHTML( $resUser );
			}
			$wgOut->addHTML( "<td class='table-green-contentcell'>$resDate" );
			if ( $wish->userCan( 'delete' ) ) {
				$wgOut->addHTML( "<td class='table-green-contentcell'>" .
					$this->createButton( "cancel.gif", "remove", "Remove this item",
						$wish->getId() ) );
			}
		}
	}

	function createUnresolvedRow( $wish, $altrow ) {
		global $wgOut, $wgUser;

		$login = $wgUser->isLoggedIn();

		$wishId = $wish->getId();
		$url = $wish->getTitle()->getFullURL();
		$title = $wish->getTitle()->getText();
		$user = $wish->getRequestUser();
		$user = RequestContext::getMain()->getSkin()->userLink( $user->getId(),
			$user->getName() );
		$date = self::getSortTimeDate( $wish->getRequestDate() );
		$watching = $wish->userIsWatching() ? "CHECKED" : "";
		$votes = (int)$wish->countVotes();
		$fullComment = str_replace( '"', "'", $wish->getComments() );
		$comment = $this->truncateComment( $wish, 75 ); // Cutoff comment at 20 chars
		$voteButton = '<td class="table-blue-contentcell" style="border:0px">';
		if ( $wish->userCan( 'vote' ) ) {
			$voteButton .= $this->createButton( 'plus.png', 'vote', 'Vote for this pathway',
				$wishId );
		} elseif ( $wish->userCan( 'unvote' ) ) {
			$voteButton .= $this->createButton( 'minus.png', 'unvote', 'Remove your vote',
				$wishId );
		}
		$wgOut->addHTML( "<tr class='{$altrow}'><td class='table-blue-contentcell'>" .
			"<b><a href='$url'>$title</a></b><td class='table-blue-contentcell'>$user" .
			"<td class='table-blue-contentcell'>$date" .
			"<td class='table-blue-contentcell' title=\"$fullComment\">" );
		$wgOut->addWikiText( $comment );
		$wgOut->addHTML( "<td class='table-blue-contentcell'>" .
			"<table class='prettytable' style='border:0px'><tr class='{$altrow}'>" .
			"<td style='border:0px'>$votes" . $voteButton . '</table>' );
		if ( $login ) { // Following columns only when user is logged in
			$wgOut->addHTML( "<td class='table-blue-contentcell' align='center'>" .
				"<input type=checkbox value='1' name='check_$wishId' $watching>" );
			$wgOut->addHTML( "<td class='table-blue-contentcell'>" .
				"<table class='prettytable' style='border:0px'><tr class='{$altrow}'>" );
			if ( $wish->userCan( 'resolve' ) ) {
				$wgOut->addHTML( '<td style="border:0px">' .
					$this->createButton( "apply.gif", "resolved",
						"Resolve this item", $wishId ) );
			}
			if ( $wish->userCan( 'delete' ) ) {
				$wgOut->addHTML( '<td style="border:0px">' .
					$this->createButton( "cancel.gif", "remove", "Remove this item",
						$wishId ) );
			}
			$wgOut->addHTML( "</table>" );
		}
	}

	public static function getSortTimeDate( $time ) {
		global $wgLang;
		return "<span style='display:none'>$time</span>" .
			$wgLang->timeanddate( $time, true );
	}

	function truncateComment( $wish, $cutoff = 0 ) {
		$pagename = $wish->getTitle()->getFullText();
		$comment = $wish->getComments();
		if ( $cutoff && strlen( $comment ) > $cutoff ) {
			// Truncate comment to cutoff length
			$comment = substr( $comment, 0, $cutoff );
			// Append with 'more' link
			$comment .= "...'''[[$pagename |more]]'''";
		}
		return $comment;
	}

	function createButton( $image, $action, $title, $wishId ) {
		global $wgScriptPath;
		return "<a href='{$this->thisUrl}&wishaction=$action&id=$wishId'>
<img align='right' style='border:1' src='$wgScriptPath/skins/common/images/$image'
	 title='$title'/></a>";
	}

	function createLink( $label, $action, $title, $wishId ) {
		return "<a href='{$this->thisUrl}&wishaction=$action&id=$wishId'
                   title='$title'>$label</a>";
	}

}
