<?php

// Protect against hack attempts
if (!defined('NGCMS')) die ('HAL');

registerActionHandler('index', 'plugin_favorites');

function plugin_favorites() {
	global $config, $mysql, $tpl, $template;

	$counter = intval(pluginGetVariable('favorites','counter'));
	$number = intval(pluginGetVariable('favorites','number'));
	$maxlength = intval(pluginGetVariable('favorites','maxlength'));

	// Generate cache file name [ we should take into account SWITCHER plugin ]
	$cacheFileName = md5('favorites'.$config['theme'].$config['default_lang'].$year.$month).'.txt';

	if (pluginGetVariable('favorites','cache')) {
		$cacheData = cacheRetrieveFile($cacheFileName, pluginGetVariable('favorites','cacheExpire'), 'favorites');
		if ($cacheData != false) {
			// We got data from cache. Return it and stop
			$template['vars']['plugin_favorites'] = $cacheData;
			return;
		}
	}

	if (!$number)		{ $number = 10; }
	if (!$maxlength)	{ $maxlength = 100; }

	// Determine paths for all template files
	$tpath = locatePluginTemplates(array('entries', 'favorites'), 'favorites', pluginGetVariable('favorites', 'localsource'));

	foreach ($mysql->select("select alt_name, postdate, title, views, catid from ".prefix."_news where favorite = '1' and approve = '1' limit 0,$number") as $row) {
		$tvars['vars'] = array(
			'link' => newsGenerateLink($row),
			'views' => ($counter) ? ' [ '.$row['views'].' ]' : ''
		);
		if (mb_strlen($row['title'], 'UTF-8') > $maxlength) {
			$tvars['vars']['title'] = substr(secure_html($row['title']), 0, $maxlength)."...";
		} else {
			$tvars['vars']['title'] = secure_html($row['title']);
		}

		$tpl -> template('entries', $tpath['entries']);
		$tpl -> vars('entries', $tvars);
		$result .= $tpl -> show('entries');
	}

	unset($tvars);
	$tvars['vars'] = array ( 'tpl_url' => tpl_url, 'favourite' => $result);

	$tpl -> template('favorites', $tpath['favorites']);
	$tpl -> vars('favorites', $tvars);

	$output = $tpl -> show('favorites');
	$template['vars']['plugin_favorites'] = $output;

	if (pluginGetVariable('favorites','cache')) {
		cacheStoreFile($cacheFileName, $output, 'favorites');
	}
}