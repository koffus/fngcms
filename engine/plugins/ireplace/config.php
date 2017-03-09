<?php

// Protect against hack attempts
if (!defined('NGCMS')) die ('HAL');

pluginsLoadConfig();
Lang::loadPlugin('ireplace', 'main', '', '', ':');

$cfg = array();
array_push($cfg, array('descr' => __('ireplace:descr')));
array_push($cfg, array('name' => 'area', 'title' => __('ireplace:area'), 'descr' => __('ireplace:area.descr'), 'type' => 'select', 'values' => array ( '' => __('ireplace:area.choose'), 'news' => __('ireplace:area.news'), 'static' => __('ireplace:area.static'), 'comments' => __('ireplace:area.comments'))));
array_push($cfg, array('name' => 'src', 'title' => __('ireplace:source'), 'type' => 'input', 'html_flags' => 'size=40', 'value' => ''));
array_push($cfg, array('name' => 'dest', 'title' => __('ireplace:destination'),'type' => 'input', 'html_flags' => 'size=40', 'value' => ''));

if ($_REQUEST['action'] == 'commit') {
	// Perform a replace
	$query = '';

	do {
		// Check src/dest values
		$src	= $_REQUEST['src'];
		$dest	= $_REQUEST['dest'];

		if (!mb_strlen($src, 'UTF-8') || !mb_strlen($dest, 'UTF-8')) {
			// No src/dest text
			msg(array('type' => 'danger', 'message' => __('ireplace:error.notext')));
			break;
		}

		// Check area
		switch ($_REQUEST['area']) {
			case 'news':
				$query = "update ".prefix."_news set content = replace(content, ".db_squote($src).", ".db_squote($dest).")";
				break;
			case 'static':
				$query = "update ".prefix."_static set content = replace(content, ".db_squote($src).", ".db_squote($dest).")";
				break;
			case 'comments':
				$query = "update ".prefix."_comments set text = replace(text, ".db_squote($src).", ".db_squote($dest).")";
				break;
		}

		if (!$query) {
			// No area selected
			msg(array('type' => 'danger', 'message' => __('ireplace:error.noarea')));
			break;
		}
	} while (0);

	// Check if we should make replacement
	if ($query) {
		// Yeah !!
		$result = $mysql->select($query);
		$count = $mysql->affected_rows($mysql->connect);
		if ($count) {
			msg(array('type' => 'info', 'message' => str_replace('{count}', $count, __('ireplace:info.done'))));
		} else {
			msg(array('type' => 'info', 'message' => __('ireplace:info.nochange')));
		}
	}
	print_commit_complete($plugin, $cfg);

} else {
	generate_config_page($plugin, $cfg);
}
