<?php

//
// Configuration file for plugin
//

// Protect against hack attempts
if (!defined('NGCMS')) die ('HAL');

// Load lang files
Lang::loadPlugin($plugin, 'config', '', '', ':');

// Prepare configuration parameters
$skList = array();
if ($skDir = opendir(extras_dir.'/'.$plugin.'/tpl/skins')) {
	while ($skFile = readdir($skDir)) {
		if (!preg_match('/^\./', $skFile)) {
			$skList[$skFile] = $skFile;
		}
	}
	closedir($skDir);
}

// Fill configuration parameters
$cfg = array('description' => __($plugin.':description'));

$cfgX = array();
	array_push($cfgX, array(
		'name' => 'limit',
		'title' => __($plugin.':sidebar.limit'),
		'descr' => __($plugin.':sidebar.limit#desc'),
		'type' => 'input',
		'value' => pluginGetVariable($plugin, 'limit'),
		));
	array_push($cfgX, array(
		'name' => 'orderby',
		'title' => __($plugin.':ppage.orderby'),
		'descr' => __($plugin.':ppage.orderby#desc'),
		'type' => 'select',
		'values' => array (
			'0' => __($plugin.':ppage.order.rand'),
			'1' => __($plugin.':ppage.order.tag_asc'),
			'2' => __($plugin.':ppage.order.tag_desc'),
			'3' => __($plugin.':ppage.order.pop_asc'),
			'4' => __($plugin.':ppage.order.pop_desc'),
			),
		'value' => intval(pluginGetVariable($plugin, 'orderby')) ? intval(pluginGetVariable($plugin, 'orderby')) : 4,
		));
	array_push($cfgX, array(
		'name' => 'catfilter',
		'title' => __($plugin.':sidebar.catfilter'),
		'descr' => __($plugin.':sidebar.catfilter#desc'),
		'type' => 'select',
		'values' => array (
			'0' => __($plugin.':sidebar.filter.all'),
			'1' => __($plugin.':sidebar.filter.category'),
			),
		'value' => pluginGetVariable($plugin, 'catfilter'),
		));
	array_push($cfgX, array(
		'name' => 'newsfilter',
		'title' => __($plugin.':sidebar.newsfilter'),
		'descr' => __($plugin.':sidebar.newsfilter#desc'),
		'type' => 'select',
		'values' => array (
			'0' => __($plugin.':sidebar.filter.all'),
			'1' => __($plugin.':sidebar.filter.news'),
			),
		'value' => pluginGetVariable($plugin, 'newsfilter'),
		));
	array_push($cfgX, array(
		'name' => 'age',
		'title' => __($plugin.':sidebar.age'),
		'descr' => __($plugin.':sidebar.age#desc'),
		'type' => 'input',
		'value' => pluginGetVariable($plugin, 'age'),
		));
array_push($cfg, array(
	'mode' => 'group',
	'title' => __($plugin.':block.sidebar'),
	'entries' => $cfgX,
	));

$cfgX = array();
	array_push($cfgX, array(
		'name' => 'ppage_orderby',
		'title' => __($plugin.':ppage.orderby'),
		'descr' => __($plugin.':ppage.orderby#desc'),
		'type' => 'select',
		'values' => array (
			'0' => __($plugin.':ppage.order.rand'),
			'1' => __($plugin.':ppage.order.tag_asc'),
			'2' => __($plugin.':ppage.order.tag_desc'),
			'3' => __($plugin.':ppage.order.pop_asc'),
			'4' => __($plugin.':ppage.order.pop_desc'),
			),
		'value' => intval(pluginGetVariable($plugin, 'ppage_orderby')) ? intval(pluginGetVariable($plugin, 'ppage_orderby')) : 4,
		));
	array_push($cfgX, array(
		'name' => 'ppage_paginator',
		'title' => __($plugin.':ppage.paginator'),
		'descr' => __($plugin.':ppage.paginator#desc'),
		'type' => 'select',
		'values' => array('1' => __('yesa'), '0' => __('noa')),
		'value' => (pluginGetVariable($plugin, 'ppage_paginator')) ? pluginGetVariable($plugin, 'ppage_paginator') : 0,
		));
	array_push($cfgX, array(
		'name' => 'ppage_limit',
		'title' => __($plugin.':ppage.limit'),
		'descr' => __($plugin.':ppage.limit#desc'),
		'type' => 'input',
		'value' => pluginGetVariable($plugin, 'ppage_limit'),
		));
array_push($cfg, array(
	'mode' => 'group',
	'title' => __($plugin.':block.ppage'),
	'entries' => $cfgX,
	));

$cfgX = array();
	array_push($cfgX, array(
		'name' => 'tpage_paginator',
		'title' => __($plugin.':tpage.paginator'),
		'descr' => __($plugin.':tpage.paginator#desc'),
		'type' => 'select',
		'values' => array('1' => __('yesa'), '0' => __('noa')),
		'value' => (pluginGetVariable($plugin, 'tpage_paginator')) ? pluginGetVariable($plugin, 'tpage_paginator') : 0,
		));
	array_push($cfgX, array(
		'name' => 'tpage_limit',
		'title' => __($plugin.':tpage.limit'),
		'descr' => __($plugin.':tpage.limit#desc'),
		'type' => 'input',
		'value' => (pluginGetVariable($plugin, 'tpage_limit')) ? pluginGetVariable($plugin, 'tpage_limit') : 0,
		));
array_push($cfg, array(
	'mode' => 'group',
	'title' => __($plugin.':block.tpage'),
	'entries' => $cfgX,
	));

$cfgX = array();
	array_push($cfgX, array(
		'name' => 'manualstyle',
		'title' => __($plugin.':manualstyle'),
		'descr' => __($plugin.':manualstyle#desc'),
		'type' => 'select',
		'values' => array('1' => __('yesa'), '0' => __('noa')),
		'value' => (pluginGetVariable($plugin, 'manualstyle')) ? pluginGetVariable($plugin, 'manualstyle') : 0,
		));
	array_push($cfgX, array(
		'name' => 'styles',
		'title' => __($plugin.':styles'),
		'descr' => __($plugin.':styles#desc'),
		'type' => 'input',
		'value' => pluginGetVariable('tags','styles'),
		));
	array_push($cfgX, array(
		'name' => 'styles_weight',
		'title' => __($plugin.':styles.weight'),
		'descr' => __($plugin.':styles.weight#desc'),
		'type' => 'text',
		'html_flags' => 'rows="4"',
		'value' => pluginGetVariable('tags','styles_weight'),
		));
array_push($cfg, array(
	'mode' => 'group',
	'title' => __($plugin.':block.stylecontrol'),
	'entries' => $cfgX,
	));

$cfgX = array();
	$cfgX = array();
	array_push($cfgX, array(
		'name' => 'localSource',
		'title' => __('localSource'),
		'descr' => __('localSource#desc'),
		'type' => 'select',
		'values' => array('0' => __('localSource_0'), '1' => __('localSource_1'),),
		'value' => intval(pluginGetVariable($plugin, 'localSource')) ? intval(pluginGetVariable($plugin, 'localSource')) : 0,
		));
	array_push($cfgX, array(
		'name' => 'localSkin',
		'title' => __('localSkin'),
		'descr' => __('localSkin#desc'),
		'type' => 'select',
		'values' => $skList,
		'value' => pluginGetVariable($plugin,'localSkin') ? pluginGetVariable($plugin,'localSkin') : 'basic',
		));
	array_push($cfgX, array('name' => 'cloud3d', 'title' => __($plugin.':cloud3d'), 'descr' => __($plugin.':cloud3d#desc'), 'type' => 'select', 'values' => array( '0' => __('noa'), '1' => __('yesa') ), 'value' => pluginGetVariable('tags','cloud3d')));
	array_push($cfgX, array('name' => 'show_always', 'title' => __($plugin.':show_always'), 'descr' => __($plugin.':show_always#desc'), 'type' => 'select', 'values' => array( '0' => __('noa'), '1' => __('yesa') ), 'value' => pluginGetVariable('tags','show_always')));
	array_push($cfgX, array(
		'name' => 'extends',
		'title' => __('localExtends'),
		'descr' => __('localExtends#desc'),
		'type' => 'select',
		'values' => array (
			'main' => __('extends_main'),
			'additional' => __('extends_additional'),
			'owner' => __('extends_owner'),
			/*'js' => __('extends_js'),
			'css' => __('extends_css'),*/
			),
		'value' => pluginGetVariable($plugin,'extends') ? pluginGetVariable($plugin,'extends') : 'owner',
		));
array_push($cfg, array(
	'mode' => 'group',
	'title' => __('group.source'),
	'entries' => $cfgX,
	));

$cfgX = array();
	array_push($cfgX, array(
		'name' => 'cache',
		'title' => __('cache'),
		'descr' => __('cache#desc'),
		'type' => 'select',
		'values' => array('1' => __('yesa'), '0' => __('noa')),
		'value' => intval(pluginGetVariable($plugin, 'cache')) ? intval(pluginGetVariable($plugin, 'cache')) : 1,
		));
	array_push($cfgX, array(
		'name' => 'cacheExpire',
		'title' => __('cacheExpire'),
		'descr' => __('cacheExpire#desc'),
		'type' => 'input',
		'value' => intval(pluginGetVariable($plugin, 'cacheExpire')) ? intval(pluginGetVariable($plugin, 'cacheExpire')) : 60,
		));
array_push($cfg, array(
	'mode' => 'group',
	'title' => __('group.cache'),
	'entries' => $cfgX,
	));

$cfgX = array();
array_push($cfgX, array(
	'name' => 'rebuild', 
	'title' => __('rebuild'),
	'descr' => __('rebuild#desc'),
	'type' => 'select', 
	'value' => 0, 
	'values' => array('1' => __('yesa'), '0' => __('noa')),
	'nosave' => 1
	));
array_push($cfg, array(
	'mode' => 'group',
	'title' => __('group.rebuild'),
	'entries' => $cfgX,
	));

// RUN
if ($_REQUEST['action'] == 'commit') {
	// Rebuild index table
	if ($_REQUEST['rebuild']) {
		// * Truncate index
		$mysql->query("truncate table ".prefix."_tags_index");
		// * LOCK
		$mysql->query("lock tables ".prefix."_tags write, ".prefix."_tags_index write, ".prefix."_tags_index i write, ".prefix."_news read, ".prefix."_news n read");
		// * Zero counters
		$mysql->query("update ".prefix."_tags set posts = 0");
		// * Scan news [ FILL TAGS ARRAY IN MEMORY ] [ FILL NEWS with tags ARRAY IN MEMORY ]
		$tags = array();
		$tagIndexSQL = $mysql->select("select id, tags from ".prefix."_news where (tags is not NULL) and (tags <> '') and (approve = 1)");
		foreach ($tagIndexSQL as $row) {
			$ntags = mb_split(',', trim($row['tags']));
			foreach ($ntags as $ntag) {
				if ( $ntag = trim($ntag) )
					$tags[$ntag] = $tags[$ntag] + 1;
			}
		}

		// * Process counters
		foreach ($tags as $tag => $cnt) {
			$mysql->query("insert into ".prefix."_tags (tag, posts) values (".db_squote($tag).",".intval($cnt).") on duplicate key update posts = posts + ".intval($cnt));
		}

		// * Regenerate counters
		foreach ($tagIndexSQL as $row) {
			$ntags = mb_split(',', trim($row['tags']));
			$ntagsQ = array();
			foreach ($ntags as $tag) {
				if ( $tag = trim($tag) )
					$ntagsQ[] = db_squote($tag);
			}
			if (sizeof($ntagsQ))
				$mysql->query("insert into ".prefix."_tags_index (newsID, tagID) select ".db_squote($row['id']).", id from ".prefix."_tags where tag in (".join(",",$ntagsQ).")");
		}

		// * DELETE unused tags
		$mysql->query("delete from ".prefix."_tags where posts = 0");

		$mysql->query("unlock tables");

		msg(array('message' => __('rebuild.done')));
		generate_config_page($plugin, $cfg);
	} else {
		// If submit requested, do config save
		commit_plugin_config_changes($plugin, $cfg);
		print_commit_complete($plugin, $cfg);
	}
} else {
	generate_config_page($plugin, $cfg);
}
