<?php

/*
 * Configuration file for plugin
*/

// Protect against hack attempts
if (!defined('BBCMS')) die ('HAL');

function plugin_bookmarks_install($action) {

    if ('autoapply' != $action) {
        Lang::loadPlugin('bookmarks', 'admin', '', ':');
    }

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

            break;
    }
    return true;
}
