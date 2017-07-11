<?php

//
// Configuration file for plugin
//

// Protect against hack attempts
if (!defined('NGCMS')) die ('HAL');

$action = isset($_REQUEST['action']) ? $_REQUEST['action'] : false;

switch ($action) {
    case 'edit': case 'add': editform(); break;
    case 'confirm': editform(); break;
    case 'delete': delete(); break;
    case 're_map': re_map(); showlist(); break;
    default: showlist();
}

function showlist()
{
    global $tpl, $mysql;
    $static_page = $mysql->select('select `id`, `title` from '.prefix.'_static order by `title`, `id`');
    $tpath = locatePluginTemplates(array('conf.list', 'conf.list.row'), 're_stat');
    $output = ''; $no = 1; $t_values = array();
    if(is_array($values = pluginGetVariable('re_stat', 'values'))) {
        foreach($values as $key => $row) {
            $title = '';
            foreach ($static_page as $page) if (intval($page['id']) == $row['id']){$title = $page['title']; break;}
            $pvars['vars'] = array (
                'id' => $key,
                'no' => $no ++,
                'code' => $row['code'],
                'title' => ($title?$title:'Такой страницы не сущуствует'),
                'error' => '',
                );
            if (in_array($row['code'], $t_values, true)) $pvars['vars']['error'] = 'Повторяющийся код';
            $t_values[] = $row['code'];
            $tpl->template('conf.list.row', $tpath['conf.list.row']);
            $tpl->vars('conf.list.row', $pvars);
            $output .= $tpl->show('conf.list.row');
        }
    }
    $tvars['vars']['entries'] = $output;
    $tpl->template('conf.list', $tpath['conf.list']);
    $tpl->vars('conf.list', $tvars);
    print $tpl->show('conf.list');
}

function editform()
{
    global $mysql, $tpl, $config;
    if (!isset($_REQUEST['id'])) {
        msg(array('type' => 'danger', 'message' => 'Значение для редактирования/добавления не определено!'));
        showlist();	return false; }
    $id = intval($_REQUEST['id']);
    $values = pluginGetVariable('re_stat', 'values');
    if ($id != -1 and !is_array($values)) {
        msg(array('type' => 'danger', 'message' => 'В базе отсутствуют значения, редактировать нечего!'));
        showlist();	return false; }
    if ($id != -1 and !array_key_exists($id, $values)) {
        msg(array('type' => 'danger', 'message' => 'Ключ id='.$id.' отсутствует в базе'));
        showlist(); return false; } 
    $if_error = false; $idstat = 0; $code = '';
    if (isset($_REQUEST['code']) and isset($_REQUEST['idstat'])){
        $code = secure_html(convert($_REQUEST['code']));
        if (!$code) { 
            msg(array('type' => 'danger', 'message' => 'Значение <b>код</b> не может быть пустым'));
            $if_error = true; }
        foreach ($values as $key => $row) if ($row['code'] === $code and $key != $id){
            msg(array('type' => 'danger', 'message' => 'Такое значение <b>код</b> уже присутствует в списке'));
            $if_error = true; }
        if (!$if_error){
            $idstat = intval($_REQUEST['idstat']);
            $ULIB = new UrlLibrary();
            $ULIB->loadConfig();
            if ($id == -1) {
                $values[] = array('code' => $code, 'id' => $idstat);
            } else {
                $ULIB->removeCommand('re_stat', $values[$id]['code']);
                $values[$id]['code'] = $code;
                $values[$id]['id'] = $idstat;}
            pluginSetVariable('re_stat', 'values', $values);
            // Load CORE Plugin
            $cPlugin = CPlugin::instance();
            // Save configuration parameters of plugins
            $cPlugin->saveConfig();
            $title = 'Такой страницы не сущуствует';
            foreach ($mysql->select('select `title` from '.prefix.'_static where `id`='.$idstat.' limit 1') as $page) $title = $page['title'];
            $ULIB->registerCommand('re_stat', $code, array('vars' => array(), 'descr' => array ($config['default_lang'] => $title)));
            $ULIB->saveConfig();
            showlist();
            return;
        }
    }
    $static_page = $mysql->select('select `id`, `title` from '.prefix.'_static order by `title`, `id`');
    $tpath = locatePluginTemplates(array('conf.edit'), 're_stat');
    $statlist = array();
    foreach ($static_page as $row)
        $statlist[$row['id']] = $row['title'];
    $tvars['vars']['statlist'] = MakeDropDown($statlist, 'idstat', ($if_error?$idstat:(isset($values[$id]['id'])?$values[$id]['id']:-1)));
    $tvars['vars']['code'] = ($if_error?$code:(isset($values[$id]['code'])?$values[$id]['code']:''));
    $tvars['vars']['id'] = $id;
    $tvars['regx']['/\[add\](.*?)\[\/add\]/si'] = '';
    $tvars['regx']['/\[edit\](.*?)\[\/edit\]/si'] = '';
    if ($id == -1) $tvars['regx']['/\[add\](.*?)\[\/add\]/si'] = '$1'; else $tvars['regx']['/\[edit\](.*?)\[\/edit\]/si'] = '$1';
    $tpl->template('conf.edit', $tpath['conf.edit']);
    $tpl->vars('conf.edit', $tvars);
    print $tpl->show('conf.edit');
}

function delete()
{
    if (!isset($_REQUEST['id'])) {
        msg(array('type' => 'danger', 'message' => 'Значение для удаления не определено, удалять нечего!'));
        showlist();	return false; }
    $id = intval($_REQUEST['id']);
    $values = pluginGetVariable('re_stat', 'values');
    if (!is_array($values)) {
        msg(array('type' => 'danger', 'message' => 'В базе отсутствуют значения, удалять нечего!'));
        showlist();	return false; }
    if (!array_key_exists($id, $values)) {
        msg(array('type' => 'danger', 'message' => 'Ключ id='.$id.' отсутствует в базе'));
        showlist(); return false; }

    $ULIB = new UrlLibrary();
    $ULIB->loadConfig();
    $ULIB->removeCommand('re_stat', $values[$id]['code']);
    $ULIB->saveConfig();
    
    unset($values[$id]);
    pluginSetVariable('re_stat', 'values', $values);
    // Load CORE Plugin
    $cPlugin = CPlugin::instance();
    // Save configuration parameters of plugins
    $cPlugin->saveConfig();
    showlist();
}

function re_map()
{
    global $mysql, $config;
    $ULIB = new UrlLibrary();
    $ULIB->loadConfig();
    if (isset($ULIB->CMD['re_stat']))
        unset($ULIB->CMD['re_stat']);
    $values = pluginGetVariable('re_stat', 'values');
    foreach ($values as $key => $row){
        $title = 'Такой страницы не сущуствует';
        foreach ($mysql->select('select `title` from '.prefix.'_static where `id`='.$row['id'].' limit 1') as $page) $title = $page['title'];
        $ULIB->registerCommand('re_stat', $row['code'], array('vars' => array(), 'descr' => array ($config['default_lang'] => $title)));
    }
    $ULIB->saveConfig();
    msg(array('message' => 'Карта ссылок успешно перестроена'));
}