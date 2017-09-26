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
    )
    );

$cfgX = array();
	array_push($cfgX, array(
		'name' => 'msg_per_page', 
		'title' => "Количество сообщений на странице<br /><small>По умолчанию: <code>10</code></small>", 
		'type' => 'input', 
		'value' => intval(pluginGetVariable($plugin, 'msg_per_page') ? intval(pluginGetVariable($plugin, 'msg_per_page')) : 10)
		));
	array_push($cfgX, array(
		'name' => 'title_length', 
		'title' => "Максимальная длина темы сообщения<br /><small>По умолчанию: <code>50</code></small>", 
		'type' => 'input', 
		'value' => intval(pluginGetVariable($plugin, 'title_length') ? intval(pluginGetVariable($plugin, 'title_length')) : 50)
		));
	array_push($cfgX, array(
		'name' => 'message_length', 
		'title' => "Максимальная длина сообщения<br /><small>По умолчанию: <code>3000</code></small>", 
		'type' => 'input', 
		'value' => intval(pluginGetVariable($plugin, 'message_length') ? intval(pluginGetVariable($plugin, 'message_length')) : 3000)
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
	'name' => 'rebuild', 
	'title' => __('rebuild'),
	'descr' => __('rebuild#desc'),
	'type' => 'select', 
	'value' => 0, 
	'values' => array('1' => __('yesa'), '0' => __('noa')),
	'nosave' => 1
	));
array_push($cfg, array(
	'mode' => 'group',
	'title' => __('group.rebuild'),
	'entries' => $cfgX,
	));

// RUN
if ('commit' == $action) {
	// Rebuild index table
	if ($_REQUEST['rebuild']) {
		if($mysql->query('UPDATE '.prefix.'_users SET `pm_sync` = 0'))
			msg(array('message' => __('rebuild.done')));
	}

	// If submit requested, do config save
	commit_plugin_config_changes($plugin, $cfg);
}

generate_config_page($plugin, $cfg);
