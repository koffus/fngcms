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
$cfg = array('description' => __($plugin.':description'));

$cfgX = array();
	array_push($cfgX, array(
		'name' => 'block_full_path',
		'title' => __($plugin.':block_full_path'),
		//'descr' => __($plugin.':block_full_path#desc'),
		'type' => 'select',
		'values' => array ('0' => __('noa'), '1' => __('yesa')),
		'value' => intval(pluginGetVariable($plugin, 'block_full_path')),
		));

array_push($cfg, array(
	'mode' => 'group',
	'title' => __($plugin.':group.config'),
	'entries' => $cfgX,
	));

$cfgX = array();
	array_push($cfgX, array(
		'name' => 'template_source',
		'title' => __($plugin.':template_source_title'),
		'descr' => __($plugin.':template_source_title#desc'),
		'type' => 'select',
		'values' => array ('0' => __($plugin.':template_source_site'), '1' => __($plugin.':template_source_plugin')),
		'value' => intval(pluginGetVariable($plugin, 'template_source')),
		));
array_push($cfg, array(
	'mode' => 'group',
	'title' => __($plugin.':template_source'),
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
