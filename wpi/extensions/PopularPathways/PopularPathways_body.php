<?php
class LegacyPopularPathways extends LegacySpecialPage {
	function __construct() {
		parent::__construct( "PopularPathwaysPage", "PopularPathways" );
	}
}

class PopularPathways extends QueryPage {
	public $namespace = NS_PATHWAY;
	public $tagged;
	function __construct( ) {
		parent::__construct( __CLASS__ );
		$this->tagged = CurationTag::getPagesForTag('Curation:Tutorial');
	}

	function isExpensive() {
		# page_counter is not indexed
		return true;
	}
	function isSyndicated() { return false; }

	function getQueryInfo() {
		return array(
			'fields' => array(
				"'Popularpages' as type",
				"page_namespace as namespace",
				"page_title as title",
				"page_id as id",
				"page_counter as value"
			),
			'tables' => 'page',
			'conds' => array(
				'page_namespace' => $this->namespace,
				'page_is_redirect' => 0,
			)
		);
	}

	function formatResult( $skin, $result ) {
		global $wgLang, $wgContLang;
		if (in_array($result->id, $this->tagged)){
			return null;
		}
		$pathway = Pathway::newFromTitle($result->title);
		if(!$pathway->getTitleObject()->userCan('read')) return null; //Skip private pathways
		$title = Title::makeTitle( $result->namespace, $pathway->getSpecies().":".$pathway->getName() );
		$id = Title::makeTitle( $result->namespace, $result->title );
		$link = $skin->linkKnown( $id, htmlspecialchars( $wgContLang->convert( $title->getBaseText() ) ) );
		$nv = wfMessage( 'nviews', $wgLang->formatNum( $result->value ) )->text();
		return $wgLang->specialList($link, $nv);
	}
}
