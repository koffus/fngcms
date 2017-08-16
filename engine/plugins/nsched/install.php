<?php

// Protect against hack attempts
if (!defined('NGCMS')) die ('HAL');

Lang::loadPlugin('nsched', 'install');

$db_update = array(
    array(
        'table' => 'news',
        'action' => 'modify',
        'fields' => array(
            array('action' => 'cmodify', 'name' => 'nsched_activate', 'type' => 'datetime'),
            array('action' => 'cmodify', 'name' => 'nsched_deactivate', 'type' => 'datetime'),
        )
    ),
);

// RUN
if ('commit' == $action) {
    // If submit requested, do config save
    if (fixdb_plugin_install('nsched', $db_update)) {
        plugin_mark_installed('nsched');
    }
} else {
    $text = 'Плагин <b>nsched</b> позволяет публиковать/снимать с публикации новости по расписанию.<br><br>';
    generate_install_page('nsched', $text);
}
