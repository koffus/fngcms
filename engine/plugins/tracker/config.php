<?php

// Protect against hack attempts
if (!defined('NGCMS')) die ('HAL');

//
// Configuration file for plugin
//

// Preload config file
pluginsLoadConfig();

Lang::loadPlugin('tracker', 'config', '', '', ':');

// Fill configuration parameters
$cfg = array();
$cfgX = array();
array_push($cfgX, array('descr' => __('tracker:desc')));

array_push($cfgX, array('name' => 'storrent', 'title' => __('tracker:support.torrent'), 'descr' => __('tracker:support.torrent#desc'), 'type' => 'select', 'values' => array ('0' => __('noa'), '1' => __('yesa')), 'value' => pluginGetVariable($plugin,'storrent')));
array_push($cfgX, array('name' => 'smagnet', 'title' => __('tracker:support.magnet'), 'descr' => __('tracker:support.magnet#desc'), 'type' => 'select', 'values' => array ('0' => __('noa'), '1' => __('yesa')), 'value' => pluginGetVariable($plugin,'smagnet')));
array_push($cfgX, array('name' => 'tracker', 'title' => __('tracker:tracker'), 'descr' => str_replace('{tracker_url}', generatePluginLink('tracker', 'announce', array(), array(), false, true), __('tracker:tracker#desc')), 'type' => 'select', 'values' => array ('0' => __('noa'), '1' => __('yesa')), 'value' => pluginGetVariable($plugin,'tracker')));
array_push($cfg, array('mode' => 'group', 'title' => '<b>'.__('tracker:main').'</b>', 'entries' => $cfgX));

// RUN
if ($_REQUEST['action'] == 'commit') {
	// If submit requested, do config save
	commit_plugin_config_changes($plugin, $cfg);
	print_commit_complete($plugin, $cfg);
} else {
	generate_config_page($plugin, $cfg);
}