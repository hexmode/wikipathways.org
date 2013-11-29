<?php


class LegacyNewPathways extends LegacySpecialPage {
	function __construct() {
		parent::__construct( "NewPathwaysPage", "NewPathways" );
	}
}

class NewPathways extends QueryPage {
	function __construct() {
		parent::__construct(__CLASS__);
	}

	protected $name = __CLASS__;

	function isExpensive() {
		# page_counter is not indexed
		return true;
	}
	function isSyndicated() { return false; }

	function getQueryInfo() {
		return array(
			'fields' => array(
				"rc_namespace as namespace",
				"page_title as title",
				"rc_user as user_id",
				"rc_user_text as utext",
				"rc_timestamp as value"
			),
			"tables" => array( "recentchanges", "page" ),
			"query" => array(
				"page_title" => "rc_title",
				"rc_new" => 1,
				"rc_bot" => 0,
				"rc_namespace" => NS_PATHWAY,
				"rc_namespace" => "page_namespace"
			),
			'join_conds' => array(
				'page' => array( 'LEFT JOIN', 'rc_cur_id=page_id' )
			)
		);
	}

	function formatResult( $skin, $result ) {
		global $wgLang, $wgContLang, $wgUser;
		$titleName = $result->title;
		try {
			$pathway = Pathway::newFromTitle($result->title);
			if( !$pathway->getTitleObject()->userCan( 'read' ) ||
				$pathway->isDeleted() ) {
				//Don't display this title when user is not allowed to read
				return null;
			}
			$titleName = $pathway->getSpecies().":".$pathway->getName();
		} catch(Exception $e) {}
		$title = Title::makeTitle( $result->namespace, $titleName );
		$id = Title::makeTitle( $result->namespace, $result->title );
		$link = $skin->linkKnown( $id, htmlspecialchars( $wgContLang->convert( $title->getBaseText() ) ) );
		$nv = "<b>". $wgLang->date($result->value) . "</b> by <b>" . RequestContext::getMain()->getSkin()->userlink($result->user_id, $result->utext) ."</b>";

		global $wgLang;
		return $wgLang->specialList( $link, $nv );
	}
}
