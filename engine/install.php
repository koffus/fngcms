<?php

//
// Copyright (C) 2006-2017 BixBite CMS (http://bixbite.site/)
// Name: install.php
// Description: System installer
// Author: Vitaly Ponomarev
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

// Check for minimum supported PHP version
if (version_compare(PHP_VERSION, '5.4.0') < 0)
    @require('data/errors/core_php_version.php');

// Sets the default timezone
date_default_timezone_set('UTC');

// Configure error display mode
error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('error_log', 'cache/errorPHP.log');
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

// Disable cache
@header('Expires: Sat, 08 Jun 1985 09:10:00 GMT'); // дата в прошлом
@header('Last-Modified: ' . gmdate('D, d M Y H:i:s') . ' GMT'); // всегда модифицируется
@header('Cache-Control: no-store, no-cache, must-revalidate'); // HTTP/1.1
@header('Cache-Control: post-check=0, pre-check=0', false);
@header('Pragma: no-cache'); // HTTP/1.0
if (function_exists('opcache_get_status')) ini_set('opcache.enable', 0);
if (function_exists('opcache_get_status')) ini_set('opcache.enable_cli', 0);
if (function_exists('xcache_get')) ini_set('xcache.cacher', 0);

// Автозагрузка классов
spl_autoload_register(function ($className) {
    $file = __DIR__ . '/classes/' . $className . '.class.php';

    if (file_exists($file)) {
        require_once($file); // !!! require once !!!
        return;
    } elseif (file_exists($file = __DIR__ . '/classes/' . strtolower($className) . '.class.php')) {
        require_once($file); // !!! require once !!!
        return;
    } else {
        return false;
    }
});

function __($key, $default_value = '')
{
    return Lang::get($key, $default_value = '');
}

// Задаем дефолт константы для tpl.class.php
// чтобы не ругался php об asumed
if (!defined('admin_url'))
    define('admin_url', '../engine');
if (!defined('tpl_url'))
    define('tpl_url', '../templates/default');
if (!defined('scriptLibrary'))
    define('scriptLibrary', '../lib');
if (!defined('skins_url'))
    define('skins_url', 'skins/default');

// Пошла система
@define('BBCMS', true);

// !!! Переписать это установочное дерьмо

// Basic variables
@define('root', dirname(__FILE__) . '/');
@define('confroot', root . 'conf/');
@define('tplRoot',root . '/skins/default/install');

// Check if config file already exists
if ((@fopen(confroot . 'config.php', 'r')) and (filesize(confroot . 'config.php'))) {
    die('<font color="red"><b>Error: configuration file already exists!</b></font><br />Delete it and continue.<br />\n');
}

// =============================================================
// Fine, we are ready to start installation
// =============================================================

// Determine user's language
$currentLanguage = isset($_REQUEST['language']) ? $_REQUEST['language'] : 'english';

if (!file_exists($toinc = root . 'lang/' . $currentLanguage . '/install.ini')) {
    $toinc = root . 'lang/english/install.ini';
    $currentLanguage = 'english';
}

if (!file_exists($toinc)) {
    $toinc = root . 'lang/russian/install.ini';
    $currentLanguage = 'russian';
}

// Load language variables
Lang::load('install');
Lang::load('extra-config', 'admin');

$tpl = new Tpl;

// Determine current admin working directory
list($adminDirName) = array_slice($ADN = preg_split('/(\\\|\/)/', root, -1, PREG_SPLIT_NO_EMPTY), -1, 1);
$installDir = ((substr(root, 0, 1) == '/') ? '/' : '') . join("/", array_slice($ADN, 0, -1));
$templateDir = root . 'skins/default/install';

// Determine installation URL
$scheme = isset($_SERVER['HTTP_SCHEME']) ? 'https://' :
    (((isset($_SERVER['HTTPS']) and $_SERVER['HTTPS'] !== 'off') or 443 == $_SERVER['SERVER_PORT']) ? 'https://' : 'http://');
$homeURL = $scheme . $_SERVER['HTTP_HOST'] . '/' . ($a = join("/", array_slice(preg_split('/(\\\|\/)/', $_SERVER['REQUEST_URI'], -1, PREG_SPLIT_NO_EMPTY), 0, -2))) . ($a ? '/' : '');
$templateURL = $homeURL . $adminDirName . '/skins/default/install';
$scriptLibrary = $homeURL . 'lib';
$ERR = array();

$tvars = array('vars' => array('templateURL' => $templateURL, 'homeURL' => $homeURL, 'scriptLibrary' => $scriptLibrary));
foreach (array('begin', 'db', 'plugins', 'template', 'perm', 'common', 'install') as $v) {
    $tvars['vars']['menu_' . $v] = '';
}

// If action is specified, but license is not accepted - stop installation
if (isset($_POST['action']) and !isset($_POST['agree'])) {
    notAgree();
}

//
// Determine required action
//
if (isset($_POST['action'])) {
    switch ($_POST['action']) {
        case 'config':
            doConfig();
            break;
        case 'install':
            doInstall();
            break;
        default:
            doWelcome();
            break;
    }
} else {
    doWelcome();
}

function printHeader()
{
    global $tpl, $templateDir, $tvars;

    // Print installation header
    $tpl->template('header', $templateDir);
    $tpl->vars('header', $tvars);
    echo $tpl->show('header');
}

function doWelcome()
{
    global $tpl, $tvars, $templateDir;

    include_once root . 'includes/inc/functions.inc.php';

    // Print header
    $tvars['vars']['menu_begin'] = ' class="hover"';
    printHeader();

    $lang_select = '';
    $langs = ListFiles('lang', '');
    foreach ($langs as $k => $v) {
        $lang_select .= '<li><a href="install.php?language=' . $v . '">' . $v . '</a></li>';
    }
    $tvars['vars']['lang_select'] = $lang_select;

    // Load license
    $license = @file_get_contents(root . '../license.html');
    if (!$license) {
        $license = __('msg.nolicense');
        $tvars['vars']['ad'] = 'disabled="disabled" ';
    } else {
        $tvars['vars']['ad'] = '';
    }

    $tvars['vars']['license'] = $license;
    $tpl->template('welcome', $templateDir);
    $tpl->vars('welcome', $tvars);
    echo $tpl->show('welcome');
}

function notAgree()
{
    global $tpl, $tvars, $templateDir;

    $tvars['vars']['menu_begin'] = ' class="hover"';
    printHeader();
    $tpl->template('notagree', $templateDir);
    $tpl->vars('notagree', $tvars);
    echo $tpl->show('notagree');
    exit;
}

// Вывод формы для ввода параметров установки
function doConfig()
{
    switch ($_POST['stage']) {
        default:
            doConfig_db(0);
            break;
        case '1':
            if (!doConfig_db(1)) break;
            doConfig_perm();
            break;
        case '2':
            doConfig_plugins();
            break;
        case '3':
            doConfig_templates();
            break;
        case '4':
            doConfig_common();
            break;
    }
}

function doConfig_db($check)
{
    global $tvars, $tpl, $templateDir, $SQL_VERSION;

    $myparams = array(
        'action',
        'stage',
        'reg_dbhost',
        'reg_dbname',
        'reg_dbuser',
        'reg_dbpass',
        'reg_dbprefix',
        'reg_autocreate',
        'reg_dbadminuser',
        'reg_dbadminpass',
    );
    $DEFAULT = array(
        'action' => null,
        'stage' => null,
        'reg_dbhost' => 'localhost',
        'reg_dbname' => 'ng',
        'reg_dbuser' => 'root',
        'reg_dbpass' => null,
        'reg_dbprefix' => 'ng',
        'reg_autocreate' => null,
        'reg_dbadminuser' => null,
        'reg_dbadminpass' => null,
    );

    // Show form
    $hinput = array();
    foreach ($_POST as $k => $v)
        if (array_search($k, $myparams) === FALSE)
            $hinput[] = '<input type="hidden" name="' . $k . '" value="' . htmlspecialchars($v, ENT_COMPAT | ENT_HTML401, 'UTF-8') . '"/>';

    $tvars['vars']['hinput'] = join("\n", $hinput);
    $tvars['vars']['error_message'] = '';

    if ($check) {
        // Check passed parameters. Check for required params
        $error = 0;
        foreach (array('reg_dbhost', 'reg_dbname', 'reg_dbuser') as $k) {
            if (!strlen($_POST[$k])) {
                $tvars['vars']['err:' . $k] = __('error.notfilled');
                $error++;
            }
        }

        // Check for autocreate mode
        if (isset($_POST['reg_autocreate'])) {
            // Check for user filled
            if (!strlen($_POST['reg_dbadminuser'])) {
                $tvars['vars']['err:reg_dbadminuser'] = '<font color="red">' . __('err.reg_dbadminuser') . '</font>';
                $error++;
            }
            $ac = 1;
        } else {
            $ac = 0;
        }

        $mysql = new Database;

        // Try to connect
        if (!$error) {
            if (($link = $mysql->connect($_POST['reg_dbhost'], $_POST['reg_db' . ($ac ? 'admin' : '') . 'user'], $_POST['reg_db' . ($ac ? 'admin' : '') . 'pass'], '', 1)) === FALSE) {
                $tvars['vars']['error_message'] = '<div class="alert alert-danger">' . __('error.dbconnect') . ' "' . $_POST['reg_dbhost'] . '" ' . $mysql->db_errno() . ' ' . $mysql->db_error() . '</div>';
                $error = 1;
            }
        }
        // Try to fetch SQL version
        if (!$error) {
            if (($sqlf = $mysql->query("show variables like 'version'", $link)) === FALSE) {
                $tvars['vars']['error_message'] = '<div class="alert alert-danger">' . __('err.dbversion') . ' "' . $_POST['reg_dbhost'] . '":<br/> (' . $mysql->db_errno() . ') ' . $mysql->db_error() . '</div>';
                $error = 1;
            } else {
                $sqlr = $mysql->record("show variables like 'version'", -1);
                if (preg_match('/^(\d+)\.(\d+)/', $sqlr[1], $regex)) {
                    $SQL_VERSION = array($sqlr[1], intval($regex[1]), intval($regex[2]));
                } else {
                    $SQL_VERSION = $sqlr[1];
                }
            }
        }

        @$mysql->close($link);

        if (!$error)
            return true;

    }

    foreach (array(
                 'reg_dbhost',
                 'reg_dbuser',
                 'reg_dbpass',
                 'reg_dbname',
                 'reg_dbprefix',
                 'reg_autocreate',
                 'reg_dbadminuser',
                 'reg_dbadminpass',
             ) as $k) {
        $tvars['vars'][$k] = htmlspecialchars(isset($_POST[$k]) ? $_POST[$k] : $DEFAULT[$k], ENT_COMPAT | ENT_HTML401, 'UTF-8');
        if (!isset($tvars['vars']['err:' . $k]))
            $tvars['vars']['err:' . $k] = '';
    }
    if (isset($_POST['reg_autocreate']))
        $tvars['vars']['reg_autocreate'] = 'checked="checked"';

    $tvars['vars']['menu_db'] = ' class="hover"';
    printHeader();

    // Выводим форму проверки
    $tpl->template('config_db', $templateDir);
    $tpl->vars('config_db', $tvars);
    echo $tpl->show('config_db');

    return false;
}

function doConfig_perm()
{
    global $tvars, $tpl, $templateDir, $installDir, $adminDirName, $SQL_VERSION;

    $tvars['vars']['menu_perm'] = ' class="hover"';
    printHeader();

    // Error flag
    $error = 0;
    $warning = 0;

    // Check file permissions
    $chmod = '';
    $permList = array('.htaccess', 'uploads/', 'uploads/avatars/', 'uploads/files/',
        'uploads/images/', 'uploads/photos/', 'uploads/dsn/', $adminDirName . '/backups/',
        $adminDirName . '/cache/', $adminDirName . '/conf/');
    foreach ($permList as $dir) {
        $perms = (($x = @fileperms($installDir . '/' . $dir)) === FALSE) ? 'n/a' : (decoct($x) % 1000);
        $chmod .= '<tr><td>./' . $dir . '</td><td>' . $perms . '</td><td>' . (is_writable($installDir . '/' . $dir) ? __('perm.access.on') : '<font color="red"><b>' . __('perm.access.off') . '</b></font>') . '</td></tr>';
        if (!is_writable($installDir . '/' . $dir))
            $error++;
    }

    $tvars['vars']['chmod'] = $chmod;

    // PHP Version
    if (version_compare(phpversion(), '5.4') < 0) {
        $tvars['vars']['php_version'] = '<font color="red">' . phpversion() . '</font>';
        $error = 1;
    } else {
        $tvars['vars']['php_version'] = phpversion();
    }

    // SQL Version
    if (!is_array($SQL_VERSION)) {
        $tvars['vars']['sql_version'] = '<font color="red">unknown</font>';
        $error = 1;
    } else {
        if (($SQL_VERSION[1] < 3) or (($SQL_VERSION[1] == 3) and ($SQL_VERSION[2] < 23))) {
            $tvars['vars']['sql_version'] = '<font color="red">' . $SQL_VERSION[0] . '</font>';
            $error = 1;
        } else {
            $tvars['vars']['sql_version'] = $SQL_VERSION[0];
        }
    }

    // GZIP support
    if (extension_loaded('zlib') and function_exists('ob_gzhandler')) {
        $tvars['vars']['gzip'] = __('perm.yes');
    } else {
        $tvars['vars']['gzip'] = '<font color="red">' . __('perm.no') . '</font>';
        $error = 1;
    }

    // PDO support
    if (extension_loaded('PDO') and extension_loaded('pdo_mysql') and class_exists('PDO')) {
        $tvars['vars']['pdo'] = __('perm.yes');
    } else {
        $tvars['vars']['pdo'] = '<font color="red">' . __('perm.no') . '</font>';
        $error = 0;
    }

    // XML support
    if (function_exists('xml_parser_create')) {
        $tvars['vars']['xml'] = __('perm.yes');
    } else {
        $tvars['vars']['xml'] = '<font color="red">' . __('perm.no') . '</font>';
        $error = 1;
    }

    // GD support
    if (function_exists('imagecreatetruecolor')) {
        $tvars['vars']['gdlib'] = __('perm.yes');
    } else {
        $tvars['vars']['gdlib'] = '<font color="red">' . __('perm.no') . '</font>';
        $error = 1;
    }

    //
    // PHP features configuraton
    //

    // * flags that should be turned off
    foreach (array(
                 'register_globals',
                 'magic_quotes_gpc',
                 'magic_quotes_runtime',
                 'magic_quotes_sybase',
             ) as $flag) {
        $tvars['vars']['flag:' . $flag] = ini_get($flag) ? '<font color="red">' . __('perm.php.on') . '</font>' : __('perm.php.off');
        if (ini_get($flag))
            $warning++;
    }

    if ($error) {
        $tvars['vars']['error_message'] .= '<div class="alert alert-danger">' . __('perm.error') . '</div>';
    }
    if ($warning) {
        $tvars['vars']['error_message'] .= '<div class="alert alert-warning">' . __('perm.warning') . '</div>';
    }

    $tvars['regx']["'\[error_button\](.*?)\[/error_button\]'si"] = ($error or $warning) ? '$1' : '';

    $myparams = array('action', 'stage');

    // Show form
    $hinput = array();
    foreach ($_POST as $k => $v)
        if (array_search($k, $myparams) === FALSE)
            $hinput[] = '<input type="hidden" name="' . $k . '" value="' . htmlspecialchars($v, ENT_COMPAT | ENT_HTML401, 'UTF-8') . '"/>';
    $tvars['vars']['hinput'] = join("\n", $hinput);

    // Выводим форму проверки
    $tpl->template('config_perm', $templateDir);
    $tpl->vars('config_perm', $tvars);
    echo $tpl->show('config_perm');
}

function doConfig_plugins()
{
    global $tvars, $tpl, $templateDir;

    $tvars['vars']['menu_plugins'] = ' class="hover"';
    printHeader();

    // Now we should scan plugins for preinstall configuration
    $pluglist = array();
    $pluginsDir = root . 'plugins';
    if ($dRec = opendir($pluginsDir)) {
        while (($dName = readdir($dRec)) !== false) {
            if (($dName == '.') or ($dName == '..'))
                continue;

            if (is_dir($pluginsDir . '/' . $dName) and file_exists($vfn = $pluginsDir . '/' . $dName . '/version') and (filesize($vfn)) and ($vf = @fopen($vfn, 'r'))) {

                $pluginRec = array();
                while (!feof($vf)) {
                    $line = fgets($vf);
                    if (preg_match("/^(.+?) *\: *(.+?) *$/i", trim($line), $m)) {
                        if (in_array(strtolower($m[1]), array(
                            'id',
                            'title',
                            'information',
                            'preinstall',
                            'preinstall_vars',
                            'install',
                        )))
                            $pluginRec[strtolower($m[1])] = $m[2];
                    }
                }
                fclose($vf);
                if (isset($pluginRec['id']) and isset($pluginRec['title']))
                    array_push($pluglist, $pluginRec);
            }
        }
        closedir($dRec);
    }

    // Prepare array for input list
    $hinput = array();

    // Collect data for all plugins
    $output = '';
    $tpl->template('config_prow', $templateDir);
    foreach ($pluglist as $plugin) {
        $tv = array(
            'id' => $plugin['id'],
            'title' => $plugin['title'],
            'information' => isset($plugin['information']) ? $plugin['information'] : 'not/avalaible',
            'enable' => (isset($plugin['preinstall']) and (in_array(strtolower($plugin['preinstall']), array('yes', 'no')))) ? ' disabled="disabled"' : '',
        );
        // Add hidden field for DISABLED plugins
        if (isset($plugin['preinstall']) and strtolower($plugin['preinstall']) == 'yes') {
            $output .= '<input type="hidden" name="plugin:' . $plugin['id'] . '" value="1"/>' . "\n";
        }

        if (isset($_POST['plugin:' . $plugin['id']])) {
            $tv['check'] = $_POST['plugin:' . $plugin['id']] ? ' checked="checked"' : '';
        } else {
            $tv['check'] = (isset($plugin['preinstall']) and (in_array(strtolower($plugin['preinstall']), array('default_yes', 'yes')))) ? ' checked="checked"' : '';
        }

        //$hinput[] = '<input type="hidden" name="plugin:'.$plugin['id'].'" value="0"/>';

        $tpl->vars('config_prow', array('vars' => $tv));
        $output .= $tpl->show('config_prow');
    }
    $tvars['vars']['plugins'] = $output;

    // Show form
    $myparams = array('action', 'stage');
    foreach ($_POST as $k => $v)
        if ((array_search($k, $myparams) === FALSE) and (!preg_match('/^plugin\:/', $k)))
            $hinput[] = '<input type="hidden" name="' . $k . '" value="' . htmlspecialchars($v, ENT_COMPAT | ENT_HTML401, 'UTF-8') . '"/>';
    $tvars['vars']['hinput'] = join("\n", $hinput);

    // Выводим форму проверки
    $tpl->template('config_plugins', $templateDir);
    $tpl->vars('config_plugins', $tvars);
    echo $tpl->show('config_plugins');
}

function doConfig_templates()
{
    global $tvars, $tpl, $templateDir, $installDir, $adminDirName, $homeURL, $SQL_VERSION;

    $tvars['vars']['menu_template'] = ' class="hover"';
    printHeader();

    // Now we should scan templates for version information
    $tlist = array();
    $tDir = $installDir . '/templates';
    if ($dRec = opendir($tDir)) {
        while (($dName = readdir($dRec)) !== false) {
            if (($dName == '.') or ($dName == '..'))
                continue;

            if (is_dir($tDir . '/' . $dName) and file_exists($vfn = $tDir . '/' . $dName . '/version') and (filesize($vfn)) and ($vf = @fopen($vfn, 'r'))) {
                $tRec = array('name' => $dName);
                while (!feof($vf)) {
                    $line = fgets($vf);
                    if (preg_match("/^(.+?) *\: *(.+?) *$/i", trim($line), $m)) {
                        if (in_array(strtolower($m[1]), array('id', 'title', 'author', 'version', 'reldate', 'plugins', 'image', 'imagepreview')))
                            $tRec[strtolower($m[1])] = $m[2];
                    }
                }
                fclose($vf);
                if (isset($tRec['id']) and isset($tRec['title']))
                    array_push($tlist, $tRec);
            }
        }
        closedir($dRec);
    }

    usort($tlist, function ($a, $b) {
        return strcmp($a['id'], $b['id']);
    });

    // Set default template name
    if (!isset($_POST['template']))
        $_POST['template'] = 'default';

    $output = '';

    foreach ($tlist as $trec) {
        $trvars = array('vars' => $trec);
        $trvars['vars']['checked'] = ($_POST['template'] == $trec['name']) ? ' checked="checked"' : '';
        $trvars['vars']['templateURL'] = $homeURL . '/templates';

        $tpl->template('config_templates_rec', $templateDir);
        $tpl->vars('config_templates_rec', $trvars);
        $output .= $tpl->show('config_templates_rec');
    }
    $tvars['vars']['templates'] = $output;

    $myparams = array('action', 'stage', 'template');
    // Show form
    $hinput = array();
    foreach ($_POST as $k => $v)
        if (array_search($k, $myparams) === FALSE)
            $hinput[] = '<input type="hidden" name="' . $k . '" value="' . htmlspecialchars($v, ENT_COMPAT | ENT_HTML401, 'UTF-8') . '"/>';
    $tvars['vars']['hinput'] = join("\n", $hinput);

    // Выводим форму проверки
    $tpl->template('config_templates', $templateDir);
    $tpl->vars('config_templates', $tvars);
    echo $tpl->show('config_templates');

}

function doConfig_common()
{
    global $tvars, $tpl, $templateDir, $installDir, $adminDirName, $SQL_VERSION, $homeURL;

    $tvars['vars']['menu_common'] = ' class="hover"';
    printHeader();

    $myparams = array('action', 'stage', 'admin_login', 'admin_password', 'admin_email', 'autodata', 'home_url', 'home_title');
    // Show form
    $hinput = array();
    foreach ($_POST as $k => $v)
        if (array_search($k, $myparams) === FALSE)
            $hinput[] = '<input type="hidden" name="' . $k . '" value="' . htmlspecialchars($v, ENT_COMPAT | ENT_HTML401, 'UTF-8') . '"/>';
    $tvars['vars']['hinput'] = join("\n", $hinput);

    // Preconfigure some paratemers
    if (!isset($_POST['home_url']))
        $_POST['home_url'] = $homeURL;
    if (!isset($_POST['home_title']))
        $_POST['home_title'] = __('common.title.default');

    foreach (array('admin_login', 'admin_password', 'admin_email', 'home_url', 'home_title') as $k) {
        $tvars['vars'][$k] = isset($_POST[$k]) ? htmlspecialchars($_POST[$k], ENT_COMPAT | ENT_HTML401, 'UTF-8') : '';
    }

    $tvars['vars']['autodata_checked'] = (isset($_POST['autodata']) and ($_POST['autodata'] == '1')) ? ' checked="checked"' : '';

    // Выводим форму проверки
    $tpl->template('config_common', $templateDir);
    $tpl->vars('config_common', $tvars);
    echo $tpl->show('config_common');
}

// Генерация конфигурационного файла
function doInstall()
{
    global $tvars, $tpl, $templateDir, $installDir, $adminDirName, $pluginInstallList, $currentLanguage;
    
    $parse = new Parse;

    $tvars['vars']['menu_install'] = ' class="hover"';
    printHeader();

    $myparams = array('action', 'stage');
    // Show form
    $hinput = array();
    foreach ($_POST as $k => $v)
        if (array_search($k, $myparams) === FALSE)
            $hinput[] = '<input type="hidden" name="' . $k . '" value="' . htmlspecialchars($v, ENT_COMPAT | ENT_HTML401, 'UTF-8') . '"/>';
    $tvars['vars']['hinput'] = join("\n", $hinput);

    // Error indicator
    $frec = array();
    $error = 0;
    $LOG = array();
    $ERROR_LOG = array();
    do {

        // Stage #01 - Try to create config files
        foreach (array('config.php', 'plugins.php', 'plugdata.php') as $k) {
            if (($frec[$k] = fopen(confroot . $k, 'w')) == NULL) {
                array_push($ERROR_LOG, __('err.createconfig1') . ' <b>' . $k . '</b><br/>' . __('err.createconfig2'));
                $error = 1;
                break;
            }
            array_push($LOG, __('msg.fcreating') . ' <b>' . $k . '</b> ... ' . __('msg.ok'));
        }
        array_push($LOG, '');

        if ($error) break;

        $mysql = new Database;

        // Stage #02 - Connect to DB
        // Если заказали автосоздание, то подключаемся рутом
        if (!empty($_POST['reg_autocreate'])) {
            if ($mysql->connect($_POST['reg_dbhost'], $_POST['reg_dbadminuser'], $_POST['reg_dbadminpass'], '', 1)) {
                // Успешно подключились
                array_push($LOG, 'Подключение к серверу БД "' . $_POST['reg_dbhost'] . '" используя административный логин "' . $_POST['reg_dbadminuser'] . '" ... ' . __('msg.ok'));

                // 1. Создание БД
                if (!$mysql->db_exists($_POST['reg_dbname'])) {
                    // БД нет. Пытаемся создать
                    if (!$mysql->query('CREATE DATABASE IF NOT EXISTS ' . $_POST['reg_dbname'] . ' CHARACTER SET utf8 COLLATE utf8_general_ci')) {
                        // Не удалось создать. Фатально.
                        array_push($ERROR_LOG, 'Не удалось создать БД "' . $_POST['reg_dbname'] . '" используя административную учётную запись. Скорее всего у данной учётной записи нет прав на создание баз данных.');
                        $error = 1;
                        break;
                    } else {
                        array_push($LOG, 'Создание БД "' . $_POST['reg_dbname'] . '" ... ' . __('msg.ok'));
                    }
                } else {
                    array_push($LOG, 'БД "' . $_POST['reg_dbname'] . '" уже существует ... ' . __('msg.ok'));
                }

                // 2. Предоставление доступа к БД
                if (!$mysql->query("grant all privileges on " . $_POST['reg_dbname'] . ".* to '" . $_POST['reg_dbuser'] . "'@'" . $_POST['reg_dbhost'] . "' identified by '" . $_POST['reg_dbpass'] . "'")) {
                    array_push($ERROR_LOG, 'Невозможно обеспечить доступ пользователя "' . $_POST['reg_dbuser'] . '" к БД "' . $_POST['reg_dbname'] . '" используя административные права.');
                    $error = 1;
                    break;
                } else {
                    array_push($LOG, 'Предоставление доступа пользователю "' . $_POST['reg_dbuser'] . '" к БД "' . $_POST['reg_dbname'] . '" ... ' . __('msg.ok'));
                }
            } else {
                array_push($ERROR_LOG, 'Невозможно подключиться к серверу БД "' . $_POST['reg_dbhost'] . '" используя административный логин "' . $_POST['reg_dbadminuser'] . '"');
                $error = 1;
                break;
            }
            // Отключаемся от сервера
            $mysql->close();
        }

        // Подключаемся к серверу используя права пользователя
        if (!$mysql->connect($_POST['reg_dbhost'], $_POST['reg_dbuser'], $_POST['reg_dbpass'],'', 1)) {
            array_push($ERROR_LOG, 'Невозможно подключиться к серверу БД "' . $_POST['reg_dbhost'] . '" используя логин "' . $_POST['reg_dbuser'] . '" и пароль: "' . $_POST['reg_dbpass'] . '"');
            $error = 1;
            break;
        }
        array_push($LOG, 'Подключение к серверу БД "' . $_POST['reg_dbhost'] . '" используя логин "' . $_POST['reg_dbuser'] . '" ... ' . __('msg.ok'));

        // Открываем нужную БД
        if (!$mysql->db_exists($_POST['reg_dbname']) or !$mysql->db_select($_POST['reg_dbname'])) {
            array_push($ERROR_LOG, 'Невозможно открыть БД "<b>' . $_POST['reg_dbname'] . '</b>". Вам необходимо создать эту БД самостоятельно.');
            $error = 1;
            break;
        }

        // Check if different character set are supported [ version >= 4.1.1 ]
        $charsetEngine = 0;

        if (($msq = $mysql->query("show variables like 'character_set_client'")) and ($mysql->num_rows($msq))) {
            $charsetEngine = 1;
        }
        $charset = $charsetEngine ? ' default charset=utf8' : '';
        array_push($LOG, 'Версия сервера БД mySQL ' . ((!$charsetEngine) ? 'не' : '') . 'поддерживает множественные кодировки.');

        // Создаём таблицы в mySQL
        // 1. Проверяем наличие пересекающихся таблиц
        // 1.1. Загружаем список таблиц из БД

        $list = array();

        if (!($query = $mysql->query("show tables"))) {
            array_push($ERROR_LOG, 'Внутренняя ошибка SQL при получении списка таблиц БД. Обратитесь к автору проекта за разъяснениями.');
            $error = 1;
            break;
        }

        $SQL_table = array();
        foreach ($mysql->select("show tables", -1) as $item) {
            $SQL_table[$item[0]] = 1;
        }

        // 1.2. Парсим список таблиц
        $dbsql = explode(';', file_get_contents('trash/tables.sql'));

        // 1.3. Проверяем пересечения
        foreach ($dbsql as $dbCreateString) {
            if (!trim($dbCreateString)) {
                continue;
            }

            // Добавляем кодировку (если поддерживается)
            $dbCreateString .= $charset;

            // Получаем имя таблицы
            if (preg_match('/CREATE TABLE `(.+?)`/', $dbCreateString, $match)) {
                $tname = str_replace('XPREFIX_', $_POST['reg_dbprefix'] . '_', $match[1]);
                if (isset($SQL_table[$tname])) {
                    array_push($ERROR_LOG, 'В БД "' . $_POST['reg_dbname'] . '" уже существует таблица "' . $tname . '"<br/>Используйте другой префикс для создания таблиц!');
                    $error = 1;
                    break;
                }
            } else {
                array_push($ERROR_LOG, 'Внутренняя ошибка парсера SQL. Обратитесь к автору проект за разъяснениями [' . $dbCreateString . ']');
                $error = 1;
                break;
            }
        }
        if ($error) break;

        array_push($LOG, 'Проверка наличия дублирующихся таблиц ... ' . __('msg.ok'));
        array_push($LOG, '');

        $SUPRESS_CHARSET = 0;
        $SUPRESS_ENGINE = 0;

        // 1.4. Создаём таблицы
        for ($i = 0; $i < count($dbsql); $i++) {
            
            $dbCreateString = str_replace(array('XPREFIX_', 'XENGINE'), array($_POST['reg_dbprefix'] . '_', $_POST['reg_dbengine']), $dbsql[$i]) . $charset;

            if ($SUPRESS_CHARSET) {
                $dbCreateString = str_replace('default charset=utf8', '', $dbCreateString);
            }
            if ($SUPRESS_ENGINE) {
                $dbCreateString = str_replace('ENGINE=' . $_POST['reg_dbengine'], '', $dbCreateString);
            }

            if (preg_match('/CREATE TABLE `(.+?)`/', $dbCreateString, $match)) {
                $tname = $match[1];
                $err = 0;
                $mysql->query($dbCreateString);

                if ($mysql->db_errno()) {
                    if (!$SUPRESS_CHARSET) {
                        $SUPRESS_CHARSET = 1;
                        array_push($LOG, 'Внимание! Попытка отключить настройки кодовой страницы');
                        $i--;
                        continue;
                    }
                    if (!$SUPRESS_ENGINE) {
                        $SUPRESS_ENGINE = 1;
                        array_push($LOG, 'Внимание! Попытка отключить настройки формата хранения данных');
                        $i--;
                        continue;
                    }
                    array_push($ERROR_LOG, 'Не могу создать таблицу "' . $tname . '"!<br>Обратитесь к автору проекта за разъяснениями<br>Код SQL запроса:<br>' . $dbCreateString);
                    $error = 1;
                    break;
                }
                array_push($LOG, 'Создание таблицы "<b>' . $tname . '</b>" ... ' . __('msg.ok'));
            }
        }
        array_push($LOG, 'Все таблицы успешно созданы ... ' . __('msg.ok'));
        array_push($LOG, '');

        // 1.5 Создание пользователя-администратора
        $query = "insert into `" . $_POST['reg_dbprefix'] . "_users` (`name`, `pass`, `mail`, `status`, `reg`) VALUES (" . $mysql->db_quote($_POST['admin_login']) . ", " . $mysql->db_quote(md5(md5($_POST['admin_password']))) . ", " . $mysql->db_quote($_POST['admin_email']) . ", '1', unix_timestamp(now()))";
        if (!@$mysql->query($query)) {
            array_push($LOG, 'Активация пользователя-администратора ... <font color="red">' . __('msg.error') . '</font>');
        } else {
            array_push($LOG, 'Активация пользователя-администратора ... ' . __('msg.ok'));
        }
        // 1.6 Сохраняем конфигурационную переменную database.engine.version
        @$mysql->query("insert into `" . $_POST['reg_dbprefix'] . "_config` (name, value) values ('database.engine.version', 'v0.9.6.4-alfa')");

        // Вычищаем лишний перевод строки из 'home_url'
        if (substr($_POST['home_url'], -1, 1) == '/')
            $_POST['home_url'] = substr($_POST['home_url'], 0, -1);

        // 1.7 Копируем шаблон
        $tDir = $installDir . DIRECTORY_SEPARATOR . 'templates' . DIRECTORY_SEPARATOR . $_POST['template'];
        $theme = $parse->translit($_POST['home_title']);
        $themeDir = $installDir . DIRECTORY_SEPARATOR . 'templates' . DIRECTORY_SEPARATOR . $theme;

        if (!file_exists($themeDir)) {
            @mkdir($themeDir, 0755, true);
        }

        $dirIterator = new RecursiveDirectoryIterator($tDir, RecursiveDirectoryIterator::SKIP_DOTS);
        $iterator = new RecursiveIteratorIterator($dirIterator, RecursiveIteratorIterator::SELF_FIRST);

        foreach ($iterator as $object) {
            $themePath = $themeDir . DIRECTORY_SEPARATOR . $iterator->getSubPathName();
            ($object->isDir()) ? @mkdir($themePath, 0755, true) : copy($object, $themePath);
        }
        array_push($LOG, 'Копирование шаблона в ' . $theme . ' ...' . __('msg.ok'));
        
        // 1.8. Формируем конфигурационный файл
        $newconf = array(
            'dbengine' => $_POST['reg_dbengine'],
            'dbhost' => $_POST['reg_dbhost'],
            'dbname' => $_POST['reg_dbname'],
            'dbuser' => $_POST['reg_dbuser'],
            'dbpasswd' => $_POST['reg_dbpass'],
            'prefix' => $_POST['reg_dbprefix'],
            'home_url' => $_POST['home_url'],
            'admin_url' => $_POST['home_url'] . '/' . $adminDirName,
            'images_dir' => $installDir . '/uploads/images/',
            'files_dir' => $installDir . '/uploads/files/',
            'attach_dir' => $installDir . '/uploads/dsn/',
            'avatars_dir' => $installDir . '/uploads/avatars/',
            'photos_dir' => $installDir . '/uploads/photos/',
            'images_url' => $_POST['home_url'] . '/uploads/images',
            'files_url' => $_POST['home_url'] . '/uploads/files',
            'attach_url' => $_POST['home_url'] . '/uploads/dsn',
            'avatars_url' => $_POST['home_url'] . '/uploads/avatars',
            'photos_url' => $_POST['home_url'] . '/uploads/photos',
            'home_title' => $_POST['home_title'],
            'admin_mail' => $_POST['admin_email'],
            'lock' => '0',
            'lock_reason' => 'Сайт на реконструкции!',
            'lock_retry' => 3600,
            'meta' => '1',
            'meta_title' => 'Главная страница',
            'description' => 'Здесь описание вашего сайта',
            'keywords' => 'Здесь ключевые слова, через запятую (,)',
            'skin' => 'yeti',
            'theme' => $theme,
            'default_lang' => $currentLanguage,
            'auto_backup' => '0',
            'auto_backup_time' => '48',
            'use_gzip' => '0',
            'use_captcha' => '1',
            'captcha_font' => 'blowbrush',
            'use_cookies' => '0',
            'use_sessions' => '1',
            'number' => '9',
            'news_translit' => '1',
            'news_view_counters' => '1',
            'category_counters' => '1',
            'category_link' => '1',
            'add_onsite' => '1',
            'add_onsite_guests' => '0',
            'date_adjust' => '0',
            'timestamp_active' => 'j F Y',
            'smilies' => 'smile,biggrin,confused,cool,crazy,cry,hm,shok,tongue,angry,unsure,wink',
            'blocks_for_reg' => '1',
            'use_smilies' => '1',
            'use_bbcodes' => '1',
            'use_htmlformatter' => '1',
            'forbid_comments' => '0',
            'reverse_comments' => '0',
            'auto_wrap' => '50',
            'flood_time' => '20',
            'timestamp_comment' => 'j.m.Y - H:i',
            'users_selfregister' => '1',
            'register_type' => '4',
            'user_aboutsize' => '500',
            'use_avatars' => '1',
            'avatar_wh' => '140',
            'avatar_max_size' => '32',
            'use_photos' => '1',
            'photos_max_size' => '256',
            'photos_thumb_size_x' => '140',
            'photos_thumb_size_y' => '140',
            'images_ext' => 'gif, jpg, jpeg, png',
            'images_max_size' => '512',
            'thumb_size_x' => '180',
            'thumb_size_y' => '180',
            'thumb_quality' => '80',
            'wm_image' => 'stamp',
            'wm_image_transition' => '50',
            'files_ext' => 'zip, rar, gz, tgz, bz2',
            'files_max_size' => '2048',
            'auth_module' => 'basic',
            'auth_db' => 'basic',
            'crypto_salt' => substr(md5(uniqid(rand(), 1)), 0, 8),
            '404_mode' => 0,
            'debug' => 0,
            'debug_queries' => 0,
            'debug_profiler' => 0,
            'news_multicat_url' => '1',
            'UUID' => md5(mt_rand() . mt_rand()) . md5(mt_rand() . mt_rand()),
        );

        array_push($LOG, 'Подготовка параметров конфигурационного файла ... ' . __('msg.ok'));

        // Записываем конфиг
        $confData = "<?php\n" . '$config = ' . var_export($newconf, true) . ";\n";

        if (!fwrite($frec['config.php'], $confData)) {
            array_push($ERROR_LOG, 'Ошибка записи конфигурационного файла!');
            $error = 1;
            break;
        }

        // Активируем плагин auth_basic
        $plugConf = array(
            'active' => array(
                'auth_basic' => 'auth_basic',
            ),
            'actions' => array(
                'auth' => array(
                    'auth_basic' => 'auth_basic/auth_basic.php',
                ),
            ),
        );

        $plugData = "<?php\n" . '$array = ' . var_export($plugConf, true) . ";\n";
        if (!fwrite($frec['plugins.php'], $plugData)) {
            array_push($ERROR_LOG, 'Ошибка записи конфигурационного файла [список активных плагинов]!');
            $error = 1;
            break;
        }

        // Закрываем все файлы
        foreach (array_keys($frec) as $k)
            fclose($frec[$k]);

        array_push($LOG, 'Сохранение конфигурационного файла ... ' . __('msg.ok'));

        // Подготавливаем список плагинов для установки
        $pluginInstallList = array();
        foreach ($_POST as $k => $v) {
            if (preg_match('/^plugin\:(.+?)$/', $k, $m) and ($v == 1)) {
                array_push($pluginInstallList, $m[1]);
            }
        }
    } while (0);

    /*
     * STOP this step, If error occured !!!
     */
    if (!$error) {

        // Now let's install plugins
        include_once root . 'core.php';
        include_once root . 'includes/inc/extraconf.inc.php';

        // Load CORE Plugin
        $cPlugin = CPlugin::instance();
        // First: Load informational `version` files
        $list = $cPlugin->getInfo();
        foreach ($pluginInstallList as $pName) {
            if (isset($list[$pName]['install'])) {
                include_once root . 'plugins/' . $pName . '/' . $list[$pName]['install'];
                $res = call_user_func('plugin_' . $pName . '_install', 'autoapply');

                if ($res) {
                    array_push($LOG, __('msg.plugin.installation') . ' <b>' . $pName . '</b> ... ' . __('msg.ok'));
                } else {
                    array_push($ERROR_LOG, __('msg.plugin.installation') . ' <b>' . $pName . '</b> ... ' . __('msg.error'));
                    $error = 1;
                    break;
                }
            }
            array_push($LOG, __('msg.plugin.activation') . ' <b>' . $pName . '</b> ... ' . (pluginSwitch($pName, 'on') ? __('msg.ok') : __('msg.error')));
        }
    }

    $output = join("<br/>\n", $LOG);

    if ($error) {
        foreach ($ERROR_LOG as $errText) {
            $output .= '<div class="alert alert-danger"><b><u>' . __('msg.error') . '</u>!</b><br/>' . $errText . '</div>';
        }
        if (!count($ERROR_LOG)) {
            $output .= '<div class="alert alert-warning">' . __('msg.errorInfo') . '</div>';
        }

        // TRY to config DB
        $output .= '<div class="alert alert-warning">Если Вы что-то неверно ввели в настройках БД, то Вы можете исправить ошибку. <a href="#" onclick="document.getElementById(\'stage\').value=\'0\'; document.getElementById(\'db\').submit();">Вернуться к настройке БД</a></div>';
        $output .= '<div class="alert alert-warning">Если Вы самостоятельно устранили ошибку, то попробуйте еще раз. <a href="#" onclick="document.getElementById(\'action\').value=\'install\'; document.getElementById(\'db\').submit();">Попробовать ещё раз</a></div>';
    }

    $tvars['vars']['actions'] = $output;
    $tvars['regx']["'\[complete\](.*?)\[/complete\]'si"] = $error ? '' : '$1';
    $tvars['vars']['complete_link'] = $_POST['home_url'] . '/' . $adminDirName;

    // Выводим форму
    $tpl->template('config_process', $templateDir);
    $tpl->vars('config_process', $tvars);
    print $tpl->show('config_process');

}

