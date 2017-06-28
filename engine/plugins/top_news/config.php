<?php

//
// Configuration file for plugin
//

// Protect against hack attempts
if (!defined('NGCMS')) die ('HAL');

// Load lang files
Lang::loadPlugin($plugin, 'config', '', '', ':');

// Prepare configuration parameters
$count = intval(pluginGetVariable($plugin, 'count'));
if ($count < 1 or $count > 50)
	$count = 1;

// Fill configuration parameters
$cfg = array('description' => __($plugin.':description'));

$cfgX = array();
	array_push($cfgX, array(
		'name' => 'count', 
		'title' => __($plugin.':count_title'), 
		'type' => 'input', 
		'value' => $count));
array_push($cfg, array(
	'mode' => 'group',
	'title' => __('group.config'),
	'entries' => $cfgX,
	));

for ($i = 1; $i <= $count; $i++) {

	$currentVar = "top_news{$i}";
	$blockName = pluginGetVariable($plugin, "{$currentVar}_name") ? 'top_news_'.pluginGetVariable('top_news', "{$currentVar}_name") : $currentVar;
	$orderby = array(
		'views' => __($plugin.':orderby_views'), 
		'comments' => __($plugin.':orderby_comments'), 
		'random' => __($plugin.':orderby_random'), 
		'last' => __($plugin.':orderby_last')
		);

	$cfgX = array(); 
		array_push($cfgX, array(
			'name' => "{$currentVar}_number", 
			'title' => __($plugin.':number_title'), 
			'type' => 'input', 
			'value' => intval(pluginGetVariable($plugin, "{$currentVar}_number")) ? pluginGetVariable($plugin, "{$currentVar}_number") : 10)
			);
		array_push($cfgX, array(
			'name' => "{$currentVar}_maxlength", 
			'title' => __($plugin.':maxlength'), 
			'type' => 'input', 
			'value' => intval(pluginGetVariable($plugin, "{$currentVar}_maxlength")) ? pluginGetVariable($plugin , "{$currentVar}_maxlength") : 100)
			);
		array_push($cfgX, array(
			'name' => "{$currentVar}_newslength", 
			'title' => __($plugin.':newslength'), 
			'type' => 'input', 
			'value' => intval(pluginGetVariable($plugin, "{$currentVar}_newslength")) ? pluginGetVariable($plugin , "{$currentVar}_newslength") : 100)
			);
		array_push($cfgX, array(
			'name' => "{$currentVar}_offset", 
			'title' => __($plugin.':offset'), 
			'type' => 'input', 
			'value' => intval(pluginGetVariable($plugin, "{$currentVar}_offset")) ? pluginGetVariable($plugin , "{$currentVar}_offset") : 1)
			);
		array_push($cfgX, array(
			'name' => "{$currentVar}_date", 
			'title' => __($plugin.':date'), 
			'type' => 'input', 
			'value' => intval(pluginGetVariable($plugin, "{$currentVar}_date")))
			);
		array_push($cfgX, array(
			'name' => "{$currentVar}_orderby", 
			'type' => 'select', 
			'title' => __($plugin.':orderby_title'), 
			'values' => $orderby, 
			'value' => pluginGetVariable($plugin, "{$currentVar}_orderby"))
			);
		array_push($cfgX, array(
			'name' => "{$currentVar}_categories", 
			'title' => __($plugin.':categories'), 
			'type' => 'input',
			'value' => pluginGetVariable($plugin, "{$currentVar}_categories"))
			);
		array_push($cfgX, array(
			'name' => "{$currentVar}_ifcategory", 
			'title' => __($plugin.':ifcategory'), 
			'type' => 'checkbox', 
			'value' => pluginGetVariable($plugin, "{$currentVar}_ifcategory"))
			);
		array_push($cfgX, array(
			'name' => "{$currentVar}_content",
			'title' => __($plugin.':content'),
			'type' => 'checkbox',
			'value' => pluginGetVariable($plugin ,"{$currentVar}_content"))
			);
		array_push($cfgX, array(
			'name' => "{$currentVar}_mainpage",
			'title' => __($plugin.':mainpage'), 
			'type' => 'checkbox', 
			'value' => pluginGetVariable($plugin, "{$currentVar}_mainpage"))
			);
		array_push($cfgX, array(
			'name' => "{$currentVar}_img", 
			'title' => __($plugin.':img'), 
			'type' => 'checkbox', 
			'value' => pluginGetVariable('top_news',"{$currentVar}_img"))
			);
		array_push($cfgX, array(
			'name' => "{$currentVar}_name", 
			'title' => str_replace('currentVar', $currentVar, __($plugin.':name')),
			'type' => 'input', 
			'value' => pluginGetVariable($plugin, "{$currentVar}_name"))
			);
		array_push($cfgX, array(
			'name' => "{$currentVar}_dateformat", 
			'title' => __($plugin.':dateformat'), 
			'descr' => __($plugin.':dateformat_descr'), 
			'type' => 'input', 
			'value' => pluginGetVariable($plugin, "{$currentVar}_dateformat"))
			);
	array_push($cfg, array(
		'mode' => 'group', 
		'title' => __($plugin.':group').$blockName,
		'toggle' => 'hide', 
		'entries' => $cfgX,
		)
	);
}

$cfgX = array();
	array_push($cfgX, array(
		'name' => 'localSource',
		'title' => __('localSource'),
		'descr' => __('localSource#desc'),
		'type' => 'select',
		'values' => array('0' => __('localSource_0'), '1' => __('localSource_1'),),
		'value' => intval(pluginGetVariable($plugin, 'localSource')) ? intval(pluginGetVariable($plugin, 'localSource')) : '0',
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
