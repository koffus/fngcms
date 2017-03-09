<?php

// Protect against hack attempts
if (!defined('NGCMS')) die ('HAL');

//
// Configuration file for plugin
//

// Preload config file
pluginsLoadConfig();
Lang::loadPlugin($plugin, 'config', '', '', ':');
//Lang::loadPlugin($plugin, 'main', '', 'topusers');

// Fill configuration parameters
$cfg = array();
$cfgX = array();
//array_push($cfg, array('descr' => __('topusers_descr')));
array_push($cfg, array('descr' => __('top_active_users:description')));

$cfgX = array();
array_push($cfgX, array('name' => 'localsource', 'title' => __('top_active_users:localsource'), 'descr' => __('top_active_users:localsource#desc'), 'type' => 'select', 'values' => array ( '0' => 'Шаблон сайта', '1' => 'Плагин'), 'value' => intval(pluginGetVariable($plugin,'localsource'))));
array_push($cfg, array('mode' => 'group', 'title' => __('top_active_users:group.source'), 'entries' => $cfgX));

// RUN
if ($_REQUEST['action'] == 'commit') {
	// If submit requested, do config save
	commit_plugin_config_changes($plugin, $cfg);
	print_commit_complete($plugin, $cfg);
} else {
	generate_config_page($plugin, $cfg);
}

?>