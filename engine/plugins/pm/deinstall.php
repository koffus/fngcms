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
        'table' => 'pm',
        'action' => 'drop',
    ),
    
    array(
        'table' => 'users',
        'action' => 'modify',
        'fields' => array(
                    array('action' => 'drop', 'name' => 'pm_unread'),
                    array('action' => 'drop', 'name' => 'pm_all'),
                    array('action' => 'drop', 'name' => 'pm_sync'),
                    array('action' => 'drop', 'name' => 'pm_email'),
        )
    ),
);

// RUN
if ('commit' == $action) {
    if (fixdb_plugin_install('pm', $db_update, 'deinstall')) {
        plugin_mark_deinstalled('pm');
    }
} else {
    generate_install_page('pm', __('pm:uninstall'), 'deinstall');
}