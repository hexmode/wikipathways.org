<?php
class PopularPathwaysPage extends SpecialPage
{
		function PopularPathwaysPage() {
				SpecialPage::SpecialPage("PopularPathwaysPage");
		}

		function execute( $par ) {
				global $wgRequest, $wgOut;

				$this->setHeaders();

				list( $limit, $offset ) = wfCheckLimits();

				$ppp = new PPQueryPage();

				return $ppp->doQuery( $offset, $limit );
		}

}

class PPQueryPage extends QueryPage {

	function getName() {
		return "PopularPathwaysPage";
	}

	function isExpensive() {
		# page_counter is not indexed
		return true;
	}
	function isSyndicated() { return false; }

	function getSQL() {
		$dbr =& wfGetDB( DB_SLAVE );
		$page = $dbr->tableName( 'page' );

		return
			"SELECT 'Popularpages' as type,
					page_namespace as namespace,
					page_title as title,
				page_id as id,
					page_counter as value
			FROM $page
			WHERE page_namespace=".NS_PATHWAY."
			AND page_is_redirect=0";
	}

	function formatResult( $skin, $result ) {
		global $wgLang, $wgContLang;
		$taggedIds = CurationTag::getPagesForTag('Curation:Tutorial');
		if (in_array($result->id, $taggedIds)){
			return null;
		}
		$pathway = Pathway::newFromTitle($result->title);
		if(!$pathway->getTitleObject()->userCan('read')) return null; //Skip private pathways
		$title = Title::makeTitle( $result->namespace, $pathway->getSpecies().":".$pathway->getName() );
				$id = Title::makeTitle( $result->namespace, $result->title );
		$link = $skin->makeKnownLinkObj( $id, htmlspecialchars( $wgContLang->convert( $title->getBaseText() ) ) );
		$nv = wfMsgExt( 'nviews', array( 'parsemag', 'escape'),
			$wgLang->formatNum( $result->value ) );
		return wfSpecialList($link, $nv);
	}
}
