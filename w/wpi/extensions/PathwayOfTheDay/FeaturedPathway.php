<?php

/**
Featured pathway is a slight modification on PathwayOfTheDay, it does get
pathways from a limited collection, kept on the FeaturedPathway wiki page
**/
class FeaturedPathway extends PathwayOfTheDay {
	private $listPage;

	function __construct($id, $date, $listPage) {
		$this->listPage = $listPage;
		parent::__construct($id, $date);
	}

	/**
	Select a random pathway from the list
	on the wiki page FeaturedPathway
	**/
	protected function fetchRandomPathway() {
		wfProfileIn( __METHOD__ );
		wfDebug("Fetching random pathway...\n");
		$pathwayList = Pathway::parsePathwayListPage($this->listPage);
		if(count($pathwayList) == 0) {
			throw new Exception("{$this->listPage} doesn't contain any valid pathway!");
		}
		$pathway = $pathwayList[rand(0, count($pathwayList) - 1)]->getTitleObject()->getDbKey();
		wfProfileOut( __METHOD__ );
		return $pathway;
	}
}
