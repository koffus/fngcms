<?php

//
// Copyright (C) 2006-2014 Next Generation CMS (http://ngcms.ru/)
// Name: configuration.php
// Description: Configuration managment
// Author: Vitaly Ponomarev, Alexey Zinchenko
//

// Protect against hack attempts
if (!defined('NGCMS')) die ('HAL');

Lang::load('configuration', 'admin');

function twigmkSelect($params) {
	$values = '';
	if ( isset($params['values']) and is_array($params['values']) ) {
		foreach ( $params['values'] as $k => $v ) {
			$values .= '<option value="' . $k . '"' . ( ($k == $params['value']) ? ' selected="selected"' : '' ) . '>' . $v . '</option>';
		}
	}

	return '<select ' . (isset($params['id']) ? 'id="' . $params['id'] . '" ':'').'name="' . $params['name'] . '" class="form-control">' . $values . '</select>';
}

function twigmkSelectYN($params) {
	$params['values'] = array(1 => __('yesa'), 0 => __('noa'));
	return twigmkSelect($params);
}

function twigmkSelectNY($params) {
	$params['values'] = array(0 => __('noa'), 1 => __('yesa'));
	return twigmkSelect($params);
}

$twig->addFunction('mkSelect',		new Twig_Function_Function('twigmkSelect'));
$twig->addFunction('mkSelectYN',	new Twig_Function_Function('twigmkSelectYN'));
$twig->addFunction('mkSelectNY',	new Twig_Function_Function('twigmkSelectNY'));

//
// Save system config
function systemConfigSave(){
	global $config, $mysql;

	// Check for permissions
	if (!checkPermission(array('plugin' => '#admin', 'item' => 'configuration'), null, 'modify')) {
		msg(array('type' => 'danger', 'message' => __('perm.denied')), 1, 1);
		ngSYSLOG(array('plugin' => '#admin', 'item' => 'configuration', 'ds_id' => $id), array('action' => 'saveConfig'), null, array(0, 'SECURITY.PERM'));
		return false;
	}

	// Check for security token
	if ((!isset($_REQUEST['token'])) or ($_REQUEST['token'] != genUToken('admin.configuration'))) {
		msg(array('type' => 'danger', 'message' => __('error.security.token'), 'info' => __('error.security.token#desc')));
		ngSYSLOG(array('plugin' => '#admin', 'item' => 'configuration', 'ds_id' => $id), array('action' => 'saveConfig'), null, array(0, 'SECURITY.TOKEN'));
		return false;
	}

	$save_con = $_REQUEST['save_con'];
	if (is_null($save_con) or !is_array($save_con)) {
		return false;
	}

	// Check if DB connection params are correct
	$sqlTest = new Database;
	if (!$sqlTest->connect($save_con['dbhost'],$save_con['dbuser'], $save_con['dbpasswd'], $save_con['dbname'], 1)) {
		msg(array('type' => 'danger', 'message' => __('dbcheck_error')));
		return false;
	};

	// Save our UUID or regenerate LOST UUID
	$save_con['UUID'] = $config['UUID'];
	if ($save_con['UUID'] == '') {
		$save_con['UUID'] = md5(mt_rand().mt_rand()).md5(mt_rand().mt_rand());
	}

	// Manage "load_profiler" variable
	$save_con['load_profiler'] = intval($save_con['load_profiler']);
	if (($save_con['load_profiler'] > 0) and ($save_con['load_profiler'] < 86400)) {
		$save_con['load_profiler'] = time() + $save_con['load_profiler'];
	} else {
		$save_con['load_profiler'] = 0;
	}

	// Prepare resulting config content
	$fcData = "<?php\n".'$config = '.var_export($save_con, true)."\n;?>";

	// Try to save config
	$fcHandler = @fopen(confroot.'config.php', 'w');
	if ($fcHandler) {
		fwrite($fcHandler, $fcData);
		fclose($fcHandler);
		msg(array('message' => __('msgo_saved')));
	} else {
		msg(array('type' => 'danger', 'title' => __('msge_save_error'), 'message' => __('msge_save_error#desc')));
		return false;
	}

	ngSYSLOG(array('plugin' => '#admin', 'item' => 'configuration', 'ds_id' => $id), array('action' => 'saveConfig', 'list' => $fcData), null, array(1, ''));
	return true;
}

//
// Show configuration form
function systemConfigEditForm(){
	global $AUTH_CAPABILITIES, $PHP_SELF, $twig, $multiconfig;

	// Check for token
	if (!checkPermission(array('plugin' => '#admin', 'item' => 'configuration'), null, 'details')) {
		msg(array('type' => 'danger', 'message' => __('perm.denied')));
		ngSYSLOG(array('plugin' => '#admin', 'item' => 'configuration', 'ds_id' => $id), array('action' => 'showConfig'), null, array(0, 'SECURITY.PERM'));
		return false;
	}

	$auth_modules = array();
	$auth_dbs = array();

	foreach ($AUTH_CAPABILITIES as $k => $v) {
		if ($v['login']) {
			$auth_modules[$k] = $k;
		}
		if ($v['db']) {
			$auth_dbs[$k] = $k;
		}
	}

	// Load config file from configuration
	// Now in $config we have original version of configuration data
	include confroot.'config.php';

	$load_profiler = $config['load_profiler'] - time();
	if (($load_profiler < 0) or ($load_profiler > 86400))
		$config['load_profiler'] = 0;

	$mConfig = array();
	if(is_array($multiconfig))
		foreach ($multiconfig as $k => $v) {
			$v['key'] = $k;
			$mConfig []= $v;
		}

	// Set default timeZone if it's empty
	if (!$config['timezone'])
		$config['timezone'] = 'Europe/Moscow';

	$tVars = array(
		// SYSTEM CONFIG is available via `config` variable
		'config' => $config,
		'list' => array(
			'captcha_font' => ListFiles('trash', 'ttf'),
			'theme' => ListFiles('../templates',''),
			'default_lang' => ListFiles('lang', ''),
			'wm_image' => ListFiles('trash', array('gif', 'png'), 2),
			'auth_module' => $auth_modules,
			'auth_db' => $auth_dbs,
			'timezoneList' => timezone_identifiers_list(),
		),
		'php_self' => $PHP_SELF,
		'timestamp_active_now' => Lang::retDate($config['timestamp_active'], time()),
		'timestamp_updated_now' => Lang::retDate($config['timestamp_updated'], time()),
		'token' => genUToken('admin.configuration'),
		'multiConfig' => $mConfig,
	);

	//
	// Fill parameters for multiconfig
	$multiList = array();
	$tmpline = '';
	if (is_array($multiconfig)) {
		foreach ($multiconfig as $mid => $mline) {
			$tmpdom = implode("\n",$mline['domains']);
			$tmpline .= "<tr class='contentEntry1'><td>".($mline['active']?'On':'Off')."</td><td>$mid</td><td>".($tmpdom?$tmpdom:'-не указано-')."</td><td>&nbsp;</td></tr>\n";
		}
	}
	$tvars['vars']['multilist'] = $tmpline;
	$tvars['vars']['defaultSection'] = (isset($_REQUEST['selectedOption']) and $_REQUEST['selectedOption'])?htmlspecialchars($_REQUEST['selectedOption'], ENT_COMPAT | ENT_HTML401, 'UTF-8'):'news';

	$xt = $twig->loadTemplate('skins/default/tpl/configuration.tpl');
	echo $xt->render($tVars);
}

//
// Check if SAVE is requested and SAVE was successfull
if ( isset($_REQUEST['subaction']) and ($_REQUEST['subaction'] == 'save') and ($_SERVER['REQUEST_METHOD'] == 'POST') and systemConfigSave() ) {
	@include(confroot.'config.php');
}

// Show configuration form
systemConfigEditForm();
