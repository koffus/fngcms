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
        'table' => 'category',
        'action' => 'modify',
        'fields' => array(
            array('action' => 'cmodify', 'name' => 'xmenu', 'type' => 'char(10)', 'params' => 'default "#"')
        ) 
    )
);

// RUN
if ('commit' == $action) {
    // If submit requested, do config save
    if (fixdb_plugin_install('xmenu', $db_update)) {
        plugin_mark_installed('xmenu');
    }
} else {
    $text = "Плагин <b>xmenu</b> реализует расширенные возможности генерации меню.<br /><br />Внимание! При установке плагин производит изменения в БД системы!";
    generate_install_page('xmenu', $text);
}
