<?php
// Protect against hack attempts
if (!defined('NGCMS')) die ('HAL');

//
// Configuration file for plugin
//

// Preload config file
pluginsLoadConfig();

// Load lang files
Lang::loadPlugin('switcher', 'config');

// Fill configuration parameters
$cfg = array();
array_push($cfg, array('descr' => __('switcher_description')));

$lang_list[] = __('switcher_bydefault');
$lang_list = array_merge($lang_list, ListFiles('lang', ''));

$tpl_list[] = __('switcher_bydefault');
$tpl_list = array_merge($tpl_list, ListFiles('../templates', ''));

$cfgX = array();
$profile_count = intval(pluginGetVariable('switcher','count'));
if (!$profile_count) { $profile_count = 3; }

array_push($cfgX, array('name' => 'count', 'title' => __('switcher_count'), 'descr' => __('switcher_count_desc'), 'type' => 'input', 'html_flags' => ' size="5"', 'value' => $profile_count));
array_push($cfgX, array('name' => 'selfpage', 'title' => __('switcher_selfpage'), 'descr' => __('switcher_selfpage_desc'), 'type' => 'select', 'values' => array ( '1' => __('yesa'), '0' => __('noa')), 'value' => pluginGetVariable('switcher','selfpage')));
array_push($cfgX, array('name' => 'localsource', 'title' => __('switcher_localsource'), 'descr' => __('switcher_localsource#desc'), 'type' => 'select', 'values' => array ( '0' => __('switcher_localsource_site'), '1' => __('switcher_localsource_plugin')), 'value' => intval(pluginGetVariable($plugin,'localsource'))));
array_push($cfg, array('mode' => 'group', 'title' => '<b>'.__('switcher_commonconfig').'</b>', 'entries' => $cfgX));

for ($i = 1; $i <= $profile_count; $i++) {
	$cfgX = array();
	array_push($cfgX, array('name' => 'profile'.$i.'_active', 'title' => __('switcher_flagactive'), 'type' => 'select', 'values' => array ( '1' => __('yesa'), '0' => __('noa')), 'value' => pluginGetVariable('switcher','profile'.$i.'_active')));
	array_push($cfgX, array('name' => 'profile'.$i.'_template', 'title' => __('switcher_template'), 'descr' => __('switcher_template_desc'), 'type' => 'select', 'values' => $tpl_list, 'value' => pluginGetVariable('switcher','profile'.$i.'_template')));
	array_push($cfgX, array('name' => 'profile'.$i.'_lang', 'title' => __('switcher_lang'), 'descr' => __('switcher_lang_desc'),'type' => 'select', 'values' => $lang_list, 'value' => pluginGetVariable('switcher','profile'.$i.'_lang')));
	array_push($cfgX, array('name' => 'profile'.$i.'_name', 'title' => __('switcher_name'), 'descr' => __('switcher_name_desc'),'type' => 'input', 'value' => pluginGetVariable('switcher','profile'.$i.'_name')));
	array_push($cfgX, array('name' => 'profile'.$i.'_id', 'title' => __('switcher_id'), 'descr' => __('switcher_id_desc'),'type' => 'input', 'value' => pluginGetVariable('switcher','profile'.$i.'_id')));
	array_push($cfgX, array('name' => 'profile'.$i.'_redirect', 'title' => __('switcher_redirect'), 'descr' => __('switcher_redirect_desc'),'type' => 'input', 'html_flags' => ' size="45"','value' => pluginGetVariable('switcher','profile'.$i.'_redirect')));
	array_push($cfgX, array('name' => 'profile'.$i.'_domains', 'title' => __('switcher_domains'), 'descr' => __('switcher_domains_desc'),'type' => 'text', 'html_flags' => 'cols=30 rows=3', 'value' => pluginGetVariable('switcher','profile'.$i.'_domains')));
	array_push($cfg, array('mode' => 'group', 'title' => '<b>'.__('switcher_profile').' №'.$i.'</b>', 'entries' => $cfgX));
}

// RUN
if ($_REQUEST['action'] == 'commit') {
	// If submit requested, do config save
	commit_plugin_config_changes($plugin, $cfg);
	print_commit_complete($plugin, $cfg);
} else {
	generate_config_page($plugin, $cfg);
}
