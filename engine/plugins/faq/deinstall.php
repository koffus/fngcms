<?php

// Protect against hack attempts
if (!defined('BBCMS')) die ('HAL');

//
// Configuration file for plugin
//



$db_update = array(
    array(
        'table'  => 'faq',
        'action' => 'drop',
    ),
);

// RUN
if ('commit' == $action) {
    // If submit requested, do config save
    if (fixdb_plugin_install($plugin, $db_update, 'deinstall')) {
        plugin_mark_deinstalled($plugin);
    }
} else {
    $text = 'Удаление плагина';
    generate_install_page($plugin, $text, 'deinstall');
}
