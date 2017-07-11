<?php

//
// Configuration file for plugin
//

// Protect against hack attempts
if (!defined('NGCMS')) die ('HAL');

// Load lang files
Lang::loadPlugin($plugin, 'config', '', ':');

// Set default values if values are not set [for new variables]
foreach (array(
	'maxnum' => 12,
	'counter' => 1,
	'tcounter' => 1,
	'localSource' => 0,
	'cache' => 1,
	'cacheExpire' => 60,
	) as $k => $v) {
	if ( pluginGetVariable($plugin, $k) == null )
		pluginSetVariable($plugin, $k, $v);
}

// Fill configuration parameters
$cfg = array('description' => __($plugin.':description'));

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
		'name' => 'localSource',
		'title' => __('localSource'),
		'descr' => __('localSource#desc'),
		'type' => 'select',
		'values' => array('0' => __('localSource_0'), '1' => __('localSource_1'),),
		'value' => intval(pluginGetVariable($plugin, 'localSource')) ? intval(pluginGetVariable($plugin, 'localSource')) : 0,
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
		'name' => 'cacheExpire',
		'title' => __('cacheExpire'),
		'descr' => __('cacheExpire#desc'),
		'type' => 'input',
		'value' => intval(pluginGetVariable($plugin, 'cacheExpire')) ? intval(pluginGetVariable($plugin, 'cacheExpire')) : 60,
		));
array_push($cfg, array(
	'mode' => 'group',
	'title' => __('group.cache'),
	'entries' => $cfgX,
	));

// RUN
if (isset($_REQUEST['action']) and $_REQUEST['action'] == 'commit') {
	// If submit requested, do config save
	commit_plugin_config_changes($plugin, $cfg);
}

generate_config_page($plugin, $cfg);
