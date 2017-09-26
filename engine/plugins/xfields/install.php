<?php

/*
 * Install script for plugin.
 * $action: possible action modes
 *      confirm - screen for installation confirmation
 *      apply - apply installation, with handy confirmation
 *      autoapply - apply installation in automatic mode [INSTALL script]
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
            array('action' => 'cmodify', 'name' => 'xfields', 'type' => 'text', 'params' => 'default null'),
        )
    ),
    array(
        'table' => 'category',
        'action' => 'modify',
        'fields' => array(
            array('action' => 'cmodify', 'name' => 'xf_group', 'type' => 'char(40)', 'params' => 'default 0'),
        )
    ),
    array(
        'table' => 'users',
        'action' => 'modify',
        'fields' => array(
            array('action' => 'cmodify', 'name' => 'xfields', 'type' => 'text', 'params' => 'default null'),
        )
    ),
    array(
        'table' => 'xfields',
        'action' => 'cmodify',
        'key' => 'primary key(id)',
        'fields' => array(
            array('action' => 'cmodify', 'name' => 'id', 'type' => 'int', 'params' => 'not null auto_increment'),
            array('action' => 'cmodify', 'name' => 'linked_ds', 'type' => 'int', 'params' => 'default 0'),
            array('action' => 'cmodify', 'name' => 'linked_id', 'type' => 'int', 'params' => 'default 0'),
            array('action' => 'cmodify', 'name' => 'xfields', 'type' => 'text', 'params' => 'default null'),
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
    generate_install_page($plugin, __('xfields:desc_install'));
}
