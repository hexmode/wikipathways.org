<?php

class MostEditedPathwayPage extends QueryPage {
	private $namespace;

	function __construct() {
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
			)
			'tables' => array( 'revision', 'page' ),
			'query'  => array(
				'page_namespace'   => $this->namespace,
				'page_is_redirect' => 0,
			)
		);
	}

	private $taggedIds;

	function formatResult( $skin, $result ) {
		global $wgLang, $wgContLang;
		if (in_array($result->id, $this->taggedIds)){
			return null;
		}
		$pathway = Pathway::newFromTitle($result->title);
		if(!$pathway->isReadable()) return null; //Skip private pathways
		$title = Title::makeTitle( $result->namespace, $pathway->getSpecies().":".$pathway->getName() );
		$id = Title::makeTitle( $result->namespace, $result->title );
		$text = $wgContLang->convert("$result->value revisions");
		$plink = $skin->linkKnown( $id, $wgContLang->convert( $title->getBaseText() ) );

		return wfSpecialList($plink, $text);
	}
}
