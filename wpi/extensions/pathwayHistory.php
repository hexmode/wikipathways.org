<?php

class GpmlHistoryPager extends HistoryPager {
	private $pathway;
	private $nrShow = 5;
	private $style;
	private $linesOnPage;

	function __construct( $pathway, $pageHistory ) {
		parent::__construct( $pageHistory );
		$this->pathway = $pathway;
	}

	function formatRow( $row ) {
		$latest = $this->mCounter == 1;
		$firstInList = $this->mCounter == 1;
		$this->style = ($this->mCounter <= $this->nrShow) ? '' : 'class="toggleMe"';

		$s = $this->historyRow(
			$this->historyLine(
				$row,
				$this->getNumRows(),
				$this->mCounter++,
				$latest,
				$firstInList
			)
		);

		$this->mLastRow = $row;
		return $s;
	}

	function getStartBody() {
		$this->mLastRow = false;
		$this->mCounter = 1;

		$nr = $this->getNumRows();

		if($nr < 1) {
			$table = '';
		} else {
			$table = '<form action="' . SITE_URL . '/index.php" method="get">';
			$table .= '<input type="hidden" name="title" value="Special:DiffAppletPage"/>';
			$table .= '<input type="hidden" name="pwTitle" value="' . $this->pathway->getTitleObject()->getFullText() . '"/>';
			$table .= '<input type="submit" value="Compare selected versions"/>';
			$table .= "<TABLE  id='historyTable' class='wikitable'><TR><TH>Compare<TH>Revision<TH>Action<TH>Time<TH>User<TH>Comment<TH id='historyHeaderTag' style='display:none'>";

		}

		$table = Pathway::toggleAll( 'historyTable', $nr, $this->nrShow ) . $table;

		return $table;
	}

	function getEndBody() {
		$end = "</TABLE>";
		$end .= '<input type="submit" value="Compare selected versions"></form>';
		return $end;
	}

	static function history( $input, $argv, $parser ) {
		try {
			$pathway = Pathway::newFromTitle($parser->mTitle);
			return self::getHistory($pathway);
		} catch(Exception $e) {
			return "Error: $e";
		}
	}

	static function getHistory($pathway) {
		global $wgUser, $wpiScriptURL;

		if( !method_exists( $pathway, "getTitleObject" ) ) {
			return "";
		}
		$gpmlTitle = $pathway->getTitleObject();
		$gpmlArticle = new Article($gpmlTitle);
		$hist = new HistoryPager($gpmlArticle);

		$pager = new GpmlHistoryPager( $pathway, $hist );

		$s = $pager->getBody();
		return $s;
	}

	function historyRow($h) {

		if($h) {
			$row = "<TR ". $this->style . ">";
			$row .= "<TD>$h[diff]";
			$row .= "<TD id=\"historyTable_$h[id]_tag\">$h[id]";
			$row .= "<TD>$h[rev]$h[view]";
			$row .= "<TD>$h[date]";
			$row .= "<TD>$h[user]";
			$row .= "<TD>$h[descr]";
			return $row;
		} else {
			return "";
		}
	}

	function historyLine($row, $nr, $counter = '', $cur = false, $firstInList = false) {
		global $wpiScript, $wgLang, $wgUser, $wgTitle;

		$rev = new Revision( $row );

		$user = User::newFromId($rev->getUser());
		/* Show bots
		   if($user->isBot()) {
		   //Ignore bots
		   return "";
		   }
		*/

		$rev->setTitle( $this->pathway->getFileTitle(FILETYPE_GPML) );

		$revUrl = WPI_SCRIPT_URL . '?action=revert&pwTitle=' .
			$this->pathway->getTitleObject()->getPartialURL() .
			"&oldid={$rev->getId()}";

		$this->linesOnPage = $nr;
		$diff = self::diffButtons( $rev, $firstInList );

		$revert = "";
		if($wgUser->getID() != 0 && $wgTitle && $wgTitle->userCan('edit') ) {
			$revert = $cur ? "" : "(<A href=$revUrl>revert</A>), ";
		}

		$dt = $wgLang->timeanddate( wfTimestamp(TS_MW, $rev->getTimestamp()), true );
		$oldid = $firstInList ? array() : array( "oldid" => $rev->getId() );
		$view = Linker::linkKnown($this->pathway->getTitleObject(), 'view', array(), $oldid );

		$date = $wgLang->timeanddate( $rev->getTimestamp(), true );
		$user = RequestContext::getMain()->getSkin()->userLink( $rev->getUser(), $rev->getUserText() );
		$descr = $rev->getComment();
		return array(
			'diff' => $diff,
			'rev'=>$revert,
			'view'=>$view,
			'date'=>$date,
			'user'=>$user,
			'descr'=>$descr,
			'id'=>$rev->getId()
		);
	}

	/**
	 * Generates dynamic display of radio buttons for selecting versions to compare
	 */
	function diffButtons( $rev, $firstInList) {
		if( $this->linesOnPage > 1) {
			$radio = array(
				'type'  => 'radio',
				'value' => $rev->getId(),
				# do we really need to flood this on every item?
				#'title' => wfMessage( 'selectolderversionfordiff' )->escaped()
			);

			if( !$rev->userCan( Revision::DELETED_TEXT ) ) {
				$radio['disabled'] = 'disabled';
			}

			/** @todo: move title texts to javascript */
			if ( $firstInList ) {
				$first = Html::Element( 'input',
					array_merge( $radio,
						array(
							'style' => 'visibility:hidden',
							'name'  => 'old' ) ) );
				$checkmark = array( 'checked' => 'checked' );
			} else {
				if( $this->mCounter == 2 ) {
					$checkmark = array( 'checked' => 'checked' );
				} else {
					$checkmark = array();
				}
				$first = Html::Element( 'input',
					array_merge(
						$radio,
						$checkmark,
						array( 'name'  => 'old' ) ) );
				$checkmark = array();
			}
			$second = Html::Element( 'input', array_merge(
					$radio,
					$checkmark,
					array( 'name'  => 'new' ) ) );
			return $first . "&nbsp;" . $second;
		} else {
			return '';
		}
	}

}
