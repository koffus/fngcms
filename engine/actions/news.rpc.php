<?php

//
// Copyright (C) 2006-2017 BixBite CMS (http://bixbite.site/)
// Name: news.rpc.php
// Description: RPC library for NEWS manipulation
// Author: RusiQ
//

// Protect against hack attempts
if (!defined('BBCMS')) die ('HAL');

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
        return array( 'status' => 1, 'errorCode' => 999, 'info' => 'Измените условия поиска' );

    // Check for searched word
    $search_words = mb_split('[ \,\.]+', $search_words); // (\\x20|\t|\r|\n)+
    if (!is_array($search_words)) {
        return array( 'status' => 1, 'errorCode' => 999, 'info' => 'Измените условия поиска' );
    }

    $search_array = array();
    foreach ($search_words as $s) {
        if (mb_strlen(trim($s), 'UTF-8') > 3) {
            $search_array[] = "(title Like ".db_squote('%'.$s.'%').")";
        }
    }

    if ( !count($search_array) ) {
        return array( 'status' => 1, 'errorCode' => 999, 'info' => 'Измените условия поиска' );
    }
    $SQL['search'] = "(". join(" AND ", $search_array).")"; // ONLY AND it is dubl search
    if (isset($params['news_id']) and intval(secure_html($params['news_id'])) )
        $SQL['search'] .= " AND id !=".$mysql->db_quote(intval($params['news_id']));

    $selectResult = $mysql->select( "SELECT id, title, catid, alt_name, postdate FROM ".prefix."_news WHERE ".$SQL['search'], 1 );

    $data = array();
    foreach ($selectResult as $row) {
        $data[] = array(
            'id' => intval($row['id']),
            'url' => News::generateLink($row),
            'title' => secure_html($row['title']),
            );
    }

    if ( count($data) ) {
        return array('status' => 1, 'errorCode' => 0, 'header' => __('msgi_dubl'), 'data' => $data);
    } else {
        return array('status' => 1, 'errorCode' => 0, 'info' => __('msgi_no_dubl'));
    }

    return array('status' => 0, 'errorCode' => 999, 'errorText' => 'Params: '.secure_html($params['title']));
}

if (function_exists('rpcRegisterAdminFunction')) {
    rpcRegisterAdminFunction('admin.news.double', 'admNewsRPCdouble');
}
