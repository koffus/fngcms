<?php

# protect against hack attempts
if (!defined('NGCMS')) die ('HAL');

/*
 * configuration file for plugin
 */

# preload config file
pluginsLoadConfig();

# load lang files 
Lang::loadPlugin($plugin, 'config', '', '', ':');

# fill configuration parameters
$cfg = array();
array_push($cfg, array('descr' => __('bookmarks:descr')));

$cfgX = array();
array_push($cfgX, array('name' => 'sidebar',
						'title' => __('bookmarks:sidebar'), 
						'type' => 'select', 
						'values' => array ('1' => __('bookmarks:label_yes'), '0' => __('bookmarks:label_no')), 
						'value' => intval(pluginGetVariable($plugin,'sidebar'))));

array_push($cfgX, array('name' => 'max_sidebar', 
						'title' => __('bookmarks:max_sidebar'), 
						'type' => 'input', 
						'value' => intval(pluginGetVariable($plugin,'max_sidebar')) ? pluginGetVariable($plugin,'max_sidebar') : '10'));

array_push($cfgX, array('name' => 'hide_empty', 
						'title' => __('bookmarks:hide_empty'), 
						'type' => 'select', 
						'values' => array ( '1' => __('bookmarks:label_yes'), '0' => __('bookmarks:label_no')), 
						'value' => intval(pluginGetVariable($plugin,'hide_empty'))));	
						
array_push($cfgX, array('name' => 'maxlength', 
						'title' => __('bookmarks:maxlength'), 
						'type' => 'input', 
						'value' => intval(pluginGetVariable($plugin,'maxlength')) ? pluginGetVariable($plugin,'maxlength') : '100'));
						
array_push($cfgX, array('name' => 'counter',
						'title' => __('bookmarks:counter'), 
						'type' => 'select', 
						'values' => array ('1' => __('bookmarks:label_yes'), '0' => __('bookmarks:label_no')), 
						'value' => intval(pluginGetVariable($plugin,'counter'))));

array_push($cfgX, array('name' => 'news_short',
						'title' => __('bookmarks:news.short'), 
						'type' => 'select', 
						'values' => array ('1' => __('bookmarks:label_yes'), '0' => __('bookmarks:label_no')), 
						'value' => intval(pluginGetVariable($plugin,'news_short'))));
						
array_push($cfgX, array('name' => 'bookmarks_limit', 
						'title' => __('bookmarks:bookmarks_limit'), 
						'type' => 'input', 
						'value' => intval(pluginGetVariable($plugin,'bookmarks_limit')) ? pluginGetVariable($plugin,'bookmarks_limit') : '100'));
						
array_push($cfg, array('mode' => 'group', 'title' => __('bookmarks:title_plugin_settings'), 'entries' => $cfgX));

$cfgX = array();
array_push($cfgX, array('name' => 'localsource',
						'title' => __('bookmarks:templates_source'), 
						'type' => 'select', 
						'values' => array ('0' => __('bookmarks:select_main_tpl'), '1' => __('bookmarks:select_plugin_tpl')), 
						'value' => intval(pluginGetVariable($plugin,'localsource'))));
						
array_push($cfg, array('mode' => 'group', 'title' => __('bookmarks:title_view'), 'entries' => $cfgX));

$cfgX = array();
array_push($cfgX, array('name' => 'cache', 
						'title' => __('bookmarks:use_cache'), 
						'type' => 'select', 
						'values' => array ( '1' => __('bookmarks:label_yes'), '0' => __('bookmarks:label_no')), 
						'value' => intval(pluginGetVariable($plugin,'cache'))));
						
array_push($cfgX, array('name' => 'cacheExpire', 
						'title' => __('bookmarks:cache_expire'), 
						'type' => 'input', 
						'value' => intval(pluginGetVariable($plugin,'cacheExpire')) ? pluginGetVariable($plugin,'cacheExpire') : '60'));
						
array_push($cfg, array('mode' => 'group', 'title' => __('bookmarks:title_cache'), 'entries' => $cfgX));

# RUN 
if ($_REQUEST['action'] == 'commit') {
	# if submit requested, do config save
	commit_plugin_config_changes($plugin, $cfg);
	print_commit_complete($plugin, $cfg);
} else {
	generate_config_page($plugin, $cfg);
}