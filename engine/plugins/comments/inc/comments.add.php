<?php

// Protect against hack attempts
if (!defined('NGCMS')) die ('HAL');

//
// Params for filtering and processing
//
function comments_add(){
	global $mysql, $config, $AUTH_METHOD, $userROW, $ip, $parse, $catmap, $catz, $PFILTERS;

	// Check membership
	// If login/pass is entered (either logged or not)
	if ($_POST['name'] && $_POST['password']) {
		$auth = $AUTH_METHOD[$config['auth_module']];
		$user = $auth->login(0, $_POST['name'], $_POST['password']);
		if (!is_array($user)) {
			msg(array('type' => 'danger', 'message' => __('comments:err.password')));
			return;
		}
	}

	// Entered data have higher priority then login data
	$memberRec = null;
	if (is_array($user)) {
		$SQL['author'] = $user['name'];
		$SQL['author_id'] = $user['id'];
		$SQL['mail'] = $user['mail'];
		$is_member = 1;
		$memberRec = $user;
	} else if (is_array($userROW)) {
		$SQL['author'] = $userROW['name'];
		$SQL['author_id'] = $userROW['id'];
		$SQL['mail'] = $userROW['mail'];
		$is_member = 1;
		$memberRec = $userROW;
	} else {
		$SQL['author'] = secure_html($_POST['name']);
		$SQL['author_id'] = 0;
		$SQL['mail'] = secure_html($_POST['mail']);
		$is_member = 0;
	}

	// CSRF protection variables
	$sValue = '';
	if (preg_match('#^(\d+)\#(.+)$#', $_POST['newsid'], $m)) {
		$SQL['post'] = $m[1];
		$sValue = $m[2];
	}

	if ($sValue != genUToken('comment.add.'.$SQL['post'])) {
		msg(array('type' => 'danger', 'message' => __('comments:err.regonly')));
		return;
	}

	$SQL['text'] = secure_html($_POST['content']);

	// If user is not logged, make some additional tests
	if (!$is_member) {
		// Check if unreg are allowed to make comments
		if (pluginGetVariable('comments', 'regonly')) {
			msg(array('type' => 'danger', 'message' => __('comments:err.regonly')));
			return;
		}
		// Check captcha for unregistered visitors
		if ($config['use_captcha']) {
			$vcode = $_POST['vcode'];

			if ($vcode != $_SESSION['captcha']) {
				msg(array('type' => 'danger', 'message' => __('comments:err.vcode')));
				return;
			}

			// Update captcha
			$_SESSION['captcha'] = rand(00000, 99999);
		}

		if (!$SQL['author']) {
			msg(array('type' => 'danger', 'message' => __('comments:err.name')));
			return;
		}
		if (!$SQL['mail']) {
			msg(array('type' => 'danger', 'message' => __('comments:err.mail')));
			return;
		}

		// Check if author name use incorrect symbols. Check should be done only for unregs
		if ((!$SQL['author_id']) && (preg_match("/[^(\w)|(\x7F-\xFF)|(\s)]/", $SQL['author']) or mb_strlen($SQL['author'], 'UTF-8') > 60)) {
			msg(array('type' => 'danger', 'message' => __('comments:err.badname')));
			return;
		}
		if (strlen($SQL['mail']) > 70 or !preg_match("/^[\.A-z0-9_\-]+[@][A-z0-9_\-]+([.][A-z0-9_\-]+)+[A-z]{1,4}$/", $SQL['mail'])) {
			msg(array('type' => 'danger', 'message' => __('comments:err.badmail')));
			return;
		}
		// Check if guest wants to use email of already registered user
		if (pluginGetVariable('comments', 'guest_edup_lock')) {
			if (is_array($mysql->record("select * from ".uprefix."_users where mail = ".db_squote($SQL['mail'])." limit 1"))) {
				msg(array('type' => 'danger', 'message' => __('comments:err.edupmail')));
				return;
			}
		}
	}

	$maxlen = intval(pluginGetVariable('comments', 'maxlen'));
	$maxlen = ($maxlen > 2) ? $maxlen : 500;
	if (mb_strlen($SQL['text'], 'UTF-8') > $maxlen or mb_strlen($SQL['text'], 'UTF-8') < 2) {
		msg(array('type' => 'danger', 'message' => str_replace('{maxlen}', $maxlen, __('comments:err.badtext'))));
		return;
	}

	// Check for flood
	if (checkFlood(0, $ip, 'comments', 'add', $is_member?$memberRec:null, $is_member?null:$SQL['author'])) {
		msg(array('type' => 'danger', 'message' => str_replace('{timeout}',$config['flood_time'] ,__('comments:err.flood'))));
		return;
	}

	// Check for bans
	if ($ban_mode = checkBanned($ip, 'comments', 'add', $is_member?$memberRec:null, $is_member?null:$SQL['author'])) {
		// If hidden mode is active - say that news is not found
		if ($ban_mode == 2) {
			msg(array('type' => 'danger', 'message' => __('comments:err.notfound')));
		} else {
			msg(array('type' => 'danger', 'message' => __('comments:err.ipban')));
		}
		return;
	}

	// Locate news
	if ($news_row = $mysql->record("select * from ".prefix."_news where id = ".db_squote($SQL['post']))) {
		// Determine if comments are allowed in this specific news
		$allowCom = $news_row['allow_com'];
		if ($allowCom == 2) {
			// `Use default` - check master category
			$masterCat = intval(array_shift(explode(',', $news_row['catid'])));
			if ($masterCat && isset($catmap[$masterCat])) {
				$allowCom = intval($catz[$catmap[$masterCat]]['allow_com']);
			}

			// If we still have 2 (no master category or master category also have 'default' - fetch plugin's config
			if ($allowCom == 2) {
				$allowCom = pluginGetVariable('comments', 'global_default');
			}
		}
		if (!$allowCom) {
			msg(array('type' => 'danger', 'message' => __('comments:err.forbidden')));
			return;
		}
	} else {
		msg(array('type' => 'danger', 'message' => __('comments:err.notfound')));
		return;
	}

	// Check for multiple comments block [!!! ADMINS CAN DO IT IN ANY CASE !!!]
	$multiCheck = 0;

	// Make tests only for non-admins
	if (!is_array($userROW)) {
		// Not logged
		$multiCheck = !intval(pluginGetVariable('comments', 'multi'));
	} else {
		// Logged. Skip admins
		if ($userROW['status'] != 1) {
			// Check for author
			$multiCheck = !intval(pluginGetVariable('comments', (($userROW['id'] == $news_row['author_id'])?'author_':'').'multi'));
		}
	}

	if ($multiCheck) {

		// Locate last comment for this news
		if (is_array($lpost = $mysql->record("select author_id, author, ip, mail from ".prefix."_comments where post=".db_squote($SQL['post'])." order by id desc limit 1"))) {
			// Check for post from the same user
			if (is_array($userROW)) {
				 if ($userROW['id'] == $lpost['author_id']) {
					msg(array('type' => 'danger', 'message' => __('comments:err.multilock')));
					return;
				}
			} else {
				//print "Last post: ".$lpost['id']."<br>\n";
				if (($lpost['author'] == $SQL['author']) or ($lpost['mail'] == $SQL['mail'])) {
					msg(array('type' => 'danger', 'message' => __('comments:err.multilock')));
					return;
				}
			}
		}
	}

	$SQL['postdate'] = time() + ($config['date_adjust'] * 60);

	if (pluginGetVariable('comments', 'maxwlen') > 1){
		$SQL['text'] = preg_replace('/(\S{'.intval(pluginGetVariable('comments', 'maxwlen')).'})(?!\s)/', '$1 ', $SQL['text']);

		if ((!$SQL['author_id']) && (mb_strlen($SQL['author'], 'UTF-8') > pluginGetVariable('comments', 'maxwlen'))) {
			$SQL['author'] = mb_substr( $SQL['author'], 0, pluginGetVariable('comments', 'maxwlen'), 'UTF-8' )." ...";
		}
	}
	$SQL['text']	= str_replace("\r\n", "<br />", $SQL['text']);
	$SQL['ip']		= $ip;
	$SQL['reg']		= ($is_member) ? '1' : '0';

	// RUN interceptors
	loadActionHandlers('comments:add');

	if (is_array($PFILTERS['comments']))
		foreach ($PFILTERS['comments'] as $k => $v) {
			$pluginResult = $v->addComments($memberRec, $news_row, $tvars, $SQL);
			if ((is_array($pluginResult) && ($pluginResult['result'])) or (!is_array($pluginResult) && $pluginResult))
				continue;

			msg(array('type' => 'danger', 'message' => str_replace(array('{plugin}', '{errorText}'), array($k, (is_array($pluginResult) && isset($pluginResult['errorText'])?$pluginResult['errorText']:'')), __('comments:err.'.((is_array($pluginResult) && isset($pluginResult['errorText']))?'e':'').'pluginlock'))));
			return 0;
		}

	// Create comment
	$vnames = array(); $vparams = array();
	foreach ($SQL as $k => $v) {
		$vnames[] = $k; $vparams[] = db_squote($v);
	}

	$mysql->query("insert into ".prefix."_comments (".implode(",",$vnames).") values (".implode(",",$vparams).")");

	// Retrieve comment ID
	$comment_id = $mysql->result("select LAST_INSERT_ID() as id");

	// Update comment counter in news
	$mysql->query("update ".prefix."_news set com=com+1 where id=".db_squote($SQL['post']));

	// Update counter for user
	if ($SQL['author_id']) {
		$mysql->query("update ".prefix."_users set com=com+1 where id = ".db_squote($SQL['author_id']));
	}

	// Update flood protect database
	checkFlood(1, $ip, 'comments', 'add', $is_member?$memberRec:null, $is_member?null:$SQL['author']);

	// RUN interceptors
	if (is_array($PFILTERS['comments']))
		foreach ($PFILTERS['comments'] as $k => $v)
			$v->addCommentsNotify($memberRec, $news_row, $tvars, $SQL, $comment_id);

	// Email informer
	if (pluginGetVariable('comments', 'inform_author') or pluginGetVariable('comments', 'inform_admin')) {
		$alink = ($SQL['author_id']) ? generatePluginLink('uprofile', 'show', array('name' => $SQL['author'], 'id' => $SQL['author_id']), array(), false, true) : '';
		$body = str_replace(
			array(	'{username}',
					'[userlink]',
					'[/userlink]',
					'{comment}',
					'{newslink}',
					'{newstitle}'),
			array(	$SQL['author'],
					($SQL['author_id'])?'<a href="'.$alink.'">':'',
					($SQL['author_id'])?'</a>':'',
					$parse->bbcodes($parse->smilies(secure_html($SQL['text']))),
					newsGenerateLink($news_row, false, 0, true),
					$news_row['title'],
					),
			__('notice')
		);

		if (pluginGetVariable('comments', 'inform_author')) {
			// Determine author's email
			if (is_array($umail=$mysql->record("select * from ".uprefix."_users where id = ".db_squote($news_row['author_id'])))) {
				zzMail($umail['mail'], __('newcomment'), $body, 'html');
			}
		}

		if (pluginGetVariable('comments', 'inform_admin'))
			zzMail($config['admin_mail'], __('newcomment'), $body, 'html');

	}

	@setcookie("com_username", urlencode($SQL['author']), 0, '/');
	@setcookie("com_usermail", urlencode($SQL['mail']), 0, '/');

	return array($news_row, $comment_id);
}