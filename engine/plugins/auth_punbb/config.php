<?php

/*
 * Configuration file for plugin
 */

// Protect against hack attempts
if (!defined('BBCMS')) die ('HAL');

// Load lang files
Lang::loadPlugin($plugin, 'admin', 'auth');

// Fill configuration parameters
$cfg = array(
    'description' => __('auth_description'),
    'submit' => array(
        array('type' => 'default'),
    )
    );


$cfgX = array();
array_push($cfgX, array('descr' => __('auth_extdb_fulldesc')));
array_push($cfgX, array('name' => 'extdb', 'title' => __('auth_extdb_extdb'), 'descr' => __('auth_extdb_extdb_desc'), 'type' => 'select', 'values' => array('1' => __('yesa'), '0' => __('noa')), 'value' => pluginGetVariable('auth_punbb','extdb')));
array_push($cfgX, array('name' => 'dbhost', 'title' => __('auth_extdb_dbhost'), 'type' => 'input', value => pluginGetVariable('auth_punbb','dbhost')));
array_push($cfgX, array('name' => 'dbname', 'title' => __('auth_extdb_dbname'), 'type' => 'input', value => pluginGetVariable('auth_punbb','dbname')));
array_push($cfgX, array('name' => 'dblogin', 'title' => __('auth_extdb_dblogin'), 'type' => 'input', value => pluginGetVariable('auth_punbb','dblogin')));
array_push($cfgX, array('name' => 'dbpass', 'title' => __('auth_extdb_dbpass'), 'type' => 'input', value => pluginGetVariable('auth_punbb','dbpass')));
array_push($cfg, array('mode' => 'group', 'title' => __('auth_extdb'), 'entries' => $cfgX));

$cfgX = array();
array_push($cfgX, array('name' => 'dbprefix', 'title' => __('auth_params_prefix'), 'descr' => __('auth_params_prefix_desc'), 'type' => 'input', value => pluginGetVariable('auth_punbb','dbprefix')));
array_push($cfgX, array('name' => 'cookie_seed', 'title' => __('auth_params_seed'), 'descr' => __('auth_params_seed_desc'),'type' => 'input', value => pluginGetVariable('auth_punbb','cookie_seed')));
array_push($cfgX, array('name' => 'cookie_domain', 'title' => __('auth_params_domain'), 'descr' => __('auth_params_domain_desc'),'type' => 'input', value => pluginGetVariable('auth_punbb','cookie_domain')));
array_push($cfg, array('mode' => 'group', 'title' => __('auth_params'), 'entries' => $cfgX));

$cfgX = array();
array_push($cfgX, array('name' => 'initial_group_id', 'title' => __('auth_reg_group'), 'descr' => __('auth_reg_group_desc'),'type' => 'input', value => pluginGetVariable('auth_punbb','initial_group_id')));
array_push($cfgX, array('name' => 'reg_lang', 'title' => __('auth_reg_lang'), 'descr' => __('auth_reg_lang_desc'),'type' => 'input', value => pluginGetVariable('auth_punbb','reg_lang')));
array_push($cfgX, array('name' => 'reg_style', 'title' => __('auth_reg_style'), 'descr' => __('auth_reg_style_desc'),'type' => 'input', value => pluginGetVariable('auth_punbb','reg_style')));
array_push($cfg, array('mode' => 'group', 'title' => __('auth_reg'), 'entries' => $cfgX));

$cfgX = array();
array_push($cfgX, array('name' => 'userjoin', 'title' => __('auth_auto_join'), 'descr' => __('auth_auto_join_desc'), 'type' => 'select', 'values' => array('1' => __('yesa'), '0' => __('noa')), 'value' => pluginGetVariable('auth_punbb','userjoin')));
array_push($cfgX, array('name' => 'autocreate_ng', 'title' => __('auth_auto_ng'), 'descr' => __('auth_auto_ng_desc'), 'type' => 'select', 'values' => array('1' => __('yesa'), '0' => __('noa')), 'value' => pluginGetVariable('auth_punbb','autocreate_ng')));
array_push($cfgX, array('name' => 'autocreate_punbb', 'title' => __('auth_auto_punbb'), 'descr' => __('auth_auto_punbb_desc'), 'type' => 'select', 'values' => array('1' => __('yesa'), '0' => __('noa')), 'value' => pluginGetVariable('auth_punbb','autocreate_punbb')));
array_push($cfg, array('mode' => 'group', 'title' => __('auth_auto'), 'entries' => $cfgX));

// RUN
if ('commit' == $action) {
    // If submit requested, do config save
    commit_plugin_config_changes($plugin, $cfg);
}

generate_config_page($plugin, $cfg);
