<?php

//
// Copyright (C) 2006-2017 BixBite CMS (http://bixbite.site/)
// Name: files.rpc.php
// Description: Externally available library for File/Image management
// Author: Vitaly Ponomarev
//

// Protect against hack attempts
if (!defined('BBCMS')) die ('HAL');

//
// Manage upload request from external uploadify script
function admRPCFilesUpload($params){
	global $mysql, $AUTH_METHOD, $config, $userROW;

	// Don't allow to do anything by guests
	if (!is_array($userROW)) {
		// Not authenticated, return.
		return array('status' => 0, 'errorCode' => 1, 'errorText' => '[RPC] You are not logged in');
	}

	// Now user is authenticated.
	$fmanager = new FileManagment();
	$imanager = new ImageManagment();

	// Check parameters:
	// - type: file / image
	$uploadType = $params['uploadType'];
	if (($uploadType != 'file') and ($uploadType != 'image')) {
		@header('HTTP/1.1 404 Wrong upload type');
		return;
	}

	$fmanager->get_limits($uploadType);
	$dir = $fmanager->dname;
//print json_encode(array( 'status' => 0, 'errorCode' => '-1', 'errorText' =>  var_export($_REQUEST, true) ));
	$ures = $fmanager->file_upload(array(
		'rpc' => 1,
		'dsn' => 0,
		'category' => ($params['category'] == '')?'default':$params['category'],
		'type' => $uploadType,
		'replace' => $params['replace'],
		'randprefix' => $params['rand'],
		'http_var' => 'Filedata',
	));

	// Return if this is a file or we have upload error
	if (($uploadType == 'file') or (!$ures['status'])) {
		return $ures;
	}

	// For images - we need to transform images
	$mkThumb = (($config['thumb_mode'] == 2) or (!$config['thumb_mode'] and $params['thumb']))?1:0;
	$mkStamp = (($config['stamp_mode'] == 2) or (!$config['stamp_mode'] and $params['stamp']))?1:0;
	$mkShadow = (($config['shadow_mode'] == 2) or (!$config['shadow_mode'] and $params['shadow']))?1:0;

    $stamp = '';
	$stampFileName = '';
	if (file_exists(root.'trash/'.$config['wm_image'].'.gif')) {
		$stampFileName = root.'trash/'.$config['wm_image'].'.gif';
	} else if (file_exists(root.'trash/'.$config['wm_image'])) {
		$stampFileName = root.'trash/'.$config['wm_image'];
	}

	$thumb = '';
	if ($mkThumb) {
		$tsx = intval($config['thumb_size_x'])?intval($config['thumb_size_x']):intval($config['thumb_size']);
		$tsy = intval($config['thumb_size_y'])?intval($config['thumb_size_y']):intval($config['thumb_size']);
		if (($tsx < 10)||($tsx > 1000)) $tsx = 150;
		if (($tsy < 10)||($tsy > 1000)) $tsy = 150;
		$thumb = $imanager->create_thumb($config['images_dir'].$ures['data']['category'], $ures['data']['name'], $tsx,$tsy, $config['thumb_quality'], array('rpc' => 1));
		$ures['data']['thumb'] = $thumb;
		if (is_array($thumb) and ($thumb['status'])) {
			// If we created thumb - check if we need to transform it
			$stampThumb = ( $mkStamp and $config['stamp_place'] and (trim($stampFileName)) )?1:0;
			$shadowThumb = ($mkShadow and $config['shadow_place'])?1:0;
			if ($shadowThumb or $stampThumb) {
				$stamp = $imanager->image_transform(
					array('image' => $dir.$ures['data']['category'].'/thumb/'.$ures['data']['name'],
					'stamp' => $stampThumb,
					'stamp_transparency' => $config['wm_image_transition'],
					'shadow' => $shadowThumb,
					'stampfile' => $stampFileName));
				$ures['data']['thumbstamp'] = $stamp;
			}
		}
	}

	$stampOrig = ( $mkStamp and ($config['stamp_place'] != 1) and (trim($stampFileName)) )?1:0;
	$shadowOrig = ($mkShadow and ($config['shadow_place'] != 1))?1:0;

	if ($shadowOrig or $stampOrig) {
		$stamp = $imanager->image_transform(
			array('image' => $dir.$ures['data']['category'].'/'.$ures['data']['name'],
			'stamp' => $stampOrig,
			'stamp_transparency' => $config['wm_image_transition'],
			'shadow' => $shadowOrig,
			'stampfile' => $stampFileName,
			'rpc' => 1));
		$ures['data']['stamp'] = $stamp;
	}

	// Now write info about image into DB
	if (is_array($sz = $imanager->get_size($dir.$ures['data']['category'].'/'.$ures['data']['name']))) {
		$fmanager->get_limits($uploadType);

		// Gather filesize for thumbinals
		$thumb_size_x = 0;
		$thumb_size_y = 0;
		if (is_array($thumb) and is_readable($dir.$ures['data']['category'].'/thumb/'.$ures['data']['name']) and is_array($szt = $imanager->get_size($dir.$ures['data']['category'].'/thumb/'.$ures['data']['name']))) {
			$thumb_size_x = $szt[1];
			$thumb_size_y = $szt[2];
		}
		$mysql->query("update ".prefix."_".$fmanager->tname." set width=".db_squote($sz[1]).", height=".db_squote($sz[2]).", preview=".db_squote(is_array($thumb)?1:0).", p_width=".db_squote($thumb_size_x).", p_height=".db_squote($thumb_size_y).", stamp=".db_squote(is_array($stamp)?1:0)." where id = ".db_squote($ures['data']['id']));
	}

	return $ures;
}

if (function_exists('rpcRegisterAdminFunction')) {
	rpcRegisterAdminFunction('admin.files.upload', 'admRPCFilesUpload');
}
