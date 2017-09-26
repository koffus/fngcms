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
            array('action' => 'cmodify', 'name' => 'nsched_activate', 'type' => 'datetime'),
            array('action' => 'cmodify', 'name' => 'nsched_deactivate', 'type' => 'datetime'),
        )
    ),
);

// RUN
if ('commit' == $action) {
    // If submit requested, do config save
    if (fixdb_plugin_install('nsched', $db_update)) {
        plugin_mark_installed('nsched');
    }
} else {
    $text = 'Плагин <b>nsched</b> позволяет публиковать/снимать с публикации новости по расписанию.<br><br>';
    generate_install_page('nsched', $text);
}
