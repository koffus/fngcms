<?php

//
// Copyright (C) 2006-2011 Next Generation CMS (http://ngcms.ru)
// Name: captcha.php
// Description: printing captcha
// Author: NGCMS project team
//

@require_once('core.php');

// Print HTTP headers
@header('Content-type: image/png');
@header('Expires: '.		gmdate('D, d M Y H:i:s', time()+ 30).	' GMT');
@header('last-modified: '.	gmdate('D, d M Y H:i:s', time()).		' GMT');

// Determine captcha block identifier
$blockName = isset($_REQUEST['id'])?$_REQUEST['id']:'';

// Determine captcha number to show
$cShowNumber = 'n/c';

// Check if special block is requested
if ( trim($blockName) ) {
	// Check if captchaID is prepared for this block
	if (isset($_SESSION['captcha.'.$blockName]) and ($_SESSION['captcha.'.$blockName])) {
		$cShowNumber = $_SESSION['captcha.'.$blockName];
	} else {
		// No, captcha is not set. But we can generate it dynamically for ACTIVE plugins
		if (pluginIsActive($blockName)) {
			$cShowNumber = rand(00000, 99999);
			$_SESSION['captcha.'.$blockName] = $cShowNumber;
		}
	}
} else {
	// Prepare general captcha
	$cShowNumber = $_SESSION['captcha'];
}

$captc = new Captcha;
$captc->makeimg($cShowNumber);
