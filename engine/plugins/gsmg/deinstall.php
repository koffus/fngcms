<?php

// Protect against hack attempts
if (!defined('BBCMS')) die ('HAL');

// Load library
include_once(root."/plugins/gsmg/lib/common.php");

//
// Configuration file for plugin
//

// RUN
if ('commit' == $action) {
    remove_gsmg_urls();
    plugin_mark_deinstalled($plugin);
} else {
    generate_install_page($plugin, 'Удаление плагина', 'deinstall');
}
