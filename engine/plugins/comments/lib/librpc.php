<?php

function comments_rpc_manage($params)
{
    global $mysql, $config, $AUTH_METHOD, $userROW, $ip, $parse, $catmap, $catz, $PFILTERS, $HTTP_REFERER;

    // TO DO Action: add,edit,delete

    Lang::loadPlugin('comments', 'site', '', ':');

    $SQL = array();

    if (!empty($params['postid'])) {
        $SQL['post'] = (int)$params['postid'];
    } else {
        return array('status' => 0, 'errorCode' => 999, 'errorText' => 'Не задан ID записи');
    }

    // CSRF protection variables
    if (empty($params['tokken']) or $params['tokken'] != genUToken('comment.add.' . $SQL['post'])) {
        return array('status' => 0, 'errorCode' => 999, 'errorText' => __('comments:err.regonly'));
    }

    // Check membership
    // If login/pass is entered (either logged or not)
    if (isset($params['name']) and isset($params['password'])) {
        $auth = $AUTH_METHOD[$config['auth_module']];
        $user = $auth->login(0, $params['name'], $params['password']);
        if (!is_array($user)) {
            return array('status' => 0, 'errorCode' => 999, 'errorText' => __('comments:err.password'));
        }
    }

    // Entered data have higher priority then login data
    $memberRec = null;
    if (isset($user) and is_array($user)) {
        $SQL['author'] = $user['name'];
        $SQL['author_id'] = $user['id'];
        $SQL['mail'] = $user['mail'];
        $is_member = 1;
        $memberRec = $user;
    } elseif (is_array($userROW)) {
        $SQL['author'] = $userROW['name'];
        $SQL['author_id'] = $userROW['id'];
        $SQL['mail'] = $userROW['mail'];
        $is_member = 1;
        $memberRec = $userROW;
    } else {
        $SQL['author'] = isset($params['name']) ? secure_html($params['name']) : 0;
        $SQL['author_id'] = 0;
        $SQL['mail'] = isset($params['mail']) ? secure_html($params['mail']) : 0;
        $is_member = 0;
    }

    // If user is not logged, make some additional tests
    if (!$is_member) {
        // Check if unreg are allowed to make comments
        if (pluginGetVariable('comments', 'regonly')) {
            return array('status' => 0, 'errorCode' => 999, 'errorText' => __('comments:err.regonly'));
        }
        // Check captcha for unregistered visitors
        if ($config['use_captcha']) {
            $captcha = md5($params['captcha']);

            if ($captcha != $_SESSION['captcha']) {
                return array('status' => 0, 'errorCode' => 999, 'errorText' => __('comments:err.captcha'));
            }
        }

        if (!$SQL['author']) {
            return array('status' => 0, 'errorCode' => 999, 'errorText' => __('comments:err.name'));
        }
        if (!$SQL['mail']) {
            return array('status' => 0, 'errorCode' => 999, 'errorText' => __('comments:err.mail'));
        }

        // Check if author name use incorrect symbols. Check should be done only for unregs
        if ((!$SQL['author_id']) and (preg_match("/[^(\w)|(\x7F-\xFF)|(\s)]/", $SQL['author']) or mb_strlen($SQL['author'], 'UTF-8') > 60)) {
            return array('status' => 0, 'errorCode' => 999, 'errorText' => __('comments:err.badname'));
        }
        if (strlen($SQL['mail']) > 70 or !preg_match("/^[\.A-z0-9_\-]+[@][A-z0-9_\-]+([.][A-z0-9_\-]+)+[A-z]{1,4}$/", $SQL['mail'])) {
            return array('status' => 0, 'errorCode' => 999, 'errorText' => __('comments:err.badmail'));
        }
        // Check if guest wants to use email of already registered user
        if (pluginGetVariable('comments', 'guest_edup_lock')) {
            if (is_array($mysql->record("SELECT id FROM `".uprefix."_users` WHERE mail=".db_squote($SQL['mail'])." LIMIT 1"))) {
                return array('status' => 0, 'errorCode' => 999, 'errorText' => __('comments:err.edupmail'));
            }
        }
    }

    $SQL['text'] = secure_html($params['content']);
    $maxlen = (int)pluginGetVariable('comments', 'maxlen');
    $maxlen = ($maxlen > 4) ? $maxlen : 500;
    $minlen = (int)pluginGetVariable('comments', 'minlen');
    $minlen = ($minlen > 0 and $minlen < $maxlen) ? $minlen : 4;
    $curlen = (int)mb_strlen($SQL['text'], 'UTF-8');
    if ($minlen > $curlen or $curlen > $maxlen) {
        $errorText = strtr(__('comments:err.badtext'), array('{minlen}' => $minlen,'{maxlen}' => $maxlen));
        return array('status' => 0, 'errorCode' => 999, 'errorText' => $errorText);
    }
    unset($maxlen,$minlen,$curlen);

    // Check for flood
    if (checkFlood(0, $ip, 'comments', 'add', $is_member?$memberRec:null, $is_member?null:$SQL['author'])) {
        return array('status' => 0, 'errorCode' => 999, 'errorText' => str_replace('{timeout}',$config['flood_time'] ,__('comments:err.flood')));
    }

    // Check for bans
    if ($ban_mode = checkBanned($ip, 'comments', 'add', $is_member?$memberRec:null, $is_member?null:$SQL['author'])) {
        // If hidden mode is active - say that news is not found
        if ($ban_mode == 2) {
            return array('status' => 0, 'errorCode' => 999, 'errorText' => __('comments:err.notfound'));
        } else {
            return array('status' => 0, 'errorCode' => 999, 'errorText' => __('comments:err.ipban'));
        }
    }

    // Check module table
    if (!empty($params['module']) and in_array($params['module'], ['news','images']) ) {
        $params['table'] = $SQL['module'] = secure_html($params['module']);
    } else {
        return array('status' => 0, 'errorCode' => 999, 'errorText' => 'WTF');
    }

    // Locate news
    if ($postRow = $mysql->record("SELECT * FROM `" . prefix . "_" . $params['table'] . "` WHERE id=" . db_squote($SQL['post']))) {
        // Determine if comments are allowed in this specific news
        $allowCom = $postRow['allow_com'];
        if ($allowCom == 2 and 'news' == $params['table']) {
            // `Use default` - check master category
            $catid = explode(',', $postRow['catid']);
            $masterCat = intval(array_shift($catid));
            if ($masterCat and isset($catmap[$masterCat])) {
                $allowCom = intval($catz[$catmap[$masterCat]]['allow_com']);
            }
        } elseif ($allowCom == 2) {
            // If we still have 2 (no master category or master category also have 'default' - fetch plugin's config
            $allowCom = pluginGetVariable('comments', 'global_default');
        }
        if (!$allowCom) {
            return array('status' => 0, 'errorCode' => 999, 'errorText' => __('comments:err.forbidden'));
        }
    } else {
        return array('status' => 0, 'errorCode' => 999, 'errorText' => __('comments:err.notfound'));
    }

    // Check for multiple comments block [!!! ADMINS CAN DO IT IN ANY CASE !!!]
    $multiCheck = 0;

    // Make tests only for non-admins
    if (is_array($userROW) and $userROW['status'] != 1 and isset($postRow['author_id'])) {
        // Logged. Skip admins
        $multiCheck = !intval(pluginGetVariable('comments', (($userROW['id'] == $postRow['author_id']) ? 'author_' : '') . 'multi'));
    } else {
        // Not logged
        $multiCheck = !intval(pluginGetVariable('comments', 'multi'));
    }

    if ($multiCheck) {
        // Locate last comment for this news
        if (is_array($lpost = $mysql->record("
            SELECT 
                author_id, author, ip, mail 
            FROM ".prefix."_comments 
            WHERE 
                post=".db_squote($SQL['post'])." AND module=" . db_squote($SQL['module']) ." ORDER BY id desc LIMIT 1
            "))) {
            // Check for post from the same user
            if (is_array($userROW)) {
                 if ($userROW['id'] == $lpost['author_id']) {
                    return array('status' => 0, 'errorCode' => 999, 'errorText' => __('comments:err.multilock'));
                }
            } else {
                if (($lpost['author'] == $SQL['author']) or ($lpost['mail'] == $SQL['mail'])) {
                    return array('status' => 0, 'errorCode' => 999, 'errorText' => __('comments:err.multilock'));
                }
            }
        }
    }

    $SQL['postdate'] = time() + ($config['date_adjust'] * 60);

    if (pluginGetVariable('comments', 'maxwlen') > 1){
        $SQL['text'] = preg_replace('/(\S{'.intval(pluginGetVariable('comments', 'maxwlen')).'})(?!\s)/', '$1 ', $SQL['text']);

        if ((!$SQL['author_id']) and (mb_strlen($SQL['author'], 'UTF-8') > pluginGetVariable('comments', 'maxwlen'))) {
            $SQL['author'] = mb_substr( $SQL['author'], 0, pluginGetVariable('comments', 'maxwlen'), 'UTF-8' )." ...";
        }
    }
    $SQL['text'] = str_replace("\r\n", "<br />", $SQL['text']);
    $SQL['ip'] = $ip;
    //$SQL['reg'] = ($is_member) ? '1' : '0';

    // RUN interceptors
    loadActionHandlers('comments:add');

    if (isset($PFILTERS['comments']) and is_array($PFILTERS['comments'])) {
        foreach ($PFILTERS['comments'] as $k => $v) {
            $pluginResult = $v->addComments($memberRec, $postRow, $tvars, $SQL);
            if ((is_array($pluginResult) and ($pluginResult['result'])) or (!is_array($pluginResult) and $pluginResult))
                continue;

            return array('status' => 0, 'errorCode' => 999, 'errorText' => str_replace(array('{plugin}', '{errorText}'), array($k, (is_array($pluginResult) and isset($pluginResult['errorText'])?$pluginResult['errorText']:'')), __('comments:err.'.((is_array($pluginResult) and isset($pluginResult['errorText']))?'e':'').'pluginlock')));
        }
    }

    $moderate = (1 == pluginGetVariable('comments', 'moderate') and (empty($userROW['status']) or $userROW['status'] > 1)) ? true : false;

    if (!$moderate) {
        $SQL['approve'] = 1;
    }

    // Create comment
    $vnames = array();
    $vparams = array();
    foreach ($SQL as $k => $v) {
        $vnames[] = $k;
        $vparams[] = db_squote($v);
    }

    $mysql->query("insert into `".prefix."_comments` (".implode(",",$vnames).") values (".implode(",",$vparams).")");

    // Retrieve comment ID
    $comment_id = $mysql->result("select LAST_INSERT_ID() as id");

    // Update comment counter in news if NOT moderate
    if (!$moderate) {
        $mysql->query("update `".prefix."_" . $params['table'] . "` set com=com+1 where id=".db_squote($SQL['post']));
    }

    // Update counter for user
    if ($SQL['author_id'] and !$moderate) {
        $mysql->query("update ".prefix."_users set com=com+1 where id = ".db_squote($SQL['author_id']));
    }

    // Update flood protect database
    checkFlood(1, $ip, 'comments', 'add', $is_member?$memberRec:null, $is_member?null:$SQL['author']);

    // RUN interceptors
    if (isset($PFILTERS['comments']) and is_array($PFILTERS['comments'])) {
        foreach ($PFILTERS['comments'] as $k => $v) {
            $v->addCommentsNotify($memberRec, $postRow, $tvars, $SQL, $comment_id);
        }
    }

    // Email informer
    if (pluginGetVariable('comments', 'inform_author') or pluginGetVariable('comments', 'inform_admin') or $moderate) {
        $alink = ($SQL['author_id']) ? generatePluginLink('uprofile', 'show', array('name' => $SQL['author'], 'id' => $SQL['author_id']), array(), false, true) : '';
        $body = str_replace(
            array('{username}',
                    '[userlink]',
                    '[/userlink]',
                    '{comment}',
                    '{newslink}',
                    '{newstitle}'),
            array($SQL['author'],
                    ($SQL['author_id']) ? '<a href="'.$alink.'">' : '',
                    ($SQL['author_id']) ? '</a>' : '',
                    $parse->bbcodes($parse->smilies(secure_html($SQL['text']))),
                    (preg_match('#^(http|https)\:\/\/#', $HTTP_REFERER, $tmp) ? $HTTP_REFERER : $config['home_url']),
                    isset($postRow['title']) ? $postRow['title'] : $postRow['name'],
                    ),
            __('comments:notice')
        );
        if (pluginGetVariable('comments', 'inform_admin') or $moderate) {
            sendEmailMessage($config['admin_mail'], __('comments:newcomment'), $body);
        }
        if (pluginGetVariable('comments', 'inform_author') or $moderate) {
            // Determine author's email
            if (is_array($umail = $mysql->record("select * from ".uprefix."_users where id = ".db_squote($postRow['author_id'])))) {
                if ($umail['mail'] != $config['admin_mail'])
                    sendEmailMessage($umail['mail'], __('comments:newcomment'), $body);
            }
        }
    }

    @setcookie("com_username", urlencode($SQL['author']), 0, '/');
    @setcookie("com_usermail", urlencode($SQL['mail']), 0, '/');

    // Set we need to override news template
    $callingCommentsParams = array('outprint' => true, 'module' => $params['table']);

    // desired template
    $templateName = 'comments.show';

    // Check if isset custom template for category in news
    if (!empty($postRow['catid']) and $tPath = getCatTemplate($postRow['catid'], $templateName)) {
        $callingCommentsParams['overrideTemplatePath'] = $tPath;
    }
    

    // Connect library
    include_once(root . "/plugins/comments/inc/comments.show.php");

    return array(
        'status' => 1,
        'errorCode' => 0,
        'content' => comments_show($postRow['id'], $comment_id, $postRow['com'] + 1, $callingCommentsParams),
        'message' => ($moderate) ? __('comments:msg_add_moderate') : __('comments:msg_add_success'),
        'rev' => intval(pluginGetVariable('comments', 'backorder')),
    );
}

rpcRegisterFunction('plugin.comments.update', 'comments_rpc_manage');
