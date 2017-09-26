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
		'name' => 'mode',
		'title' => 'В каком режиме генерируется вывод плагина<br /><small><code>Автоматически</code> - при включении плагина автоматически генерируется блок {{ plugin_calendar }}<br /><code>TWIG</code> - вывод плагина генерируется только через TWIG функцию <code>callPlugin()</code></small>',
		'type' => 'select',
		'values' => array('0' => 'Автоматически', '1' => 'TWIG'),
		'value' => intval(pluginGetVariable($plugin,'mode')),
		));
array_push($cfg, array(
	'mode' => 'group',
	'title' => 'Режим запуска',
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
