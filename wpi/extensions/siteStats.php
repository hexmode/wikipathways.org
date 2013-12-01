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

		if( ! is_dir( WPI_CACHE_PATH ) && ! wfMkdirParents( WPI_CACHE_PATH ) ) {
			wfDebug( "Can't create: " . WPI_CACHE_PATH );
			throw new Exception( "Can't create WPI_CACHE_PATH!" );
		}

		$table = "";
		foreach(Pathway::getAvailableSpecies() as $species) {
			$nr = StatisticsCache::howManyPathways($species);
			$genes = StatisticsCache::howManyUniqueGenes($species);
			if ($nr > 0) {  // skip listing species with 0 pathways
				$table .= "\n| $species || '''$nr''' || ''($genes)''\n|-\n";
			}
		}
		$output  = "\n* There are '''{$nrPathways}''' pathways.\n";
		$output .= "* Number of '''pathways''' ''(and unique genes)'' per species:\n";
		$output .= "{| align='center' $tableAttr\n" . $table . "\n|}\n";
		$output .= "* There are '''{{NUMBEROFUSERS}}''' registered users\n";
		$output .= "* There are '''{{NUMBEROFACTIVEUSERS}}''' active users";

		// The reason we don't just return $output here is because we need
		// to parse the magic words.
		$output = $parser->recursiveTagParse( $output );
		return array( $output, 'isHTML' => true, 'noparse' => true,
			'nowiki' => true );
	}

	static function getSpecies() {
		return Pathway::getAvailableSpecies();
	}
}
