<?php

// Protect against hack attempts
if (!defined('NGCMS')) die ('HAL');

//
// Configuration file for plugin

Lang::loadPlugin('jchat', 'config', '', '', ':');

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

if (isset($_REQUEST['action']) and $_REQUEST['action'] == 'commit') {
	if (fixdb_plugin_install($plugin, $db_update, 'deinstall')) {
		plugin_mark_deinstalled($plugin);
	}
} else {
	generate_install_page('jchat', __('jchat:desc_deinstall'), 'deinstall');
}
