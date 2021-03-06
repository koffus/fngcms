<?php

// Protect against hack attempts
if (!defined('BBCMS')) die ('HAL');

//
// Configuration file for plugin
//



// RUN
if ('commit' == $action) {

    $ULIB = new UrlLibrary();
    $ULIB->loadConfig();
    $ULIB->removeCommand('nsm', '');
    $ULIB->removeCommand('nsm', 'add');
    $ULIB->removeCommand('nsm', 'edit');
    $ULIB->removeCommand('nsm', 'del');
    $ULIB->saveConfig();

    plugin_mark_deinstalled($plugin);

} else {
    generate_install_page($plugin, "Удаление NSM", 'deinstall');
}
