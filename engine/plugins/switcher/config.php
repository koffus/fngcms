<?php

//
// Configuration file for plugin
//

// Protect against hack attempts
if (!defined('NGCMS')) die ('HAL');

// Preload config file
pluginsLoadConfig();

// Load lang files
Lang::loadPlugin($plugin, 'config', '', '', ':');

// Prepare configuration parameters
$lang_list[] =  __($plugin.':bydefault');
$lang_list = array_merge($lang_list, ListFiles('lang', ''));

$tpl_list[] =  __($plugin.':bydefault');
$tpl_list = array_merge($tpl_list, ListFiles('../templates', ''));

$profile_count = intval(pluginGetVariable('switcher','count'));
if (!$profile_count) { $profile_count = 3; }

// Fill configuration parameters
$cfg = array('description' => __($plugin.':description'));


$cfgX = array();
array_push($cfgX, array(
		'name' => 'count',
		'title' =>  __($plugin.':count'),
		'descr' =>  __($plugin.':count#desc'),
		'type' => 'input', 'html_flags' => ' size="5"',
		'value' => $profile_count));
array_push($cfgX, array(
		'name' => 'selfpage',
		'title' =>  __($plugin.':selfpage'),
		'descr' =>  __($plugin.':selfpage#desc'),
		'type' => 'select',
		'values' => array('1' => __('yesa'), '0' => __('noa')),
		'value' => pluginGetVariable('switcher','selfpage'),
		));
array_push($cfg, array(
	'mode' => 'group',
	'title' => __('group.config'),
	'entries' => $cfgX,
	));

for ($i = 1; $i <= $profile_count; $i++) {
	$cfgX = array();
		array_push($cfgX, array(
			'name' => 'profile'.$i.'_active',
			'title' =>  __($plugin.':flagactive'),
			'type' => 'select',
			'values' => array('1' => __('yesa'), '0' => __('noa')),
			'value' => pluginGetVariable('switcher','profile'.$i.'_active'),
			));
		array_push($cfgX, array(
			'name' => 'profile'.$i.'_template',
			'title' =>  __($plugin.':template'),
			'descr' =>  __($plugin.':template#desc'),
			'type' => 'select',
			'values' => $tpl_list,
			'value' => pluginGetVariable('switcher','profile'.$i.'_template'),
			));
		array_push($cfgX, array(
			'name' => 'profile'.$i.'_lang',
			'title' =>  __($plugin.':lang'),
			'descr' =>  __($plugin.':lang#desc'),
			'type' => 'select',
			'values' => $lang_list,
			'value' => pluginGetVariable('switcher','profile'.$i.'_lang'),
			));
		array_push($cfgX, array(
			'name' => 'profile'.$i.'_name',
			'title' =>  __($plugin.':name'),
			'descr' =>  __($plugin.':name#desc'),
			'type' => 'input',
			'value' => pluginGetVariable('switcher','profile'.$i.'_name'),
			));
		array_push($cfgX, array(
			'name' => 'profile'.$i.'_id',
			'title' =>  __($plugin.':id'),
			'descr' =>  __($plugin.':id#desc'),
			'type' => 'input',
			'value' => pluginGetVariable('switcher','profile'.$i.'_id'),
			));
		array_push($cfgX, array(
			'name' => 'profile'.$i.'_redirect',
			'title' =>  __($plugin.':redirect'),
			'descr' =>  __($plugin.':redirect#desc'),
			'type' => 'input',
			'value' => pluginGetVariable('switcher','profile'.$i.'_redirect'),
			));
		array_push($cfgX, array(
			'name' => 'profile'.$i.'_domains',
			'title' =>  __($plugin.':domains'),
			'descr' =>  __($plugin.':domains#desc'),
			'type' => 'text',
			'html_flags' => 'rows="3"',
			'value' => pluginGetVariable('switcher','profile'.$i.'_domains'),
			));
	array_push($cfg, array(
		'mode' => 'group',
		'title' => __($plugin.':profile').' №'.$i,
		'toggle' => 'hide',
		'entries' => $cfgX,
		));
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

// RUN
if ($_REQUEST['action'] == 'commit') {
	// If submit requested, do config save
	commit_plugin_config_changes($plugin, $cfg);
	print_commit_complete($plugin, $cfg);
} else {
	generate_config_page($plugin, $cfg);
}
