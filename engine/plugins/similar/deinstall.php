<?php

// Protect against hack attempts
if (!defined('NGCMS')) die ('HAL');

//
// Configuration file for plugin
//


Lang::loadPlugin($plugin, 'config');

$db_update = array(
	array(
		'table' => 'similar_index',
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
