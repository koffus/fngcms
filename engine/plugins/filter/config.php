<?php

//
// Configuration file for plugin
//

// Protect against hack attempts
if (!defined('NGCMS')) die ('HAL');

// Preload config file
pluginsLoadConfig();

// Load lang files
Lang::loadPlugin($plugin, 'config', '', '', ':');

// Fill configuration parameters
$cfg = array('description' => __($plugin.':description'));

$cfgX = array();
	array_push($cfgX, array(
		'name' => 'replace',
		'title' => __($plugin.':replace'),
		'descr' => __($plugin.':replace#desc'),
		'type' => 'text',
		'html_flags' => 'rows="8"',
		'value' => pluginGetVariable('filter','replace'),
		));
	array_push($cfgX, array(
		'name' => 'block',
		'title' => __($plugin.':block'),
		'descr' => __($plugin.':block#desc'),
		'type' => 'text',
		'html_flags' => 'rows="8"',
		'value' => pluginGetVariable('filter','block'),
		));
array_push($cfg, array(
	'mode' => 'group',
	'title' => __('group.config'),
	'entries' => $cfgX,
	));

// RUN 
if ($_REQUEST['action'] == 'commit') {
	// If submit requested, do config save
	commit_plugin_config_changes('filter', $cfg);
	print_commit_complete($plugin, $cfg);
} else {
	generate_config_page('filter', $cfg);
}
