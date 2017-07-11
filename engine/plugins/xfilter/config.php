<?php

//
// Configuration file for plugin
//

// Protect against hack attempts
if (!defined('NGCMS')) die ('HAL');

// Load lang files
Lang::loadPlugin($plugin, 'config', '', ':');

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

$orderby = array(
	'id_desc' => __($plugin.':orderby_iddesc'), 
	'id_asc' => __($plugin.':orderby_idasc'), 
	'postdate_desc' => __($plugin.':orderby_postdatedesc'), 
	'postdate_asc' => __($plugin.':orderby_postdateasc'), 
	'title_desc' => __($plugin.':orderby_titledesc'), 
	'title_asc' => __($plugin.':orderby_titleasc'),
	);

// Fill configuration parameters
$cfg = array('description' => __($plugin.':description'));

$cfgX = array();
	array_push($cfgX, array(
		'name' => "{$currentVar}_skipcat", 
		'title' => __($plugin.':skipcat'), 
		'descr' => __($plugin.':skipcat#desc'), 
		'type' => 'input',
		'value' => pluginGetVariable($plugin, "{$currentVar}_skipcat"),
		));
	array_push($cfgX, array(
		'name' => "{$currentVar}_showAllCat",
		'type' => 'select',
		'title' => __($plugin.':showAllCat'),
		'values' => array(1 => __('yesa'), 0 => __('noa')), 
		'value' => pluginGetVariable($plugin, "{$currentVar}_showAllCat"),
		));
	array_push($cfgX, array(
		'name' => "{$currentVar}_order",
		'type' => 'select',
		'title' => __($plugin.':orderby_title'),
		'values' => $orderby,
		'value' => pluginGetVariable($plugin, "{$currentVar}_order"),
		));
	array_push($cfgX, array(
		'name' => "{$currentVar}_showNumber",
		'title' => __($plugin.':number_title'),
		'descr' => __($plugin.':number_title#desc'),
		'type' => 'input',
		'value' => intval(pluginGetVariable($plugin, "{$currentVar}_showNumber")) ? pluginGetVariable($plugin, "{$currentVar}_showNumber") : 10,
		));
array_push($cfg, array(
	'mode' => 'group', 
	'title' => __($plugin.':group'), 
	'entries' => $cfgX,
	));

$cfgX = array();
	array_push($cfgX, array(
		'name' => 'localSource',
		'title' => __('localSource'),
		'descr' => __('localSource#desc'),
		'type' => 'select',
		'values' => array('0' => __('localSource_0'), '1' => __('localSource_1'),),
		'value' => intval(pluginGetVariable($plugin, 'localSource')) ? intval(pluginGetVariable($plugin, 'localSource')) : '0',
		));
array_push($cfg, array(
	'mode' => 'group',
	'title' => __('group.source'),
	'entries' => $cfgX,
	));

// RUN
if (isset($_REQUEST['action']) and $_REQUEST['action'] == 'commit') {
	// If submit requested, do config save
	commit_plugin_config_changes($plugin, $cfg);
}

generate_config_page($plugin, $cfg);
