<?php

//
// Copyright (C) 2006-2011 Next Generation CMS (http://ngcms.ru/)
// Name: comments.show.php
// Description: Routines for showing comments
// Author: Vitaly Ponomarev, Alexey Zinchenko
//

// Protect against hack attempts
if (!defined('NGCMS')) die ('HAL');

//
// Show comments for a news
// $postid - [required] ID of the news for that comments should be showed
// $commID - [optional] ID of comment for showing in case if we just added it
// $commDisplayNum - [optional] num that is showed in 'show comment' template
// $callingParams
//		'plugin' => if is called from plugin - ID of plugin
//		'module' => table of DB
//		'overridetName' => alternative template for display
//		'overrideTemplatePath' => alternative path for searching of template
//		'limitStart' => order comment no to start (for pagination)
//		'limitCount' => number of comments to show (for pagination)
//		'outprint'	 => flag: if set, output will be returned, elsewhere - will be added to mainblock
//		'total' => total number of comments in this news
function comments_show($postid, $commID = 0, $commDisplayNum = 0, $callingParams = array())
{
    global $mysql, $twig, $template, $config, $userROW, $parse, $PFILTERS, $TemplateCache;

    // Preload template configuration variables
    templateLoadVariables();

    // Use default <noavatar> file
    // - Check if noavatar is defined on template level
    $tplVars = $TemplateCache['site']['#variables'];
    $noAvatarURL = (isset($tplVars['configuration']) and is_array($tplVars['configuration']) and isset($tplVars['configuration']['noAvatarImage']) and $tplVars['configuration']['noAvatarImage'])?(tpl_url."/".$tplVars['configuration']['noAvatarImage']):(avatars_url."/noavatar.png");

    // Desired template path and template name
    if (!empty($callingParams['overrideTemplatePath']) and !empty($callingParams['overridetName'])) {
        $tName = $callingParams['overrideTemplatePath'] . DS . $callingParams['overridetName'] . '.tpl';
    } else if (!empty($callingParams['overrideTemplatePath'])) {
        $tName = $callingParams['overrideTemplatePath'] . DS . 'comments.show.tpl';
    }
    if(empty($tName) or !file_exists($tName)) {
        $tPath = locatePluginTemplates('comments.show', 'comments', pluginGetVariable('comments', 'localSource') );
        $tName = $tPath['comments.show'] . 'comments.show.tpl';
    }

    $xt = $twig->loadTemplate($tName);

    $joinFilter = array();
    if ($config['use_avatars']) {
        $joinFilter = array('users' => array('fields' => array('avatar')));
    }

    // RUN interceptors
    if (isset($PFILTERS['comments']) and is_array($PFILTERS['comments'])) {
        foreach ($PFILTERS['comments'] as $k => $v) {
            $xcfg = $v->commentsJoinFilter();
            if (is_array($xcfg) and isset($xcfg['users']) and isset($xcfg['users']['fields']) and is_array($xcfg['users']['fields'])) {
                $joinFilter['users']['fields'] = array_unique(array_merge($joinFilter['users']['fields'], $xcfg['users']['fields']));
            }
        }
    }

    function _cs_am($k){
        return 'u.'.$k.' AS `users_'.$k.'`';
    }
    if (isset($joinFilter['users']) and isset($joinFilter['users']['fields']) and is_array($joinFilter['users']['fields']) and (count($joinFilter['users']['fields']) > 0)) {
        $sql = "SELECT c.*, ".
            join(", ", array_map('_cs_am', $joinFilter['users']['fields'])).
            ' FROM '.prefix.'_comments c'.
            ' LEFT JOIN '.uprefix.'_users u ON c.author_id = u.id WHERE c.post='.db_squote($postid).($commID?(" AND c.id=".db_squote($commID)):'');
    } else {
        $sql = "SELECT c.* FROM ".prefix."_comments c WHERE c.post=".db_squote($postid).($commID ? (" AND c.id=".db_squote($commID)) : '');
    }

    // Check module table exist
    if (!empty($callingParams['module']) and in_array($callingParams['module'], ['news', 'images']) ) {
        $module = secure_html($callingParams['module']);
    } else {
        $module = 'news';
    }

    $sql .= " AND c.module=" . db_squote($module);

    $moderate = (1 == pluginGetVariable('comments', 'moderate')) ? true : false;
    if ($moderate) {
        $sql .= " AND c.approve='1'";
    }

    $sql .= " order by c.id".(pluginGetVariable('comments', 'backorder')?' desc':'');

    // Comments counter
    $comnum = 0;

    // Check if we need to use limits
    $limitStart = isset($callingParams['limitStart']) ? intval($callingParams['limitStart']) : 0;
    $limitCount = isset($callingParams['limitCount']) ? intval($callingParams['limitCount']) : 0;
    if ($limitStart or $limitCount) {
        $sql .= ' limit '.$limitStart.", ".$limitCount;
        $comnum = $limitStart;
    }

    $timestamp = pluginGetVariable('comments', 'timestamp') ? pluginGetVariable('comments', 'timestamp') : 'j.m.Y - H:i';

    $output = '';
    $rows = $mysql->select($sql);

    foreach ($rows as $row) {
        $comnum++;

        $tVars = array(
            //'havePerm' => (is_array($userROW) and (($userROW['status'] == 1) or ($userROW['status'] == 2) or ($row['author_id'] == $userROW['id']))) ? true : false,
            'havePerm' => (is_array($userROW) and 1 == $userROW['status']) ? true : false,
            'isProfile' => (0 != $row['author_id'] and pluginIsActive('uprofile')) ? true : false,
            'useBB' => $config['use_bbcodes'] ? true : false,
            'hasAnswer' => !empty($row['answer']) ? true : false,
            
            'id' => $row['id'],
            'author' => $row['author'],
            'mail' => $row['mail'],
            'date' => Lang::retDate($timestamp, $row['postdate']),
            'dateStamp' => intval($row['postdate']),
            'alternating' => ($comnum%2) ? "comment-even" : "comment-odd",
            );

        if ($tVars['isProfile']) {
            $tVars['profile_link'] = checkLinkAvailable('uprofile', 'show')?
                generateLink('uprofile', 'show', array('name' => $row['author'], 'id' => $row['author_id'])):
                generateLink('core', 'plugin', array('plugin' => 'uprofile', 'handler' => 'show'), array('id' => $row['author_id']));
        }

        $text = $row['text'];
        if ($config['blocks_for_reg']) { $text = $parse->userblocks($text); }
        if ($config['use_bbcodes']) { $text = $parse->bbcodes($text); }
        if ($config['use_htmlformatter']) { $text = $parse->htmlformatter($text); }
        if ($config['use_smilies']) { $text = $parse->smilies($text); }
        $tVars['comment'] = $text;

        // Здесь ДЕЛАЕМ ДРЕВОВИДНУЮ СТРУКТУРУ
        if($tVars['hasAnswer']){
            $answer = $row['answer'];
            if ($config['blocks_for_reg']) { $answer = $parse->userblocks($row['answer']); }
            if ($config['use_htmlformatter']) { $answer = $parse->htmlformatter($answer); }
            if ($config['use_bbcodes']) { $answer = $parse->bbcodes($answer); }
            if ($config['use_smilies']) { $answer = $parse->smilies($answer); }
            $tVars['answer'] = $answer;
            $tVars['name'] = $row['name'];
        }

        if ($commID and $commDisplayNum) {
            $tVars['comnum'] = $commDisplayNum;
        } else {
            if (pluginGetVariable('comments', 'backorder') and (intval($callingParams['total'])>0)) {
                $tVars['comnum'] = intval($callingParams['total']) - $comnum + 1;
            } else {
                $tVars['comnum'] = $comnum;
            }
        }

        if ($config['use_avatars']) {
            if ($row['users_avatar']) {
                $tVars['avatar'] = avatars_url."/".$row['users_avatar'];
            } else {
                // If gravatar integration is active, show avatar from GRAVATAR.COM

                if ($config['avatars_gravatar']) {
                    $tVars['avatar'] = 'http://www.gravatar.com/avatar/'.md5(strtolower($row['mail'])).'.jpg?s='.$config['avatar_wh'].'&amp;d='.urlencode($noAvatarURL);
                } else {
                    $tVars['avatar'] = $noAvatarURL;
                }
            }
        } else {
            $tVars['avatar'] = '';
        }

        if ($tVars['havePerm']) {
            $tVars['edit_link'] = admin_url."/admin.php?mod=extra-config&plugin=comments&action=edit&comid=".$row['id'];
            $tVars['delete_link'] = generateLink('core', 'plugin', 
                    array('plugin' => 'comments', 'handler' => 'delete'),
                    array('id' => $row['id'], 'module' => $module, 'uT' => genUToken($row['id'])),
                    true);
            $tVars['ip'] = "<a href=\"http://www.nic.ru/whois/?ip=$row[ip]\" title=\"".__('whois')."\">".__('whois').'</a>';
        }

        // RUN interceptors
        if (isset($PFILTERS['comments']) and is_array($PFILTERS['comments'])) {
            foreach ($PFILTERS['comments'] as $k => $v) {
                $v->showComments($postid, $row, $comnum, $tVars);
            }
        }

        // run OLD-STYLE interceptors
        executeActionHandler('comments');

        // Show template
        $output .= $xt->render($tVars);//die(dd($PFILTERS));
    }

    unset($rows);

    if ($callingParams['outprint']) {
        return $output;
    }
    $template['vars']['mainblock'] .= $output;
}

// postid => ID in news DB table or in $callingParams['plugin'] DB table, example images (plugin gallery)
// $callingParams
//		'plugin' => if is called from plugin - table DB of plugin - TABLE not PLUGIN name
//		'overridetName' => alternative template for display
//		'overrideTemplatePath' => alternative path for searching of template
//		'outprint' => flag: if set, output will be returned, elsewhere - will be added to mainblock
function comments_showform($postid, $callingParams = array())
{
    global $mysql, $config, $template, $twig, $userROW, $PFILTERS, $REQUEST_URI;

    // Desired template path and template name
    if (!empty($callingParams['overrideTemplatePath']) and !empty($callingParams['overridetName'])) {
        $tName = $callingParams['overrideTemplatePath'] . DS . $callingParams['overridetName'] . '.tpl';
    } else if (!empty($callingParams['overrideTemplatePath'])) {
        $tName = $callingParams['overrideTemplatePath'] . DS . 'comments.form.tpl';
    }
    if(empty($tName) or !file_exists($tName)) {
        $tPath = locatePluginTemplates('comments.form', 'comments', pluginGetVariable('comments', 'localSource') );
        $tName = $tPath['comments.form'] . 'comments.form.tpl';
    }

    $xt = $twig->loadTemplate($tName);

    $tVars = array(
        'useBB' => $config['use_bbcodes'] ? true : false,
        'useSmilies' => $config['use_smilies'] ? true : false,
        'useCaptcha' => $config['use_captcha'] ? true : false,
        
        'admin_url' => admin_url,
        'skins_url' => skins_url,
        'post_url' => generateLink('core', 'plugin', array('plugin' => 'comments', 'handler' => 'add')),
        'module' => isset($callingParams['module']) ? $callingParams['module'] : 'news',
        'postid' => $postid,
        'tokken' => genUToken('comment.add.' . $postid),
        'redirect' => $REQUEST_URI,
        'bbcodes' => $config['use_bbcodes'] ? BBCodes() : '',
        'smilies' => $config['use_smilies'] ? Smilies('comments', 10) : '',
        'captcha_url' => $config['use_captcha'] ? admin_url . '/captcha.php' : '',
        'captcha_rand' => $config['use_captcha'] ? mt_rand() / mt_getrandmax() : '',
        );

    if (!empty($_COOKIE['com_username'])) {
        $tVars['savedname'] = secure_html(urldecode($_COOKIE['com_username']));
        $tVars['savedmail'] = secure_html(urldecode($_COOKIE['com_usermail']));
    } else {
        $tVars['savedname'] = '';
        $tVars['savedmail'] = '';
    }

    // RUN interceptors
    if (isset($PFILTERS['comments']) and is_array($PFILTERS['comments'])) {
        foreach ($PFILTERS['comments'] as $k => $v) {
            $v->addCommentsForm($postid, $tvars);
        }
    }

    // RUN interceptors ( OLD-style )
    executeActionHandler('comments_form');

    $output = $xt->render($tVars);
    if ($callingParams['outprint']) {
        return $output;
    }
    $template['vars']['mainblock'] .= $output;
}

// preload plugins
loadActionHandlers('comments');
loadActionHandlers('comments:show');
