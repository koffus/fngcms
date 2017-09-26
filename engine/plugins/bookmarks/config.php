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
        'name' => 'sidebar',
        'title' => __($plugin.':sidebar'),
        'type' => 'select',
        'values' => array ('1' => __('yesa'), '0' => __('noa')),
        'value' => intval(pluginGetVariable($plugin, 'sidebar')),
        ));
    array_push($cfgX, array(
        'name' => 'max_sidebar',
        'title' => __($plugin.':max_sidebar'),
        'descr' => __($plugin.':max_sidebar#descr'),
        'type' => 'input',
        'value' => intval(pluginGetVariable($plugin, 'max_sidebar')) ? pluginGetVariable($plugin,'max_sidebar') : '10',
        ));
    array_push($cfgX, array(
        'name' => 'hide_empty',
        'title' => __($plugin.':hide_empty'),
        'type' => 'select',
        'values' => array ('1' => __('yesa'), '0' => __('noa')),
        'value' => intval(pluginGetVariable($plugin, 'hide_empty')),
        ));
    array_push($cfgX, array(
        'name' => 'maxlength',
        'title' => __($plugin.':maxlength'),
        'descr' => __($plugin.':maxlength#descr'),
        'type' => 'input',
        'value' => intval(pluginGetVariable($plugin, 'maxlength')) ? pluginGetVariable($plugin,'maxlength') : '100',
        ));
    array_push($cfgX, array(
        'name' => 'counter',
        'title' => __($plugin.':counter'),
        'descr' => __($plugin.':counter#descr'),
        'type' => 'select',
        'values' => array ('1' => __('yesa'), '0' => __('noa')),
        'value' => intval(pluginGetVariable($plugin, 'counter')),
        ));
    array_push($cfgX, array(
        'name' => 'bookmarks_limit',
        'title' => __($plugin.':bookmarks_limit'),
        'descr' => __($plugin.':bookmarks_limit#descr'),
        'type' => 'input',
        'value' => intval(pluginGetVariable($plugin, 'bookmarks_limit')) ? pluginGetVariable($plugin,'bookmarks_limit') : '100',
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
    array_push($cfgX, array(
        'name' => 'news_short',
        'title' => __($plugin.':news_short'),
        'descr' => __($plugin.':news_short#descr'),
        'type' => 'select',
        'values' => array ('1' => __('yesa'), '0' => __('noa')),
        'value' => intval(pluginGetVariable($plugin, 'news_short')),
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
        'value' => intval(pluginGetVariable($plugin, 'cache')) ? intval(pluginGetVariable($plugin, 'cache')) : 1,
        ));
    array_push($cfgX, array(
        'name' => 'cache_expire',
        'title' => __('cache_expire'),
        'descr' => __('cache_expire#desc'),
        'type' => 'input',
        'value' => intval(pluginGetVariable($plugin, 'cache_expire')) ? intval(pluginGetVariable($plugin, 'cache_expire')) : 60,
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
