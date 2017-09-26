<?php

/*
 * Deinstall file for plugin
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
            array('action' => 'drop', 'name' => 'xfields', 'type' => 'text'),
        )
    ),
    array(
        'table' => 'category',
        'action' => 'modify',
        'fields' => array(
            array('action' => 'drop', 'name' => 'xf_group', 'type' => 'text'),
        )
    ),
    array(
        'table' => 'users',
        'action' => 'modify',
        'fields' => array(
            array('action' => 'drop', 'name' => 'xfields', 'type' => 'text'),
        )
    )
);

// RUN
if ('commit' == $action) {
    // If submit requested, do config save
    if (fixdb_plugin_install($plugin, $db_update, 'deinstall')) {
        plugin_mark_deinstalled($plugin);
    }
} else {
    generate_install_page($plugin, __($plugin.':desc_uninstall'), 'deinstall');
}
