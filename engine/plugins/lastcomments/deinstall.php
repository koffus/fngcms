<?php

// Protect against hack attempts
if (!defined('NGCMS')) die ('HAL');

// RUN
if ('commit' == $action) {
    $ULIB = new UrlLibrary();
    $ULIB->loadConfig();
    $ULIB->removeCommand('lastcomments', '');
    $ULIB->removeCommand('lastcomments', 'rss');
    $ULIB->saveConfig();
    plugin_mark_deinstalled('lastcomments');
} else {
    generate_install_page('lastcomments', "Bye-Bye!", 'deinstall');
}