<?php

//
// Copyright (C) 2006-2014 Next Generation CMS (http://ngcms.ru)
// Name: rpc.php
// Description: Remote Procedure Call (Service functions controller)
// Author: Vitaly Ponomarev
//

@include_once 'core.php';

// Protect against hack attempts
if (!defined('NGCMS')) die ('HAL');

// Load additional handlers [ common ]
loadActionHandlers('rpc');
loadActionHandlers('rpc:'.((isset($userROW) and is_array($userROW)) ? 'active' : 'inactive'));

// We support one types of RPC calls: HTTP/JSON-RPC
if (isset($_POST['json']) and isset($_POST['methodName'])) {
    processJSON();
} else {
    print json_encode(array( 'status' => 0, 'errorCode' => 4, 'errorText' => 'Method ' . secure_html($_POST['methodName']) . ' is not supported.') );
}

// HTTP/JSON-RPC processor
function processJSON(){
    global $RPCFUNC, $RPCADMFUNC;

    // Set correct content/type
    @header('Content-Type: application/json; charset=UTF-8', true);

    // Scan and Decode incoming params
    if (isset($_POST['uploadType'])) { // To upload files, images !!!
        $params = $_POST;
    } else if (!empty($_POST['params'])) {
        $params = json_decode($_POST['params'], true);
        if(json_last_error()) {
            print json_encode(array( 'status' => 0, 'errorCode' => '-1', 'errorText' => json_last_error()) );
            coreNormalTerminate(1);
            exit;
        }
    } else {
        print json_encode(array( 'status' => 0, 'errorCode' => 4, 'errorText' => __('wrong_params_type') ));
        coreNormalTerminate(1);
        exit;
    }

    $methodName = $_POST['methodName'];

    // Check for permissions from ajax
    if(!isset($_SERVER['HTTP_X_REQUESTED_WITH']) or 'xmlhttprequest' != strtolower($_SERVER['HTTP_X_REQUESTED_WITH'])) {
        ngSYSLOG(array('plugin' => '#admin', 'item' => 'RPC'), array('action' => $methodName), null, array(0, 'Non ajax request'));
        print json_encode(array( 'status' => 0, 'errorCode' => -1, 'errorText' => __('access_denied') ));
        coreNormalTerminate(1);
        exit;
    }

    switch ($methodName) {
        case 'admin.rewrite.submit': $out = rpcRewriteSubmit($params); break;
        case 'core.users.search': $out = rpcAdminUsersSearch($params); break;
        case 'core.registration.checkParams': $out = coreCheckRegParams($params);break;
        case 'core.system.update': $manager = new CSystemUpdate($params); $out = $manager->execute();break;
        default:
            // Load CORE Plugin
            $cPlugin = CPlugin::instance();
            if (isset($RPCFUNC[$methodName])) {
                $out = call_user_func($RPCFUNC[$methodName], $params);
            } else if (preg_match('#^plugin\.(.+?)\.#', $methodName, $m) and $cPlugin->loadPlugin($m[1], 'rpc') and isset($RPCFUNC[$methodName])) {
                // If method "plugin.NAME.something" is called, try to load action "rpc" for plugin "NAME"
                $out = call_user_func($RPCFUNC[$methodName], $params);
            } else if (preg_match('#^admin\.(.+?)\.#', $methodName, $m) and loadAdminRPC($m[1]) and isset($RPCADMFUNC[$methodName])) {
                // If method "plugin.NAME.something" is called, try to load action "rpc" for plugin "NAME"
                $out = call_user_func($RPCADMFUNC[$methodName], $params);
            } else {
                $out = rpcUnknown($methodName, $params);
            }
            break;
    }
    $out = json_encode($out);
    if(json_last_error()) {
        $out = json_encode(array( 'status' => 0, 'errorCode' => 0, 'errorText' => json_last_error()) );
    }

    print $out;
    coreNormalTerminate(1);
    exit;
}

// Method is unknown
function rpcUnknown($methodName = '', $params = array()) {
    return array('status' => 0, 'errorCode' => 1, 'errorText' => 'rpcUnknown: method ['.$methodName.'] is unknown');
}

// Function to preload ADMIN rpc funcs
function loadAdminRPC($mod) {
    if (in_array($mod, array('categories', 'news', 'extras', 'files', 'templates', 'configuration'))) {
        @include_once('./actions/'.$mod.'.rpc.php');
        return true;
    }
    return false;
}

// Register RPC ADMIN function
function rpcRegisterAdminFunction($name, $instance, $permanent = false) {
    global $RPCADMFUNC;
    $RPCADMFUNC[$name] = $instance;
}

//
// RPC function: rewrite.submit
// Description : Submit changes into REWRITE library
function rpcRewriteSubmit($params) {
    global $userROW;

    // Check for permissions
    if (!checkPermission(array('plugin' => '#admin', 'item' => 'rewrite'), null, 'modify')) {
        ngSYSLOG(array('plugin' => '#admin', 'item' => 'rewrite'), array('action' => 'modify'), null, array(0, 'SECURITY.PERM'));
        coreNormalTerminate(1);
        return array('status' => 0, 'errorCode' => 3, 'errorText' => 'Access denied (perm)');
    }

    // Check for security token
    if ((!isset($params['token'])) or ($params['token'] != genUToken('admin.rewrite'))) {
        ngSYSLOG(array('plugin' => '#admin', 'item' => 'rewrite'), array('action' => 'modify'), null, array(0, 'SECURITY.TOKEN'));
        coreNormalTerminate(1);
        return array('status' => 0, 'errorCode' => 3, 'errorText' => 'Access denied (token)');
    }
    unset($params['token']);
    
    $ULIB = new UrlLibrary();
    $ULIB->loadConfig();

    $UHANDLER = new UrlHandler();
    $UHANDLER->loadConfig();

    $hList = array();

    // Scan all params
    foreach ($params as $pID => $pData) {
            // Skip empty elements
            if ($pData == NULL)
                continue;

            $rcall = $UHANDLER->populateHandler($ULIB, $pData);
            if ($rcall[0][0]) {
                // Error
                return array('status' => 0, 'errorCode' => 4, 'errorText' => 'Parser error: '.$rcall[0][1], 'recID' => $pID);
            }
            $hList[] = $rcall[1];
    }

    // Now let's overwrite current config
    $UHANDLER->hList = array();
    foreach ($hList as $handler) {
        $UHANDLER->registerHandler(-1, $handler);
    }
    if (!$UHANDLER->saveConfig()) {
        return array('status' => 0, 'errorCode' => 5, 'errorText' => 'Error writing to disk');
    }

    ngSYSLOG(array('plugin' => '#admin', 'item' => 'rewrite'), array('action' => 'modify', 'list' => $params), null, array(1, ''));

    return array('status' => 1, 'errorCode' => 0, 'errorText' => var_export($rcall[1], true));
}

// Admin panel: search for users
function rpcAdminUsersSearch($params){
    global $userROW, $mysql;

    // Check for permissions
    if (!is_array($userROW) or ($userROW['status'] > 3)) {
        // ACCESS DENIED
        return array('status' => 0, 'errorCode' => 3, 'errorText' => 'Access denied');
    }

    $searchName = $params;

    // Check search mode
    // ! - show TOP users by posts
    if ($searchName == '!') {
        $SQL = 'select name, news from '.uprefix.'_users where news > 0 order by news desc limit 20';
    } else {
        // Return a list of users
        $SQL = 'select name, news from '.uprefix.'_users where name like '.db_squote('%'.$searchName.'%').' and news > 0 order by news desc limit 20';
    }

    // Scan incoming params
    $output = array();
    foreach ($mysql->select($SQL) as $row) {
        $output[] = array($row['name'], $row['news'] . ' новостей');
    }

    return array('status' => 1, 'errorCode' => 0, 'data' => array($params, $output));
}

// Online check if registration params are correct (login, email,...)
function coreCheckRegParams($params){
    global $config, $AUTH_METHOD;

    // Scan incoming params
    if (!is_array($params)) {
        return array('status' => 0, 'errorCode' => 999, 'errorText' => 'Wrong params type');
    }

    $auth = $AUTH_METHOD[$config['auth_module']];
    if (method_exists($auth, 'onlineCheckRegistration')) {
        $output = $auth->onlineCheckRegistration($params);
        return array('status' => 1, 'errorCode' => 0, 'data' => $output);
    }
    return array('status' => 0, 'errorCode' => 999, 'errorText' => 'Method "onlineCheckRegistration" does not exists');
}
