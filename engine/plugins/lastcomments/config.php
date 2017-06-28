<?php

//
// Configuration file for plugin
//

// Protect against hack attempts
if (!defined('NGCMS')) die ('HAL');

// Load lang files
Lang::loadPlugin($plugin, 'config', '', '', ':');

// Fill configuration parameters
$cfg = array('description' => __($plugin.':description'));

$cfgX = array();
	array_push($cfgX, array(
		'name' => 'sidepanel',
		'title' => __($plugin.':sidepanel'),
		'descr' => __($plugin.':sidepanel#desc'),
		'type' => 'select',
		'values' => array(0 => __('noa'), 1 => __('yesa')),
		'value' => pluginGetVariable('lastcomments','sidepanel'),
		));
	array_push($cfgX, array(
		'name' => 'number',
		'title' => __($plugin.':number'),
		'descr' => __($plugin.':number#desc'),
		'type' => 'input',
		'value' => pluginGetVariable('lastcomments','number'),
		));
	array_push($cfgX, array(
		'name' => 'comm_length',
		'title' => __($plugin.':comm_length'),
		'descr' => __($plugin.':comm_length#desc'),
		'type' => 'input',
		'value' => pluginGetVariable('lastcomments','comm_length'),
		));
array_push($cfg, array(
	'mode' => 'group',
	'title' => __($plugin.':group.sidepanel'),
	'entries' => $cfgX,
	));

$cfgX = array();
	array_push($cfgX, array(
		'name' => 'ppage',
		'title' => __($plugin.':ppage'),
		'descr' => __($plugin.':ppage#desc'),
		'type' => 'select',
		'values' => array(0 => __('noa'), 1 => __('yesa')),
		'value' => pluginGetVariable('lastcomments','ppage'),
		));
	array_push($cfgX, array(
		'name' => 'pp_number',
		'title' => __($plugin.':pp_number'),
		'descr' => __($plugin.':pp_number#desc'),
		'type' => 'input',
		'value' => pluginGetVariable('lastcomments','pp_number'),
		));
	array_push($cfgX, array(
		'name' => 'pp_comm_length',
		'title' => __($plugin.':pp_comm_length'),
		'descr' => __($plugin.':pp_comm_length#desc'),
		'type' => 'input',
		'value' => pluginGetVariable('lastcomments','pp_comm_length'),
		));
array_push($cfg, array(
	'mode' => 'group',
	'title' => __($plugin.':group.ppage'),
	'entries' => $cfgX));

$cfgX = array();
	array_push($cfgX, array(
		'name' => 'rssfeed',
		'title' => __($plugin.':rssfeed'),
		'descr' => __($plugin.':rssfeed#desc'),
		'type' => 'select',
		'values' => array(0 => __('noa'), 1 => __('yesa')),
		'value' => pluginGetVariable('lastcomments','rssfeed'),
		));
	array_push($cfgX, array(
		'name' => 'rss_number',
		'title' => __($plugin.':rss_number'),
		'descr' => __($plugin.':rss_number#desc'),
		'type' => 'input',
		'value' => pluginGetVariable('lastcomments','rss_number'),
		));
	array_push($cfgX, array(
		'name' => 'rss_comm_length',
		'title' => __($plugin.':rss_comm_length'),
		'descr' => __($plugin.':rss_comm_length#desc'),
		'type' => 'input',
		'value' => pluginGetVariable('lastcomments','rss_comm_length'),
		));
array_push($cfg, array(
	'mode' => 'group',
	'title' => __($plugin.':group.rss'),
	'entries' => $cfgX));

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
