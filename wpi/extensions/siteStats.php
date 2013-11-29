<?php

/*
Statistics for main page
- how many pathways	{{PAGESINNS:NS_PATHWAY}}
- how many organisms
- how many pathways per organism
*/

class wpiSiteStats {

	static function getSiteStats( &$parser, $tableAttr ) {
		$nrPathways = StatisticsCache::howManyPathways('total');
		$output = "\n* There are '''{$nrPathways}''' pathways";

		if( ! is_dir( WPI_CACHE_PATH ) && ! wfMkdirParents( WPI_CACHE_PATH ) ) {
			wfDebug( "Can't create: " . WPI_CACHE_PATH );
			throw new Exception( "Can't create WPI_CACHE_PATH!" );
		}

		$table = <<<EOD
* Number of '''pathways''' ''(and unique genes)'' per species:
{| align="center" $tableAttr
EOD;
		foreach(Pathway::getAvailableSpecies() as $species) {
			$nr = StatisticsCache::howManyPathways($species);
			$genes = StatisticsCache::howManyUniqueGenes($species);
			if ($nr > 0) {  // skip listing species with 0 pathways
				$table .= <<<EOD

|-align="left"
|$species:
|'''$nr'''
|''($genes)''
EOD;
			}
		}
		$table .= "\n|}";
		$output .= $table;
		$output .= "\n* There are '''{{NUMBEROFUSERS}}''' registered users";
		//$output .= "\n* Active user [[Special:ContributionScores|statistics]]";
		$output = $parser->recursiveTagParse( $output );
		return array( $output, 'isHTML' => true, 'noparse' => true,
			'nowiki' => true );
	}

	static function getSpecies() {
		return Pathway::getAvailableSpecies();
	}
}
