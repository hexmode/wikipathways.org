<?php

class LocalHooks {
	/* http://developers.pathvisio.org/ticket/1559 */
	static function stopDisplay( $output, $sk ) {
		if( strtolower( 'MediaWiki:Questycaptcha-qna' ) === strtolower( $output->getPageTitle() ) ||
			strtolower( 'MediaWiki:Questycaptcha-q&a' ) === strtolower( $output->getPageTitle() ) ) {
			global $wgUser, $wgTitle;
			if( !$wgTitle->userCan( "edit" ) ) {
				$output->clearHTML();
				$wgUser->mBlock = new Block( '127.0.0.1', 'WikiSysop', 'WikiSysop', 'none', 'indefinite' );
				$wgUser->mBlockedby = 0;
				$output->blockedPage();
				return false;
			}
		}
		return true;
	}

	/* http://www.pathvisio.org/ticket/1539 */
	static public function externalLink ( &$url, &$text, &$link, &$attribs = null ) {
		global $wgExternalLinkTarget;
		wfProfileIn( __METHOD__ );
		wfDebug(__METHOD__.": Looking at the link: $url\n");

		$linkTarget = "_blank";
		if( isset( $wgExternalLinkTarget ) && $wgExternalLinkTarget != "") {
			$linkTarget = $wgExternalLinkTarget;
		}

		/**AP20070417 -- moved from Linker.php by mah 20130327
		 * Added support for opening external links as new page
		 * Usage: [http://www.genmapp.org|_new Link]
		 */
		if ( substr( $url, -5 ) == "|_new" ) {
			$url = substr( $url, 0, strlen( $url ) - 5 );
			$linkTarget = "new";
		} elseif ( substr( $url, -7 ) == "%7c_new" ) {
			$url = substr( $url, 0, strlen( $url ) - 7 );
			$linkTarget = "new";
		}

		# Hook changed to include attribs in 1.15
		if( $attribs !== null ) {
			$attribs["target"] = $linkTarget;
			return true;		/* nothing else should be needed, so we can leave the rest */
		}

		/* ugh ... had to copy this bit from makeExternalLink */
		$l = new Linker;
		$style = $l->getExternalLinkAttributes( $url, $text, 'external ' );
		global $wgNoFollowLinks, $wgNoFollowNsExceptions;
		if( $wgNoFollowLinks && !(isset($ns) && in_array($ns, $wgNoFollowNsExceptions)) ) {
			$style .= ' rel="nofollow"';
		}

		$link = '<a href="'.$url.'" target="'.$linkTarget.'"'.$style.'>'.$text.'</a>';
		wfProfileOut( __METHOD__ );

		return false;
	}


	static public function updateTags( &$article, &$user, $text, $summary, $minoredit, $watchthis, $sectionanchor, &$flags,
		$revision, &$status = null, $baseRevId = null ) {
		$title = $article->getTitle();
		if( $title->getNamespace() !== NS_PATHWAY ) {
			return true;
		}

		if( !$title->userCan( "autocurate" ) ) {
			wfDebug( __METHOD__ . ": User can't autocurate\n" );
			return true;
		}

		wfDebug( __METHOD__ . ": Autocurating tags for {$title->getText()}\n" );
		$db = wfGetDB( DB_MASTER );
		$tags = MetaTag::getTagsForPage( $title->getArticleID() );
		foreach( $tags as $tag ) {
			$oldRev = $tag->getPageRevision();
			if ( $oldRev ) {
				wfDebug( __METHOD__ . ": Setting {$tag->getName()} to {$revision->getId()}\n" );
				$tag->setPageRevision( $revision->getId() );
				$tag->save();
			} else {
				wfDebug( __METHOD__ . ": No revision information for {$tag->getName()}\n" );
			}
		}
		return true;
	}

	static public function blockPage( &$pagePriv ) {
		$setPerms = array(
			'Listusers', 'Statistics', 'Randompage', 'Lonelypages',
			'Uncategorizedpages', 'Uncategorizedcategories',
			'Uncategorizedimages', 'Uncategorizedtemplates',
			'Unusedcategories', 'Unusedimages', 'Wantedpages',
			'Wantedcategories', 'Mostlinked', 'Mostlinkedcategories',
			'Mostlinkedtemplates', 'Mostcategories', 'Mostimages',
			'Mostrevisions', 'Fewestrevisions', 'Shortpages',
			'Longpages', 'Newpages', 'Ancientpages', 'Deadendpages',
			'Protectedpages', 'Protectedtitles', 'Allpages',
			'Prefixindex', 'Ipblocklist', 'Categories', 'Export',
			'Allmessages', 'Log', 'MIMEsearch', 'Listredirects',
			'Unusedtemplates', 'Withoutinterwiki', 'Filepath'
			);

		foreach( $setPerms as $page ) {
			if( isset( $pagePriv[ $page ] ) ) {
				$priv = $pagePriv[ $page ];
				if( !is_array( $priv ) ) {
					$pagePriv[$page] = array( "SpecialPage", $page, "block" );
				} elseif( !in_array( "block", $priv ) ) {
					$pagePriv[$page][] = "block";
				}
			}
		}
		return true;
	}

	public static function loginMessage( &$user, &$html ) {
		global $wgScriptPath;

		# Run any hooks; ignore results
		$addr = $user->getEmail();
		$name = $user->getName();
		$realname = $user->getRealName();
		$prefs = $wgScriptPath . '/index.php/Special:Preferences';
		$watch = $wgScriptPath . '/index.php/Special:Watchlist/edit';
		$injected_html = "<p>You are now logged in as:
<ul><li><i>Username:</i> <b>$name</b>
<li><i>Real name:</i> <b>$realname</b> (<a href=$prefs>change</a>)
<li><i>Email:</i> <b>$addr</b> (<a href=$prefs>change</a>)</ul></p>
<p>Your <i>real name</i> will show up in the author list of any
pathway you create or edit.  Your <i>email</i> will not be shown
to other users, but it will be used to contact you if a pathway
you have created or added to your <a href=$watch>watchlist</a> is
altered or commented on by other users. Your <i>email</i> is the
only means by which WikiPathways can contact you if any of your
content requires special attention. <b>Please keep your
<i>email</i> up-to-date.</b></p>";
		return true;
	}

	public static function addSnoopLink( &$item, $row ) {
		//AP20081006 - replaced group info with links to User_snoop
		$snoop = Title::makeTitle( NS_SPECIAL, 'User_snoop');
		$snooplink = $this->getSkin()->makeKnownLinkObj( $snoop, 'info', wfArrayToCGI( array('username' => $row->user_name)), '','','');

		$item = wfSpecialList( $name, $snooplink);
		return true;
	}

	// This one, based on preg_replace, is a bit more iffy.  Needs some good testing.
	public static function contributionLineEnding( $specialPage, &$ret, $row ) {
		$page = Title::makeTitle( $row->page_namespace, $row->page_title );
		if (!$page->isRedirect() && $row->page_namespace == NS_PATHWAY){
			$linkOld = $sk->makeKnownLinkObj( $page );
			$pathway = Pathway::newFromTitle($row->page_title );
			$name = $pathway->getSpecies() .":". $pathway->getName();
			$linkNew = $sk->makeKnownLinkObj( $page, $name );
			preg_replace( "/$linkOld/", $linkNew, $ret );
			return true;
		}
		return true;
	}

	public static function dontWatchRedirect(&$conds,&$tables,&$join_conds,&$fields) {
		$conds[] = 'page_is_redirect = 0';
		return true;
	}

	public static function subtitleOverride(&$article, &$oldid) {
		global $wgLang, $wgOut, $wgUser;


		$revision = Revision::newFromId( $oldid );

		$current = ( $oldid == $article->mLatest );
		$td = $wgLang->timeanddate( $article->getTimestamp(), true );
		$sk = RequestContext::getMain()->getSkin();
		$lnk = $current
			? wfMessage( 'currentrevisionlink' )->escaped()
			: $sk->makeKnownLinkObj( $article->mTitle, wfMessage( 'currentrevisionlink' )->escaped() );
		$curdiff = $current
			? wfMessage( 'diff' )->escaped()
			: $sk->makeKnownLinkObj( 'Special:DiffAppletPage', wfMessage( 'diff' )->escaped(),
				"old={$oldid}&new={$article->mLatest}&pwTitle={$article->mTitle}" );
		$prev = $article->mTitle->getPreviousRevisionID( $oldid ) ;
		$prevlink = $prev
			? $sk->makeKnownLinkObj( $article->mTitle, wfMessage( 'previousrevision' )->escaped(), 'direction=prev&oldid='.$oldid )
			: wfMessage( 'previousrevision' )->escaped();
		$prevdiff = $prev
			? $sk->makeKnownLinkObj( 'Special:DiffAppletPage', wfMessage( 'diff' )->escaped(),
				"old={$oldid}&new={$prev}&pwTitle={$article->mTitle}" )
			: wfMessage( 'diff' )->escaped();
		$next = $article->mTitle->getNextRevisionID( $oldid ) ;
		$nextlink = $current
			? wfMessage( 'nextrevision' )->escaped()
			: $sk->makeKnownLinkObj( $article->mTitle, wfMessage( 'nextrevision' )->escaped(), 'direction=next&oldid='.$oldid );
		$nextdiff = $current
			? wfMessage( 'diff' )->escaped()
			: $sk->makeKnownLinkObj( 'Special:DiffAppletPage', wfMessage( 'diff' )->escaped(),
				"old={$oldid}&new={$next}&pwTitle={$article->mTitle}" );

		$cdel='';
		if( $wgUser->isAllowed( 'deleterevision' ) ) {
			$revdel = SpecialPage::getTitleFor( 'Revisiondelete' );
			if( $revision->isCurrent() ) {
			// We don't handle top deleted edits too well
				$cdel = wfMessage( 'rev-delundel' )->escaped();
			} else if( !$revision->userCan( Revision::DELETED_RESTRICTED ) ) {
			// If revision was hidden from sysops
				$cdel = wfMessage( 'rev-delundel' )->escaped();
			} else {
				$cdel = $sk->makeKnownLinkObj( $revdel,
					wfMessage( 'rev-delundel' )->escaped(),
					'target=' . urlencode( $article->mTitle->getPrefixedDbkey() ) .
					'&oldid=' . urlencode( $oldid ) );
				// Bolden oversighted content
				if( $revision->isDeleted( Revision::DELETED_RESTRICTED ) )
					$cdel = "<strong>$cdel</strong>";
			}
			$cdel = "(<small>$cdel</small>) ";
		}
		# Show user links if allowed to see them. Normally they
		# are hidden regardless, but since we can already see the text here...
		$userlinks = $sk->revUserTools( $revision, false );

		$m = wfMessage( 'revision-info-current' )->text();
		$infomsg = $current && !wfMessage( 'revision-info-current' )->inContentLanguage()->isBlank() && $m !== '-'
			? 'revision-info-current'
			: 'revision-info';

		$r = "\n\t\t\t\t<div id=\"mw-{$infomsg}\">" . wfMsgExt( $infomsg, array( 'parseinline', 'replaceafter' ), $td, $userlinks, $revision->getID() ) . "</div>\n" .

			 "\n\t\t\t\t<div id=\"mw-revision-nav\">" . $cdel . wfMsgExt( 'revision-nav', array( 'escapenoentities', 'parsemag', 'replaceafter' ),
				$prevdiff, $prevlink, $lnk, $curdiff, $nextlink, $nextdiff ) . "</div>\n\t\t\t";
		$wgOut->setSubtitle( $r );

		return false;
	}

	static public function linkText( $linker, $target, &$text ) {
		$text = "";
		if( $target instanceOf Title ) {
			if ($target->getNamespace() == NS_PATHWAY){
				$pathway = Pathway::newFromTitle($target);
				// Keep private pathway names obscured
				if(!$pathway->isReadable()) {
					$text = $target->getName();
				} else {
					$text = Title::makeTitle( $target->getNsText(),
						$pathway->getSpecies().":".$pathway->getName() );
				}
				return false;
			}
		}
		return true;
	}

	static public function checkPathwayImgAuth( &$title, &$path, &$name, &$result ) {
		## WPI Mod 2013-Aug-22
		//Check for pathway cache
		$id = Pathway::parseIdentifier($title);
		if($id) {
			//Check pathway permissions
			$pwTitle = Title::newFromText($id, NS_PATHWAY);

			if(!$pwTitle->userCan('read')) {
				wfDebugLog( 'img_auth', "User not permitted to view pathway $id" );
				wfForbidden();
				return false;
			}
		}
		return true;
	}

	static function addPreloaderScript($out) {
		global $wgTitle, $wgUser, $wgScriptPath;

		if($wgTitle->getNamespace() == NS_PATHWAY && $wgUser->isLoggedIn() &&
			strstr( $out->getHTML(), "pwImage" ) !== false ) {
			$base = $wgScriptPath . "/wpi/applet/";
			$class = "org.wikipathways.applet.Preloader.class";

			$out->addHTML("<applet code='$class' codebase='$base'
			width='1' height='1' name='preloader'></applet>");
		}
		return true;
	}

	static function getMagic( &$magicWords, $langCode ) {
		# Add the magic word
		# The first array element is case sensitive, in this case it is not case sensitive
		# All remaining elements are synonyms for our parser function
		$magicWords['PathwayViewer'] = array( 0, 'PathwayViewer' );
		$magicWords['pwImage'] = array( 0, 'pwImage' );
		$magicWords['pathwayOfTheDay'] = array( 0, 'pathwayOfTheDay' );
		$magicWords['pathwayInfo'] = array( 0, 'pathwayInfo' );
		$magicWords['maxImageSize'] = array( 0, 'maxImageSize' );
		return true;
	}

	static function extensionFunctions() {
		global $wgParser;
		$wgParser->setHook( "pathwayBibliography",     "PathwayBibliography::output" );
		$wgParser->setHook( "pathwayHistory",          "GpmlHistoryPager::history" );
		$wgParser->setHook( "batchDownload",           "BatchDownloader::createDownloadLinks" );
		$wgParser->setFunctionHook( "PathwayViewer",   "PathwayViewer::display" );
		$wgParser->setFunctionHook( "pwImage",         "PathwayThumb::render" );
		$wgParser->setFunctionHook( 'maxImageSize',    'LocalHooks::getSize' );
		$wgParser->setFunctionHook( 'pathwayOfTheDay', 'LocalHooks::getPathwayOfTheDay' );
		$wgParser->setFunctionHook( 'pathwayInfo',     'LocalHooks::getPathwayInfoText' );
	}

	static function getPathwayInfoText( &$parser, $pathway, $type ) {
		global $wgRequest;
		$parser->disableCache();
		try {
			$pathway = Pathway::newFromTitle($pathway);
			$oldid = $wgRequest->getval('oldid');
			if($oldid) {
				$pathway->setActiveRevision($oldid);
			}
			$info = new PathwayInfo($parser, $pathway);
			if(method_exists($info, $type)) {
				return $info->$type();
			} else {
				throw new Exception("method PathwayInfo->$type doesn't exist");
			}
		} catch(Exception $e) {
			return "Error: $e";
		}
	}

	/*
	  Pathway of the day generator

	  We need:
	  - a randomized list of all pathways
	  - remove pathway that is used
	  - randomize again when we're at the end!
	  - update list when new pathways are added....randomize every time (but exclude those we've already had)

	  Concerning MediaWiki:
	  - create a new SpecialPage: Special:PathwayOfTheDay
	  - create an extension that implements above in php

	  We need:
	  - to pick a random pathway everyday (from all articles in namespace pathway)
	  - remember this pathway and the day it was picked, store that in cache
	  - on a new day, pick a new pathway, replace cache and update history
	*/

	static function getPathwayOfTheDay( &$parser, $date, $listpage = 'FeaturedPathways', $isTag = false) {
		$parser->disableCache();
		wfDebug("GETTING PATHWAY OF THE DAY for date: $date\n");
		try {
			if($isTag) {
				$potd = new TaggedPathway($listpage, $date, $listpage);
			} else {
				$potd = new FeaturedPathway($listpage, $date, $listpage);
			}
			$out =  $potd->getWikiOutput();
			wfDebug("END GETTING PATHWAY OF THE DAY for date: $date\n");
		} catch(Exception $e) {
			$out = "Unable to get pathway of the day: {$e->getMessage()}";
			wfDebug("Couldn't make pathway of the day: {$e->getMessage()}");
		}
		$out = $parser->recursiveTagParse( $out );
		return array( $out, 'isHTML' => true, 'noparse' => true, 'nowiki' => true );
	}

	/**
	 * Special user permissions once a pathway is deleted.
	 * TODO: Disable this hook for running script to transfer to stable ids
	 */
	static function checkUserCan($title, $user, $action, $result) {
		if( $title->getNamespace() == NS_PATHWAY ) {
			if($action == 'edit') {
				$pathway = Pathway::newFromTitle($title);
				if($pathway->isDeleted()) {
					if(MwUtils::isOnlyAuthor($user, $title->getArticleId())) {
						//Users that are sole author of a pathway can always revert deletion
						$result = true;
						return false;
					} else {
						//Only users with 'delete' permission can revert deletion
						//So disable edit for all other users
						$result = $title->getUserPermissionsErrors('delete', $user) == array();
						return false;
					}
				}
			} else if ( $action == 'delete' ) {
				//Users are allowed to delete their own pathway
				if(MwUtils::isOnlyAuthor($user, $title->getArticleId()) && $title->userCan('edit')) {
					$result = true;
					return false;
				}
			}
		}
		$result = null;
		return true;
	}

	/*
	 * Special delete permissions for pathways if user is sole author
	 */
	static function checkSoleAuthor($title, $user, $action, $result) {
		$result = null;
		return true;
	}

	static function getSize( &$parser, $image, $maxWidth ) {
		try {
			$img = new LocalFile(Title::newFromText($image), RepoGroup::singleton()->getLocalRepo());
			$img->loadFromFile();
			$w = $img->getWidth();
			if($w > $maxWidth) $w = $maxWidth;
			return $w . 'px';
		} catch (Exception $e) {
			return "Error: $e";
		}
	}
}

$wgHooks['SpecialListusersFormatRow'][] = 'LocalHooks::addSnoopLink';
$wgHooks['ContributionsLineEnding'][]   = 'LocalHooks::contributionLineEnding';
$wgHooks['LinkerMakeExternalLink'][]    = 'LocalHooks::externalLink';
$wgHooks['SpecialWatchlistQuery'][]     = 'LocalHooks::dontWatchRedirect';
$wgHooks['SpecialPage_initList'][]      = 'LocalHooks::blockPage';
$wgHooks['ImgAuthBeforeStream'][]       = 'LocalHooks::checkPathwayImgAuth';
$wgHooks['ArticleSaveComplete'][]       = 'LocalHooks::updateTags';
$wgHooks['DisplayOldSubtitle'][]        = 'LocalHooks::subtitleOverride';
$wgHooks['UserLoginComplete'][]         = 'LocalHooks::loginMessage';
$wgHooks['BeforePageDisplay'][]         = 'LocalHooks::addPreloaderScript';
$wgHooks['BeforePageDisplay'][]         = 'LocalHooks::stopDisplay';
$wgHooks['LinkText'][]                  = 'LocalHooks::linkText';
$wgHooks['userCan'][]                   = 'LocalHooks::checkUserCan';
