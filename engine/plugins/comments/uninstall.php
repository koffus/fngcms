<?php

// Protect against hack attempts
if (!defined('NGCMS')) die ('HAL');

/*
 * Configuration file for plugin
*/

Lang::loadPlugin('comments', 'config', '', ':');

$db_update = array(
 array(
 'table' => 'news',
 'action' => 'modify',
 'fields' => array(
 array('action' => 'drop', 'name' => 'com'),
 array('action' => 'drop', 'name' => 'allow_com'),
 )
 ),
 array(
 'table' => 'users',
 'action' => 'modify',
 'fields' => array(
 array('action' => 'drop', 'name' => 'com'),
 )
 ),
 array(
 'table' => 'comments',
 'action' => 'drop',
 )
);

if (isset($_REQUEST['action']) and 'commit' == $_REQUEST['action']) {
    // If submit requested, do config save
    if (fixdb_plugin_install('comments', $db_update, 'deinstall')) {
        plugin_mark_deinstalled('comments');
    }
} else {
    generate_install_page('comments', __('comments:desc_uninstall'), 'deinstall');
}
