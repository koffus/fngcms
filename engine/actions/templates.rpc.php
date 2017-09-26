<?php

//
// Copyright (C) 2012 BixBite CMS (http://bixbite.site/)
// Name: templates.rpc.php
// Description: Externally available library for TEMPLATES manipulation
// Author: Vitaly Ponomarev
//

// Protect against hack attempts
if (!defined('BBCMS')) die ('HAL');

// Load library
Lang::load('templates', 'admin');

// ////////////////////////////////////////////////////////////////////////////
// Processing functions :: Get list of files from TEMPLATE
// ///////////////////////////////////////////////////////////////////////////
function admTemplatesWalkPTemplates($dir) {
	// Scan directory with plugins
	$dirBase = extras_dir;

	$lDirs = array();
	$lFiles = array();

	$flagList = false;
	if ( $dir == '' ) {
		$flagList = true;
	} else {
		if ( strpos($dir, '/') < 1 ) {
			// Return nothing if plugin is not identified
			return array($ldirs, $lfiles);
		}
		$pluginID = substr($dir, 0, strpos($dir, '/'));
		$pluginPath = substr($dir, strpos($dir, '/'));

		if ( !is_dir($dirBase .'/' . $pluginID) or ! is_dir($dirBase . '/' . $pluginID . '/tpl/') ) {
			// Return nothing if plugin doesn't have [tpl/] directory or doesn't have target directory
			return array($ldirs, $lfiles);
		}

		$dirBase = $dirBase . '/' . $pluginID . '/tpl/' . $pluginPath;
	}

	$d = opendir($dirBase);
	while ($f = readdir($d)) {
		if ( ($f == '.') or ($f == '..') )
			continue;

		// Skip plugins that don't have subdirectory [tpl/]
		if ( $flagList and (!is_dir($dirBase . '/' . $f) or !is_dir($dirBase . '/' . $f . '/tpl/')) )
			continue;

		if ( is_dir($dirBase . '/' . $f) ) {
			$lDirs []= $f;
		} else if ( !$flagList and is_file($dirBase . '/'. $f) ) {
			$lFiles []= $f;
		}

	}

	return array($lDirs, $lFiles);
}

function admTemplatesListFiles($params) {

	// Check for permissions
	if ( !checkPermission(array('plugin' => '#admin', 'item' => 'templates'), null, 'details') ) {
		return array( 'status' => 0, 'errorCode' => 3, 'errorText' => __('access_denied') );
	}

	// Scan incoming params
	if ( !is_array($params) or !isset($params['template']) or !isset($params['dir']) or !isset($params['token']) ) {
		return array( 'status' => 0, 'errorCode' => 4, 'errorText' => __('wrong_params_type') );
	}

	// Check for security token
	if ( $params['token'] != genUToken('admin.templates') ) {
		return array( 'status' => 0, 'errorCode' => 5, 'errorText' => __('wrong_security_code') );
	}

	// Prepare arrays for result
	$lDirs = array();
	$lFiles = array();

	$dir = str_replace('/../', '', $params['dir']);
	if ( $dir == '/' )
		$dir = '';

	// Check if [plugins] mode is used
	if ( $params['template'] == '#plugins' ) {
		list($lDirs, $lFiles) = admTemplatesWalkPTemplates($dir);

	} else {
		// Check if specified directory exists [ and secure it ]
		$template = str_replace('/', '', $params['template']);
		$dirBase = tpl_dir;

		if ( is_dir($dirBase . '/' . $template . '/' . $dir) ) {
			$scanDir = $dirBase . '/' . $template . '/' . $dir;
			$d = opendir($scanDir);

			while ($f = readdir($d)) {
				if (($f == '.') or ($f == '..'))
					continue;

				if (is_dir($scanDir . '/' . $f)) {
					$lDirs []= $f;
				} else if (is_file($scanDir . '/' . $f)) {
					$lFiles []= $f;
				}
			}
			closedir($d);
		}
	}

	// Sort resulting arrays
	natcasesort($lDirs);
	natcasesort($lFiles);

	$result = '';
	if (count($lDirs) or count($lFiles)) {
		$result = '<ul class="jqueryFileTree" style="display: none;">';
		foreach ($lDirs as $x) {
			$result .= '<li class="directory collapsed"><a href="#" rel="'.htmlentities($dir.$x). '/">'.htmlentities($x).'</a></li>';
		}
		foreach ($lFiles as $x) {
			$ext = '';
			if (strrpos($x, '.') > 1) {
				$ext = substr($x, strrpos($x, '.')+1);
			}
			if (!in_array($ext, array('tpl', 'ini', 'css', 'js', 'gif', 'png', 'jpg')))
				$ext = 'file';
			$result .= '<li class="file ext_'.$ext.'"><a href="#" rel="'.htmlentities($dir.$x). '">'.htmlentities($x).'</a></li>';
		}
	}
	return array('status' => 1, 'errorCode' => 0, 'content' => $result);
}

function admTemplatesGetFile($params) {
	$resultFileName = '';
	$dirBase = '';

	// Check for permissions
	if (!checkPermission(array('plugin' => '#admin', 'item' => 'templates'), null, 'details')) {
		return array( 'status' => 0, 'errorCode' => 3, 'errorText' => __('access_denied') );
	}

	// Scan incoming params
	if (!is_array($params) or !isset($params['template']) or !isset($params['file']) or !isset($params['token'])) {
		return array( 'status' => 0, 'errorCode' => 4, 'errorText' => __('wrong_params_type') );
	}

	// Check for security token
	if ($params['token'] != genUToken('admin.templates')) {
		return array( 'status' => 0, 'errorCode' => 5, 'errorText' => __('wrong_security_code') );
	}

	$template = str_replace('/', '', $params['template']);
	$file = str_replace('/../', '', $params['file']);
	$dirBase = tpl_dir;

	if ($template == '#plugins') {
		if (strpos($file, '/') < 1) {
			return array('status' => 0, 'errorCode' => 7, 'errorText' => __('strange_request_path') );
		}
		$pluginID = substr($file, 0, strpos($file, '/'));
		$pluginFile = substr($file, strpos($file, '/')+1);

		$dirBase = extras_dir;
		$resultFileName = $dirBase . '/' . $pluginID . '/tpl/' . $pluginFile;
		$resultFileURL = admin_url.'/plugins/'.$pluginID.'/tpl/'.$pluginFile;
	} else {
		$dirBase = tpl_dir;
		$resultFileName = $dirBase . $template . '/' . $file;
		$resultFileURL = home.'/templates/'.$template.'/'.$file;
	}

	if (!is_file($resultFileName)) {
		return array('status' => 0, 'errorCode' => 6, 'errorText' => __('file_not_found') . ' ['.$resultFileName.']');
	}

	$ext = '';
	if (strrpos($file, '.') > 1) {
		$ext = substr($file, strrpos($file, '.')+1);
	}

	$type = 'text';
	if (in_array($ext, array('gif', 'png', 'jpg'))) {
		list($imgW, $imgH, $imgType, $imgAttr) = @getimagesize($resultFileName);
		$data = 'Image size: <b>'.$imgW.' px</b> * <b>'.$imgH.' px</b><br/><img border="1" style="max-height: 500px; max-width: 700px;" src="'.$resultFileURL.'"/>';
		$type = 'image';
	} else {
		$data = file_get_contents($resultFileName);
	}
	
	if ( ($fileTime = @filemtime($resultFileName)) > 0 ) {
		$fileTimeStr = strftime('%d-%m-%Y %H:%M', $fileTime);
	} else {
		$fileTimeStr = 'unknown';
	}
	
	return array('status' => 1, 'errorCode' => 0, 'content' => $data, 'size' => @filesize($resultFileName), 'lastChange' => $fileTimeStr, 'type' => $type);
}

function admTemplatesUpdateFile($params) {

	// Check for permissions
	if ( !checkPermission(array('plugin' => '#admin', 'item' => 'templates'), null, 'modify') ) {
		return array('status' => 0, 'errorCode' => 3, 'errorText' => __('access_denied') );
	}

	// Scan incoming params
	if ( !is_array($params) or !isset($params['template']) or !isset($params['file']) or !isset($params['content']) or !isset($params['token']) ) {
		return array('status' => 0, 'errorCode' => 4, 'errorText' => __('wrong_params_type') );
	}

	// Check for security token
	if ( $params['token'] != genUToken('admin.templates') ) {
		return array('status' => 0, 'errorCode' => 5, 'errorText' => __('wrong_security_code') );
	}

	$template = str_replace('/', '', $params['template']);
	$file = str_replace('/../', '', $params['file']);
	$dirBase = tpl_dir;

	if ( $template == '#plugins' ) {
		if ( strpos($file, '/' ) < 1) {
			return array('status' => 0, 'errorCode' => 7, 'errorText' => __('strange_request_path') );
		}
		$pluginID = substr($file, 0, strpos($file, '/'));
		$pluginFile = substr($file, strpos($file, '/')+1);

		$dirBase = extras_dir;
		$resultFileName = $dirBase . '/' . $pluginID . '/tpl/' . $pluginFile;
		$resultFileURL = admin_url.'/plugins/'.$pluginID.'/tpl/'.$pluginFile;
	} else {
		$dirBase = tpl_dir;
		$resultFileName = $dirBase . $template . '/' . $file;
		$resultFileURL = home.'/templates/'.$template.'/'.$file;
	}

	if ( !is_file($resultFileName) ) {
		return array('status' => 0, 'errorCode' => 6, 'errorText' => __('file_does_not_exists') . ' ['.$resultFileName.']');
	}

	if ( !is_writable($resultFileName) ) {
		return array('status' => 0, 'errorCode' => 8, 'errorText' => __('dont_have_write_privileges_for') . ' ['.$resultFileName.']');
	}

	$newData = $params['content'];
	$origData = file_get_contents($resultFileName);

	// Notify if file was not changed
	if ( $newData == $origData ) {
		return array('status' => 1, 'errorCode' => 0, 'content' => __('file_was_not_modified') . ' ['.$resultFileName.']');
	}

	if ( ($fp = @fopen($resultFileName, 'wb+')) !== FALSE ) {
		fputs($fp, $newData);
		fclose($fp);
		return array('status' => 1, 'errorCode' => 0, 'content' => __('update_complete') . ' ['.$resultFileName.']');
	}

	return array('status' => 0, 'errorCode' => 9, 'errorText' => __('error_writing_into_file') . ' ['.$resultFileName.']');
}

if (function_exists('rpcRegisterAdminFunction')) {
	rpcRegisterAdminFunction('admin.templates.listFiles', 'admTemplatesListFiles');
	rpcRegisterAdminFunction('admin.templates.getFile', 'admTemplatesGetFile');
	rpcRegisterAdminFunction('admin.templates.updateFile', 'admTemplatesUpdateFile');
}
