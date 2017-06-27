<?php

//
// Configuration file for plugin
//

// Protect against hack attempts
if (!defined('NGCMS')) die ('HAL');

// Load lang files
Lang::loadPlugin($plugin, 'config', '', '', ':');

// Fill configuration parameters
$cfg = array('description' => __($plugin.':description'));

$cfgX = array();
	array_push($cfgX, array(
		'name' => 'proxy',
		'title' => __('wpinger:proxy'),
		'descr' => __('wpinger:proxy#desc'),
		'type' => 'select',
		'values' => array ('0' => __('noa'), '1' => __('yesa')),
		'value' => pluginGetVariable('wpinger', 'proxy'),
		));
	array_push($cfgX, array(
		'name' => 'urls',
		'title' => __('wpinger:urls'),
		'descr' => __('wpinger:urls#desc'),
		'type' => 'text',
		'html_flags' => 'rows="8"',
		'value' => pluginGetVariable('wpinger', 'urls'),
		));
array_push($cfg, array(
	'mode' => 'group',
	'title' => __('group.config'),
	'entries' => $cfgX,
	));

// RUN
if ($_REQUEST['action'] == 'commit') {
	// If submit requested, do config save
	commit_plugin_config_changes('wpinger', $cfg);
	print_commit_complete($plugin, $cfg);
} else {
	generate_config_page('wpinger', $cfg);
}