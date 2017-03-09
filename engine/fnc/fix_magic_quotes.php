<?php

//
// Magic quotes FIX

function fix_magic_quotes() {
	if (get_magic_quotes_gpc()) {
		$_GET = strips($_GET);
		$_POST = strips($_POST);
		$_COOKIE = strips($_COOKIE);
		$_REQUEST = strips($_REQUEST);
		$_SESSION = strips($_SESSION);
		$_SERVER = strips($_SERVER);
		$_FILES = strips($_FILES);
		$_ENV = strips($_ENV);
	}
}

function strips($value) {
	if( is_array($value) )
		$value = array_map('strips', $value) ;
	elseif (!empty($value) and is_string($value))
		$value = stripslashes($value);
	
	return $value;
}

fix_magic_quotes();
