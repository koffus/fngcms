<?php

//
// Configuration file for plugin
//

// Protect against hack attempts
if (!defined('NGCMS')) die ('HAL');

// Preload config file
pluginsLoadConfig();
Lang::loadPlugin($plugin, 'config', '', '', ':');

// Fill configuration parameters
$skList = array();
if ($skDir = opendir(extras_dir.'/rating/tpl/skins')) {
	while ($skFile = readdir($skDir)) {
		if (!preg_match('/^\./', $skFile)) {
			$skList[$skFile] = $skFile;
		}
	}
	closedir($skDir);
}

// Fill configuration parameters
$cfg = array('description' => __($plugin . ':description'));

$cfgX = array();
	array_push($cfgX, array(
		'name' => 'regonly',
		'title' => __($plugin . ':for_reg'),
		'descr' => __($plugin . ':for_reg#desc'),
		'type' => 'select',
		'values' => array('0' => __('noa'), '1' => __('yesa')),
		'value' => pluginGetVariable($plugin,'regonly'),
		));
array_push($cfg, array(
	'mode' => 'group',
	'title' => __($plugin . ':group.config'),
	'entries' => $cfgX,
	));

$cfgX = array();
	array_push($cfgX, array(
		'name' => 'localsource',
		'title' => __($plugin . ':localsource'),
		'descr' => __($plugin . ':localsource#desc'),
		'type' => 'select',
		'values' => array('0' => __($plugin . ':localsource_0'), '1' => __($plugin . ':localsource_1'),),
		'value' => intval(pluginGetVariable($plugin, 'localsource'))
		));
	array_push($cfgX, array(
		'name' => 'localskin',
		'title' => __($plugin . ':localskin'),
		'descr' => __($plugin . ':localskin#desc'),
		'type' => 'select',
		'values' => $skList,
		'value' => pluginGetVariable($plugin,'localskin') ? pluginGetVariable($plugin,'localskin') : 'basic',
		));
array_push($cfg, array(
	'mode' => 'group',
	'title' => __($plugin . ':group.source'),
	'entries' => $cfgX,
	));

// RUN
if ($_REQUEST['action'] == 'commit') {
	// If submit requested, do config save
	commit_plugin_config_changes($plugin, $cfg);
	print_commit_complete($plugin, $cfg);
} else {
	generate_config_page($plugin, $cfg);
}
