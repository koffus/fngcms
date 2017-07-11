<?php

/*
 * Configuration file for plugin
*/

// Protect against hack attempts
if (!defined('NGCMS')) die ('HAL');

// Load LANG file for plugin
Lang::loadPlugin($plugin, 'config', '', ':');

$db_update = array(
	array(
		'table' => 'bookmarks',
		'action' => 'drop',
	),
);

if (isset($_REQUEST['action']) and $_REQUEST['action'] == 'commit') {
	if (fixdb_plugin_install($plugin, $db_update, 'deinstall')) {
		plugin_mark_deinstalled($plugin);
	}
} else {
	generate_install_page($plugin, 'Внимание! При удалении плагина удаляется вся база закладок пользователей. Продолжить?', 'deinstall');
}
