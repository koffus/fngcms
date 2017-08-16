<?php

//
// Configuration file for plugin
//

// Protect against hack attempts
if (!defined('NGCMS')) die ('HAL');

// Load lang files
Lang::loadPlugin($plugin, 'config', '', ':');

// Load CORE Plugin
$cPlugin = CPlugin::instance();

// Prepare configuration parameters
$skList = $cPlugin->getFoldersSkin($plugin);

// Fill configuration parameters
$cfg = array(
    'description' => __($plugin.':description'),
    'submit' => array(
        array('type' => 'default'),
    )
    );

$cfgX = array();
    array_push($cfgX, array(
        'name' => 'regonly',
        'title' => __($plugin.':for_reg'),
        'descr' => __($plugin.':for_reg#desc'),
        'type' => 'select',
        'values' => array('0' => __('noa'), '1' => __('yesa')),
        'value' => pluginGetVariable($plugin,'regonly'),
        ));
array_push($cfg, array(
    'mode' => 'group',
    'title' => __('group.config'),
    'entries' => $cfgX,
    ));

$cfgX = array();
    array_push($cfgX, array(
        'name' => 'localSource',
        'title' => __('localSource'),
        'descr' => __('localSource#desc'),
        'type' => 'select',
        'values' => array('0' => __('localSource_0'), '1' => __('localSource_1'),),
        'value' => intval(pluginGetVariable($plugin, 'localSource')) ? intval(pluginGetVariable($plugin, 'localSource')) : 0,
        ));
    array_push($cfgX, array(
        'name' => 'localSkin',
        'title' => __('localSkin'),
        'descr' => __('localSkin#desc'),
        'type' => 'select',
        'values' => $skList,
        'value' => pluginGetVariable($plugin,'localSkin') ? pluginGetVariable($plugin,'localSkin') : 'basic',
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
