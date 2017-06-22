<?php

//
// Copyright (C) 2006-2010 Next Generation CMS (http://ngcms.ru/)
// Name: static.php
// Description: Static pages display sub-engine
// Author: Vitaly Ponomarev, Alexey Zinchenko
//

// Protect against hack attempts
if (!defined('NGCMS')) die ('HAL');

Lang::load('static', 'site');

// Params - Static page characteristics
// * id			- page ID
// * altname	- alt. name of the page
function showStaticPage($params)
{
    global $config, $tpl, $mysql, $userROW, $parse, $template, $SYSTEM_FLAGS, $PFILTERS, $SUPRESS_TEMPLATE_SHOW;

    loadActionHandlers('static');

    $limit = '';
    if (intval($params['id'])) {
        $limit = 'id = ' . db_squote($params['id']);
    } elseif ($params['altname']) {
        $limit = 'alt_name = ' . db_squote($params['altname']);
    }

    if ((!$limit) or (!is_array($row = $mysql->record("select * from " . prefix . "_static where approve = 1 and " . $limit)))) {
        if (!$params['FFC']) {
            error404();
        }
        return false;
    }

    // Save some significant news flags for plugin processing
    $SYSTEM_FLAGS['static']['db.id'] = $row['id'];

    if (is_array($PFILTERS['static']))
        foreach ($PFILTERS['static'] as $k => $v) {
            $v->showStaticPre($row['id'], $row, array());
        }

    $content = $row['content'];

    // If HTML code is not permitted - LOCK it
    if (!($row['flags'] == 2))
        $content = str_replace('<', '&lt;', $content);

    if ($config['blocks_for_reg']) $content = $parse->userblocks($content);
    if ($config['use_htmlformatter'] and (!($row['flags'] & 1))) $content = $parse->htmlformatter($content);
    if ($config['use_bbcodes']) $content = $parse->bbcodes($content);
    if ($config['use_smilies']) $content = $parse->smilies($content);

    $SYSTEM_FLAGS['info']['title']['item'] = secure_html($row['title']);

    $template['vars']['titles'] .= ' — ' . $row['title'];
    $tvars['vars'] = array(
        'title' => $row['title'],
        'content' => $content,
        'postdate' => ($row['postdate'] > 0) ? strftime('%d.%m.%Y %H:%M', $row['postdate']) : '',
    );

    if (is_array($userROW) and ($userROW['status'] == 1 or $userROW['status'] == 2)) {
        $tvars['vars']['[edit-static]'] = '<a href="' . admin_url . '/admin.php?mod=static&action=edit&id=' . $row['id'] . '" target="_blank">';
        $tvars['vars']['[/edit-static]'] = '</a>';
        $tvars['vars']['[del-static]'] = '<a onclick="confirmIt(\'' . admin_url . '/admin.php?mod=static&subaction=do_mass_delete&selected[]=' . $row['id'] . '\', \'' . __('sure_del') . '\');" target="_blank" style="cursor: pointer;">';
        $tvars['vars']['[/del-static]'] = '</a>';
    } else {
        $tvars['regx']["'\\[edit-static\\].*?\\[/edit-static\\]'si"] = '';
        $tvars['regx']["'\\[del-static\\].*?\\[/del-static\\]'si"] = '';
    }

    $tvars['vars']['[print-link]'] = '<a href="' . generatePluginLink('static', 'print', array('id' => $row['id'], 'altname' => $params['altname']), array(), true) . '">';
    $tvars['vars']['[/print-link]'] = '</a>';

    if (is_array($PFILTERS['static']))
        foreach ($PFILTERS['static'] as $k => $v) {
            $v->showStatic($row['id'], $row, $tvars, array());
        }

    executeActionHandler('static');

    if ($row['template']) {
        $templateName = 'static/' . $row['template'];
    } else {
        $templateName = 'static/default';
    }

    // Check for print mode
    if ($params['print'] and file_exists(tpl_dir . $config['theme'] . '/static/' . ($row['template'] ? $row['template'] : 'default') . '.print.tpl')) {
        $templateName .= '.print';
        $SUPRESS_TEMPLATE_SHOW = true;
    }

    // Check for OWN main.tpl for static page
    if (($row['flags'] & 4) and file_exists(tpl_dir . $config['theme'] . '/static/' . ($row['template'] ? $row['template'] : 'default') . '.main.tpl')) {
        $SYSTEM_FLAGS['template.main.name'] = ($row['template'] ? $row['template'] : 'default') . '.main';
        $SYSTEM_FLAGS['template.main.path'] = tpl_dir . $config['theme'] . '/static';
    }

    $tpl->template($templateName, tpl_dir . $config['theme']);
    $tpl->vars($templateName, $tvars);
    $template['vars']['mainblock'] .= $tpl->show($templateName);

    // Set meta tags for news page
    $SYSTEM_FLAGS['meta']['description'] = $row['description'];
    $SYSTEM_FLAGS['meta']['keywords'] = $row['keywords'];

    return true;
}
