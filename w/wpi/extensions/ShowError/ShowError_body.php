<?php
class ShowError extends SpecialPage {
	function ShowError() {
		SpecialPage::SpecialPage("ShowError");
	}

	function execute($par) {
		global $wgOut, $wgUser, $wgLang;
		$this->setHeaders();
		$error = htmlentities($_REQUEST['error']);
		$wgOut->addWikiText("<pre><nowiki>$error</nowiki></pre>");
	}

}
