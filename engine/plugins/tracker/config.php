<?php

//
// Configuration file for plugin
//

// Protect against hack attempts
if (!defined('NGCMS')) die ('HAL');

// Load lang files
Lang::loadPlugin($plugin, 'config', '', ':');

// Fill configuration parameters
$cfg = array('description' => __($plugin.':description'));

$cfgX = array();
	array_push($cfgX, array(
		'name' => 'storrent',
		'title' => __('tracker:support.torrent'),
		'descr' => __('tracker:support.torrent#desc'),
		'type' => 'select',
		'values' => array ('0' => __('noa'), '1' => __('yesa')),
		'value' => pluginGetVariable($plugin,'storrent'),
		));
	array_push($cfgX, array(
		'name' => 'smagnet',
		'title' => __('tracker:support.magnet'),
		'descr' => __('tracker:support.magnet#desc'),
		'type' => 'select',
		'values' => array ('0' => __('noa'), '1' => __('yesa')),
		'value' => pluginGetVariable($plugin,'smagnet'),
		));
	array_push($cfgX, array(
		'name' => 'tracker',
		'title' => __('tracker:tracker'),
		'descr' => str_replace('{tracker_url}', generatePluginLink('tracker', 'announce', array(), array(), false, true), __('tracker:tracker#desc')),
		'type' => 'select',
		'values' => array ('0' => __('noa'), '1' => __('yesa')),
		'value' => pluginGetVariable($plugin,'tracker'),
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
		'name' => 'extends',
		'title' => __('localExtends'),
		'descr' => __('localExtends#desc'),
		'type' => 'select',
		'values' => array (
			'main' => __('extends_main'),
			'additional' => __('extends_additional'),
			'owner' => __('extends_owner'),
			/*'js' => __('extends_js'),
			'css' => __('extends_css'),*/
			),
		'value' => pluginGetVariable($plugin,'extends') ? pluginGetVariable($plugin,'extends') : 'owner',
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
