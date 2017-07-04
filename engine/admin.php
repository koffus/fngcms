<?php

//
// Copyright (C) 2006-2016 Next Generation CMS (http://ngcms.ru)
// Name: admin.php
// Description: administration panel
// Author: Vitaly Ponomarev, Alexey Zinchenko
//

// Call debug from PhpConsole\Handler
require_once('../PhpConsole/__autoload.php');
$handler = PhpConsole\Handler::getInstance();
$handler->start();

// Override charset
header('Content-type: text/html; charset=UTF-8');
ini_set('default_charset', 'UTF-8');
@ini_set('mbstring.internal_encoding', 'UTF-8'); // Deprecated PHP => 5.6
@ini_set('iconv.internal_encoding', 'UTF-8'); // Deprecated PHP => 5.6
ini_set('php.internal_encoding', 'UTF-8');
if (function_exists('mb_internal_encoding'))
    mb_internal_encoding('UTF-8');
if (function_exists('mb_regex_encoding'))
    mb_regex_encoding('UTF-8');
if (function_exists('mb_http_output'))
    mb_http_output('UTF-8');

// Sets the default timezone
date_default_timezone_set('UTC');

// Configure error display mode
error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('error_log', 'errorPHP.log');
ini_set('log_errors', 1);

// Чтобы было, хоть и не работает
ini_set('register_globals', 0);

// отключение волшебных кавычек
// во время выполнения скрипта
if (get_magic_quotes_runtime())
    set_magic_quotes_runtime(0);
ini_set('magic_quotes_runtime', 0);

// отключение волшебных кавычек
// для данных, полученных от пользователя
if (get_magic_quotes_gpc())
    include_once 'fnc/fix_magic_quotes.php';
ini_set('magic_quotes_gpc', 0);
ini_set('magic_quotes_sybase', 0);

// LOAD CORE MODULE
if (file_exists($core = 'core.php'))
    require_once $core;
else
    die('Unable to load CORE!');

// Protect against hack attempts
if (!defined('NGCMS')) die ('HAL');

// Для админ. панели отключаем кеширование
// т.к. возникают проблемы с сохранением конфигурационного файла в PHP > 5.5
header('Expires: Sat, 08 Jun 1985 09:10:00 GMT'); // дата в прошлом
header('Last-Modified: ' . gmdate('D, d M Y H:i:s') . ' GMT'); // всегда модифицируется
header('Cache-Control: no-store, no-cache, must-revalidate'); // HTTP/1.1
header('Cache-Control: post-check=0, pre-check=0', false);
header('Pragma: no-cache'); // HTTP/1.0

if (function_exists('opcache_get_status'))
    ini_set('opcache.enable', 0);
if (function_exists('opcache_get_status'))
    ini_set('opcache.enable_cli', 0);
if (function_exists('xcache_get'))
    ini_set('xcache.cacher', 0);

// Pre-configure required global variables
global $action, $subaction, $mod;
$action = isset($_REQUEST['action']) ? $_REQUEST['action'] : '';
$subaction = isset($_REQUEST['subaction']) ? $_REQUEST['subaction'] : '';
$mod = isset($_REQUEST['mod']) ? $_REQUEST['mod'] : '';

// Activate output buffer
ob_start();

//define('DEBUG', 1);

if (defined('DEBUG')) {
    print "HTTP CALL PARAMS: <pre>";
    var_dump(array('GET' => $_GET, 'POST' => $_POST, 'COOKIE' => $_COOKIE));
    print "</pre><br>\n";
    print "SERVER PARAMS: <pre>";
    var_dump($_SERVER);
    print "</pre><br>\n";
}

$PHP_SELF = 'admin.php';

//
// Handle LOGIN
if (isset($_REQUEST['action']) and ($_REQUEST['action'] == 'login')) {
    include_once root . 'cmodules.php';
    coreLogin();
}

//
// Handle LOGOUT
if (isset($_REQUEST['action']) and ($_REQUEST['action'] == 'logout')) {
    include_once root . 'cmodules.php';
    coreLogout();
}

//
// Show LOGIN screen if user is not logged in
if (empty($userROW) or !is_array($userROW)) {
    $tvars['vars'] = array(
        'php_self' => $PHP_SELF,
        'redirect' => $REQUEST_URI,
        'year' => date('Y'),
        'home_title' => home_title,
        'error' => isset($SYSTEM_FLAGS['auth_fail']) ? __('msge_login') : '',
    );
    $tvars['regx']['#\[error\](.+?)\[/error\]#is'] = isset($SYSTEM_FLAGS['auth_fail']) ? '$1' : '';

    $tpl->template('login', tpl_actions);
    $tpl->vars('login', $tvars);
    echo $tpl->show('login');
    exit;
}

// Check if visitor has permissions to view admin panel
if (!checkPermission(array('plugin' => '#admin', 'item' => 'system'), null, 'admpanel.view')) {
    ngSYSLOG(array('plugin' => '#admin', 'item' => 'system'), array('action' => 'admpanel.view'), null, array(0, 'SECURITY.PERM'));
    @header('Location: ' . home);
    exit;
}

//
// Only admins can reach this location
define('ADMIN', 1);

// Load library
require_once './includes/inc/lib_admin.php';

// Load plugins, that need to make any changes during user in admin panel
loadActionHandlers('admin:init');

// Configure user's permissions (access to modules, depends on user's status)
$permissions = array(
    'perm' => '1',
    'ugroup' => 1,
    'configuration' => 99,
    'cron' => 99,
    'dbo' => 99,
    'extras' => '1',
    'extra-config' => '1',
    'statistics' => '1',
    'themes' => 99,
    'templates' => 99,
    'users' => 99,
    'rewrite' => '1',
    'static' => '1',
    'editcomments' => '2',
    'ipban' => 99,
    'options' => '2',
    'categories' => 99,
    'news' => 99,
    'files' => '3',
    'images' => '3',
    'pm' => '3',
    'preview' => '3',
);

executeActionHandler('admin_header');

// Print skin header (if we're not in preview mode)
if ($mod != 'preview') {
    //echo $skin_header;

    // Default action
    if (empty($mod)) {
        $mod = ($userROW['status'] == 1) ? 'statistics' : 'news';
    }

    $skins_url = skins_url;

    // Load common lang to admin
    Lang::load('index', 'admin');

    // Get user variables
    $cPlugin->loadLibrary('uprofile', 'lib');
    $status = $UGROUP[$userROW['status']]['langName'][$config['default_lang']];
    $userPhoto = function_exists('userGetPhoto') ? userGetPhoto($userROW) : $skins_url . '/assets/img/default-avatar.jpg';
    $userAvatar = (!empty($userROW['avatar']) and function_exists('userGetAvatar')) ? userGetAvatar($userROW)[1] : $skins_url . '/assets/img/default-avatar.jpg';

    // Calculate number of un-approved
    $unnAppCount = '0';
    $newpm = '';
    $unapp1 = '';
    $unapp2 = '';
    $unapproved1 = '';
    $unapproved2 = '';
    $unapproved3 = '';
    if ($userROW['status'] == 1 or $userROW['status'] == 2) {
        $unapp1 = $mysql->result("SELECT count(id) FROM " . prefix . "_news WHERE approve = '-1'");
        $unapp2 = $mysql->result("SELECT count(id) FROM " . prefix . "_news WHERE approve = '0'");
        $unapp3 = $mysql->result("SELECT count(id) FROM " . prefix . "_static WHERE approve = '0'");
        if ($unapp1)
            $unapproved1 = '<li><a href="' . $PHP_SELF . '?mod=news&status=1"><i class="fa fa-ban"></i> ' . $unapp1 . ' ' . Padeg($unapp1, __('head_news_draft_skl')) . '</a></li>';
        if ($unapp2)
            $unapproved2 = '<li><a href="' . $PHP_SELF . '?mod=news&status=2"><i class="fa fa-times"></i> ' . $unapp2 . ' ' . Padeg($unapp2, __('head_news_pending_skl')) . '</a></li>';
        if ($unapp3)
            $unapproved3 = '<li><a href="' . $PHP_SELF . '?mod=static"><i class="fa fa-times"></i> ' . $unapp3 . ' ' . Padeg($unapp3, __('head_stat_pending_skl')) . '</a></li>';
    }

    $unnAppCount = (int)$newpm + (int)$unapp1 + (int)$unapp2 + (int)$unapp3;
    $unnAppLabel = ($unnAppCount != "0") ? '<span class="label label-danger">' . $unnAppCount . '</span>' : '';
    $unnAppText = __('head_notify') . (($unnAppCount != "0") ? $unnAppCount . ' ' . Padeg($unnAppCount, __('head_notify_skl')) : __('head_notify_no'));

    $newpm = $mysql->result("SELECT count(pmid) FROM " . prefix . "_users_pm WHERE to_id = " . db_squote($userROW['id']) . " AND viewed = '0'");
    $newpmText = ($newpm != "0") ? $newpm . ' ' . Padeg($newpm, __('head_pm_skl')) : __('head_pm_no');

    $tVars = array(
        'php_self' => $PHP_SELF,
        'titles' => __('adminpanel') . ' : ' . $config['home_title'],
        'skin' => $skins_url . '/themes/' . $config['skin'] . '.css', // switchTheme
        'user' => array(
            'id' => $userROW['id'],
            'name' => $userROW['name'],
            'status' => $status,
            'avatar' => $userAvatar,
            'flags' => array(
                'hasAvatar' => $config['use_avatars'] and $userAvatar,
            ),
        ),
        'unapproved1' => $unapproved1,
        'unapproved2' => $unapproved2,
        'unapproved3' => $unapproved3,
        'unnAppLabel' => $unnAppLabel,
        'unnAppText' => $unnAppText,
        'newpmText' => $newpmText,
    );

    $xt = $twig->loadTemplate('skins/default/tpl/header.tpl');
    echo $xt->render($tVars);
}

// Check requested module exists
if (isset($permissions[$mod]) and ($permissions[$mod])) {
    $level = $permissions[$mod];

    // If user's status fits - call module. Else - show an error
    if ($userROW['status'] <= $level) {
        // Load plugins, that need to make any changes in this mod
        loadActionHandlers('admin:mod:' . $mod);
        require_once './actions/' . $mod . '.php';
    } else {
        msg(array('type' => 'danger', 'message' => __('msge_mod')));
    }
} else {
    msg(array('type' => 'danger', 'message' => __('msge_mod')));
}

// Print skin footer (if we're not in preview mode)
if ($mod != 'preview') {
    //echo $skin_footer;

    $tVars = array(
        'php_self' => $PHP_SELF,
        'year' => date('Y'),
    );

    $xt = $twig->loadTemplate('skins/default/tpl/footer.tpl');
    echo $xt->render($tVars);
}

if (defined('DEBUG')) {
    echo "SQL queries:<br />\n-------<br />\n " . implode("<br />\n", $mysql->query_list);
}

executeActionHandler('admin_footer');
