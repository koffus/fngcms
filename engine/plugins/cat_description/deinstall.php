<?php

// Protect against hack attempts
if (!defined('NGCMS')) die ('HAL');

// RUN
if ('commit' == $action) {
    plugin_mark_deinstalled('cat_description');
} else {
    $text = 'Cейчас плагин будет удален';
    generate_install_page('cat_description', $text, 'deinstall');
}