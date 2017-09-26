<?php

// Protect against hack attempts
if (!defined('BBCMS')) die ('HAL');

//
// Configuration file for plugin
//

$db_update = array(
 array(
 'table' => 'guestbook',
 'action' => 'drop',
 ),
 array(
 'table' => 'guestbook_fields',
 'action' => 'drop',
 ),
);

// RUN
if ('commit' == $action) {
    if (fixdb_plugin_install($plugin, $db_update, 'deinstall')) {
        plugin_mark_deinstalled($plugin);
    }
} else {
    generate_install_page($plugin, '', 'deinstall');
}
