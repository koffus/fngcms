<?php

//
// Copyright (C) 2006-2017 BixBite CMS (http://bixbite.site/)
// Name: files.php
// Description: File managment
// Author: Vitaly Ponomarev
//

// Protect against hack attempts
if (!defined('BBCMS')) die ('HAL');

Lang::load('files', 'admin');
@include_once root.'includes/inc/file_managment.php';

// =======================================
// BODY
// =======================================

// Init file managment class
$fmanager = new FileManagment();

if($userROW['status'] > "3" or ($userROW['status'] != "1" and ($action == "imagedelete" or $action == "move")) or ($userROW['status'] > "3" and $action == "rename")) {
	msg(array('type' => 'danger', 'message' => __('msge_mod')));
}


switch($subaction){
	case "newcat": $fmanager->category_create("file", $_REQUEST['newfolder']);	break;
	case "delcat": $fmanager->category_delete("file", $_REQUEST['category']);		break;
	case "delete": manage_delete('file'); break;
	case "rename": $fmanager->file_rename(array('type' => 'file', 'id' => $_REQUEST['id'], 'newname' => $_REQUEST['rf'])); break;
	case "move": manage_move('file'); break;
	case "upload":
	case "uploadurl":	manage_upload('file'); break;

}

manage_showlist('file');


