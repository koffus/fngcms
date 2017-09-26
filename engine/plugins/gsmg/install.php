<?php

/*
 * Configuration file for plugin
 */

// Protect against hack attempts
if (!defined('BBCMS')) die ('HAL');

// Load lang files
Lang::loadPlugin($plugin, 'admin', '', ':');

// Load library
include_once(root."/plugins/gsmg/lib/common.php");

function plugin_gsmg_install($action) {
 
    // Apply requested action
    switch ($action) {
        case 'confirm':
            generate_install_page('gsmg', __('gsmg:description'));
            break;
        case 'autoapply':
        case 'apply':
            create_gsmg_urls();
            plugin_mark_installed('gsmg');
            $url = home."/engine/admin.php?mod=extras";
            header("HTTP/1.1 301 Moved Permanently");
            header("Location: {$url}");
            break;
    }
    return true;
}
