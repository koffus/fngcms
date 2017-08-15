<?php

// Protect against hack attempts
if (!defined('NGCMS')) die ('HAL');

//
// Configuration file for plugin
//

// Load lang files
Lang::load('users', 'admin');
Lang::loadPlugin($plugin, 'config', '', ':');

// Prepare configuration parameters
// Default user group for new users
$regGroup = intval(pluginGetVariable($plugin, 'regstatus'));
if (!isset($UGROUP[$regGroup])) {
    // If GROUP is not defined - set "4" as default, Commenter
    $regGroup = 4;
}

$groupOptions = array();
foreach ($UGROUP as $k => $v) {
    $groupOptions[$k] = $k . ' - '. $v['name'];
}

$lastupdate = intval(pluginGetVariable($plugin, 'lastupdate'));
if ($lastupdate<1) 
    $lastupdate = '';

// Fill configuration parameters
$cfg = array('description' => __($plugin.':description'));

$cfgX = array();
    array_push($cfgX, array(
        'name' => 'lastupdate',
        'title' => __($plugin.':lastupdate'),
        'descr' => __($plugin.':lastupdate#descr'),
        'type' => 'input',
        'value' => $lastupdate,
        ));
    array_push($cfgX, array(
        'name' => 'regstatus',
        'title' => __($plugin.':regstatus'),
        'descr' => __($plugin.':regstatus#descr'),
        'type' => 'select',
        'values' => $groupOptions,
        'value' => $regGroup,
        ));
    array_push($cfgX, array(
        'name' => 'restorepw',
        'title' => __($plugin.':restorepw'),
        'descr' => __($plugin.':restorepw#descr'),
        'type' => 'select',
        'values' => array (
            '0' => __($plugin.':restore_disabled'),
            'login' => __($plugin.':restore_login'),
            'email' => __($plugin.':restore_email'),
            'both' => __($plugin.':restore_both')),
            'value' => pluginGetVariable($plugin,'restorepw'),
            ));
    array_push($cfgX, array(
        'name' => 'regcharset',
        'title' => __($plugin.':regcharset'),
        'descr' => __($plugin.':regcharset#descr'),
        'type' => 'select',
        'values' => array (
            '0' => 'Eng',
            '1' => 'Rus',
            '2' => 'Eng+Rus',
            '3' => 'All',
            ),
        'value' => pluginGetVariable($plugin,'regcharset'),
        ));
    array_push($cfgX, array(
        'name' => 'iplock',
        'title' => __($plugin.':iplock'),
        'descr' => __($plugin.':iplock#descr'),
        'type' => 'select',
        'values' => array('0' => __('noa'), '1' => __('yesa')),
        'value' => pluginGetVariable($plugin,'iplock'),
        ));
    array_push($cfgX, array(
        'name' => 'multilogin',
        'title' => __($plugin.':multilogin'),
        'descr' => __($plugin.':multilogin#descr'),
        'type' => 'select',
        'values' => array('0' => __('noa'), '1' => __('yesa')),
        'value' => pluginGetVariable($plugin,'multilogin'),
        ));
array_push($cfg, array(
    'mode' => 'group',
    'title' => __('group.config'),
    'entries' => $cfgX,
    ));

$cfgX = array();
    array_push($cfgX, array(
        'name' => 'en_dbprefix',
        'title' => __($plugin.':en_dbprefix'),
        'descr' => __($plugin.':en_dbprefix#descr'),
        'type' => 'checkbox',
        'value' => pluginGetVariable($plugin,'en_dbprefix'),
        ));
    array_push($cfgX, array(
        'name' => 'dbprefix',
        'title' => __($plugin.':dbprefix'),
        'descr' => __($plugin.':dbprefix#descr'),
        'type' => 'input',
        'value' => pluginGetVariable($plugin,'dbprefix'),
        ));
array_push($cfg, array(
    'mode' => 'group',
    'title' => __($plugin.':block.uprefix'),
    'entries' => $cfgX,
    ));

// RUN
if ('commit' == $action) {
    // If submit requested, do config save
    commit_plugin_config_changes($plugin, $cfg);
}

generate_config_page($plugin, $cfg);
