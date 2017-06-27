<?php

//
// Online Auto-Keys generator
//

// Load library
Lang::load('news.rpc', 'admin');

function akeysGenerate($params){
    global $userROW;

    // Load library
    @include_once(root.'/plugins/autokeys/lib/Autokeys.class.php');

    // Check for permissions
    if (!checkPermission(array('plugin' => '#admin', 'item' => 'news'), null, 'modify')) {
        return array( 'status' => 0, 'errorCode' => 3, 'errorText' => __('access_denied') );
    }

    // Check for permissions
    if (!isset($userROW) and !is_array($userROW) or ($userROW['status'] != 1)) {
        return array( 'status' => 0, 'errorCode' => 3, 'errorText' => __('access_denied') );
    }

   // Scan incoming params
	if (!is_array($params) or !isset($params['title']) or !isset($params['token'])) {
		return array( 'status' => 0, 'errorCode' => 4, 'errorText' => __('wrong_params_type') );
	}

    // Generate keywords
    $words = akeysGetKeys(array('title' => $params['title'], 'content' => $params['content']));

    // Return output
    return array('status' => 1, 'errorCode' => 0, 'data' => $words);
}

rpcRegisterFunction('plugin.autokeys.generate', 'akeysGenerate');

