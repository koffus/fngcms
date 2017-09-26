<?php

/*
 * Configuration file for plugin
 */

// Protect against hack attempts
if (!defined('BBCMS')) die ('HAL');

// Load lang files
Lang::loadPlugin($plugin, 'admin', '', ':');

// Load CORE Plugin
$cPlugin = CPlugin::instance();

// Prepare configuration parameters
if (empty($skList = $cPlugin->getThemeSkin($plugin))) {
    msg(array( 'type' => 'danger', 'message' => __('msg.no_skin')));
}

function getPlayersNames($path) {
    $dirs = array_filter(glob($path.'*'), 'is_dir');
    $dirNames = array();
    foreach($dirs as $key => $dir) {
        $basename = basename($dir);
        $dirNames[$basename] = $basename;
    }
    return $dirNames;
}

$dirNames = getPlayersNames(__DIR__.'/players/');

// Fill configuration parameters
$cfg = array(
    'description' => 'Плагин добавляет поддержку BB кода [MEDIA]',
    'submit' => array(
        array('type' => 'default'),
    )
    );

$cfgX = array();
    array_push($cfgX, array(
        'name' => 'skin',
        'title' => __('skin'),
        'descr' => __('skin#desc'),
        'type' => 'select',
        'values' => $skList,
        'value' => pluginGetVariable($plugin, 'skin'),
    ));
array_push($cfg, array(
    'mode' => 'group',
    'title' => __('group.source'),
    'entries' => $cfgX,
    ));

// RUN
if ('commit' == $action) {
    // If submit requested, do config save
    commit_plugin_config_changes($plugin, $cfg);
}

generate_config_page($plugin, $cfg);
