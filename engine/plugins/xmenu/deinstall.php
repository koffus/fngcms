<?php

// Protect against hack attempts
if (!defined('NGCMS')) die ('HAL');

//
// Configuration file for plugin
//


Lang::loadPlugin('xmenu', 'config');

$db_update = array(
 array(
 'table' => 'category',
 'action' => 'modify',
 'fields' => array(
 array('action' => 'drop', 'name' => 'xmenu')
 ) 
 )
);

// RUN
if ('commit' == $action) {
	// If submit requested, do config save
	if (fixdb_plugin_install('xmenu', $db_update, 'deinstall')) {
		plugin_mark_deinstalled('xmenu');
	}	
} else {
	generate_install_page('xmenu', 'Удалить плагин', 'deinstall');
}
