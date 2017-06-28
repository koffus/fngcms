<?php

// Protect against hack attempts
if (!defined('NGCMS')) die ('HAL');

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

if (isset($_REQUEST['action']) and $_REQUEST['action'] == 'commit') {
 if (fixdb_plugin_install($plugin, $db_update, 'deinstall')) {
 plugin_mark_deinstalled($plugin);
 }
} else {
 generate_install_page($plugin, '', 'deinstall');
}

?>
