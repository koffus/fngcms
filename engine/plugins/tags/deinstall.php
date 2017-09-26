<?php

/*
 * Configuration file for plugin
 */

// Protect against hack attempts
if (!defined('BBCMS')) die ('HAL');

// Load lang files
Lang::loadPlugin($plugin, 'admin', '', ':');

$db_update = array(
    array(
        'table' => 'tags',
        'action' => 'drop',
    ),
    array(
        'table' => 'tags_index',
        'action' => 'drop',
    ),
//	array(
//		'table' => 'news',
//		'action' => 'modify',
//		'fields' => array(
//			array('action' => 'drop', 'name' => 'tags')
//		)
//	)
);

// RUN
if ('commit' == $action) {
    if (fixdb_plugin_install('tags', $db_update, 'deinstall')) {
        
        $ULIB = new UrlLibrary();
        $ULIB->loadConfig();
        $ULIB->removeCommand('tags', 'tag');
        $ULIB->removeCommand('tags', '');

        $UHANDLER = new UrlHandler();
        $UHANDLER->loadConfig();
        $UHANDLER->removePluginHandlers('tags', 'tag');
        $UHANDLER->removePluginHandlers('tags', '');

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
        $UHANDLER->saveConfig();

        plugin_mark_deinstalled('tags');
    }
} else {
    generate_install_page('tags', 'Сейчас плагин будет удален', 'deinstall');
}
