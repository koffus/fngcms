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

// Назначение дефолтной временной зоны
date_default_timezone_set('UTC');

// Configure error display mode
error_reporting (E_ALL);
ini_set('display_errors', 0);
ini_set('error_log', 'errorPHP.txt');
ini_set('log_errors', 1);

// Чтобы было, хоть и не работает
ini_set('register_globals', 0);

// отключение волшебных кавычек
// во время выполнения скрипта
if(get_magic_quotes_runtime())
 set_magic_quotes_runtime(0);
ini_set('magic_quotes_runtime', 0);

// отключение волшебных кавычек
// для данных, полученных от пользователя
if (get_magic_quotes_gpc())
	include_once 'fnc/fix_magic_quotes.php';
ini_set('magic_quotes_gpc', 0);
ini_set('magic_quotes_sybase', 0);

// LOAD CORE MODULE
if(file_exists($core = 'core.php'))
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
	ini_set('opcache.enable', '0');
if (function_exists('xcache_get'))
	ini_set('xcache.cacher', '0');

//
// Show LOGIN screen if user is not logged in
if ( !is_array($userROW) ) {
	$tvars['vars'] = array(
		'php_self' => $PHP_SELF,
		'redirect' => $REQUEST_URI,
		'year' => date('Y'),
		'home_title' => home_title,
		'error' => $SYSTEM_FLAGS['auth_fail'] ? __('msge_login') : '',
	);
	$tvars['regx']['#\[error\](.+?)\[/error\]#is'] = ($SYSTEM_FLAGS['auth_fail']) ? '$1' : '';

	$tpl -> template('login', tpl_actions);
	$tpl -> vars('login', $tvars);
	echo $tpl -> show('login');
	exit;
}

// Check if visitor has permissions to view admin panel
if ( !checkPermission(array('plugin' => '#admin', 'item' => 'system'), null, 'admpanel.view') ) {
	ngSYSLOG(array('plugin' => '#admin', 'item' => 'system'), array('action' => 'admpanel.view'), null, array(0, 'SECURITY.PERM'));
	@header('Location: ' . home);
	exit;
}

/////////////////////////
$time = microtime(true);

$db = $config['dbname'];
$login = $config['dbuser'];
$passw = $config['dbpasswd'];
$host = $config['dbhost'];

$res = mysql_connect($host, $login, $passw);
mysql_select_db($db);

mysql_query('SET NAMES utf8;');

$rs = mysql_query('SHOW TABLES;');
print mysql_error(); //the notorious 'command out of synch' message :(
while (($row=mysql_fetch_assoc($rs))!==false) {

$time1 = microtime(true);
//print $row['Tables_in_vspomni2']."<br>\n";
$table_name = $row['Tables_in_'.$db];
$query = 'SHOW CREATE TABLE '.$table_name;

$row_create = mysql_query($query);
print mysql_error();
$row1 = mysql_fetch_assoc($row_create);

if (strpos($row1['Create Table'], 'DEFAULT CHARSET=utf8') !== false)
{
print 'Table '.$table_name.' - skipped'."<br>\n";
continue;
}

$create_table_scheme = str_ireplace('cp1251', 'utf8', $row1['Create Table']); // CREATE TABLE SCHEME
$create_table_scheme = str_ireplace('ENGINE=InnoDB', 'MyISAM', $create_table_scheme);
$create_table_scheme .= ' COLLATE utf8_bin';

//print $create_table_scheme;
//continue;

$query = 'RENAME TABLE '.$table_name.' TO '.$table_name.'_tmp_export'; // RENAME TABLE;
mysql_query($query);
$error = mysql_error();
if (strlen($error) > 0)
{
print $error.' - LINE '.__LINE__."<br>\n";
break;
}

$query = $create_table_scheme;
mysql_query($query);
$error = mysql_error();
if (strlen($error) > 0)
{
print $error.' - LINE '.__LINE__."<br>\n";
break;
}

$query = 'ALTER TABLE '.$table_name.' DISABLE KEYS';
mysql_query($query);
$error = mysql_error();
if (strlen($error) > 0)
{
print $error.' - LINE '.__LINE__."<br>\n";
break;
}

$query = 'INSERT INTO '.$table_name.' SELECT * FROM '.$table_name.'_tmp_export';
mysql_query($query);
$error = mysql_error();
if (strlen($error) > 0)
{
print $error.' - LINE '.__LINE__."<br>\n";
break;
}

$query = 'DROP TABLE '.$table_name.'_tmp_export';
mysql_query($query);
$error = mysql_error();
if (strlen($error) > 0)
{
print $error.' - LINE '.__LINE__."<br>\n";
break;
}

$time3 = microtime(true);
$query = 'ALTER TABLE '.$table_name.' ENABLE KEYS';
mysql_query($query);
$error = mysql_error();
if (strlen($error) > 0)
{
print $error.' - LINE '.__LINE__."<br>\n";
break;
}

print 'Enable keys to '.$table_name.'. time -'.(microtime(true) - $time3)."<br>\n";
print 'converted '.$table_name.'. time - '.(microtime(true) - $time1)."<br>\n<br>\n";

}
mysql_free_result($rs);

print 'done. total time -'.(microtime(true) - $time);
?>