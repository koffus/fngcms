<?php

//
// Copyright (C) 2006-2014 Next Generation CMS (http://ngcms.ru/)
// Name: extras.rpc.php
// Description: Externally available library for EXTRAS manipulation
// Author: Vitaly Ponomarev
//

// Protect against hack attempts
if (!defined('NGCMS')) die ('HAL');

header('Content-Type: text/html; charset=utf-8');
setlocale(LC_ALL, 'ru_RU.UTF-8');

// Load library
Lang::load('extras', 'admin');

// ////////////////////////////////////////////////////////////////////////////
// Processing functions :: show list of categories
// ///////////////////////////////////////////////////////////////////////////

function admExtrasGetConfig($params)
{
    global $userROW, $mysql;

    // Check for permissions
    if (!checkPermission(array('plugin' => '#admin', 'item' => 'extras'), null, 'modify')) {
        // ACCESS DENIED
        return array('status' => 0, 'errorCode' => 3, 'errorText' => 'Access denied');
    }

    // Check for permissions
    if (!is_array($userROW) or ($userROW['status'] != 1)) {
        // ACCESS DENIED
        return array('status' => 0, 'errorCode' => 3, 'errorText' => 'Access denied');
    }

    // Check for security token
    if ($params['token'] != genUToken('admin.extras')) {
        return array('status' => 0, 'errorCode' => 5, 'errorText' => 'Wrong security code');
    }

    // Load CORE Plugin
    $cPlugin = CPlugin::instance();
    // Load of plugins configurations
    $pConfig = $cPlugin->getConfig();

    $confLine = json_encode($pConfig);
    $confLine = jsonFormatter($confLine);

    return (array('status' => 1, 'errorCode' => 0, 'errorText' => 'Ok', 'content' => $confLine));

}

// Plugins switch ON/OFF
function admExtrasOnOff($params)
{
    global $userROW, $mysql;

    // Check for permissions
    if (!checkPermission(array('plugin' => '#admin', 'item' => 'extras'), null, 'modify')) {
        // ACCESS DENIED
        return array('status' => 0, 'errorCode' => 3, 'errorText' => 'Access denied');
    }

    // Check for permissions
    if (!is_array($userROW) or ($userROW['status'] != 1)) {
        // ACCESS DENIED
        return array('status' => 0, 'errorCode' => 3, 'errorText' => 'Access denied');
    }

    // Check for security token
    if ($params['token'] != genUToken('admin.extras')) {
        return array('status' => 0, 'errorCode' => 5, 'errorText' => 'Wrong security code');
    }

    // Check if plugin name is specified
    if (!$params['plugin'] or !$params['state']) {
        return array('status' => 0, 'errorCode' => 6, 'errorText' => 'Plugin name or state is not specified');
    }

    // Load CORE Plugin
    $cPlugin = CPlugin::instance();
    // Load plugin list  
    $extras = $cPlugin->getInfo();
    if (!isset($extras[$params['plugin']])) {
        return array('status' => 0, 'errorCode' => 7, 'errorText' => 'Plugin [' . $params['plugin'] . ' is not found');
    }

    // Manage `ON` call
    if ($params['state'] == 'on') {
        if (pluginSwitch($params['plugin'], 'on')) {
            ngSYSLOG(array('plugin' => '#admin', 'item' => 'extras'), array('action' => 'switch_on', 'list' => array('plugin' => $params['plugin'])), null, array(1, ''));
            return array('status' => 1, 'errorCode' => 0, 'errorText' => 'Ok', 'content' => 'Plugin [' . $params['plugin'] . ']is switched on');
        } else {
            // generate error message
            ngSYSLOG(array('plugin' => '#admin', 'item' => 'extras'), array('action' => 'switch_on', 'list' => array('plugin' => $params['plugin'])), null, array(0, 'ERROR: ' . $params['state']));
            return array('status' => 0, 'errorCode' => 8, 'errorText' => 'Error turning plugin [' . $params['plugin'] . '] on');
        }
    }

    if ($params['state'] == 'off') {
        if ($extras[$params['plugin']]['permanent']) {
            ngSYSLOG(array('plugin' => '#admin', 'item' => 'extras'), array('action' => 'switch_off', 'list' => array('plugin' => $params['plugin'])), null, array(0, 'ERROR: PLUGIN is permanent ' . $params['state']));
            return array('status' => 0, 'errorCode' => 9, 'errorText' => 'Cannot turn off permanent plugin [' . $params['plugin'] . ']');
        } else {
            if (pluginSwitch($params['plugin'], 'off')) {
                ngSYSLOG(array('plugin' => '#admin', 'item' => 'extras'), array('action' => 'switch_off', 'list' => array('plugin' => $params['plugin'])), null, array(1, ''));
                return array('status' => 1, 'errorCode' => 0, 'errorText' => 'Ok', 'content' => 'Plugin [' . $params['plugin'] . ']is switched on');
            } else {
                ngSYSLOG(array('plugin' => '#admin', 'item' => 'extras'), array('action' => 'switch_on', 'list' => array('plugin' => $params['plugin'])), null, array(0, 'ERROR: ' . $params['state']));
                return array('status' => 0, 'errorCode' => 10, 'errorText' => 'Error turning plugin [' . $params['plugin'] . '] off');
            }
        }
    }
    return array('status' => 0, 'errorCode' => 11, 'errorText' => 'Unknown state');
}

if (function_exists('rpcRegisterAdminFunction')) {
    rpcRegisterAdminFunction('admin.extras.getPluginConfig', 'admExtrasGetConfig');
    rpcRegisterAdminFunction('admin.extras.switch', 'admExtrasOnOff');
}
