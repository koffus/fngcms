<?php

// Protect against hack attempts
if (!defined('NGCMS')) die ('HAL');

/*
 * Configuration file for plugin
*/

Lang::loadPlugin('bb_media', 'config');

if (isset($_REQUEST['action']) and 'commit' == $_REQUEST['action']) {
    // If submit requested, do config save
    plugin_mark_deinstalled('bb_media');
} else {
    generate_install_page('bb_media', 'Cейчас плагин будет удален', 'deinstall');
}