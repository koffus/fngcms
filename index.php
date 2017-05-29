<?php

//
// Copyright (C) 2006-2016 Next Generation CMS (http://ngcms.ru)
// Name: index.php
// Description: core index file
// Author: NGCMS project team
//

// Call debug from PhpConsole\Handler
require_once('PhpConsole/__autoload.php');
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
ini_set('error_log', 'engine/errorPHP.txt');
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

// Load CORE module
if( file_exists($core = 'engine/core.php') ) {
	require_once $core;
} else {
	die('Unable to load CORE!');
}

// Init GZip handler
initGZipHandler();

// Define default TITLE
$SYSTEM_FLAGS['info']['title'] = [];
$SYSTEM_FLAGS['info']['title']['header'] = home_title;

// Initialize main template array
$template = array(
	'vars'	=> array(
		'what'			=>	engineName,
		'version'		=>	engineVersion,
		'home'			=>	home,
		'titles'		=>	home_title,
		'home_title'	=>	home_title,
		'mainblock'	=>	'',
		'htmlvars'	=>	 '',
	),
);

// ===================================================================
// Check if site access is locked [ for everyone except admins ]
// ===================================================================
if ( $config['lock'] and ( !is_array($userROW ) or ( !checkPermission(array('plugin' => '#admin', 'item' => 'system'), null, 'lockedsite.view'))) ) {
 $tvars = $template;
 $tvars['vars']['lock_reason'] = $config['lock_reason'];

	// If template 'sitelock.tpl' exists - show only this template
	// ELSE: show template 'lock.tpl' within template 'main.tpl'
	if (file_exists(tpl_site . 'sitelock.tpl')) {
		$tpl->template('sitelock', tpl_site);
		$tpl->vars('sitelock', $tvars);
		echo $tpl->show('sitelock');
	} else {
		$tpl -> template('lock', tpl_site);
		$tpl -> vars('lock', $tvars);
		$template['regx']["'\[sitelock\].*?\[/sitelock\]'si"] = $tpl -> show('lock');
		$template['regx']["'\[debug\].*?\[/debug\]'si"] = '';
		$template['vars']['htmlvars'] = '';

		$tpl -> template('main', tpl_site);
		$tpl -> vars('main', $template);
		echo $tpl->show('main');
	}

	// STOP SCRIPT EXECUTION
	exit;
}

// ===================================================================
// Start generating page
// ===================================================================

// External call: before executing URL handler
executeActionHandler('index_pre');

// Deactivate block [sitelock] ... [/sitelock]
$template['vars']["[sitelock]"] = '';
$template['vars']["[/sitelock]"] = '';

// /////////////////////////////////////////////////////////// //
// You may modify variable $systemAccessURL here (for hacks) //
// /////////////////////////////////////////////////////////// //

// /////////////////////////////////////////////////////////// //
$timer->registerEvent('Search route for URL "'.$systemAccessURL.'"');

// Give domainName to URL handler engine for generating absolute links
$UHANDLER->setOptions(array('domainPrefix' => $config['home_url']));

// Check if engine is installed in subdirectory
if (preg_match('#^http\:\/\/([^\/])+(\/.+)#', $config['home_url'], $match))
	$UHANDLER->setOptions(array('localPrefix' => $match[2]));
$runResult = $UHANDLER->run($systemAccessURL, array('debug' => false));

// [[MARKER]] URL handler execution is finished
$timer->registerEvent('URL handler execution is finished');

// Generate fatal 404 error [NOT FOUND] if URL handler didn't found any task for execution
if (!$runResult) {
	error404();
}

// External call: after executing URL handler
executeActionHandler('index');

// ===================================================================
// Generate additional informational blocks
// ===================================================================
$timer->registerEvent('General plugins execution is finished');

// Generate category menu
$template['vars']['categories'] = generateCategoryMenu();
$timer->registerEvent('Category menu created');

// Generate page title
$template['vars']['titles'] = join(' — ', array_values($SYSTEM_FLAGS['info']['title']));

// Generate user menu
coreUserMenu();

// Generate search form
coreSearchForm();

// Save 'category' variable
$template['vars']['category'] = (isset($_REQUEST['category']) and ($_REQUEST['category'] != ''))?secure_html($_REQUEST['category']) : '';

// ====================================================================
// External call: All variables for main template are generated
// ===================================================================
executeActionHandler('index_post');

// ===================================================================
// Prepare JS/CSS/RSS references

// Fill extra CSS links
foreach ($EXTRA_CSS as $css => $null)
	$EXTRA_HTML_VARS[] = array('type' => 'css', 'data' => $css);

// Generate metatags
array_unshift($EXTRA_HTML_VARS, array('type' => 'plain', 'data' => GetMetatags())); //$EXTRA_HTML_VARS[]=array('type'=>'plain','data'=>GetMetatags());

// Fill additional HTML vars
$htmlrow = array();
$dupCheck = array();
foreach ($EXTRA_HTML_VARS as $htmlvar) {
	// Skip empty
	if (!$htmlvar['data'])
		continue;

	// Check for duplicated rows
	if (in_array($htmlvar['data'], $dupCheck))
		continue;
	$dupCheck[] = $htmlvar['data'];

	switch ($htmlvar['type']) {
		case 'css': $htmlrow[] = '<link href="' . $htmlvar['data'] . '" rel="stylesheet" />'; break;
		case 'js': $htmlrow[] = '<script src="' . $htmlvar['data'] . '"></script>'; break;
		case 'rss': $htmlrow[] = '<link href="' . $htmlvar['data'] . '" rel="alternate" type="application/rss+xml" title="RSS" />'; break;
		case 'plain': $htmlrow[] = $htmlvar['data']; break;
	}
}
if (count($htmlrow))
	$template['vars']['htmlvars'] .= join("\n\t", $htmlrow);

// Add support of blocks [is-logged] .. [/isnt-logged] in main template
$template['regx']['#\[is-logged\](.+?)\[/is-logged\]#is']		= is_array($userROW)?'$1':'';
$template['regx']['#\[isnt-logged\](.+?)\[/isnt-logged\]#is']	= is_array($userROW)?'':'$1';

// ***** EXECUTION TIME CATCH POINT *****
// Calculate script execution time
$template['vars']['queries'] = $mysql -> qcnt();
$template['vars']['exectime'] = $timer -> stop();

// Fill debug information (if it is requested)
if ($config['debug']) {
	$timer->registerEvent('Templates generation time: '.$tpl->execTime. ' ('.$tpl->execCount.' times called)');
	$timer->registerEvent('Generate DEBUG output');
	if (is_array($userROW) && ($userROW['status'] == 1)) {
		$template['vars']['debug_queries'] = ($config['debug_queries'])?('<b><u>SQL queries:</u></b><br>'.implode("<br />\n",$mysql->query_list)."<br />"):'';
		$template['vars']['debug_profiler'] = ($config['debug_profiler'])?('<b><u>Time profiler:</u></b>'.$timer->printEvents(1)."<br />"):'';
		$template['vars']['[debug]'] = '';
		$template['vars']['[/debug]'] = '';
	} else {
		$template['regx']["#\[debug\].*?\[/debug\]#si"] = '';
	}
}

// ===================================================================
// Generate template for main page
// ===================================================================
// 0. Calculate memory PEAK usage
$template['vars']['memPeakUsage'] = sprintf("%7.3f", (memory_get_peak_usage()/1024/1024));

// 1. Determine template name & path
$mainTemplateName = isset($SYSTEM_FLAGS['template.main.name']) ? $SYSTEM_FLAGS['template.main.name'] : 'main';
$mainTemplatePath = isset($SYSTEM_FLAGS['template.main.path']) ? $SYSTEM_FLAGS['template.main.path'] : tpl_site;

// 2. Load & show template
$tpl -> template($mainTemplateName, $mainTemplatePath);
$tpl -> vars($mainTemplateName, $template);
if (!$SUPRESS_TEMPLATE_SHOW) {
	printHTTPheaders();
	echo $tpl -> show($mainTemplateName);
} else if (!$SUPRESS_MAINBLOCK_SHOW) {
	printHTTPheaders();
	echo $template['vars']['mainblock'];
}

// ===================================================================
// Maintanance activities
// ===================================================================
// Close opened sessions to avoid blocks
session_write_close();

// Run CRON
$cron->run();

// Terminate execution of script
coreNormalTerminate();
