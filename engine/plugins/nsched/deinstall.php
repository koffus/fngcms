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
 'table' => 'news',
 'action' => 'modify',
 'fields' => array(
 array('action' => 'drop', 'name' => 'nsched_activate'),
 array('action' => 'drop', 'name' => 'nsched_deactivate'),
 )
 ),
);

// RUN
if ('commit' == $action) {
	// If submit requested, do config save
	if (fixdb_plugin_install('nsched', $db_update, 'deinstall')) {
		plugin_mark_deinstalled('nsched');
	}	
} else {
	$text = 'При удалении плагина <b>nsched</b> вся информация о расписании размещения/удаления новостей будет потеряна!<br><br>';
	generate_install_page('nsched', $text, 'deinstall');
}
