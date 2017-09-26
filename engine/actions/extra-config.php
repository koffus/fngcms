<?php

//
// Copyright (C) 2006-2017 BixBite CMS (http://bixbite.site/)
// Name: extra-config.php
// Description: Plugin managment
// Author: Vitaly Ponomarev
//

// Protect against hack attempts
if (!defined('BBCMS')) die ('HAL');

@include_once root . 'includes/inc/extraconf.inc.php';

// ==============================================================
// Main module code
// ==============================================================

// Load CORE Plugin
$cPlugin = CPlugin::instance();
// Load plugin list  
$extras = $cPlugin->getInfo();

// Load lang files
Lang::load('extras', 'admin');
Lang::load('extra-config', 'admin');

// Load passed variables:
// ID of called plugin
$plugin = isset($_REQUEST['plugin']) ? $_REQUEST['plugin'] : false;

if ($plugin and 'clearCacheFiles' == $action) {
    clearCacheFiles($plugin);
}

// Type of script to call ( install / deinstall / config )
$stype = isset($_REQUEST['stype']) ? $_REQUEST['stype'] : false;
// Call 'install'/'deinstall' script if it's requested. Else - call config script.
if (('install' != $stype) and ('deinstall' != $stype)) {
    $stype = 'config';
}

if (!is_array($extras[$plugin])) {

    $tVars = array(
        'action' => __('config_text'),
        'action_text' => __('noplugin'),
        'plugin' => $plugin,
        'php_self' => $PHP_SELF
    );

    echo $twig->render(tpl_actions . 'extra-config/nomodule.tpl', $tVars);

} else {

    // Check if such type of script is configured in plugin & exists
    if (!empty($extras[$plugin][$stype]) and is_file($cfg_file = extras_dir . '/' . $extras[$plugin]['dir'] . '/' . $extras[$plugin][$stype])) {

        // Security update: for stype == 'config' and POST update action - check for token
        if (('config' == $stype) and ('commit' == $action) and ($_REQUEST['token'] != genUToken('admin.extra-config'))) {
            msg(array('type' => 'danger', 'title' => __('error.security.token'), 'message' => __('error.security.token#desc')));
            ngSYSLOG(array('plugin' => '#admin', 'item' => 'config#' . $plugin), array('action' => 'modify'), null, array(0, 'SECURITY.TOKEN'));
            exit;
        }

        // Include required script file
        include $cfg_file;

        // Run install function if it exists in file
        if (($stype == 'install') and function_exists('plugin_' . $plugin . '_install')) {
            call_user_func('plugin_' . $plugin . '_install', ('commit' == $action) ? 'apply' : 'confirm');
        }

    } else {
        $tVars = array(
            'action' => __($stype . '_text'),
            'action_text' => __('nomod_' . $stype),
            'plugin' => $plugin,
            'php_self' => $PHP_SELF
        );
        echo $twig->render(tpl_actions . 'extra-config/nomodule.tpl', $tVars);
    }
}
