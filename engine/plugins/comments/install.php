<?php

// Protect against hack attempts
if (!defined('BBCMS')) die ('HAL');

//
// Configuration file for plugin
//

//
// Install script for plugin.
// $action: possible action modes
// 	confirm		- screen for installation confirmation
//	apply		- apply installation, with handy confirmation
//	autoapply - apply installation in automatic mode [INSTALL script]
//
function plugin_comments_install($action) {

    if ($action != 'autoapply')
        Lang::loadPlugin('comments', 'admin', '', ':');

    // Fill DB_UPDATE configuration scheme
    $db_update = array(
        array(
            'table' => 'comments',
            'action' => 'cmodify',
            'key' => 'primary key(id), KEY `c_post` (`post`)',
            'fields' => array(
                array('action' => 'cmodify', 'name' => 'id', 'type' => 'int', 'params' => "NOT NULL AUTO_INCREMENT"),
                array('action' => 'cmodify', 'name' => 'post', 'type' => 'int', 'params' => "default '0'"),
                array('action' => 'cmodify', 'name' => 'module', 'type' => 'char(100)', 'params' => "default 'news'"),
                array('action' => 'cmodify', 'name' => 'text', 'type' => 'text'),
                array('action' => 'cmodify', 'name' => 'postdate', 'type' => 'int', 'params' => "default '0'"),
                array('action' => 'cmodify', 'name' => 'approve', 'type' => 'tinyint(1)', 'params' => "NOT NULL default '0'"),
                array('action' => 'cmodify', 'name' => 'parent_id', 'type' => 'int(11)', 'params' => "NOT NULL default '0'"),
                array('action' => 'cmodify', 'name' => 'author', 'type' => 'char(100)', 'params' => "default ''"),
                array('action' => 'cmodify', 'name' => 'author_id', 'type' => 'int', 'params' => "NOT NULL default '0'"),
                array('action' => 'cmodify', 'name' => 'mail', 'type' => 'char(100)', 'params' => "default ''"),
                array('action' => 'cmodify', 'name' => 'ip', 'type' => 'char(15)', 'params' => "default ''"),
            )
        ),
        array(
            'table' => 'news',
            'action' => 'cmodify',
            'fields' => array(
                array('action' => 'cmodify', 'name' => 'allow_com', 'type' => 'tinyint(1)', 'params' => "default '2'"),
                array('action' => 'cmodify', 'name' => 'com', 'type' => 'int', 'params' => "default '0'"),
            )
        ),
        array(
            'table' => 'category',
            'action' => 'cmodify',
            'fields' => array(
                array('action' => 'cmodify', 'name' => 'allow_com', 'type' => 'tinyint(1)', 'params' => "default '2'"),
            )
        ),
        array(
            'table' => 'users',
            'action' => 'modify',
            'fields' => array(
                array('action' => 'cmodify', 'name' => 'com', 'type' => 'int', 'params' => "default '0'"),
            )
        )
    );

    // Apply requested action
    switch ($action) {
        case 'confirm':
            generate_install_page('comments', __('comments:desc_install'));
            break;
        case 'autoapply':
        case 'apply':
            if (fixdb_plugin_install('comments', $db_update, 'install', ($action=='autoapply')?true:false)) {
                plugin_mark_installed('comments');
            } else {
                return false;
            }

            // Now we need to set some default params
            $params = array(
                'regonly' => 0,
                'moderate' => 1,
                'backorder' => 0,
                'maxlen' => 500,
                'maxwlen' => 50,
                'multi' => 1,
                'author_multi' => 1,
                'timestamp' => 'j.m.Y - H:i',
                'multipage' => 1,
                'multi_mcount' => 10,
                'multi_scount' => 10,
                'inform_author' => 0,
                'inform_admin' => 0,
                'global_default' => 1,
                'default_news' => 2,
                'default_categories' => 2
            );

            foreach ($params as $k => $v) {
                pluginSetVariable('comments', $k, $v);
            }
            // Load CORE Plugin
            $cPlugin = CPlugin::instance();
            // Save configuration parameters of plugins
            $cPlugin->saveConfig();

            break;
    }
    return true;
}
