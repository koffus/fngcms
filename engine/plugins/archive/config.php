<?php

/*
 * Configuration file for plugin
 */

// Protect against hack attempts
if (!defined('BBCMS')) die ('HAL');

// Load lang files
Lang::loadPlugin($plugin, 'admin', '', ':');

// Set default values if values are not set [for new variables]
foreach (array(
    'maxnum' => 12,
    'counter' => 1,
    'tcounter' => 1,
    'skin' => 'basic',
    'cache' => 1,
    'cache_expire' => 60,
    ) as $k => $v) {
    if ( pluginGetVariable($plugin, $k) == null )
        pluginSetVariable($plugin, $k, $v);
}

// Load CORE Plugin
$cPlugin = CPlugin::instance();

// Prepare configuration parameters
if (empty($skList = $cPlugin->getThemeSkin($plugin))) {
    msg(array( 'type' => 'danger', 'message' => __('msg.no_skin')));
}

// Fill configuration parameters
$cfg = array(
    'description' => __($plugin.':description'),
    'submit' => array(
        array('type' => 'default'),
        array('type' => 'clearCacheFiles'),
    )
    );

$cfgX = array();
    array_push($cfgX, array(
        'name' => 'maxnum',
        'title' => __($plugin.':maxnum'),
        'descr' => __($plugin.':maxnum#desc'),
        'type' => 'input',
        'value' => intval(pluginGetVariable($plugin, 'maxnum'))
        ));
    array_push($cfgX, array(
        'name' => 'counter',
        'title' => __($plugin.':counter'),
        'descr' => __($plugin.':counter#desc'),
        'type' => 'select',
        'values' => array('0' => __('noa'), '1' => __('yesa')),
        'value' => intval(pluginGetVariable($plugin, 'counter'))
        ));
    array_push($cfgX, array(
        'name' => 'tcounter',
        'title' => __($plugin.':tcounter'),
        'descr' => __($plugin.':tcounter#desc'),
        'type' => 'select',
        'values' => array('0' => __('noa'), '1' => __('yesa')),
        'value' => intval(pluginGetVariable($plugin, 'tcounter'))
        ));
array_push($cfg, array(
    'mode' => 'group',
    'title' => __('group.config'),
    'entries' => $cfgX,
    ));

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

$cfgX = array();
    array_push($cfgX, array(
        'name' => 'cache',
        'title' => __('cache'),
        'descr' => __('cache#desc'),
        'type' => 'select',
        'values' => array('1' => __('yesa'), '0' => __('noa')),
        'value' => intval(pluginGetVariable($plugin, 'cache'))
        ));
    array_push($cfgX, array(
        'name' => 'cache_expire',
        'title' => __('cache_expire'),
        'descr' => __('cache_expire#desc'),
        'type' => 'input',
        'value' => intval(pluginGetVariable($plugin, 'cache_expire'))
        ));
array_push($cfg, array(
    'mode' => 'group',
    'title' => __('group.cache'),
    'entries' => $cfgX,
    ));

// RUN
if ('commit' == $action) {
    // If submit requested, do config save
    commit_plugin_config_changes($plugin, $cfg);
}

generate_config_page($plugin, $cfg);
