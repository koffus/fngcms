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

function plugin_voting_install($action)
{

    // Load lang files
    if ($action != 'autoapply')
        Lang::loadPlugin('voting', 'admin', '', ':');

    $db_update = array(
        array(
            'table' => 'vote',
            'action' => 'cmodify',
            'key' => 'primary key(id)',
            'fields' => array(
                array('action' => 'cmodify', 'name' => 'id', 'type' => 'int', 'params' => 'not null auto_increment'),
                array('action' => 'cmodify', 'name' => 'newsid', 'type' => 'int', 'params' => 'default 0'),
                array('action' => 'cmodify', 'name' => 'name', 'type' => 'char(50)'),
                array('action' => 'cmodify', 'name' => 'descr', 'type' => 'text'),
                array('action' => 'cmodify', 'name' => 'active', 'type' => 'int', 'params' => 'default 0'),
                array('action' => 'cmodify', 'name' => 'closed', 'type' => 'int', 'params' => 'default 0'),
                array('action' => 'cmodify', 'name' => 'regonly', 'type' => 'int', 'params' => 'default 0'),
            )
        ),
        array(
            'table' => 'voteline',
            'action' => 'cmodify',
            'key' => 'primary key(id)',
            'fields' => array(
                array('action' => 'cmodify', 'name' => 'id', 'type' => 'int', 'params' => 'not null auto_increment'),
                array('action' => 'cmodify', 'name' => 'voteid', 'type' => 'int', 'params' => 'default 0'),
                array('action' => 'cmodify', 'name' => 'position', 'type' => 'int', 'params' => 'default 0'),
                array('action' => 'cmodify', 'name' => 'name', 'type' => 'char(50)'),
                array('action' => 'cmodify', 'name' => 'cnt', 'type' => 'int', 'params' => 'default 0'),
                array('action' => 'cmodify', 'name' => 'active', 'type' => 'int', 'params' => 'default 1'),
            )
        ),
        array(
            'table' => 'votestat',
            'action' => 'cmodify',
            'key'	 => 'primary key(id)',
            'fields' => array(
                array('action' => 'cmodify', 'name' => 'id', 'type' => 'int', 'params' => 'not null auto_increment'),
                array('action' => 'cmodify', 'name' => 'userid', 'type' => 'int', 'params' => 'default 0'),
                array('action' => 'cmodify', 'name' => 'voteid', 'type' => 'int', 'params' => 'default 0'),
                array('action' => 'cmodify', 'name' => 'voteline', 'type' => 'int', 'params' => 'default 0'),
                array('action' => 'cmodify', 'name' => 'ip', 'type' => 'char(15)'),
                array('action' => 'cmodify', 'name' => 'dt', 'type' => 'datetime'),
            ),
        ),
    );

    // Apply requested action
    switch ($action) {
        case 'confirm':
            generate_install_page('voting', __('voting:install#desc'));
            break;
        case 'autoapply':
        case 'apply':
            if (fixdb_plugin_install('voting', $db_update, 'install', ($action=='autoapply') ? true : false)) {
                plugin_mark_installed('voting');
            }

            // Now we need to set some default params
            $params = array(
                'access' => 1,
                'refresh' => 30,
                'history' => 30,
                'rate_limit' => 0,
                'maxidle' => 0,
                'maxwlen' => 40,
                'maxlen' => 500,
            );

            foreach ($params as $k => $v) {
                pluginSetVariable('voting', $k, $v);
            }
            // Load CORE Plugin
            $cPlugin = CPlugin::instance();
            // Save configuration parameters of plugins
            $cPlugin->saveConfig();

            break;
    }
    return true;
}
