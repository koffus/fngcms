<?php

// Protect against hack attempts
if (!defined('NGCMS')) die ('HAL');

//
// Configuration file for plugin
//

pluginsLoadConfig();
Lang::loadPlugin('tags', 'config', '', '', ':');

// Fill configuration parameters
$skList = array();
if ($skDir = opendir(extras_dir.'/tags/tpl/skins')) {
	while ($skFile = readdir($skDir)) {
		if (!preg_match('/^\./', $skFile)) {
			$skList[$skFile] = $skFile;
		}
	}
	closedir($skDir);
}

$cfg = array();
//array_push($cfg, array('descr' => __('tags:descr')));
array_push($cfg, array('name' => 'rebuild', 'title' => __('tags:cmd.rebuild'), 'descr' => __('tags:cmd.rebuild#desc'), 'type' => 'select', 'value' => 0, 'values' => array ( 0 => __('noa'), 1 => __('yesa')), 'nosave' => 1));

$cfgX = array();
array_push($cfgX, array('name' => 'limit', 'title' => __('tags:sidebar.limit'), 'descr' => __('tags:sidebar.limit#desc'), 'type' => 'input', 'html_flags' => 'size="4"', 'value' => pluginGetVariable($plugin, 'limit')));
array_push($cfgX, array('name' => 'orderby', 'title' => __('tags:ppage.orderby'), 'descr' => __('tags:ppage.orderby#desc'), 'type' => 'select', 'values' => array ( '0' => __('tags:ppage.order.rand'), '1' => __('tags:ppage.order.tag_asc'), '2' => __('tags:ppage.order.tag_desc'), '3' => __('tags:ppage.order.pop_asc'), '4' => __('tags:ppage.order.pop_desc')), 'value' => pluginGetVariable($plugin, 'orderby')));
array_push($cfgX, array('name' => 'catfilter', 'title' => __('tags:sidebar.catfilter'), 'descr' => __('tags:sidebar.catfilter#desc'), 'type' => 'select', 'values' => array ( '0' => __('tags:sidebar.filter.all'), '1' => __('tags:sidebar.filter.category')), 'value' => pluginGetVariable($plugin, 'catfilter')));
array_push($cfgX, array('name' => 'newsfilter', 'title' => __('tags:sidebar.newsfilter'), 'descr' => __('tags:sidebar.newsfilter#desc'), 'type' => 'select', 'values' => array ( '0' => __('tags:sidebar.filter.all'), '1' => __('tags:sidebar.filter.news')), 'value' => pluginGetVariable($plugin, 'newsfilter')));
array_push($cfgX, array('name' => 'age', 'title' => __('tags:sidebar.age'), 'descr' => __('tags:sidebar.age#desc'), 'type' => 'input', 'html_flags' => 'size="4"', 'value' => pluginGetVariable($plugin, 'age')));
array_push($cfg, array('mode' => 'group', 'title' => __('tags:block.sidebar'), 'entries' => $cfgX));

$cfgX = array();
array_push($cfgX, array('name' => 'ppage_orderby', 'title' => __('tags:ppage.orderby'), 'descr' => __('tags:ppage.orderby#desc'), 'type' => 'select', 'values' => array ( '0' => __('tags:ppage.order.rand'), '1' => __('tags:ppage.order.tag_asc'), '2' => __('tags:ppage.order.tag_desc'), '3' => __('tags:ppage.order.pop_asc'), '4' => __('tags:ppage.order.pop_desc')), 'value' => pluginGetVariable($plugin, 'ppage_orderby')));
array_push($cfgX, array('name' => 'ppage_paginator', 'title' => __('tags:ppage.paginator'), 'descr' => __('tags:ppage.paginator#desc'), 'type' => 'select', 'values' => array ( '1' => __('yesa'), '0' => __('noa')), 'value' => (pluginGetVariable($plugin, 'ppage_paginator'))?pluginGetVariable($plugin, 'ppage_paginator'):0));
array_push($cfgX, array('name' => 'ppage_limit', 'title' => __('tags:ppage.limit'), 'descr' => __('tags:ppage.limit#desc'), 'type' => 'input', 'html_flags' => 'size="4"', 'value' => pluginGetVariable($plugin, 'ppage_limit')));
array_push($cfg, array('mode' => 'group', 'title' => __('tags:block.ppage'), 'entries' => $cfgX));

$cfgX = array();
array_push($cfgX, array('name' => 'tpage_paginator', 'title' => __('tags:tpage.paginator'), 'descr' => __('tags:tpage.paginator#desc'), 'type' => 'select', 'values' => array ( '1' => __('yesa'), '0' => __('noa')), 'value' => (pluginGetVariable($plugin, 'tpage_paginator'))?pluginGetVariable($plugin, 'tpage_paginator'):0));
array_push($cfgX, array('name' => 'tpage_limit', 'title' => __('tags:tpage.limit'), 'descr' => __('tags:tpage.limit#desc'), 'type' => 'input', 'value' => (pluginGetVariable($plugin, 'tpage_limit'))?pluginGetVariable($plugin, 'tpage_limit'):0));
array_push($cfg, array('mode' => 'group', 'title' => __('tags:block.tpage'), 'entries' => $cfgX));

$cfgX = array();
array_push($cfgX, array('name' => 'manualstyle', 'title' => __('tags:manualstyle'), 'descr' => __('tags:manualstyle#desc'), 'type' => 'select', 'values' => array ( '1' => __('yesa'), '0' => __('noa')), 'value' => (pluginGetVariable($plugin, 'manualstyle'))?pluginGetVariable($plugin, 'manualstyle'):0));
array_push($cfgX, array('name' => 'styles', 'title' => __('tags:styles'), 'descr' => __('tags:styles#desc'), 'type' => 'input', 'html_flags' => 'size=70', 'value' => pluginGetVariable('tags','styles')));
array_push($cfgX, array('name' => 'styles_weight', 'title' => __('tags:styles.weight'), 'descr' => __('tags:styles.weight#desc'), 'type' => 'text', 'html_flags' => 'cols=65 rows=4', 'value' => pluginGetVariable('tags','styles_weight')));
array_push($cfg, array('mode' => 'group', 'title' => __('tags:block.stylecontrol'), 'entries' => $cfgX));

$cfgX = array();
array_push($cfgX, array('name' => 'localsource', 'title' => __('tags:localsource'), 'descr' => __('tags:localsource#desc'), 'type' => 'select', 'values' => array ( '0' => __('tags:lsrc_site'), '1' => __('tags:lsrc_plugin')), 'value' => intval(pluginGetVariable($plugin,'localsource'))));
array_push($cfgX, array('name' => 'skin', 'title' => __('tags:skin'), 'descr' => __('tags:skin#desc'), 'type' => 'select', 'values' => $skList, 'value' => pluginGetVariable('tags','skin')));
array_push($cfgX, array('name' => 'cloud3d', 'title' => __('tags:cloud3d'), 'descr' => __('tags:cloud3d#desc'), 'type' => 'select', 'values' => array( '0' => __('noa'), '1' => __('yesa') ), 'value' => pluginGetVariable('tags','cloud3d')));
array_push($cfgX, array('name' => 'show_always', 'title' => __('tags:show_always'), 'descr' => __('tags:show_always#desc'), 'type' => 'select', 'values' => array( '0' => __('noa'), '1' => __('yesa') ), 'value' => pluginGetVariable('tags','show_always')));
array_push($cfg, array('mode' => 'group', 'title' => __('tags:block.display'), 'entries' => $cfgX));

$cfgX = array();
array_push($cfgX, array('name' => 'cache', 'title' => __('tags:cache.use'), 'descr' => __('tags:cache.use#desc'), 'type' => 'select', 'values' => array ( '1' => __('yesa'), '0' => __('noa')), 'value' => intval(pluginGetVariable($plugin,'cache'))));
array_push($cfgX, array('name' => 'cacheExpire', 'title' => __('tags:cache.expire'), 'descr' => __('tags:cache.expire#desc'), 'type' => 'input', 'value' => intval(pluginGetVariable($plugin,'cacheExpire'))?pluginGetVariable($plugin,'cacheExpire'):'60'));
array_push($cfg, array('mode' => 'group', 'title' => __('tags:block.cache'), 'entries' => $cfgX));


if (!$_REQUEST['action']) {
	generate_config_page($plugin, $cfg);
}
elseif ($_REQUEST['action'] == 'commit') {
	if ($_REQUEST['rebuild']) {
		// Rebuild index table
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
			$ntags = preg_split("/, */", trim($row['tags']));
			foreach ($ntags as $ntag) {
				$ntag = trim($ntag);
				if (!mb_strlen($tag, 'UTF-8'))
					continue;
				$tags[$ntag] = $tags[$ntag] + 1;
			}
		}

		// * Process counters
		foreach ($tags as $tag => $cnt) {
			$mysql->query("insert into ".prefix."_tags (tag, posts) values (".db_squote($tag).",".intval($cnt).") on duplicate key update posts = posts + ".intval($cnt));
		}

		// * Regenerate counters
		foreach ($tagIndexSQL as $row) {
			$ntags = preg_split("/, */", trim($row['tags']));
			$ntagsQ = array();
			foreach ($ntags as $tag) {
				$tag = trim($tag);
				if (!mb_strlen($tag, 'UTF-8'))
					continue;
				$ntagsQ[] = db_squote($tag);
			}
			if (sizeof($ntagsQ))
				$mysql->query("insert into ".prefix."_tags_index (newsID, tagID) select ".db_squote($row['id']).", id from ".prefix."_tags where tag in (".join(",",$ntagsQ).")");
		}

		// * DELETE unused tags
		$mysql->query("delete from ".prefix."_tags where posts = 0");

		$mysql->query("unlock tables");
		print __('tags:cmd.rebuild.done').'<br/>';
	}
	commit_plugin_config_changes($plugin, $cfg);
	print_commit_complete($plugin, $cfg);
}