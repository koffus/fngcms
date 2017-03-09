<?php

//
// Copyright (C) 2009-2012 Next Generation CMS (http://ngcms.ru/)
// Name: CoreAuthPlugin.class.php
// Description: Class template definition: registration plugin
// Author: Vitaly Ponomarev
//

class CoreAuthPlugin {
	
	function login($auto_scan = 1, $username = '', $password = '') {
		return 'ERR:METHOD.NOT.IMPLEMENTED';
	}

	function save_auth($dbrow) {
		return false;
	}

	function check_auth() {
		return false;
	}

	function drop_auth() {
		return false;
	}

	function get_reg_params() {
		return false;
	}

	function register(&$params, $values, &$msg) {
		return 0;
	}

	function get_restorepw_params() {
		return false;
	}

	function restorepw(&$params, $values, &$msg) {
		return false;
	}

	function confirm_restorepw(&$msg, $reqid = NULL, $reqsecret = NULL) {
		return false;
	}

	// AJAX call - online check registration parameters for correct valuescheck if login is available
	// Input:
	// $params - array of 'fieldName' => 'fieldValue' for checking
	// Returns:
	// $result - array of 'fieldName' => status
	// List of statuses:
	// 0	- Method not implemented [ this field is not checked/can't be checked/... ] OR NOT SET
	// 1	- Occupied
	// 2	- Incorrect length
	// 3	- Incorrect format
	// 100	- Available for registration
	function onlineCheckRegistration($params) {
		return array();
	}

}
