<?php

//
// Copyright (C) 2006-2014 Next Generation CMS (http://ngcms.ru/)
// Name: static.php
// Description: Manage static pages
// Author: Vitaly Ponomarev
//

// Protect against hack attempts
if (!defined('NGCMS')) die ('HAL');

Lang::load('static', 'admin');

//
// Show list of static pages
function listStatic() {
	global $mysql, $mod, $userROW, $config, $twig, $PHP_SELF;

	// Check for permissions
	$perm = checkPermission(array('plugin' => '#admin', 'item' => 'static'), null, array('view','modify', 'details', 'publish', 'unpublish'));
	if (!$perm['view']) {
		msg(array('type' => 'danger', 'message' => __('perm.denied')));
		return;
	}

	// Load admin page based cookies
	$admCookie = admcookie_get();

	// Determine user's permissions
	$permModify		= checkPermission(array('plugin' => '#admin', 'item' => 'static'), null, 'modify');
	$permDetails	= checkPermission(array('plugin' => '#admin', 'item' => 'static'), null, 'details');

	$per_page	= isset($_REQUEST['per_page'])?intval($_REQUEST['per_page']):intval($admCookie['static']['pp']);
	if (($per_page < 2)||($per_page > 500)) $per_page = 20;

	// - Save into cookies current value
	$admCookie['static']['pp'] = $per_page;
	admcookie_set($admCookie);

	$pageNo	= intval(getIsSet($_REQUEST['page']));
	if ($pageNo < 1)	$pageNo = 1;

	$query = array();
	$query['sql']		= "select * from ".prefix."_static order by title limit ".(($pageNo - 1)* $per_page).", ".$per_page;
	$query['count']		= "select count(*) as cnt from ".prefix."_static ";

	$nCount = 0;
	$tEntries = array();
	foreach ($mysql->select($query['sql']) as $row) {
		$nCount++;

		$tEntry = array(
			'home' => home,
			'id' => $row['id'],
			'alt_name' => $row['alt_name'],
			'template' => ($row['template']=='')?'--':$row['template'],
			'date' => 	($row['postdate']> 0) ? strftime('%d.%m.%Y %H:%M', $row['postdate']) : '',
		);

		if (mb_strlen($row['title'], 'UTF-8') > 70) {
			$row['title'] = mb_substr($row['title'], 0, 70, 'UTF-8') . ' ...';
		}

		$link = checkLinkAvailable('static', '')?
					generateLink('static', '', array('altname' => $row['alt_name'], 'id' => $row['id']), array(), false, true):
					generateLink('core', 'plugin', array('plugin' => 'static'), array('altname' => $row['alt_name'], 'id' => $row['id']), false, true);

		$tEntry['url']		= $row['approve']?('<a href="'.$link.'" target="_blank">'.$link.'</a>'):'';
		$tEntry['title']	= str_replace(array("'", '"'), array("&#039;", "&quot;"), $row['title']);
		$tEntry['status']	= ($row['approve']) ? '<i class="fa fa-check text-success"></i>' : '<i class="fa fa-times text-danger"></i>';

		$tEntries []= $tEntry;
	}

	$tVars = array (
		'php_self' => $PHP_SELF,
		'per_page' => $per_page,
		'entries' => $tEntries,
		'token' => genUToken('admin.static'),
		'perm' => array(
			'details' => $permDetails,
			'modify' => $permModify,
		)
	);

	$cnt = $mysql->record($query['count']);
	$all_count_rec = $cnt['cnt'];

	$countPages = ceil($all_count_rec / $per_page);
	$pagesss = new Paginator();
	$tVars['pagesss'] = $pagesss->get(
		array(
			'current' => $pageNo,
			'count' => $countPages,
			'url' => admin_url .
				'/admin.php?mod=static&action=list'.
				(getIsSet($_REQUEST['per_page'])?'&per_page='.$per_page:'').
				'&page=%page%'
			));

	executeActionHandler('static_list');

	$xt = $twig->loadTemplate('skins/default/tpl/static/table.tpl');
	echo $xt->render($tVars);
}

//
// Mass static pages flags modifier
// $setValue - what to change in table (SQL string)
// $langParam - name of variable in lang file to show on success
// $tag - tag param to send to plugins
//
function massStaticModify($setValue, $langParam, $tag ='') {
	global $mysql;

	// Check for permissions
	if (!checkPermission(array('plugin' => '#admin', 'item' => 'static'), null, 'modify')) {
		msg(array('type' => 'danger', 'message' => __('perm.denied')));
		return;
	}

	// Check for security token
	if ((!isset($_REQUEST['token']))||($_REQUEST['token'] != genUToken('admin.static'))) {
		msg(array('type' => 'danger', 'title' => __('error.security.token'), 'message' => __('error.security.token#desc')));
		return;
	}

	$selected = getIsSet($_REQUEST['selected']);

	if (!$selected) {
		msg(array('type' => 'danger', 'title' => __('msge_selectnews'), 'message' => __('msgi_selectnews')));
		return;
	}

	foreach ($selected as $id) {
		$mysql->query("UPDATE ".prefix."_static SET $setValue WHERE id=".db_squote($id));
	}
	
	msg(array('message' => __($langParam)));
}

//
// Mass static pages delete
//
function massStaticDelete() {
	global $mysql, $PFILTERS;

	// Check for permissions
	if (!checkPermission(array('plugin' => '#admin', 'item' => 'static'), null, 'modify')) {
		msg(array('type' => 'danger', 'message' => __('perm.denied')));
		return;
	}

	// Check for security token
	if ((!isset($_REQUEST['token']))||($_REQUEST['token'] != genUToken('admin.static'))) {
		msg(array('type' => 'danger', 'title' => __('error.security.token'), 'message' => __('error.security.token#desc')));
		return;
	}

	$selected = getIsSet($_REQUEST['selected']);

	if (!$selected) {
		msg(array('type' => 'danger', 'title' => __('msge_selectnews'), 'message' => __('msgi_selectnews')));
		return;
	}

	foreach ($selected as $id) {
		if ($srow = $mysql->record("select * from ".prefix."_static where id = ".db_squote($id))) {
			if (is_array($PFILTERS['static']))
				foreach ($PFILTERS['static'] as $k => $v) { $v->deleteStatic($srow['id'], $srow); }
			$mysql->query("delete from ".prefix."_static where id=".db_squote($id));
		}

	}
	msg(array('message' => __('msgo_deleted')));
}

// Return list of available templates
function staticTemplateList() {
	global $config;

	$result = array('');
	foreach (ListFiles(tpl_site.'/static', 'tpl') as $k) {
		if (preg_match('#\.(print|main)$#', $k)) { continue; }
		$result []= $k;
	}
	return $result;
}

//
// Add/Edit static page :: FORM
// $operationMode - mode of operation
//	1 - Add `from the scratch`
//	2 - Add `repeat previous attempt` (after fail)
//	3 - Edit `from the scratch` (or after successfull add)
//	4 - Edit `repeat previous attempt` (after tail)
// $sID - static ID
//	0 - autodetect
// x - exact static ID
function addEditStaticForm($operationMode = 1, $sID = 0){
	global $parse, $mysql, $config, $twig, $mod, $PFILTERS, $tvars, $userROW, $PHP_SELF;
	global $title, $contentshort, $contentfull, $alt_name, $id, $c_day, $c_month, $c_year, $c_hour, $c_minute;

	$perm = checkPermission(array('plugin' => '#admin', 'item' => 'static'), null, array('add', 'modify', 'view', 'template', 'template.main', 'html', 'publish', 'unpublish'));

	$permModify		= checkPermission(array('plugin' => '#admin', 'item' => 'static'), null, 'modify');
	$permDetails	= checkPermission(array('plugin' => '#admin', 'item' => 'static'), null, 'details');

	if (!$perm['modify'] and !$perm['details']) {
		msg(array('type' => 'danger', 'message' => __('perm.denied')));
		return 0;
	}

	// Init `$editMode` variable
	$editMode = 0;
	$row = array();
	$origRow = array();

	$requestID = ($sID > 0)?$sID:((isset($_REQUEST['id']) and $_REQUEST['id'])?$_REQUEST['id']:0);

	// EDIT
	if (($operationMode == 3) or ($operationMode == 4)) {
		if (!$requestID or !is_array($row = $mysql->record("select * from ".prefix."_static where id = ".db_squote($requestID)))) {
			msg(array('type' => 'danger', 'message' => __('msge_not_found')));
			return 0;
		}
		$editMode = 1;
		$origRow = $row;
	}

	// ADD
	if (($operationMode == 1) or ($operationMode == 2)) {
		// ADD mode
		$row['id'] = 0;
	}

	// Populate `repeat previous attempt` data
	if (($operationMode == 2) or ($operationMode == 4)) {
		foreach (array('title', 'content', 'alt_name', 'template', 'description', 'keywords') as $k) {
			if (isset($_REQUEST[$k]))
				$row[$k] = $_REQUEST[$k];
		}
		$row['approve'] = (isset($_REQUEST['flag_published']) and $_REQUEST['flag_published'])?1:0;
		$row['flags'] = ((isset($_REQUEST['flag_raw']) and $_REQUEST['flag_raw'])?1:0) + ((isset($_REQUEST['flag_html']) and $_REQUEST['flag_html'])?2:0) + ((isset($_REQUEST['flag_template_main']) and $_REQUEST['flag_template_main'])?4:0);
	}

	// Fill basic variables
	$tVars = array(
		'php_self' => $PHP_SELF,
		'bbcodes' => BBCodes('currentInputAreaID', 'static'),
		'token'				=> genUToken('admin.static'),
		'smilies' => $config['use_smilies']?Smilies('content', 20):'',
		'templateList' => staticTemplateList(),
		'flags'				=> array(
			'editMode' => $editMode,
			'canAdd' => $perm['add'],
			'canModify' => $perm['modify'],
			'canPublish' => $perm['publish'],
			'canUnpublish' => $perm['unpublish'],
			'canHTML' => $perm['html'],
			'canTemplate' => $perm['template'],
			'canTemplateMain' => $perm['template.main'],
			'meta'				=> $config['meta'],
			'html'				=> $perm['html'],
			'isPublished' => ($editMode and ($origRow['approve']))?1:0,
		)
	);
	// Fill data entry
	$tVars['data']			= array(
			'id'				=> $row['id'],
			'title'				=> secure_html(getIsSet($row['title'])),
			'content' => secure_html(getIsSet($row['content'])),
			'alt_name' => getIsSet($row['alt_name']),
			'template' => getIsSet($row['template']),
			'description' => getIsSet($row['description']),
			'keywords' => getIsSet($row['keywords']),
			'cdate'				=> !empty($row['postdate'])?date('d.m.Y H:i', $row['postdate']):'',
			'flag_published' => getIsSet($row['approve']) ? $row['approve'] : 1,
			'flag_raw'				=> (getIsSet($row['flags']) % 2)?1:0,
			'flag_html'				=> ((getIsSet($row['flags'])/2) % 2)?1:0,
			'flag_template_main' => ((getIsSet($row['flags'])/4) % 2)?1:0,
		);

	if ($editMode and ($origRow['approve'])) {
		$tVars['data']['url'] = (checkLinkAvailable('static', '')?
				generateLink('static', '', array('altname' => $origRow['alt_name'], 'id' => $origRow['id']), array(), false, true):
				generateLink('core', 'plugin', array('plugin' => 'static'), array('altname' => $origRow['alt_name'], 'id' => $origRow['id']), false, true));
	}

	executeActionHandler('addstatic');
	executeActionHandler('editstatic');

	if (getIsSet($PFILTERS['static']) and is_array($PFILTERS['static']))
		foreach ($PFILTERS['static'] as $k => $v) {
			if ($editMode) {
				$v->editStaticForm($row['id'], $row, $tVars);
			} else {
				$v->addStaticForm($tVars);
			}
		}

	$xt = $twig->loadTemplate('skins/default/tpl/static/edit.tpl');
	echo $xt->render($tVars);
	return 1;
}

//
// Add static page
//
function addStatic(){
	global $mysql, $parse, $PFILTERS, $config, $userROW, $PHP_SELF, $tvars;

	$perm = checkPermission(array('plugin' => '#admin', 'item' => 'static'), null, array('modify', 'view', 'template', 'template.main', 'html', 'publish', 'unpublish'));

	// Check for modify permissions
	if (!$perm['modify']) {
		msg(array('type' => 'danger', 'message' => __('perm.denied')));
		return 0;
	}

	// Check for publish request if it's set
	if ( isset($_REQUEST['flag_published']) ) {
		if (!$perm['publish']) {
			msg(array('type' => 'danger', 'title' => __('perm.denied'), 'message' => __('perm.publish')));
			return 0;
		}
	}

	// Check for security token
	if ((!isset($_REQUEST['token']))||($_REQUEST['token'] != genUToken('admin.static'))) {
		msg(array('type' => 'danger', 'title' => __('error.security.token'), 'message' => __('error.security.token#desc')));
		return 0;
	}

	$title = $_REQUEST['title'];
	$content = $_REQUEST['content'];
	$content = str_replace("\r\n", "\n", $content);

	$alt_name = strtolower($parse->translit( trim($_REQUEST['alt_name']), 1, 1));

	if ( (!mb_strlen(trim($title), 'UTF-8')) or (!mb_strlen(trim($content), 'UTF-8')) ) {
		msg(array('type' => 'danger', 'title' => __('msge_fields'), 'message' => __('msgi_fields')));
		return 0;
	}

	$SQL['title'] = $title;

	// Check for dup if alt_name is specified
	if ($alt_name) {
		if ( is_array($mysql->record("select id from ".prefix."_static where alt_name = ".db_squote($alt_name)." limit 1")) ) {
			msg(array('type' => 'danger', 'title' => __('msge_alt_name'), 'message' => __('msgi_alt_name')));
			return 0;
		}
		$SQL['alt_name'] = $alt_name;
	} else {
		// Generate uniq alt_name if no alt_name specified
		$alt_name = strtolower($parse->translit(trim($title),1));
		$i = '';
		while ( is_array($mysql->record("select id from ".prefix."_static where alt_name = ".db_squote($alt_name.$i)." limit 1")) ) {
			$i++;
		}
		$SQL['alt_name'] = $alt_name.$i;
	}

	if ($config['meta']) {
		$SQL['description']	= $_REQUEST['description'];
		$SQL['keywords']	= $_REQUEST['keywords'];
	}

	$SQL['content']		= $content;

	$SQL['template']	= $_REQUEST['template'];
	$SQL['approve']		= intval($_REQUEST['flag_published']);

	// Variable FLAGS is a bit-variable:
	// 0 = RAW mode		[if set, no conversion "\n" => "<br />" will be done]
	// 1 = HTML enable	[if set, HTML codes may be used in news]

	$SQL['flags'] = (($perm['html'] and isset($_REQUEST['flag_raw']) and $_REQUEST['flag_raw'])?1:0) + (($perm['html'] and isset($_REQUEST['flag_html']) and $_REQUEST['flag_html'])?2:0) + (($perm['html'] and isset($_REQUEST['flag_template_main']) and $_REQUEST['flag_template_main'])?4:0);

	if (is_array($PFILTERS['static']))
		foreach ($PFILTERS['static'] as $k => $v) { $v->addStatic($tvars, $SQL); }

	$vnames = array(); $vparams = array();
	foreach ($SQL as $k => $v) { $vnames[] = $k; $vparams[] = db_squote($v); }

	$mysql->query("insert into ".prefix."_static (postdate, ".implode(",",$vnames).") values (unix_timestamp(now()), ".implode(",",$vparams).")");
	$id = $mysql->result("SELECT LAST_INSERT_ID() as id");

	$link = (checkLinkAvailable('static', '')?
				generateLink('static', '', array('altname' => $SQL['alt_name'], 'id' => $id), array(), false, true):
				generateLink('core', 'plugin', array('plugin' => 'static'), array('altname' => $SQL['alt_name'], 'id' => $id), false, true));
	
	msg(array(
		'title' => __('msg.added'),
		'message' => sprintf(__('msg.added#desc'), 'admin.php?mod=static&action=editForm&id=' . $id, 'admin.php?mod=static')
		));
	
	return $id;
}


//
// Edit static page
//
function editStatic(){
	global $mysql, $parse, $PFILTERS, $config, $userROW;

	$perm = checkPermission(array('plugin' => '#admin', 'item' => 'static'), null, array('modify', 'view', 'template', 'template.main', 'html', 'publish', 'unpublish'));

	// Check for permissions
	if ( !$perm['modify'] ) {
		msg(array('type' => 'danger', 'message' => __('perm.denied')));
		return -1;
	}

	// Check for security token
	if ((!isset($_REQUEST['token'])) or ($_REQUEST['token'] != genUToken('admin.static'))) {
		msg(array('type' => 'danger', 'title' => __('error.security.token'), 'message' => __('error.security.token#desc')));
		return -1;
	}

	$id			= intval($_REQUEST['id']);
	$title		= $_REQUEST['title'];
	$content	= $_REQUEST['content'];
	$alt_name	= $parse->translit(trim($_REQUEST['alt_name']),1, 1);

	// Try to find news that we're trying to edit
	if ( !is_array($row = $mysql->record("select * from ".prefix."_static where id=".db_squote($id))) ) {
		msg(array('type' => 'danger', 'message' => __('msge_not_found')));
		return -1;
	}

	if ( (!mb_strlen(trim($title), 'UTF-8')) or (!mb_strlen(trim($content), 'UTF-8')) ) {
		msg(array('type' => 'danger', 'title' => __('msge_fields'), 'message' => __('msgi_fields')));
		return 0;
	}

	$SQL['title'] = $title;

	// Check for dup if alt_name is specified
	if ( is_array($mysql->record("select id from ".prefix."_static where alt_name = ".db_squote($alt_name)." and id <> ".$row['id']." limit 1")) ) {
		msg(array('type' => 'danger', 'title' => __('msge_alt_name'), 'message' => __('msgi_alt_name')));
		return 0;
	}
	
	$SQL['alt_name'] = $alt_name;

	if ($config['meta']) {
		$SQL['description']	= $_REQUEST['description'];
		$SQL['keywords']	= $_REQUEST['keywords'];
	}

	$SQL['content']		= $content;

	$SQL['template']	= $_REQUEST['template'];
	$SQL['approve']		= intval($_REQUEST['flag_published']);
	if (isset($_POST['set_postdate']) and $_POST['set_postdate']) {
		if (preg_match('#^(\d+)\.(\d+)\.(\d+) +(\d+)\:(\d+)$#', $_REQUEST['cdate'], $m)) {
			$SQL['postdate'] = mktime($m[4], $m[5], 0, $m[2], $m[1], $m[3]) + ($config['date_adjust'] * 60);
		}
	}

	$SQL['flags'] = (($perm['html'] and isset($_REQUEST['flag_raw']) and $_REQUEST['flag_raw'])?1:0) + (($perm['html'] and isset($_REQUEST['flag_html']) and $_REQUEST['flag_html'])?2:0) + (($perm['html'] and isset($_REQUEST['flag_template_main']) and $_REQUEST['flag_template_main'])?4:0);

	if (is_array($PFILTERS['static']))
		foreach ($PFILTERS['static'] as $k => $v) { $v->editStatic($row['id'], $row, $SQL, $tvars); }

	$SQLparams = array();
	foreach ($SQL as $k => $v) { $SQLparams[] = $k.' = '.db_squote($v); }

	$mysql->query("update ".prefix."_static set ".implode(", ",$SQLparams)." where id = ".db_squote($id));

	$link = (checkLinkAvailable('static', '')?
				generateLink('static', '', array('altname' => $SQL['alt_name'], 'id' => $id), array(), false, true):
				generateLink('core', 'plugin', array('plugin' => 'static'), array('altname' => $SQL['alt_name'], 'id' => $id), false, true));

	msg(array(
		'title' => __('msg.edited'),
		'message' => sprintf(__('msg.added#desc'), 'admin.php?mod=static&action=editForm&id=' . $id, 'admin.php?mod=static')
		));
	
	// Return ID
	return $id;
}

// #=======================================#
// # Action selection #
// #=======================================#

switch ($action) {
	case 'add': 	if ($id = addStatic()) {
							addEditStaticForm(3, $id);
						} else {
							addEditStaticForm(2);
						}
						break;

	case 'addForm': addEditStaticForm(1);
						break;

	case 'edit': if (($id = editStatic())>0) {
							addEditStaticForm(3, $id);
						} else if ($id == 0) {
							addEditStaticForm(4);
						} else {
							listStatic();
						}
						break;

	case 'editForm':	addEditStaticForm(3);
						break;

	default: {
		switch ($action) {
			case 'do_mass_approve': massStaticModify('approve = 1', 'msgo_approved', 'approve');		break;
			case 'do_mass_forbidden':	massStaticModify('approve = 0', 'msgo_forbidden', 'forbidden');		break;
			case 'do_mass_delete': massStaticDelete();													break;
		}
		listStatic();
	}
}
