<?php

/*
 * Configuration file for plugin
 */

// Protect against hack attempts
if (!defined('BBCMS')) die ('HAL');

// Load lang files
Lang::loadPlugin($plugin, 'admin');

// RUN
if ('commit' == $action) {
    // If submit requested, do config save
    plugin_mark_deinstalled('bb_media');
} else {
    generate_install_page('bb_media', 'Cейчас плагин будет удален', 'deinstall');
}