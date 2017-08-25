<?php

//
// Copyright (C) 2006-2010 Next Generation CMS (http://ngcms.ru/)
// Name: static.php
// Description: Static pages display sub-engine
// Author: Vitaly Ponomarev, Alexey Zinchenko
//

// Protect against hack attempts
if (!defined('NGCMS')) die ('HAL');

// Params - Static page characteristics
// * id			- page ID
// * altname	- alt. name of the page
function showStaticPage($params = [])
{
    global $config, $twig, $mysql, $userROW, $parse, $template, $SYSTEM_FLAGS, $PFILTERS, $SUPRESS_TEMPLATE_SHOW;

    Lang::load('static', 'site');

    loadActionHandlers('static');

    $where = '';
    if (intval($params['id'])) {
        $where = 'i =' . db_squote($params['id']);
    } elseif ($params['altname']) {
        $where = 'alt_name=' . db_squote($params['altname']);
    }

    if (empty($where) or (!is_array($row = $mysql->record("SELECT * FROM " . prefix . "_static WHERE approve=1 AND " . $where)))) {
        if (!$params['FFC']) {
            error404();
        }
        return false;
    }

    // If isset static page we now, validate and prepare data
    $row['title'] = secure_html($row['title']);
    $row['alt_name'] = secure_html($row['alt_name']);
    $row['content'] = trim($row['content']);

    $row['description'] = secure_html(str_replace(["\r\n", "\n"], ' ', $row['description']));
    $row['keywords'] = secure_html($row['keywords']);

    $row['template'] = tpl_site . (!empty($row['template']) ? 'static/' . secure_html($row['template']) : 'static/default');
    $row['postdate'] = isset($row['postdate']) ? intval($row['postdate']) : null;

    // Save some significant news flags for plugin processing
    $SYSTEM_FLAGS['static']['db.id'] = $row['id'];

    if (isset($PFILTERS['static']) and is_array($PFILTERS['static'])) {
        foreach ($PFILTERS['static'] as $k => $v) {
            $v->showStaticPre($row['id'], $row, array());
        }
    }

    // If HTML code is not permitted - LOCK it
    if (!($row['flags'] & 2)) {
        $row['content'] = secure_html($row['content']);
    }

    if ($config['blocks_for_reg']) $row['content'] = $parse->userblocks($row['content']);
    if ($config['use_htmlformatter'] and (!($row['flags'] & 1))) $row['content'] = $parse->htmlformatter($row['content']);
    if ($config['use_bbcodes']) $row['content'] = $parse->bbcodes($row['content']);
    if ($config['use_smilies']) $row['content'] = $parse->smilies($row['content']);

    $SYSTEM_FLAGS['info']['title']['item'] = $row['title'];

    $template['vars']['titles'] .= ' — ' . $row['title'];
    $tVars = array(
        'staticTitle' => $row['title'],
        'staticContent' => $row['content'],
        'staticDate' => ($row['postdate'] > 0) ? strftime('%d.%m.%Y %H:%M', $row['postdate']) : '',
        'staticDateStamp' => $row['postdate'],
        'staticPrintLink' => generatePluginLink('static', 'print', array('id' => $row['id'], 'altname' => $row['alt_name']), array(), true),
    );

    if (checkPermission(array('plugin' => '#admin', 'item' => 'static'), null, 'modify')) {
        $tVars['havePermission'] = true;
        $tVars['staticEditLink'] = admin_url . '/admin.php?mod=static&action=editForm&id=' . $row['id'];
    } else {
        $tVars['havePermission'] = false;
        $tVars['staticEditLink'] = '';
    }

    if (isset($PFILTERS['static']) and is_array($PFILTERS['static'])) {
        foreach ($PFILTERS['static'] as $k => $v) {
            $v->showStatic($row['id'], $row, $tvars, array());
        }
    }

    executeActionHandler('static');

    // Check for print mode
    if ($params['print'] and file_exists($row['template'] . '.print.tpl')) {
        $row['template'] .= '.print';
        $SUPRESS_TEMPLATE_SHOW = true;
    }

    // Check for OWN main.tpl for static page
    if (($row['flags'] & 4) and file_exists($row['template'] . '.main.tpl')) {
        $SYSTEM_FLAGS['template.main.name'] = $row['template'] . '.main';
        $SYSTEM_FLAGS['template.main.path'] = tpl_site . '/static';
    }

    $template['vars']['mainblock'] .= $twig->loadTemplate($row['template'] . '.tpl')->render($tVars);

    // Set meta tags for static page
    if ($config['meta']) {
        if (!empty($row['description'])) {
            $SYSTEM_FLAGS['meta']['description'] = $row['description'];
        } else {
            $SYSTEM_FLAGS['meta']['description'] = secure_html($row['title'] . '. ' . home_title);
        }
        if (!empty($row['keywords'])) {
            $SYSTEM_FLAGS['meta']['keywords'] = $row['keywords'];
        } else {
            // Удаляем все слова меньше 3-х символов
            $row['keywords'] = preg_replace('#\b[\d\w]{1,3}\b#iu', '', secure_html($row['title']) . ' ' . home_title);
            // Удаляем знаки препинания
            $row['keywords'] = preg_replace('#[^\d\w ]+#iu', '', $row['keywords']);
            // Удаляем лишние пробельные символы
            $row['keywords'] = preg_replace('#[\s]+#iu', ' ', $row['keywords']);
            // Заменяем пробелы на запятые
            $row['keywords'] = preg_replace('#[\s]#iu', ',', $row['keywords']);
            // Выводим для леньтяев
            $SYSTEM_FLAGS['meta']['keywords'] = mb_strtolower(trim($row['keywords'], ','));
        }
    }

    return true;
}
