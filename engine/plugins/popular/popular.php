<?php

// Protect against hack attempts
if (!defined('NGCMS')) die ('HAL');

registerActionHandler('index', 'plugin_popular');

function plugin_popular() {
	global $config, $mysql, $tpl, $template, $PFILTERS;

	$counter = intval(pluginGetVariable('popular','counter'));
	$number = intval(pluginGetVariable('popular','number'));
	$maxlength = intval(pluginGetVariable('popular','maxlength'));

	// Generate cache file name [ we should take into account SWITCHER plugin ]
	$cacheFileName = md5('popular'.$config['theme'].$config['default_lang']).'.txt';

	if (pluginGetVariable('popular','cache')) {
		$cacheData = cacheRetrieveFile($cacheFileName, pluginGetVariable('popular','cacheExpire'), 'popular');
		if ($cacheData != false) {
			// We got data from cache. Return it and stop
			$template['vars']['plugin_popular'] = $cacheData;
			return;
		}
	}
	
	if (!$number)		{ $number = 10; }
	if (!$maxlength)	{ $maxlength = 100; }

	// Determine paths for all template files
	$tpath = locatePluginTemplates(array('entries', 'popular'), 'popular', pluginGetVariable('popular', 'localsource'));

	$query = "select id, alt_name, postdate, title, views, catid from ".prefix."_news where approve = '1' order by views desc limit ".$number;
	if (pluginGetVariable('popular', 'pcall')) {
		$callingParams['plugin'] = 'lastnews';
		switch (intval(pluginGetVariable('lastnews', 'pcall_mode'))) {
			case 1: $callingParams['style'] = 'short';
					break;
			case 2: $callingParams['style'] = 'full';
					break;
			default: $callingParams['style'] = 'export';
		}

		// Preload plugins
		loadActionHandlers('news:show');
		loadActionHandlers('news:show:one');

		$query = "select * from ".prefix."_news where approve = '1' order by views desc limit ".$number;
	}

	$result = '';
	foreach ($mysql->select($query) as $row) {
		// Execute filters [ if requested ]
		if (pluginGetVariable('popular', 'pcall') && is_array($PFILTERS['news']))
				foreach ($PFILTERS['news'] as $k => $v) { $v->showNewsPre($row['id'], $row, $callingParams); }

		$tvars['vars'] = array(
			'link'		=>	newsGenerateLink($row),
			'views'		=>	($counter) ? ' [ '.$row['views'].' ]' : ''
		);
		if (mb_strlen($row['title'], 'UTF-8') > $maxlength) {
			$tvars['vars']['title'] = substr(secure_html($row['title']), 0, $maxlength)."...";
		} else {
			$tvars['vars']['title'] = secure_html($row['title']);
		}

		// Execute filters [ if requested ]
		if (pluginGetVariable('popular', 'pcall') && is_array($PFILTERS['news']))
			foreach ($PFILTERS['news'] as $k => $v) { $v->showNews($row['id'], $row, $tvars, $callingParams); }

		$tpl -> template('entries', $tpath['entries']);
		$tpl -> vars('entries', $tvars);
		$result .= $tpl -> show('entries');
	}

	unset($tvars);
	$tvars['vars'] = array ( 'tpl_url' => tpl_url, 'popular' => $result);

	$tpl -> template('popular', $tpath['popular']);
	$tpl -> vars('popular', $tvars);

	$output = $tpl -> show('popular');
	$template['vars']['plugin_popular'] = $output;

	if (pluginGetVariable('popular','cache')) {
		cacheStoreFile($cacheFileName, $output, 'popular');
	}
}
