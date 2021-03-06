<?php

//
// Copyright (C) 2006-2017 BixBite CMS (http://bixbite.site/)
// Name: images.php
// Description: Images managment
// Author: Vitaly Ponomarev
//

// Protect against hack attempts
if (!defined('BBCMS')) die ('HAL');

Lang::load('images', 'admin');
include_once root.'includes/inc/file_managment.php';


// =======================================
// BODY
// =======================================

// Init file managment class
$fmanager = new FileManagment();

if($userROW['status'] > 3 or ($userROW['status'] != 1 and ($action == "imagedelete" or $action == "move")) or ($userROW['status'] > 3 and $action == "rename")) {
    msg(array('type' => 'danger', 'message' => __('msge_mod')));
}

switch($subaction){
    case "newcat": $fmanager->category_create("image", $_REQUEST['newfolder']); break;
    case "delcat": $fmanager->category_delete("image", $_REQUEST['category']); break;
    case "delete": manage_delete('image'); break;
    case "rename": $fmanager->file_rename(array('type' => 'image', 'id' => $_REQUEST['id'], 'newname' => $_REQUEST['rf'])); break;
    case "move": manage_move('image'); break;
    case "upload":
    case "uploadurl": manage_upload('image'); break;
    case "editForm": manage_editForm('image', $_REQUEST['id']); break;
    case "editApply": manage_editApply('image', $_POST['id']); break;
}

if (($subaction != 'editForm') and ($subaction != 'editApply'))
    manage_showlist('image');
