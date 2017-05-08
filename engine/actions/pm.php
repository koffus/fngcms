<?php

//
// Copyright (C) 2006-2008 Next Generation CMS (http://ngcms.ru/)
// Name: pm.php
// Description: adding news
// Author: Alexey Zinchenko
//

// Protect against hack attempts
if (!defined('NGCMS')) die ('HAL');

Lang::load('pm', 'admin');

function pm_send() {
	global $mysql, $config, $userROW;

	$time = time() + ($config['date_adjust'] * 60);

	$sendto = trim($_REQUEST['sendto']);
	$title = secure_html($_REQUEST['title']);
	$content = $_REQUEST['content'];

	if (!$title or mb_strlen($title, 'UTF-8') > 50) {
		msg(array('type' => 'danger', 'title' => __('msge_title'), 'message' => __('msgi_title')));
		return;
	}
	if (!$content or mb_strlen($content, 'UTF-8') > 3000) {
		msg(array('type' => 'danger', 'title' => __('msge_content'), 'message' => __('msgi_content')));
		return;
	}

	if ($sendto && ($torow = $mysql->record("select * from ".uprefix."_users where ".(is_numeric($sendto)?"id = ".db_squote($sendto):"name = ".db_squote($sendto))))) {
		$content = secure_html($content);
		executeActionHandler('pm_send');
		$mysql->query("insert into ".uprefix."_users_pm (from_id, to_id, pmdate, title, content) VALUES (".db_squote($userROW['id']).", ".db_squote($torow['id']).", ".db_squote($time).", ".db_squote($title).", ".db_squote($content).")");
		msg(array('message' => __('msgo_sent')));
	} else {
		msg(array('type' => 'danger', 'title' => __('msge_nouser'), 'message' => __('msgi_nouser')));
	}
}

function pm_list (){
	global $mysql, $config, $userROW, $tpl, $mod, $PHP_SELF;

	$entries = '';
	foreach($mysql->select("select pm.*, u.id as uid, u.name as uname from ".uprefix."_users_pm pm left join ".uprefix."_users u on pm.from_id=u.id where pm.to_id = ".db_squote($userROW['id'])." order by pmid desc limit 0, 30") as $row) {
		$author = '';
		if ($row['from_id'] && $row['uid']) {
			$alink = checkLinkAvailable('uprofile', 'show')?
						generateLink('uprofile', 'show', array('name' => $row['uname'], 'id' => $row['uid'])):
						generateLink('core', 'plugin', array('plugin' => 'uprofile', 'handler' => 'show'), array('name' => $row['uname'], 'id' => $row['uid']));
			$author = '<a href="'.$alink.'">'.$row['uname'].'</a>';
		} else if ($row['from_id']) {
			$author = __('udeleted');
		} else {
			$author = __('messaging');
		}


		$tvars['vars'] = array(
			'php_self'	=>	$PHP_SELF,
			'pmid'		=>	$row['pmid'],
			'pmdate'	=>	Lang::retDate('j.m.Y - H:i', $row['pmdate']),
			'title'		=>	$row['title'],
			'link'		=>	$author,
			'viewed'	=>	$row['viewed'] = ($row['viewed'] == 1 ? __("viewed") : "<font color=green><b>__(unviewed)</b></font>")
		);
		$tpl -> template('entries', tpl_actions.$mod);
		$tpl -> vars('entries', $tvars);
		$entries .= $tpl -> show('entries');
	}

	$tpl -> template('table', tpl_actions.$mod);
	$tvars['vars'] = array(
		'php_self'	=>	$PHP_SELF,
		'entries'	=>	$entries
	);
	executeActionHandler('pm');
	$tpl -> vars('table', $tvars);
	echo $tpl -> show('table');
}

function pm_read(){
	global $mysql, $config, $userROW, $tpl, $mod, $parse, $PHP_SELF;

	$pmid = $_REQUEST['pmid'];
	if ($row = $mysql->record("select * from ".uprefix."_users_pm where pmid = ".db_squote($pmid)."and (to_id = ".db_squote($userROW['id'])." or from_id=".db_squote($userROW['id']).")")) {
		$tpl -> template('read', tpl_actions.$mod);
		$tvars['vars'] = array(
			'php_self'		=>	$PHP_SELF,
			'pmid'			=>	$row['pmid'],
			'title'			=>	$row['title'],
			'from'			=>	$row['from'],
			'content'		=>	$parse->htmlformatter($parse->smilies($parse->bbcodes($row['content'])))
		);
		executeActionHandler('pm_read');
		$tpl -> vars('read', $tvars);
		echo $tpl -> show('read');
		if ((!$row['viewed'])&&($row['to_id'] == $userROW['id'])) {
			$mysql->query("update ".uprefix."_users_pm set `viewed` = '1' WHERE `pmid` = ".db_squote($row['pmid']));
		}
	} else {
		msg(array('type' => 'danger', 'message' => __('msge_bad')));
	}
}

function pm_reply(){
	global $mysql, $config, $userROW, $tpl, $mod, $parse;

	$pmid = $_REQUEST['pmid'];
	if ($row = $mysql->record("select * from ".uprefix."_users_pm where pmid = ".db_squote($pmid)."and (to_id = ".db_squote($userROW['id'])." or from_id=".db_squote($userROW['id']).")")) {
		if (!$row['from_id']) {
			msg(array('type' => 'danger', 'message' => __('msge_reply')));
			return;
		}

		$tpl -> template('reply', tpl_actions.$mod);
		$tvars['vars'] = array(
			'php_self'	=>	$PHP_SELF,
			'pmid'		=>	$row['pmid'],
			'title'		=>	'Re:'.$row['title'],
			'sendto'	=>	$row['from_id'],
			'quicktags'	=>	QuickTags(false, "pmmes")
		);
		$tvars['vars']['smilies'] = ($config['use_smilies'] == "1") ? InsertSmilies("content", 10) : '';
		executeActionHandler('pm_reply');
		$tpl -> vars('reply', $tvars);
		echo $tpl -> show('reply');
	} else {
		msg(array('type' => 'danger', 'message' => __('msge_bad')));
	}
}

function pm_write(){
	global $mysql, $config, $userROW, $tpl, $mod, $PHP_SELF;

	$tpl -> template('write', tpl_actions.$mod);
	$tvars['vars'] = array(
		'php_self'	=>	$PHP_SELF,
		'quicktags'	=>	QuickTags(false, "pmmes")
	);
	$tvars['vars']['smilies'] = ($config['use_smilies'] == "1") ? InsertSmilies("content", 10) : '';
	executeActionHandler('pm_write');
	$tpl -> vars('write', $tvars);
	echo $tpl -> show('write');
}

function pm_delete(){
	global $mysql, $config, $userROW, $tpl, $mod;

	$selected_pm = getIsSet($_REQUEST['selected_pm']);

	if(!$selected_pm) {
		msg(array('type' => 'danger', 'message' => __('msge_select')));
		return;
	}
	foreach ($selected_pm as $id) {
		$mysql->query("delete from ".uprefix."_users_pm where `pmid`=".db_squote($id)." and (from_id=".db_squote($userROW['id'])." or to_id=".db_squote($userROW['id']).")");
	}
	msg(array('message' => __('msgo_deleted')));
}

switch($action){
	case "read" : pm_read(); break;
	case "reply" : pm_reply(); break;
	case "send" : pm_send(); break;
	case "write" : pm_write(); break;
	case "delete" : pm_delete(); break;
	default : pm_list();
}