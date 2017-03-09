<?php

// Protect against hack attempts
if (!defined('NGCMS')) die ('HAL');

//
// Configuration file for plugin
//

// Preload config file
pluginsLoadConfig();

Lang::loadPlugin('wpinger', 'config', '', '', ':');

// Fill configuration parameters
$cfg = array();
$cfgX = array();
array_push($cfg, array('descr' => __('wpinger:desc')));
array_push($cfg, array('name' => 'proxy', 'title' => __('wpinger:proxy'), 'descr' => __('wpinger:proxy#desc'), 'type' => 'select', 'values' => array ('0' => __('noa'), '1' => __('yesa')), 'value' => pluginGetVariable('wpinger', 'proxy')));
array_push($cfg, array('name' => 'urls', 'title' => __('wpinger:urls'), 'descr' => __('wpinger:urls#desc'), 'type' => 'text', 'html_flags' => 'rows=4 cols=60', 'value' => pluginGetVariable('wpinger', 'urls')));
#array_push($cfg, array('mode' => 'group', 'title' => '<b>'.__('jchat:main').'</b>', 'entries' => $cfgX));

// RUN
if ($_REQUEST['action'] == 'commit') {
	// If submit requested, do config save
	commit_plugin_config_changes('wpinger', $cfg);
	print_commit_complete($plugin, $cfg);
} else {
	generate_config_page('wpinger', $cfg);
}