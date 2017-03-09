<?php

// Protect against hack attempts
if (!defined('NGCMS')) die ('HAL');

// Load lang file
Lang::loadPlugin('archive', 'main', '', '', ':');

//
// Show data block for archive plugin
// Params:
//  * maxnum - Max num entries for archive
//  * counter - Show counter in the entries
//  * tcounter - Show text counter in the entries
//  * template - Personal template for plugin
//  * cacheExpire - age of cache [in seconds]
function plugin_archive($params) {

	global $config, $mysql, $twig, $twigLoader, $CurrentHandler;

	$langMonths = Lang::$months;

	$maxnum = isset($params['maxnum']) ? $params['maxnum'] : pluginGetVariable('archive', 'maxnum');
	$counter = isset($params['counter']) ? $params['counter'] : pluginGetVariable('archive', 'counter');
	$tcounter = isset($params['tcounter']) ? $params['tcounter'] : pluginGetVariable('archive', 'tcounter');
	$overrideTemplateName = isset($params['template']) ? $params['template'] : false;
	$localsource = pluginGetVariable('archive', 'localsource');
	$cache = isset($params['cache']) ? $params['cache'] : pluginGetVariable('archive', 'cache');
	$cacheExpire = isset($params['cacheExpire']) ? $params['cacheExpire'] : pluginGetVariable('archive', 'cacheExpire');

	if ( ($maxnum < 1) or ($maxnum > 50) )
		$maxnum = 12;

	if ($overrideTemplateName) {
		$templateName = $overrideTemplateName;
	} else {
		$templateName = 'archive';
	}

	// Generate cache file name [ we should take into account SWITCHER plugin ]
	$cacheFileName = md5('archive' . $config['theme'] . $templateName . $config['default_lang']) . '.txt';
	if ($cache and $cacheExpire > 0) {
		$cacheData = cacheRetrieveFile($cacheFileName, $cacheExpire, 'archive');
		if ($cacheData != false) {
			// We got data from cache. Return it and stop
			return $cacheData;
		}
	}

	// Determine paths for all template files
	$tpath = locatePluginTemplates(array($templateName), 'archive', $localsource);

	// Load list
	foreach ($mysql->select("
		SELECT month(from_unixtime(postdate)) as month, year(from_unixtime(postdate)) as year, COUNT(id) AS cnt 
		FROM " . prefix . "_news 
		WHERE approve = '1' 
		GROUP BY year, month 
		ORDER BY year DESC, month DESC 
		limit " . $maxnum) as $row) {

		if ( checkLinkAvailable('news', 'by.month') ) {
			$month_link = generateLink('news', 'by.month', array('year' => $row['year'], 'month' => sprintf('%02u', $row['month'])));
		} else {
			$month_link = generateLink('core', 'plugin', array('plugin' => 'news', 'handler' => 'by.month'), array('year' => $row['year'], 'month' => sprintf('%02u', $row['month'])));
		}

		if ( $tcounter ) {
			$ctext = ' ' . Padeg($row['cnt'], __('archive:counter.case'));
		} else {
			$ctext = '';
		}

		$tVars['entries'][] = array(
			'link' => $month_link,
			'title' => $langMonths[$row['month'] - 1] . ' ' . $row['year'],
			'cnt' => $row['cnt'],
			'counter' => $counter,
			'ctext' => $ctext,
			);
	}

	$xt = $twig->loadTemplate($tpath[$templateName] . $templateName . '.tpl');
	$output = $xt->render($tVars);

	if ($cache and $cacheExpire > 0) {
		cacheStoreFile($cacheFileName, $output, 'archive');
	}

	return $output;

}

twigRegisterFunction('archive', 'show', plugin_archive);
