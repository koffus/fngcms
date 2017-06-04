<?php

//
// Copyright (C) 2006-2017 Next Generation CMS (http://ngcms.ru/)
// Name: news.rpc.php
// Description: RPC library for NEWS manipulation
// Author: RusiQ
//

// Protect against hack attempts
if (!defined('NGCMS')) die ('HAL');

// Load library
Lang::load('news.rpc', 'admin');

function admNewsRPCdouble($params) {
	global $userROW, $mysql, $parse;

	// Check for permissions
	if (!checkPermission(array('plugin' => '#admin', 'item' => 'news'), null, 'modify')) {
		return array( 'status' => 0, 'errorCode' => 3, 'errorText' => __('access_denied') );
	}

	// Check for permissions
	if (!is_array($userROW) or ($userROW['status'] != 1)) {
		return array( 'status' => 0, 'errorCode' => 3, 'errorText' => __('access_denied') );
	}

	// Scan incoming params
	if (!is_array($params) or !isset($params['title']) or !isset($params['token'])) {
		return array( 'status' => 0, 'errorCode' => 4, 'errorText' => __('wrong_params_type') );
	}

	// Check for security token
	if ( $params['token'] != genUToken('admin.news.'.$params['mode']) ) {
		return array('status' => 0, 'errorCode' => 5, 'errorText' => __('wrong_security_code') );
	}

	// PREPARE FILTER RULES FOR NEWS SHOWER
	$search_words = secure_html( $parse->truncateHTML( $params['title'], 64, '' ) );
	$search_words = trim(str_replace(array('<', '>', '%', '$', '#'), '', $search_words));

	if ( empty($search_words) )
		return array( 'status' => 0, 'errorCode' => 4, 'errorText' => __('wrong_params_type') );

	// Check for searched word
	$search_words = mb_split('[ \,\.]+', $search_words); // (\\x20|\t|\r|\n)+
	if (!is_array($search_words)) {
		return array( 'status' => 0, 'errorCode' => 4, 'errorText' => __('wrong_params_type') );
	}

	$search_array = array();
	foreach ($search_words as $s) {
		if (mb_strlen($s, 'UTF-8') > 3) {
			array_push($search_array, "(title Like '%".$mysql->db_quote($s)."%')");
		}
	}

	if ( !count($search_array) ) {
		return array( 'status' => 0, 'errorCode' => 4, 'errorText' => __('wrong_params_type') );
	}
	if ( intval(secure_html($params['news_id'])) )
		array_push( $search_array, "id !=".$mysql->db_quote(intval($params['news_id'])) );

	$selectResult = $mysql->select( "SELECT id, title, catid, alt_name FROM ".prefix."_news WHERE ".join(" AND ", $search_array), 1 );

	foreach ($selectResult as $row) {
		$data[] = array(
			'url' => newsGenerateLink($row),
			'title' => secure_html($row['title']),
			);
	}

	if ( count($data) ) {
		return array('status' => 1, 'errorCode' => 0, 'header' => __('msgi_dubl'), 'data' => $data);
	} else {
		return array('status' => 1, 'errorCode' => 0, 'info' => __('msgi_no_dubl'));
	}

	/*switch ($params['mode']) {
		// Delete category
		case 'del':
			foreach($mysql->select("select * from ".prefix."_category order by posorder", 1) as $v){
				$ncat[$v['id']] = $v;
				$tree[$v['id']] = array('children' => array(), 'parent' => $v['parent'], 'poslevel' => $v['poslevel']);
			}

			return (array('status' => 1, 'errorCode' => 0, 'errorText' => 'Ok', 'infoCode' => 1, 'infoText' => 'Category was deleted', 'content' => $data));

	}*/

	return array('status' => 0, 'errorCode' => 999, 'errorText' => 'Params: '.secure_html($params['title']));
}

if (function_exists('rpcRegisterAdminFunction')) {
	rpcRegisterAdminFunction('admin.news.double', 'admNewsRPCdouble');
}
