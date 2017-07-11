<?php

// Protect against hack attempts
if (!defined('NGCMS')) die ('HAL');

/*
 * Configuration file for plugin
*/

Lang::loadPlugin('ads_pro', 'config');

$db_update = array(
		array(
			'table' => 'ads_pro',
			'action' => 'drop',
		)
	);

if (isset($_REQUEST['action']) and 'commit' == $_REQUEST['action']) {
    // If submit requested, do config save
    if (fixdb_plugin_install('ads_pro', $db_update, 'deinstall')) {
        plugin_mark_deinstalled('ads_pro');
    }
} else {
    generate_install_page('ads_pro', 'Cейчас плагин будет удален', 'deinstall');
}