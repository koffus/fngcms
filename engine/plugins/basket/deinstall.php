<?php

// Protect against hack attempts
if (!defined('NGCMS')) die ('HAL');

//
// Configuration file for plugin
//


Lang::loadPlugin('basket', 'config', '', ':');

$db_update = array(
	array(
		'table' => 'basket',
		'action' => 'drop',
	),
);

if (isset($_REQUEST['action']) and $_REQUEST['action'] == 'commit') {
	if (fixdb_plugin_install($plugin, $db_update, 'deinstall')) {
		plugin_mark_deinstalled($plugin);
	}
} else {
	generate_install_page('basket', __('basket:desc_deinstall'), 'deinstall');
}

