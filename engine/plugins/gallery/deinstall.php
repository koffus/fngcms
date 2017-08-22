<?php

// Protect against hack attempts
if (!defined('NGCMS')) die ('HAL');

Lang::loadPlugin('gallery', 'config', '', ':');

$db_update = array(
    array(
        'table' => 'comments',
        'action' => 'modify',
        'fields' => array(
            array('action' => 'drop', 'name' => 'module'),
            )
    ),
    array(
        'table' => 'images',
        'action' => 'modify',
        'fields' => array(
            array('action' => 'drop', 'name' => 'com'),
            array('action' => 'drop', 'name' => 'views'),
            )
    ),
    array(
        'table'  => 'gallery',
        'action' => 'drop',
    )
);

if ('commit' == $action) {
    if (fixdb_plugin_install('gallery', $db_update, 'deinstall')) {

        $ULIB = new UrlLibrary();
        $ULIB->loadConfig();
        $ULIB->removeCommand('gallery', 'image');
        $ULIB->removeCommand('gallery', 'gallery');
        $ULIB->removeCommand('gallery', '');

        $UHANDLER = new UrlHandler();
        $UHANDLER->loadConfig();
        $UHANDLER->removePluginHandlers('gallery', 'image');
        $UHANDLER->removePluginHandlers('gallery', 'gallery');
        $UHANDLER->removePluginHandlers('gallery', '');

        // Load CORE Plugin
        $cPlugin = CPlugin::instance();
        // Load list of active plugins
        $plugins = $cPlugin->getList();
        unset($plugins['config']['gallery']);
        // Save configuration parameters of plugins
        $cPlugin->setConfig($plugins['config']);
        // Save configuration parameters of plugins
        $cPlugin->saveConfig();
        $ULIB->saveConfig();
        $UHANDLER->saveConfig();

        plugin_mark_deinstalled('gallery');
    }
} else {
	generate_install_page('gallery', __('gallery:desc_deinstall'), 'deinstall');
}
