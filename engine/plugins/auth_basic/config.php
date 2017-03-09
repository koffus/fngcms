<?php

// Protect against hack attempts
if (!defined('NGCMS')) die ('HAL');

//
// Configuration file for plugin
//

// Preload config file
pluginsLoadConfig();

Lang::load('users', 'admin');

// Load lang files
Lang::loadPlugin('auth_basic', 'config', '', 'auth', ':');

// Default user group for new users
$regGroup = intval(pluginGetVariable('auth_basic','regstatus'));
if (!isset($UGROUP[$regGroup])) {
	// If GROUP is not defined - set "4" as default
	$regGroup = 4; // Commenter
}
$groupOptions = array();
foreach ($UGROUP as $k => $v) {
	$groupOptions[$k] = $k . ' - '. $v['name'];
}

$lastupdate = intval(pluginGetVariable('auth_basic', 'lastupdate'));
if ($lastupdate<1) { $lastupdate = ''; }

// Fill configuration parameters
$cfg = array();
$cfgX = array();
array_push($cfg, array('descr' => __('auth:description')));
array_push($cfgX, array('name' => 'lastupdate', 'title' => __('auth:lastupdate'), 'descr' => __('auth:lastupdate_descr'),'type' => 'input', value => $lastupdate));
array_push($cfgX, array('name' => 'regstatus', 'title' => __('auth:regstatus'), 'descr' => __('auth:regstatus_descr'),'type' => 'select', 'values' => $groupOptions, value => $regGroup));
array_push($cfgX, array('name' => 'restorepw', 'title' => __('auth:restorepw'), 'descr' => __('auth:restorepw_descr'),'type' => 'select', 'values' => array ( '0' => __('auth:restore_disabled'), 'login' => __('auth:restore_login'), 'email' => __('auth:restore_email'), 'both' => __('auth:restore_both')), value => pluginGetVariable('auth_basic','restorepw')));
array_push($cfgX, array('name' => 'regcharset', 'title' => __('auth:regcharset'), 'descr' => __('auth:regcharset_descr'),'type' => 'select', 'values' => array ( '0' => 'Eng', '1' => 'Rus', '2' => 'Eng+Rus', '3' => 'All'), value => pluginGetVariable('auth_basic','regcharset')));
array_push($cfgX, array('name' => 'iplock', 'title' => __('auth:iplock'), 'descr' => __('auth:iplock_descr'),'type' => 'select', 'values' => array ( '0' => __('noa'), '1' => __('yesa')), value => pluginGetVariable('auth_basic','iplock')));
array_push($cfgX, array('name' => 'multilogin', 'title' => __('auth:multilogin'), 'descr' => __('auth:multilogin_descr'),'type' => 'select', 'values' => array ( '0' => __('noa'), '1' => __('yesa')), value => pluginGetVariable('auth_basic','multilogin')));
array_push($cfg, array('mode' => 'group', 'title' => '<b>'.__('auth:block.main').'</b>', 'entries' => $cfgX));

$cfgX = array();
array_push($cfgX, array('name' => 'en_dbprefix', 'title' => __('auth:en_dbprefix'), 'descr' => __('auth:en_dbprefix_descr'),'type' => 'checkbox', value => pluginGetVariable('auth_basic','en_dbprefix')));
array_push($cfgX, array('name' => 'dbprefix', 'title' => __('auth:dbprefix'), 'descr' => __('auth:dbprefix_descr'),'type' => 'input', value => pluginGetVariable('auth_basic','dbprefix')));
array_push($cfg, array('mode' => 'group', 'title' => '<b>'.__('auth:block.uprefix').'</b>', 'entries' => $cfgX));

// RUN
if ($_REQUEST['action'] == 'commit') {
	// If submit requested, do config save
	commit_plugin_config_changes('auth_basic', $cfg);
	print_commit_complete($plugin, $cfg);
} else {
	generate_config_page('auth_basic', $cfg);
}

