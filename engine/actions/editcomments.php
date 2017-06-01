<?php

//
// Copyright (C) 2006-2016 Next Generation CMS (http://ngcms.ru/)
// Name: editcomments.php
// Description: News comments managment
// Author: Vitaly Ponomarev, Alexey Zinchenko
//

// Protect against hack attempts
if (!defined('NGCMS')) die ('HAL');

Lang::load('editcomments', 'admin');

// List comments
function commentsList($newsID){
	global $mysql;
}

function deletecomment() {
	global $mysql;

	$comid = intval($_REQUEST['comid']);
	if(empty($comid)){
		msg(array('type' => 'danger', 'message' => __('comid_not_found')));
		exit();
	}

	$newsid = intval($_REQUEST['newsid']);
	if(empty($newsid)){
		msg(array('type' => 'danger', 'message' => __('comid_not_found')));
		exit();
	}

	if ($row = $mysql->record("select * from ".prefix."_comments where id=".db_squote($comid))) {
		$mysql->query("delete from ".prefix."_comments where id=".db_squote($comid));
		$mysql->query("update ".uprefix."_users set com=com-1 where id=".db_squote($row['author_id']));
		$mysql->query("update ".prefix."_news set com=com-1 where id=".db_squote($row['post']));
		msg(array('title' => __('msgo_deleted'), 'message' => sprintf(__('msgi_deleted'), "admin.php?mod=news&action=edit&id=".$row['post'])));
	} else {
		msg(array('type' => 'danger', 'message' => __('msge_not_found')));
	}
}

function editcomment() {
	global $mysql, $twig, $config, $userROW;

	$comid = intval($_REQUEST['comid']);
	if(empty($comid)){
		msg(array('type' => 'danger', 'message' => __('comid_not_found')));
		exit();
	}

	$newsid = intval($_REQUEST['newsid']);
	if(empty($newsid)){
		msg(array('type' => 'danger', 'message' => __('comid_not_found')));
		exit();
	}

	if ( $_REQUEST['subaction'] == 'doeditcomment' ) {
		if (!trim($_REQUEST['poster'])) {
			msg(array('type' => 'danger', 'message' => __('msge_namefield')));
		} else {
			$comment = str_replace("{","&#123;",str_replace("\r\n", "<br />", htmlspecialchars(trim($_REQUEST['comment']), ENT_COMPAT, 'UTF-8')));
			$content = str_replace("{","&#123;",str_replace("\r\n", "<br />", htmlspecialchars(trim($_REQUEST['content']), ENT_COMPAT, 'UTF-8')));

			$mail = trim($_REQUEST['mail']);

			$mysql->query("UPDATE ".prefix."_comments SET mail=".db_squote($mail).", text=".db_squote($comment).", answer=".db_squote($content).", name=".db_squote($userROW['name'])." WHERE id=".db_squote($comid));

			if ($content && $_REQUEST['send_notice'] && $mail) {
				$row = $mysql->record("select * from ".prefix."_news where id=".db_squote($newsid));
				$newsLink = newsGenerateLink($row, false, 0, true);
				sendEmailMessage($mail, __('comanswer'), sprintf(__('notice'), $userROW['name'], $content, $newsLink), 'html');
			}

			msg(array('message' => __('msgo_saved')));
		}
	}

	if ( $row = $mysql->record("select * from ".prefix."_comments where id = ".db_squote($comid)) ) {
		$row['text']	= str_replace("<br />", "\r\n", $row['text']);
		$row['answer']	= str_replace("<br />", "\r\n", $row['answer']);

		$tvars = array(
			'php_self'	=>	$PHP_SELF,
			'quicktags'	=>	QuickTags(false, "editcom"),
			'ip'		=>	$row['ip'],
			'author'	=>	$row['author'],
			'mail'		=>	$row['mail'],
			'text'		=>	$row['text'],
			'answer'	=>	$row['answer'],
			'newsid'	=>	$newsid,
			'comid'		=>	$comid
		);
		$tvars['smilies'] = ($config['use_smilies'] == "1") ? InsertSmilies('content', 5) : '';
		$tvars['comdate'] = Lang::retDate(pluginGetVariable('comments', 'timestamp'), $row['postdate']);

		if ($userROW['status'] < "3"){
			$tvars['[answer]'] = '';
			$tvars['[/answer]'] = '';
		} else {
			$tvars['regx']['[\[answer\](.*)\[/answer\]]'] = '';
		}

		if ( trim($row['text']) ) {
			$xt = $twig->loadTemplate('skins/default/tpl/editcomments.tpl');
			echo $xt->render($tvars);
		}
	} else {
		msg(array('type' => 'danger', 'message' => __('msge_not_found')));
	}
}

//
// Main loop
if( isset($_REQUEST['subaction']) ) {
	switch ($_REQUEST['subaction']) {
		case 'deletecomment':	deletecomment(); break;
		default:				editcomment(); break;
	}
} else {
	editcomment();
}
