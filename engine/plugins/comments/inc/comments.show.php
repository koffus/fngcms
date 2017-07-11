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
// $newsID - [required] ID of the news for that comments should be showed
// $commID - [optional] ID of comment for showing in case if we just added it
// $commDisplayNum - [optional] num that is showed in 'show comment' template
// $callingParams
//		'plugin' => if is called from plugin - ID of plugin
//		'overrideTemplateName' => alternative template for display
//		'overrideTemplatePath' => alternative path for searching of template
//		'limitStart' => order comment no to start (for pagination)
//		'limitCount' => number of comments to show (for pagination)
//		'outprint'	 => flag: if set, output will be returned, elsewhere - will be added to mainblock
//		'total' => total number of comments in this news
function comments_show($newsID, $commID = 0, $commDisplayNum = 0, $callingParams = array()){
    global $mysql, $twig, $template, $config, $userROW, $parse, $PFILTERS, $TemplateCache;

    // Preload template configuration variables
    templateLoadVariables();

    // Use default <noavatar> file
    // - Check if noavatar is defined on template level
    $tplVars = $TemplateCache['site']['#variables'];
    $noAvatarURL = (isset($tplVars['configuration']) and is_array($tplVars['configuration']) and isset($tplVars['configuration']['noAvatarImage']) and $tplVars['configuration']['noAvatarImage'])?(tpl_url."/".$tplVars['configuration']['noAvatarImage']):(avatars_url."/noavatar.png");

    //->desired template path
    $templatePath = isset($callingParams['overrideTemplatePath']) ? $callingParams['overrideTemplatePath'] : (tpl_site.'plugins/comments');

    //->desired template
    $templateName = isset($callingParams['overrideTemplateName']) ? $callingParams['overrideTemplateName'] : 'comments.show';

    if ( !file_exists($templatePath . DS . $templateName . '.tpl') ) {
        $templatePath = tpl_site.'plugins/comments';
    }

    $xt = $twig->loadTemplate($templatePath . DS . $templateName . '.tpl');

    $joinFilter = array();
    if ($config['use_avatars']) {
        $joinFilter = array('users' => array('fields' => array('avatar')));
    }

    // RUN interceptors
    if (isset($PFILTERS['comments']) and is_array($PFILTERS['comments']))
        foreach ($PFILTERS['comments'] as $k => $v) {
            $xcfg = $v->commentsJoinFilter();
            if (is_array($xcfg) and isset($xcfg['users']) and isset($xcfg['users']['fields']) and is_array($xcfg['users']['fields'])) {
                $joinFilter['users']['fields'] = array_unique(array_merge($joinFilter['users']['fields'], $xcfg['users']['fields']));
            }
        }

    function _cs_am($k){ return 'u.'.$k.' as `users_'.$k.'`';	}
    if (isset($joinFilter['users']) and isset($joinFilter['users']['fields']) and is_array($joinFilter['users']['fields']) and (count($joinFilter['users']['fields']) > 0)) {
        $sql = "select c.*, ".
            join(", ", array_map('_cs_am', $joinFilter['users']['fields'])).
            ' from '.prefix.'_comments c'.
            ' left join '.uprefix.'_users u on c.author_id = u.id where c.post='.db_squote($newsID).($commID?(" and c.id=".db_squote($commID)):'');
    } else {
        $sql = "select c.* from ".prefix."_comments c WHERE c.post=".db_squote($newsID).($commID?(" and c.id=".db_squote($commID)):'');
    }

    $sql .= " order by c.id".(pluginGetVariable('comments', 'backorder')?' desc':'');

    // Comments counter
    $comnum = 0;

    // Check if we need to use limits
    $limitStart = isset($callingParams['limitStart'])?intval($callingParams['limitStart']):0;
    $limitCount = isset($callingParams['limitCount'])?intval($callingParams['limitCount']):0;
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
            'havePerm' => (is_array($userROW) and (($userROW['status'] == 1) or ($userROW['status'] == 2))) ? true : false,
            'isProfile' => (!empty($row['reg']) and getPluginStatusActive('uprofile')) ? true : false,
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
                $tVars['avatar'] = "<img src=\"".avatars_url."/".$row['users_avatar']."\" alt=\"".$row['author']."\" />";
            } else {
                // If gravatar integration is active, show avatar from GRAVATAR.COM

                if ($config['avatars_gravatar']) {
                    $tVars['avatar'] = '<img src="http://www.gravatar.com/avatar/'.md5(strtolower($row['mail'])).'.jpg?s='.$config['avatar_wh'].'&amp;d='.urlencode($noAvatarURL).'" alt=""/>';
                } else {
                    $tVars['avatar'] = "<img src=\"".$noAvatarURL."\" alt=\"\" />";
                }
            }
        } else {
            $tVars['avatar'] = '';
        }

        if ($tVars['havePerm']) {
            $tVars['edit_link'] = admin_url."/admin.php?mod=editcomments&amp;newsid=".$newsID."&amp;comid=".$row['id'];
            $tVars['delete_link'] = generateLink('core', 'plugin', array('plugin' => 'comments', 'handler' => 'delete'), array('id' => $row['id'], 'uT' => genUToken($row['id'])), true);
            $tVars['ip'] = "<a href=\"http://www.nic.ru/whois/?ip=$row[ip]\" title=\"".__('whois')."\">".__('whois').'</a>';
        }

        // RUN interceptors
        if (isset($PFILTERS['comments']) and is_array($PFILTERS['comments'])) {
            foreach ($PFILTERS['comments'] as $k => $v) {
                $v->showComments($newsID, $row, $comnum, $tvars);
            }
        }

        // run OLD-STYLE interceptors
        executeActionHandler('comments');

        // Show template
        $output .= $xt->render($tVars);
    }

    unset($rows);

    if ($callingParams['outprint']) {
        return $output;
    }
    $template['vars']['mainblock'] .= $output;
}

// $callingParams
//		'plugin' => if is called from plugin - ID of plugin
//		'overrideTemplateName' => alternative template for display
//		'overrideTemplatePath' => alternative path for searching of template
//		'outprint'	 	=> flag: if set, output will be returned, elsewhere - will be added to mainblock
function comments_showform($newsID, $callingParams = array()){
    global $mysql, $config, $template, $twig, $userROW, $PFILTERS;

    //->desired template path
    $templatePath = isset($callingParams['overrideTemplatePath'])?$callingParams['overrideTemplatePath']:(tpl_site.'plugins/comments');

    //->desired template
    if (isset($callingParams['overrideTemplateName'])) {
        $templateName = $callingParams['overrideTemplateName'];
    } else {
        $templateName = 'comments.form';
    }
    if ( !file_exists($templatePath . DS . $templateName . '.tpl') ) {
        $templatePath = tpl_site.'plugins/comments';
    }

    $xt = $twig->loadTemplate($templatePath . DS . $templateName . '.tpl');
    $tVars = array(
        'useBB' => $config['use_bbcodes'] ? true : false,
        'useSmilies' => $config['use_smilies'] ? true : false,
        'useCaptcha' => $config['use_captcha'] ? true : false,
        
        'admin_url' => admin_url,
        'skins_url' => skins_url,
        'post_url' => generateLink('core', 'plugin', array('plugin' => 'comments', 'handler' => 'add')),
        'rand' => rand(00000, 99999),
        'newsid' => $newsID.'#'.genUToken('comment.add.'.$newsID),
        'request_uri' => secure_html($_SERVER['REQUEST_URI']),
        'bbcodes' => $config['use_bbcodes'] ? BBCodes() : '',
        'smilies' => $config['use_smilies'] ? Smilies('comments', 10) : '',
        'captcha' => $config['use_captcha'] ? rand(00000, 99999) : '',
        'captcha_url' => $config['use_captcha'] ? admin_url . '/captcha.php' : '',
        );

    if (!empty($_COOKIE['com_username'])) {
        $tVars['savedname'] = secure_html(urldecode($_COOKIE['com_username']));
        $tVars['savedmail'] = secure_html(urldecode($_COOKIE['com_usermail']));
    } else {
        $tVars['savedname'] = '';
        $tVars['savedmail'] = '';
    }

    // RUN interceptors
    if (isset($PFILTERS['comments']) and is_array($PFILTERS['comments']))
        foreach ($PFILTERS['comments'] as $k => $v)
            $v->addCommentsForm($newsID, $tvars);

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
