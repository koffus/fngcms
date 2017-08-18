<?php

// Protect against hack attempts
if (!defined('NGCMS')) die ('HAL');

Lang::loadPlugin($plugin, 'config', '', ':');

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
    fixdb_plugin_install($plugin, $db_update, 'deinstall');

    $ULIB = new UrlLibrary();
    $ULIB->loadConfig();
    $ULIB->removeCommand('gallery', '');
    $ULIB->removeCommand('gallery', 'gallery');
    $ULIB->removeCommand('gallery', 'image');

    $UHANDLER = new UrlHandler();
    $UHANDLER->loadConfig();
    $UHANDLER->removePluginHandlers('gallery', '');
    $UHANDLER->removePluginHandlers('gallery', 'gallery');
    $UHANDLER->removePluginHandlers('gallery', 'image');
    $UHANDLER->saveConfig();

    // Load CORE Plugin
    $cPlugin = CPlugin::instance();
    // Load list of active plugins
    $plugins = $cPlugin->getList();
	unset($plugins['config'][$plugin]);
    // Save configuration parameters of plugins
    $cPlugin->setConfig($plugins['config']);
    // Save configuration parameters of plugins
    $cPlugin->saveConfig();
    
	$ULIB->saveConfig();
	plugin_mark_deinstalled($plugin);
} else {
	generate_install_page($plugin, __('gallery:desc_deinstall'), 'deinstall');
}
