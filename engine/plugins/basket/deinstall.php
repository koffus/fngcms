<?php

// Protect against hack attempts
if (!defined('NGCMS')) die ('HAL');

//
// Configuration file for plugin
//

Lang::loadPlugin('basket', 'config', '', ':');

$db_update = array(
    array(
        'table' => 'basket',
        'action' => 'drop',
    ),
);

// RUN
if ('commit' == $action) {
    if (fixdb_plugin_install($plugin, $db_update, 'deinstall')) {
        plugin_mark_deinstalled($plugin);
    }
} else {
    generate_install_page('basket', __('basket:desc_deinstall'), 'deinstall');
}
