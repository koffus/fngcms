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
        'table' => 'basket',
        'action' => 'cmodify',
        'key' => 'primary key(id)',
        'fields' => array(
            array('action' => 'cmodify', 'name' => 'id', 'type' => 'int', 'params' => 'UNSIGNED not null auto_increment'),
            array('action' => 'cmodify', 'name' => 'user_id', 'type' => 'int', 'params' => 'default 0'),
            array('action' => 'cmodify', 'name' => 'cookie', 'type' => 'char(50)', 'params' => 'default ""'),
            array('action' => 'cmodify', 'name' => 'linked_ds', 'type' => 'int', 'params' => 'default 0'),
            array('action' => 'cmodify', 'name' => 'linked_id', 'type' => 'int', 'params' => 'default 0'),
            array('action' => 'cmodify', 'name' => 'title', 'type' => 'char(120)', 'params' => 'default ""'),
            array('action' => 'cmodify', 'name' => 'linked_fld', 'type' => 'text'),
            array('action' => 'cmodify', 'name' => 'price', 'type' => 'decimal(12,2)', 'params' => 'default 0'),
            array('action' => 'cmodify', 'name' => 'count', 'type' => 'int', 'params' => 'default 0'),
            )
        ),
    );

// RUN
if ('commit' == $action) {
    // If submit requested, do config save
    if (fixdb_plugin_install($plugin, $db_update)) {
        plugin_mark_installed($plugin);
    }
} else {
    generate_install_page($plugin, __($plugin.':desc_install'));
}
