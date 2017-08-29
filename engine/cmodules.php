<?php

//
// Copyright (C) 2006-2015 Next Generation CMS (http://ngcms.ru/)
// Name: cmodules.php
// Description: Common CORE modules
// Author: Vitaly Ponomarev
//

// Protect against hack attempts
if (!defined('NGCMS')) die ('HAL');

function coreActivateUser()
{
    global $config, $SYSTEM_FLAGS, $mysql, $CurrentHandler;

    Lang::load('activation', 'site');
    $SYSTEM_FLAGS['info']['title']['group']	= __('loc_activation');

    if (($CurrentHandler['pluginName'] == 'core') and 
        ($CurrentHandler['handlerName'] == 'activation')) {
        $userid = isset($CurrentHandler['params']['userid']) ? $CurrentHandler['params']['userid']:$_REQUEST['userid'];
        $code = isset($CurrentHandler['params']['code']) ? $CurrentHandler['params']['code']:$_REQUEST['code'];
    } else {
        $userid = $_REQUEST['userid'];
        $code = $_REQUEST['code'];
    }

    // Check if user exists with ID = $userid
    if (is_array($uRow = $mysql->record("select * from ".prefix."_users where id=".db_squote($userid)))) {
        // User found. Check activation.
        if ($uRow['activation']) {
            if ($uRow['activation'] == $code) {
                // Yeah, activate user!
                $mysql->query("update `".uprefix."_users` set activation = '' where id = ".db_squote($userid));

                msg(array('title' => __('activated_title'), 'message' => __('msgo_activated')), 1, 3);
                $SYSTEM_FLAGS['module.usermenu']['redirect'] = $config['home_url'].'/';
            } else {
                // Incorrect activation code
                msg(array('title' => __('activated_title'), 'message' => __('msge_activate')), 1, 3);
            }
        } else {
            // Already activated
            msg(array('title' => __('activated_title'), 'message' => __('msge_activation')), 1, 3);
        }
    } else {
        // User not found
        error404();
    }
}

function coreRegisterUser()
{
    global $ip, $config, $AUTH_METHOD, $SYSTEM_FLAGS, $userROW, $PFILTERS, $mysql;

    Lang::load('registration', 'site');
    $SYSTEM_FLAGS['info']['title']['group']	= __('loc_registration');

    // If logged in user comes to us - REDIRECT him to main page
    if (is_array($userROW)) {
            @header('Location: '.$config['home_url']);
            return;
    }

    // Check for ban
    if ($ban_mode = checkBanned($ip, 'users', 'register', $userROW, $userROW['name'])) {
        msg(array('type' => 'danger', 'message' => ($ban_mode == 1)?__('register.banned'):__('msge_regforbid')));
        return;
    }

    if (empty($_REQUEST['type']) and $config['users_selfregister']) {
        // Receiving parameter list during registration
        $auth = $AUTH_METHOD[$config['auth_module']];
        $params = $auth->get_reg_params();
        generate_reg_page($params);
    } elseif (!empty($_REQUEST['type']) and $_REQUEST['type'] == "doregister" and $config['users_selfregister']) {
        // Receiving parameter list during registration
        $auth = $AUTH_METHOD[$config['auth_module']];
        $params = $auth->get_reg_params();
        $values = array();

        foreach ($params as $param) {
            $values[$param['name']] = $_POST[$param['name']];
        }

        $msg = '';

        // Check captcha
        if ($config['use_captcha']) {
            $captcha = md5($_POST['captcha']);
            if (!$captcha or ($_SESSION['captcha'] != $captcha)) {
                // Fail
                $msg = __('msge_captcha');
            }
        }

        // Execute filters - check if user is allowed to register
        if ((!$msg) and isset($PFILTERS['core.registerUser']) and is_array($PFILTERS['core.registerUser']))
            foreach ($PFILTERS['core.registerUser'] as $k => $v) {
                if (!$v->registerUser($params, $msg)) {
                    break;
                }
            }

        // Trying register
        if (!$msg and ($uid = $auth->register($params, $values, $msg))) {
            // OK, fetch user record
            if ($uid > 1) {
                // ** COMPAT: exec action only if $uid > 1
                $urec = $mysql->record("select * from ".uprefix."_users where id = ".intval($uid));

                // LOG: Successully registered
                ngSYSLOG(array('plugin' => 'core', 'item' => 'register'), array('action' => 'register'), $urec, array(1, ''));

                // Execute filters - add additional variables
                if (is_array($urec) and isset($PFILTERS['core.registerUser']) and is_array($PFILTERS['core.registerUser']))
                    foreach ($PFILTERS['core.registerUser'] as $k => $v) { $v->registerUserNotify($uid, $urec); }
            }
        } else {
            // LOG: Registration failed
            ngSYSLOG(array('plugin' => 'core', 'item' => 'register'), array('action' => 'register'), 0, array(0, 'Registration failed'));

            // Fail
            generate_reg_page($params, $values, $msg);
        }
    } else {
        msg(array('type' => 'danger', 'message' => __('msge_regforbid')), 1, 3);
    }
}

// Registration page generation
function generate_reg_page($params, $values = array(), $msg = '')
{
    global $template, $config, $PFILTERS, $twig, $twigLoader;

    $tVars = array(
        'entries' => array(),
        'flags' => array(),
    );
    if ($msg) {
        msg(array('type' => 'danger', 'message' => $msg));
    }

    // prepare variable list
    foreach($params as $param) {
        $tVars['entries'][] = mkParamLine($param);
    }

    // Execute filters - add additional variables
    if (isset($PFILTERS['core.registerUser']) and is_array($PFILTERS['core.registerUser'])) {
        foreach ($PFILTERS['core.registerUser'] as $k => $v) {
            $v->registerUserForm($tVars);
        }
    }

    $tVars['form_action'] = checkLinkAvailable('core', 'registration')?
                                generateLink('core', 'registration', array()):
                                generateLink('core', 'plugin', array('plugin' => 'core', 'handler' => 'registration'));
    $xt = $twig->loadTemplate('registration.tpl');
    $template['vars']['mainblock'] .= $xt->render($tVars);
}

function coreRestorePassword()
{
    global $userROW, $config, $AUTH_METHOD, $SYSTEM_FLAGS, $mysql, $CurrentHandler;

    Lang::load('lostpassword', 'site');
    $SYSTEM_FLAGS['info']['title']['group']	= __('loc_lostpass');

    if (is_array($userROW)) {
        @header('Location: '.$config['home_url']);
        return;
    }

    if (($CurrentHandler['pluginName'] == 'core') and 
        ($CurrentHandler['handlerName'] == 'lostpassword')) {
        $userid = isset($CurrentHandler['params']['userid']) ? $CurrentHandler['params']['userid'] : (isset($_REQUEST['userid']) ? $_REQUEST['userid'] : null);
        $code = isset($CurrentHandler['params']['code'])?$CurrentHandler['params']['code'] : (isset($_REQUEST['code']) ? $_REQUEST['code'] : null);
    } else {
        $userid = isset($_REQUEST['userid']) ? $_REQUEST['userid'] : null;
        $code = isset($_REQUEST['code']) ? $_REQUEST['code'] : null;
    }

    // Confirmation
    if ($userid and $code) {
        $auth = $AUTH_METHOD[$config['auth_module']];
        $msg = '';

        if ($auth->confirm_restorepw($msg, $userid, $code)) {
            // OK
            msg(array('message' => $msg), 1, 3);
        } else {
            // Fail
            msg(array('type' => 'danger', 'message' => $msg), 1, 3);
        }
    } elseif (isset($_REQUEST['type']) and $_REQUEST['type'] == 'send') {
        // PROCESSING REQUEST

        // Receiving parameter list during password recovery
        $auth = $AUTH_METHOD[$config['auth_module']];
        $params = $auth->get_restorepw_params();
        $values = array();

        foreach ($params as $param) {
            if (isset($param['name']) and isset($_POST[$param['name']])) {
                $values[$param['name']] = $_POST[$param['name']];
            }
        }

        $msg = '';

        // Check captcha
        if ($config['use_captcha']) {
            $captcha = md5($_REQUEST['captcha']);
            if (!$captcha or ($_SESSION['captcha'] != $captcha)) {
                // Fail
                $msg = __('msge_captcha');
            }
        }

        // Trying password recovery
        if (($msg == '') and $auth->restorepw($params, $values, $msg)) {
            // OK
            // ...
        } else {
            // Fail and reloading page
            generate_restorepw_page($params, $values, $msg);
        }
    } else {
        // DEFAULT: SHOW RESTORE PW SCREEN

        // Receiving parameter list during password recovery
        $auth = $AUTH_METHOD[$config['auth_module']];
        $params = $auth->get_restorepw_params();

        if (!is_array($params)) {
            msg(array('type' => 'danger', 'message' => __('msge_lpforbid')), 1, 3);
            return;
        }
        generate_restorepw_page($params);

    }
}

// Registration page generation
function generate_restorepw_page($params, $values = array(), $msg = '')
{
    global $template, $config, $PFILTERS, $twig, $twigLoader;

    $tVars = array(
        'entries' => array(),
        'flags' => array(),
    );
    if ($msg) {
        msg(array('type' => 'danger', 'message' => $msg));
    }

    // prepare variable list
    foreach($params as $param) {
        if (isset($param['text'])) {
            $tVars['text'] = $param['text'];
            unset($param);
        } else {
            $tVars['entries'][] = mkParamLine($param);
        }
    }

    $tVars['form_action'] = checkLinkAvailable('core', 'lostpassword')?
                                        generateLink('core', 'lostpassword', array()):
                                        generateLink('core', 'plugin', array('plugin' => 'core', 'handler' => 'lostpassword'));
    $xt = $twig->loadTemplate('lostpassword.tpl');
    $template['vars']['mainblock'] .= $xt->render($tVars);
}

//
// Execute an action for coreLogin() function
// This is a workaround for 2-stage AUTH functions (like openID)
// Parameter: user's record [row]
function coreLoginAction($row = null, $redirect = null)
{
    global $auth, $auth_db, $username, $userROW, $is_logged, $is_logged_cookie, $SYSTEM_FLAGS, $HTTP_REFERER;
    global $twig, $twigLoader, $template, $config, $ip;

    Lang::load('login', 'site');
    $SYSTEM_FLAGS['info']['title']['group'] = __('loc_login');

    // Try to auth and check for bans
    if (is_array($row)) {

        $auth_db->save_auth($row);
        $username = $row['name'];
        $userROW = $row;
        $is_logged_cookie = true;
        $is_logged = true;

        // LOG: Successully logged in
        ngSYSLOG(array('plugin' => 'core', 'item' => 'login'), array('action' => 'login', 'list' => array('login' => $username)), NULL, array(1, ''));

        // Redirect back
        @header('Location: '.($redirect ? $redirect : home));
    } else {
        // LOG: Login error
        ngSYSLOG(array('plugin' => 'core', 'item' => 'login'), array('action' => 'login', 'list' => array('errorInfo' => $row)), NULL, array(0, 'Login failed.'));

        $SYSTEM_FLAGS['auth_fail'] = 1;
        $params = $auth->get_login_params();

        if ($row == 'ERR:NOT_ENTERED') {
            
        } elseif (preg_match ('/^ERR:NEED.ACTIVATE/', $row, $null)) {
            $msg = __('login.need.activate');
        } elseif (preg_match ('/^ERR:INVALID.USER/', $row, $null)) {
            $msg = __('auth_nouser');
        } elseif (preg_match ('/^ERR:USER.BANNED/', $row, $null)) {
            $msg = __('login.banned');
        } else {
            $msg = __('login.error');
        }

        if (!empty($msg)) {
            msg(array('type' => 'danger', 'message' => $msg));
        }

        $tVars = array(
            'entries' => array(),
            'flags' => array(),
            'form_action' => generateLink('core', 'login'),
            'redirect' => isset($_POST['redirect']) ? $_POST['redirect'] : (preg_match('#^(http|https)\:\/\/#', $HTTP_REFERER, $tmp) ? $HTTP_REFERER : $config['home_url']),
        );

        // prepare variable list
        foreach($params as $param) {
            $tVars['entries'][] = mkParamLine($param);
        }

        $xt = $twig->loadTemplate('login.tpl');
        $template['vars']['mainblock'] .= $xt->render($tVars);
    }
}

function coreLogin()
{
    global $auth, $auth_db, $username, $userROW, $is_logged, $is_logged_cookie, $SYSTEM_FLAGS, $HTTP_REFERER;
    global $tpl, $template, $config, $ip;

    // If user ALREADY logged in - redirect to main page
    if (is_array($userROW)) {
        @header('Location: '.$config['home_url']);
        return;
    }

    // Determine redirect point
    // If POST fiels (ONLY POST) 'redirect' is set - redirect
    $redirect = '';
    if (isset($_POST['redirect']) and $_POST['redirect']) {
        $redirect = $_POST['redirect'];
    } else if (isset($_REQUEST['redirect_home']) and $_REQUEST['redirect_home']) {
        $redirect = $config['home_url'];
    } else if (preg_match('#^(http|https)\:\/\/#', $HTTP_REFERER, $tmp)) {
        $redirect = $HTTP_REFERER;
    } else {
        $redirect = $config['home_url'];
    }

    // Auth can work ONLY via POST method
    if ($_SERVER['REQUEST_METHOD'] != 'POST') {
        coreLoginAction('ERR:NOT_ENTERED');
        return;
    }

    // Try to auth
    $row = $auth->login();
    coreLoginAction($row, $redirect);
}

function coreLogout()
{
    global $auth_db, $userROW, $username, $is_logged, $HTTP_REFERER, $config;

    $auth_db->drop_auth();
    @header("Location: ".(preg_match('#^(http|https)\:\/\/#', $HTTP_REFERER, $tmp) ? $HTTP_REFERER : $config['home_url']));

    // if header(); does not work
    unset($userROW);
    unset($username);
    $is_logged = false;
}
