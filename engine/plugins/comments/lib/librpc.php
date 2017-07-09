<?php

function comments_rpc_manage($params) {
    global $mysql, $config, $AUTH_METHOD, $userROW, $ip, $parse, $catmap, $catz, $PFILTERS;

    // Connect library
    include_once(root . "/plugins/comments/inc/comments.show.php");
    Lang::loadPlugin('comments', 'main', '', '', ':');
    
    $SQL = array();

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
    } else if (is_array($userROW)) {
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

    // CSRF protection variables
    $sValue = '';
    if (preg_match('#^(\d+)\#(.+)$#', $params['newsid'], $m)) {
        $SQL['post'] = intval($m[1]);
        $sValue = intval($m[2]);
    }

    if (empty($SQL['post']) or $sValue != genUToken('comment.add.'.$SQL['post'])) {
        return array('status' => 0, 'errorCode' => 999, 'errorText' => __('comments:err.regonly'));
    }

    // If user is not logged, make some additional tests
    if (!$is_member) {
        // Check if unreg are allowed to make comments
        if (pluginGetVariable('comments', 'regonly')) {
            return array('status' => 0, 'errorCode' => 999, 'errorText' => __('comments:err.regonly'));
        }
        // Check captcha for unregistered visitors
        if ($config['use_captcha']) {
            $vcode = $params['vcode'];

            if ($vcode != $_SESSION['captcha']) {
                return array('status' => 0, 'errorCode' => 999, 'errorText' => __('comments:err.vcode'));
            }

            // Update captcha
            $_SESSION['captcha'] = rand(00000, 99999);
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
            if (is_array($mysql->record("select * from ".uprefix."_users where mail = ".db_squote($SQL['mail'])." limit 1"))) {
            return array('status' => 0, 'errorCode' => 999, 'errorText' => __('comments:err.edupmail'));
            }
        }
    }

    $SQL['text'] = secure_html($params['content']);
    $maxlen = intval(pluginGetVariable('comments', 'maxlen'));
    $maxlen = ($maxlen > 2) ? $maxlen : 500;
    if (mb_strlen($SQL['text'], 'UTF-8') > $maxlen or mb_strlen($SQL['text'], 'UTF-8') < 2) {
        return array('status' => 0, 'errorCode' => 999, 'errorText' => str_replace('{maxlen}', pluginGetVariable('comments', 'maxlen'), __('comments:err.badtext')));
    }

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

    // Locate news
    if ($news_row = $mysql->record("select * from ".prefix."_news where id = ".db_squote($SQL['post']))) {
        // Determine if comments are allowed in this specific news
        $allowCom = $news_row['allow_com'];
        if ($allowCom == 2) {
            // `Use default` - check master category
            $catid = explode(',', $news_row['catid']);
            $masterCat = intval(array_shift($catid));
            if ($masterCat and isset($catmap[$masterCat])) {
                $allowCom = intval($catz[$catmap[$masterCat]]['allow_com']);
            }

            // If we still have 2 (no master category or master category also have 'default' - fetch plugin's config
            if ($allowCom == 2) {
                $allowCom = pluginGetVariable('comments', 'global_default');
            }
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
                    return array('status' => 0, 'errorCode' => 999, 'errorText' => __('comments:err.multilock'));
                }
            } else {
                //print "Last post: ".$lpost['id']."<br>\n";
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
    $SQL['reg'] = ($is_member) ? '1' : '0';

    // RUN interceptors
    loadActionHandlers('comments:add');

    if (isset($PFILTERS['comments']) and is_array($PFILTERS['comments']))
        foreach ($PFILTERS['comments'] as $k => $v) {
            $pluginResult = $v->addComments($memberRec, $news_row, $tvars, $SQL);
            if ((is_array($pluginResult) and ($pluginResult['result'])) or (!is_array($pluginResult) and $pluginResult))
                continue;

            return array('status' => 0, 'errorCode' => 999, 'errorText' => str_replace(array('{plugin}', '{errorText}'), array($k, (is_array($pluginResult) and isset($pluginResult['errorText'])?$pluginResult['errorText']:'')), __('comments:err.'.((is_array($pluginResult) and isset($pluginResult['errorText']))?'e':'').'pluginlock')));
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
    if (isset($PFILTERS['comments']) and is_array($PFILTERS['comments'])) {
        foreach ($PFILTERS['comments'] as $k => $v) {
            $v->addCommentsNotify($memberRec, $news_row, $tvars, $SQL, $comment_id);
        }
    }

    // Email informer
    if (pluginGetVariable('comments', 'inform_author') or pluginGetVariable('comments', 'inform_admin')) {
        $alink = ($SQL['author_id']) ? generatePluginLink('uprofile', 'show', array('name' => $SQL['author'], 'id' => $SQL['author_id']), array(), false, true) : '';
        $body = str_replace(
            array( '{username}',
                    '[userlink]',
                    '[/userlink]',
                    '{comment}',
                    '{newslink}',
                    '{newstitle}'),
            array( $SQL['author'],
                    ($SQL['author_id'])?'<a href="'.$alink.'">':'',
                    ($SQL['author_id'])?'</a>':'',
                    $parse->bbcodes($parse->smilies(secure_html($SQL['text']))),
                    News::generateLink($news_row, false, 0, true),
                    $news_row['title'],
                    ),
            __('notice')
        );

        if (pluginGetVariable('comments', 'inform_author')) {
            // Determine author's email
            if (is_array($umail=$mysql->record("select * from ".uprefix."_users where id = ".db_squote($news_row['author_id'])))) {
                sendEmailMessage($umail['mail'], __('newcomment'), $body);
            }
        }

        if (pluginGetVariable('comments', 'inform_admin'))
            sendEmailMessage($config['admin_mail'], __('newcomment'), $body);

    }

    @setcookie("com_username", urlencode($SQL['author']), 0, '/');
    @setcookie("com_usermail", urlencode($SQL['mail']), 0, '/');

    // Check if we need to override news template
    $callingCommentsParams = array('outprint' => true);

    // Set default template path
    $templatePath = tpl_dir . $config['theme'];

    // Find first category
    $catid = explode(',', $news_row['catid']);
    $fcat = array_shift($catid);
    // Check if there is a custom mapping
    if ($fcat and $catmap[$fcat] and ($ctname = $catz[$catmap[$fcat]]['tpl'])) {
        // Check if directory exists
        if (is_dir($templatePath . '/ncustom/' . $ctname))
            $callingCommentsParams['overrideTemplatePath'] = $templatePath . '/ncustom/' . $ctname;
    }
    //if () {
        return array(
            'status' => 1,
            'errorCode' => 0,
            'content' => comments_show($news_row['id'], $comment_id, $news_row['com'] + 1, $callingCommentsParams),
            'rev' => intval(pluginGetVariable('comments', 'backorder')),
        );
        
    /*} else {
        return array(
            'status' => 0,
            'data' => $template['vars']['mainblock'],
        );
    }*/

    $tpath = locatePluginTemplates(array('ajax.add.remove.links.style'), 'comments', pluginGetVariable('comments', 'localSource'));
    $xt = $twig->loadTemplate($tpath['ajax.add.remove.links.style'].'ajax.add.remove.links.style.tpl');
    return array('status' => 1, 'errorCode' => 0, 'content' => $xt->render($tVars));
}

//return array('status' => 0, 'errorCode' => 3, 'errorText' => 'Access denied');

rpcRegisterFunction('plugin.comments.update', 'comments_rpc_manage');
