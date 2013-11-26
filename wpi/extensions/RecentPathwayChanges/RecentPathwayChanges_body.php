<?php

class RecentPathwayChanges extends QueryPage {
	var $requestedSort = '';
	private $namespace;

	function __construct() {
		parent::__construct( "RecentPathwayChanges" );
		$this->namespace = NS_PATHWAY;
	}

	function getName() {
		return "RecentPathwayChanges";
	}

	function isExpensive() {
		# page_counter is not indexed
		return true;
	}
	function isSyndicated() { return false; }

	/**
	 * Show a drop down list to select a field for sorting.
	 */
	function getPageHeader( ) {
		global $wgRequest;
		$requestedSort = $wgRequest->getVal('sort');

		$self = $this->getTitle();

		$fields = array('Date','Title','User');
		$fieldEl = "";
		foreach( $fields as $field ) {
			$attribs = array( 'value' => $field );
			if( $field == $requestedSort )
				$attribs['selected'] = 'selected';
			$fieldEl .= Html::element( 'option', $attribs, $field );
		}
		# Form tag
		$out = Html::rawElement( 'form', array( 'method' => 'post', 'action' => $self->getLocalUrl() ),
			Html::element( 'label', array( 'for' => 'sort' ), 'Sort by:' ) . ' ' .
			Html::rawElement( 'select', array( 'name' => 'sort' ), $fieldEl ) .

			# Submit button and form bottom
			Html::element( 'input', array( 'type' => 'submit', 'value' => wfMessage( 'allpagessubmit' )->text() ) )
		);

		return $out;
	}

	function getQueryInfo() {
		return array(
			'fields' => array(
				"*",
				"'RecentPathwayChanges' as type",
				"rc_namespace as namespace",
				"rc_title as title",
				"UNIX_TIMESTAMP(rc_timestamp) as unix_time",
				"rc_timestamp as value"
			),
			'tables' => 'recentchanges',
			'query'  => array(
				'rc_namespace' => $this->namespace,
				'rc_bot'       => 0,
				'rc_minor'     => 0
			),
			"order" => $this->getOrder(),);
	}

	function getOrder() {
		global $wgRequest;
		$requestedSort = $wgRequest->getVal('sort');

		if ($requestedSort == 'Title'){
			return 'ORDER BY rc_title, rc_timestamp DESC';
		} elseif ($requestedSort == 'User'){
			return 'ORDER BY rc_user_text, rc_timestamp DESC';
		} else {
			return 'ORDER BY rc_timestamp DESC';
		}
	}

	function formatResult( $skin, $result ) {
		global $wgLang, $wgContLang;
		$userPage = Title::makeTitle( NS_USER, $result->rc_user_text );
		$name = $skin->link( $userPage, htmlspecialchars( $userPage->getText() ) );
		$date = date('d F Y', $result->unix_time);
		$comment = ($result->rc_comment ? $result->rc_comment : "no comment");
		$titleName = $result->title;
		try {
			$pathway = Pathway::newFromTitle($result->title);
			if(!$pathway->isReadable()) return null; //Skip private pathways
			$titleName = $pathway->getSpecies().":".$pathway->getName();
		} catch(Exception $e) {}
		$title = Title::makeTitle( $result->namespace, $titleName );
		$id = Title::makeTitle( $result->namespace, $result->title );

		$this->message['hist'] = wfMessage( 'hist' )->escaped();
		$histLink = $skin->linkKnown($id, $this->message['hist'], array(),
			array(
				'curid' => $result->rc_cur_id,
				'action' => 'history'));

		$this->message['diff'] = wfMessage( 'diff' )->escaped();
		if( $result->rc_type > 0 ) { //not an edit of an existing page
			$diffLink = $this->message['diff'];
		} else {
			$diffLink = "<a href='" . SITE_URL .
				"/index.php?title=Special:DiffAppletPage&old={$result->rc_last_oldid}&new={$result->rc_this_oldid}" .
				"&pwTitle={$id->getFullText()}'>diff</a>";
		}

		$text = $wgContLang->convert($result->rc_comment);
		$plink = $skin->linkKnown( $wgContLang->convert($id, $title->getBaseText()) );

		/* Not link to history for now, later on link to our own pathway history
		   $nl = wfMsgExt( 'nrevisions', array( 'parsemag', 'escape'),
		   $wgLang->formatNum( $result->value ) );
		   $nlink = $skin->linkKnown( $nt, $nl, 'action=history' );
		*/

		global $wgLang;
		return $wgLang->specialList("(".$diffLink.") . . ".$plink. ": <b>".$date."</b> by <b>".$name."</b>","<i>".$text."</i>");
	}
}
