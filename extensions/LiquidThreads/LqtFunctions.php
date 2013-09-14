<?php
if (!defined('MEDIAWIKI'))
	die();

function efArrayDefault($name, $key, $default) {
	global $$name;
	if( isset($$name) && is_array($$name) && array_key_exists($key, $$name) ) {
		$foo = $$name;
		return $foo[$key];
	}
	else {
		return $default;
	}
}

/**
 * Initialize LiquidThreads user interface messages
 */
function wfLqtInitMsg() {
	wfLoadExtensionMessages( 'LiquidThreads' );
}

/**
 * Recreate the original associative array so that a new pair with the given key
 * and value is inserted before the given existing key. $original_array gets
 * modified in-place.
*/
function efInsertIntoAssoc( $new_key, $new_value, $before, &$original_array ) {
	$ordered = array();
	$i = 0;
	foreach($original_array as $key=>$value) {
		$ordered[$i] = array($key, $value);
		$i += 1;
	}
	$new_assoc = array();
	foreach($ordered as $pair) {
		if( $pair[0] == $before ) {
			$new_assoc[$new_key] = $new_value;
		}
		$new_assoc[$pair[0]] = $pair[1];
	}
	$original_array = $new_assoc;
}

function efVarDump($value) {
	global $wgOut;
	ob_start();
	var_dump($value);
	$tmp=ob_get_contents();
	ob_end_clean();
	$wgOut->addHTML(/*'<pre>' . htmlspecialchars($tmp,ENT_QUOTES) . '</pre>'*/ $tmp);
}

function efThreadTable($ts) {
	global $wgOut;
	$wgOut->addHTML('<table>');
	foreach($ts as $t)
		efThreadTableHelper($t, 0);
	$wgOut->addHTML('</table>');
}

function efThreadTableHelper($t, $indent) {
	global $wgOut;
	$wgOut->addHTML('<tr>');
	$wgOut->addHTML('<td>' . $indent);
	$wgOut->addHTML('<td>' . $t->id());
	$wgOut->addHTML('<td>' . $t->title()->getPrefixedText());
	$wgOut->addHTML('</tr>');
	foreach($t->subthreads() as $st)
		efThreadTableHelper($st, $indent + 1);
}

function wfLqtBeforeWatchlistHook( $options, $user, &$hook_sql ) {
	global $wgOut;

	$hook_sql = "AND page_namespace != " . NS_LQT_THREAD;

	$talkpage_messages = NewMessages::newUserMessages($user);
	$tn = count($talkpage_messages);

	$watch_messages = NewMessages::watchedThreadsForUser($user);
	$wn = count($watch_messages);

	if( $tn == 0 && $wn == 0 )
		return true;

	LqtView::addJSandCSS();
	$messages_url = SpecialPage::getPage('NewMessages')->getTitle()->getFullURL();
	$new_messages = wfMessage( 'lqt-new-messages' )->text();
	$wgOut->addHTML(<<< HTML
		<a href="$messages_url" class="lqt_watchlist_messages_notice">
			&#x2712; {$new_messages}
		</a>
HTML
	);

	return true;
}
