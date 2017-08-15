<?php

# protect against hack attempts
if (!defined('NGCMS')) die ('HAL');

$db_update = array(
    array(
        'table' => 'users',
        'action' => 'modify',
        'fields' => array(
             array('action' => 'drop', 'name' => 'provider', 'type' => 'varchar(255)', 'params' => "default ''"),
             array('action' => 'drop', 'name' => 'social_id', 'type' => 'text', 'params' => "default ''"),
             array('action' => 'drop', 'name' => 'social_page', 'type' => 'text', 'params' => "default ''"),
             array('action' => 'drop', 'name' => 'sex', 'type' => 'varchar(255)', 'params' => "default ''"),
             array('action' => 'drop', 'name' => 'birthday', 'type' => 'varchar(255)', 'params' => "default ''")
        )
    ),
);

// RUN
if ('commit' == $action) {
    if (fixdb_plugin_install('auth_social', $db_update, 'deinstall')) {
        plugin_mark_deinstalled('auth_social');
    }
} else {
    generate_install_page('auth_social', 'You are shure?', 'deinstall');
}