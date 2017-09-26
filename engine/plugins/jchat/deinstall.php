<?php

/*
 * Configuration file for plugin
 */

// Protect against hack attempts
if (!defined('BBCMS')) die ('HAL');

// Load lang files
Lang::loadPlugin($plugin, 'admin', '', ':');

$db_update = array(
	array(
		'table' => 'jchat',
		'action' => 'drop',
	),
	array(
		'table' => 'jchat_events',
		'action' => 'drop',
	),
);

// RUN
if ('commit' == $action) {
	if (fixdb_plugin_install($plugin, $db_update, 'deinstall')) {
		plugin_mark_deinstalled($plugin);
	}
} else {
	generate_install_page($plugin, __($plugin.':desc_deinstall'), 'deinstall');
}
