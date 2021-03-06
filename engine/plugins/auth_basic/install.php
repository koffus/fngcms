<?php

if (!defined('BBCMS')) die ('HAL');

function plugin_auth_basic_install($action)
{

    if ($action != 'autoapply') {
        Lang::loadPlugin('auth_basic', 'admin', '', ':');
    }

    $db_update = array(
        array(
            'table' => 'users_sessions',
            'action' => 'cmodify',
            'key' => 'KEY `userUpdate` (`userID`, `authcookie`), KEY `users_auth` (`authcookie`)',
            'fields' => array(
                array('action' => 'cmodify', 'name' => 'userID', 'type' => 'int(10)', 'params' => 'NOT NULL'),
                array('action' => 'cmodify', 'name' => 'ip', 'type' => 'varchar(15)', 'params' => 'NOT NULL default "0"'),
                array('action' => 'cmodify', 'name' => 'last', 'type' => 'int(10)', 'params' => 'NOT NULL default "0"'),
                array('action' => 'cmodify', 'name' => 'authcookie', 'type' => 'varchar(50)', 'params' => 'default NULL'),
            )
        ),
    );

    // Apply requested action
    switch ($action) {
        case 'confirm':
            generate_install_page('auth_basic', __('auth_basic:description'));
            break;
        case 'autoapply':
        case 'apply':
            if (fixdb_plugin_install('auth_basic', $db_update, 'install', ('autoapply' == $action) ? true : false)) {
                plugin_mark_installed('auth_basic');
            } else {
                return false;
            }
            break;
    }
    return true;
}
