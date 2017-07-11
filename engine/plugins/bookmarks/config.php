<?php

//
// Configuration file for plugin
//

// Protect against hack attempts
if (!defined('NGCMS')) die ('HAL');

// Load lang files
Lang::loadPlugin($plugin, 'config', '', ':');
print_r(__('')['bookmarks']);
// Fill configuration parameters
$cfg = array('description' => __($plugin.':description'));

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
		'name' => 'localSource',
		'title' => __('localSource'),
		'descr' => __('localSource#desc'),
		'type' => 'select',
		'values' => array('0' => __('localSource_0'), '1' => __('localSource_1'),),
		'value' => intval(pluginGetVariable($plugin, 'localSource')) ? intval(pluginGetVariable($plugin, 'localSource')) : 0,
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
