<?php

class MostEditedPathwaysPage extends QueryPage {
	private $namespace;

	function __construct() {
		parent::__construct(__CLASS__);
		$this->namespace = NS_PATHWAY;
		$this->taggedIds = CurationTag::getPagesForTag('Curation:Tutorial');
	}

	function getName() {
		return "MostEditedPathwaysPage";
	}

	function isExpensive() {
		# page_counter is not indexed
		return true;
	}
	function isSyndicated() { return false; }

	function getQueryInfo() {
		return array(
			"fields" => array(
				"'Mostrevisions' as type",
				"page_namespace as namespace",
				"page_id as id",
				"page_title as title",
				"COUNT(*) as value"
			),
			'tables' => array( 'revision', 'page'),
			'conds'  => array(
				'page_namespace'   => $this->namespace,
				'page_is_redirect' => 0,
                'page_id = rev_page'
			),
		    'options' => array(
				'GROUP BY' => '1,2,3',
				'HAVING' => 'value > 1'
			)
		);
	}

	private $taggedIds;

	function formatResult( $skin, $result ) {
		global $wgLang, $wgContLang;
		if (in_array($result->id, $this->taggedIds)){
			return null;
		}

		if ( $result->title !== null ) {
			$pathway = Pathway::newFromTitle($result->title);
			if(!$pathway->getTitleObject()->userCan('read')) return null; //Skip private pathways
			$title = Title::makeTitle( $result->namespace, $pathway->getSpecies().":".$pathway->getName() );
			$id = Title::makeTitle( $result->namespace, $result->title );
			$text = $wgContLang->convert("$result->value revisions");
			$plink = $skin->linkKnown( $id, $wgContLang->convert( $title->getBaseText() ) );

			return wfSpecialList($plink, $text);
		}
		return null;
	}
}
