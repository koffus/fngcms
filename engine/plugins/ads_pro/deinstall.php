<?php

/*
 * Configuration file for plugin
 */

// Protect against hack attempts
if (!defined('BBCMS')) die ('HAL');

// Load lang files
Lang::loadPlugin($plugin, 'admin');

$db_update = array(
        array(
            'table' => 'ads_pro',
            'action' => 'drop',
        )
    );

// RUN
if ('commit' == $action) {
    // If submit requested, do config save
    if (fixdb_plugin_install('ads_pro', $db_update, 'deinstall')) {
        plugin_mark_deinstalled('ads_pro');
    }
} else {
    generate_install_page('ads_pro', 'Cейчас плагин будет удален', 'deinstall');
}