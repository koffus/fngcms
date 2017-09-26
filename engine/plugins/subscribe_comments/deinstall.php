<?php

// Protect against hack attempts
if (!defined('BBCMS')) die ('HAL');

//
// Configuration file for plugin
//



$db_update = array(
	array(
		'table' => 'subscribe_comments',
		'action' => 'drop',
	),
	array(
		'table' => 'subscribe_comments_temp',
		'action' => 'drop',
	),
);

// RUN
if ('commit' == $action) {
	if (fixdb_plugin_install($plugin, $db_update, 'deinstall')) {
		plugin_mark_deinstalled($plugin);
	}
} else {
	generate_install_page($plugin, 'Удаление плагина', 'deinstall');
}
