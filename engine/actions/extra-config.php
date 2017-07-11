<?php

//
// Copyright (C) 2006-2012 Next Generation CMS (http://ngcms.ru/)
// Name: extra-config.php
// Description: Plugin managment
// Author: Vitaly Ponomarev
//

// Protect against hack attempts
if (!defined('NGCMS')) die ('HAL');

@include_once root.'includes/inc/extraconf.inc.php';
@include_once root.'includes/inc/extrainst.inc.php';

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
$plugin = $_REQUEST['plugin'];

// Type of script to call ( install / deinstall / config )
$stype = isset($_REQUEST['stype']) ? $_REQUEST['stype'] : false;
$action = isset($_REQUEST['action']) ? $_REQUEST['action'] : false;

if (!is_array($extras[$plugin])) {
		$tVars = array(
			'action' => __('config_text'),
			'action_text' => __('noplugin'),
			'plugin' => $plugin,
			'php_self' => $PHP_SELF
		);
		$xt = $twig->loadTemplate('skins/default/tpl/extra-config/nomodule.tpl');
		echo $xt->render($tVars);
} else {
	//
	// Call 'install'/'deinstall' script if it's requested. Else - call config script.
	if ( ('install' != $stype) and ('deinstall' != $stype) ) {
		$stype = 'config';
	}

	// Fetch the name of corresponding configuration file
	$cfg_file = extras_dir.'/'.$extras[$plugin]['dir'].'/'.$extras[$plugin][$stype];

	// Check if such type of script is configured in plugin & exists
	if (is_array($extras[$plugin]) and ($extras[$plugin][$stype]) and is_file($cfg_file)) {

		// Security update: for stype == 'config' and POST update action - check for token
		if (('config' == $stype) and ('commit' == $action) and ($_REQUEST['token'] != genUToken('admin.extra-config'))) {
				msg(array('type' => 'danger', 'title' => __('error.security.token'), 'message' => __('error.security.token#desc')));
				ngSYSLOG(array('plugin' => '#admin', 'item' => 'config#'.$plugin), array('action' => 'modify'), null, array(0, 'SECURITY.TOKEN'));
				exit;
		}

		// Include required script file
		include extras_dir.'/'.$extras[$plugin]['dir'].'/'.$extras[$plugin][$stype];

		// Run install function if it exists in file
		if (($stype == 'install') and function_exists('plugin_'.$plugin.'_install')) {
			call_user_func('plugin_'.$plugin.'_install', ('commit' == $action) ? 'apply' : 'confirm');
		}

	} else {
		$tVars['vars'] = array(
			'action' => __($stype.'_text'),
			'action_text' => __('nomod_'.$stype),
			'plugin' => $plugin,
			'php_self' => $PHP_SELF
		);
		$xt = $twig->loadTemplate('skins/default/tpl/extra-config/nomodule.tpl');
		echo $xt->render($tVars);
	}
}