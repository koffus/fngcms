<?php

// Protect against hack attempts
if (!defined('NGCMS')) die ('HAL');

//
// Configuration file for plugin
//

Lang::loadPlugin('complain', 'config', '', '', ':');

$db_update = array(
	array(
		'table'		=>	'complain',
		'action'	=>	'drop',
	),
);

if ($_REQUEST['action'] == 'commit') {
	if (fixdb_plugin_install($plugin, $db_update, 'deinstall')) {
		plugin_mark_deinstalled($plugin);
	}
} else {
	generate_install_page($plugin, __('complain:desc_uninstall'), 'deinstall');
}

?>