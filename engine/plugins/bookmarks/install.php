<?php

/*
 * Configuration file for plugin
*/

// Protect against hack attempts
if (!defined('NGCMS')) die ('HAL');

function plugin_bookmarks_install($action) {

    if ('autoapply' != $action)
        Lang::loadPlugin('jchat', 'config', '', ':');

    // Fill DB_UPDATE configuration scheme
    $db_update = array(
        array(
            'table' => 'bookmarks',
            'action' => 'cmodify',
            'charset' => 'UTF8',
            'key' => 'primary key(id)',
            'fields' => array(
                array('action' => 'cmodify', 'name' => 'id', 'type' => 'int', 'params' => 'not null auto_increment'),
                array('action' => 'cmodify', 'name' => 'user_id', 'type' => 'int(8)', 'params' => 'default NULL'),
                array('action' => 'cmodify', 'name' => 'news_id', 'type' => 'int(8)', 'params' => 'default NULL'),
            )
        ),
    );

    // Apply requested action
    switch ($action) {
        case 'confirm':
            generate_install_page('bookmarks', 'Плагин выводит закладки пользователя');
            break;
        case 'autoapply':
        case 'apply':
            if (fixdb_plugin_install('bookmarks', $db_update, 'install', ($action=='autoapply') ? true : false)) {
                plugin_mark_installed('bookmarks');
            } else {
                return false;
            }

            // Load CORE Plugin
            $cPlugin = CPlugin::instance();
            // Save configuration parameters of plugins
            $cPlugin->saveConfig();

            break;
    }
    return true;
}
