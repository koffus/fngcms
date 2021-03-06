<?php

/*
 * Configuration file for plugin
*/

// Protect against hack attempts
if (!defined('BBCMS')) die ('HAL');

// Install script for plugin.
// $action: possible action modes
//		confirm		- screen for installation confirmation
//		apply		- apply installation, with handy confirmation
//		autoapply	- apply installation in automatic mode [INSTALL script]
function plugin_jchat_install($action) {

    if ('autoapply' != $action)
        Lang::loadPlugin('jchat', 'admin', '', ':');

    // Fill DB_UPDATE configuration scheme
    $db_update = array(
        array(
            'table' => 'jchat',
            'action' => 'cmodify',
            'charset' => 'UTF8',
            'key' => 'primary key(id)',
            'fields' => array(
                array('action' => 'cmodify', 'name' => 'id', 'type' => 'int', 'params' => 'not null auto_increment'),
                array('action' => 'cmodify', 'name' => 'chatid', 'type' => 'int', 'params' => 'default 0'),
                array('action' => 'cmodify', 'name' => 'postdate', 'type' => 'int'),
                array('action' => 'cmodify', 'name' => 'author', 'type' => 'char(50)'),
                array('action' => 'cmodify', 'name' => 'author_id', 'type' => 'int', 'params' => 'default 0'),
                array('action' => 'cmodify', 'name' => 'status', 'type' => 'int', 'params' => 'default 0'),
                array('action' => 'cmodify', 'name' => 'ip', 'type' => 'char(15)'),
                array('action' => 'cmodify', 'name' => 'text', 'type' => 'text'),
            )
        ),
        array(
            'table' => 'jchat_events',
            'action' => 'cmodify',
            'charset' => 'UTF8',
            'key' => 'primary key(id)',
            'fields' => array(
                array('action' => 'cmodify', 'name' => 'id', 'type' => 'int', 'params' => 'not null auto_increment'),
                array('action' => 'cmodify', 'name' => 'chatid', 'type' => 'int', 'params' => 'default 0'),
                array('action' => 'cmodify', 'name' => 'postdate', 'type' => 'int'),
                array('action' => 'cmodify', 'name' => 'type', 'type' => 'int'),
            )
        ),
    );

    // Apply requested action
    switch ($action) {
        case 'confirm':
            generate_install_page('jchat', __('jchat:desc_install'));
            break;
        case 'autoapply':
        case 'apply':
            if (fixdb_plugin_install('jchat', $db_update, 'install', ($action=='autoapply') ? true : false)) {
                plugin_mark_installed('jchat');
            } else {
                return false;
            }

            // Now we need to set some default params
            $params = array(
                'access' => 1,
                'rate_limit' => 0,
                'maxwlen' => 40,
                'maxlen' => 500,
                'refresh' => 30,
                'history' => 30,
                'maxidle' => 0,
                'order' => 0,
                'win.refresh' => 30,
                'win.history' => 30,
                'win.maxidle' => 0,
                'win.order' => 0,
                'enable.panel' => 1,
                'enable.win' => 0,
                'skin' => 'basic',
            );

            foreach ($params as $k => $v) {
                pluginSetVariable('jchat', $k, $v);
            }
            // Load CORE Plugin
            $cPlugin = CPlugin::instance();
            // Save configuration parameters of plugins
            $cPlugin->saveConfig();

            break;
    }
    return true;
}
