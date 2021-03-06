<?php

//
// Copyright (C) 2006-2017 BixBite CMS (http://bixbite.site/)
// Name: ugroup.php
// Description: User group management
// Author: Vitaly Ponomarev
//

// Protect against hack attempts
if (!defined('BBCMS')) die ('HAL');
Lang::load('ugroup', 'admin');

function ugroupList(){
	global $mysql, $mod, $userROW, $UGROUP, $twig;

	// Check for permissions
	if (!checkPermission(array('plugin' => '#admin', 'item' => 'ugroup'), null, 'view')) {
		msg(array('type' => 'danger', 'message' => __('perm.denied')));
		return;
	}

	$permModify		= checkPermission(array('plugin' => '#admin', 'item' => 'ugroup'), null, 'modify');
	$permDetails	= checkPermission(array('plugin' => '#admin', 'item' => 'ugroup'), null, 'details');

	// Calculate number of users in each group
	$uCount = array();

	$query = "select status, count(*) as cnt from ".uprefix."_users group by status";
	foreach ($mysql->select($query) as $row) {
		$uCount[$row['status']] = $row['cnt'];
	}

	$tEntries = array();
	foreach ($UGROUP as $id => $grp) {
		$tEntry		= array(
			'id' => $id,
			'identity' => $grp['identity'],
			'name' => $grp['name'],
			'count' => (isset($uCount[$id])&& $uCount[$id])?intval($uCount[$id]):0,
			'flags' => array(
				'canEdit' => $permDetails,
				'canDelete' => (isset($uCount[$id]) and ($uCount[$id] < 1) and $permModify)?true:false,
			),
		);

		$tEntries []= $tEntry;
	}

	$tVars = array(
		'token' => genUToken('admin.ugroup'),
		'entries' => $tEntries,
		'flags' => array(
			'canAdd' => $permModify,
		),
	);

    echo $twig->render(tpl_actions . 'ugroup/list.tpl', $tVars);
}

function ugroupForm(){
	global $mysql, $mod, $PFILTERS, $twig, $UGROUP;

	// ID of group for editing
	$id = intval(getIsSet($_REQUEST['id']));

	// Add/Edit mode flag
	$editMode = ($id > 0)?true:false;

	// Determine user's permissions
	$perm			= checkPermission(array('plugin' => '#admin', 'item' => 'ugroup'), null, array('modify', 'details'));
	$permModify		= $perm['modify'];
	$permDetails	= $perm['details'];

	// Check for permissions
	if (!$perm['modify'] and !$perm['details']) {
		ngSYSLOG(array('plugin' => '#admin', 'item' => 'ugroup', 'ds_id' => $id), array('action' => 'editForm'), null, array(0, 'SECURITY.PERM'));
		msg(array('type' => 'danger', 'message' => __('perm.denied')));
		return;
	}

	// Check if group exist
	if ($editMode and (!isset($UGROUP[$id]))) {
		ngSYSLOG(array('plugin' => '#admin', 'item' => 'ugroup', 'ds_id' => $id), array('action' => 'editForm'), null, array(0, 'NOT.FOUND'));
		msg(array('type' => 'danger', 'message' => __('msge_not_found')));
		return;
	}

	$tVars = array(
		'token' => genUToken('admin.ugroup'),
	);
	if ($editMode) {
		$eGroup = $UGROUP[$id];

		$tVars['entry'] = $eGroup;
		$tVars['entry']['id'] = $id;
	} else {
		$tVars['entry'] = array(
			'id' => 0,
			'langNames' => array(),
		);
	}

	// Update supported languages
	foreach (ListFiles('lang', '') as $langName) {
		if (!isset($tVars['entry']['langName'][$langName])) {
			$tVars['entry']['langName'][$langName] = '';
		}
	}

	$tVars['flags'] = array(
		'editMode' => $editMode,
		'canModify' => $permModify,
	);

    echo $twig->render(tpl_actions . 'ugroup/addEdit.tpl', $tVars);
}

function ugroupCommit(){
	global $mysql, $mod, $PFILTERS, $twig, $UGROUP;

	// ID of group for editing
	$id = intval($_REQUEST['id']);

	// Add/Edit mode flag
	$addMode	= ($_REQUEST['action'] == "add")?true:false;
	$editMode	= ($_REQUEST['action'] == "edit")?true:false;
	$deleteMode	= ($_REQUEST['action'] == "delete")?true:false;

	// Determine user's permissions
	$perm			= checkPermission(array('plugin' => '#admin', 'item' => 'ugroup'), null, array('modify', 'details'));
	$permModify		= $perm['modify'];
	$permDetails	= $perm['details'];

	// Check for permissions
	if (!$perm['modify']) {
		ngSYSLOG(array('plugin' => '#admin', 'item' => 'ugroup', 'ds_id' => $id), array('action' => 'editForm'), null, array(0, 'SECURITY.PERM'));
		msg(array('type' => 'danger', 'message' => __('perm.denied')));
		return;
	}

	// Check for security token
	if ((!isset($_REQUEST['token']))||($_REQUEST['token'] != genUToken('admin.ugroup'))) {
		msg(array('type' => 'danger', 'title' => __('error.security.token'), 'message' => __('error.security.token#desc')));
		ngSYSLOG(array('plugin' => '#admin', 'item' => 'users', 'ds_id' => $id), array('action' => 'editForm'), null, array(0, 'SECURITY.TOKEN'));
		return;
	}

	// Load configuration
	// ** If file exists - load it
	if (is_file(confroot.'ugroup.php')) {
		include confroot.'ugroup.php';
		$edGroup = $confUserGroup;
	} else {
		// ** ELSE - get system defaults
		$edGroup = $UGROUP;
	}

	// Check if group exist [ for EDIT/DELETE mode ]
	if (($editMode or $deleteMode) and (!isset($UGROUP[$id]))) {
		ngSYSLOG(array('plugin' => '#admin', 'item' => 'ugroup', 'ds_id' => $id), array('action' => 'editForm'), null, array(0, 'NOT.FOUND'));
		msg(array('type' => 'danger', 'message' => __('msge_not_found')));
		return;
	}

	// Check for empty identity [ for ADD/EDIT ]
	if (($addMode or $editMode) and (trim($_REQUEST['identity']) == '')) {
		msg(array('type' => 'danger', 'message' => 'Identity is empty'));
		return;
	}

	// Check for conflicted identity [ for ADD/EDIT ]
	if ($addMode or $editMode) {
		$isConflicted = false;
		foreach ($edGroup as $eid => $eval) {
			if ((strtolower($_REQUEST['identity']) == strtolower($eval['identity']))&&($_REQUEST['id'] != $eid)) {
				msg(array('type' => 'danger', 'message' => 'Specified identity is already used for other group'));
				return;
			}
		}
	}

	// ** PROCESS EDIT **
	if ($editMode) {
		// Update group info
		$edGroup[$id]['identity'] = trim($_REQUEST['identity']);

		// Update LANG info
		if (is_array($_REQUEST['langname'])) {
			foreach ($_REQUEST['langname'] as $lk => $lv) {
				$edGroup[$id]['langName'][$lk] = $lv;
			}
		}
	}

	// ** PROCESS ADD **
	if ($addMode) {
		$newGroup = array(
			'identity' => trim($_REQUEST['identity']),
			'langName' => array(),
		);
		if (is_array($_REQUEST['langname'])) {
			foreach ($_REQUEST['langname'] as $lk => $lv) {
				$newGroup['langName'][$lk] = $lv;
			}
		}
		$edGroup []= $newGroup;
	}

	// ** PROCESS DELETE **
	if ($deleteMode) {
		// Calculate number of users in each group
		$uCount = array();

		$query = "select count(*) as cnt from ".uprefix."_users where status = ".intval($id);
		if (is_array($uCount = $mysql->record($query)) and ($uCount['cnt'] > 0)) {
			// Don't allow to delete groups with users
			msg(array('type' => 'danger', 'message' => 'Cannot delete group with users'));
			return;
		}
		unset($edGroup[$id]);
	}

	// Prepare resulting config content
	$fcData = "<?php\n".'$confUserGroup = '.var_export($edGroup, true)."\n;";

	// Try to save config
	$fcHandler = @fopen(confroot.'ugroup.php', 'w');
	if ($fcHandler) {
		fwrite($fcHandler, $fcData);
		fclose($fcHandler);

		msg(array('message' => __('save_done')));

		// Reload groups
		loadGroups();
	} else {
		msg(array('type' => 'danger', 'title' => __('save_error'), 'message' => __('save_error#desc')));
		return false;
	}

	

}

if (($action == 'editForm')||($action == 'addForm')) {
	ugroupForm();
} else {
	switch ($action) {
		case 'edit'		:
		case 'delete'	:
		case 'add'		:	ugroupCommit();
							break;
	}
	ugroupList();
}

