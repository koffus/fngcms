<?php

//
// Configuration file for plugin
//

// Protect against hack attempts
if (!defined('NGCMS')) die ('HAL');

// Load lang files
Lang::load('users', 'admin');
Lang::loadPlugin('clear_config', 'config', 'с_с', ':');

$action = isset($_REQUEST['action']) ? $_REQUEST['action'] : false;
switch ($action) {
    case 'delete':
        delete();
    break;
    default:
        showlist();
}

function showlist() {
    global $twig;

    // Load CORE Plugin
    $cPlugin = CPlugin::instance();
    // Load list of active plugins
    $listActive = $cPlugin->getListActive();
    // Load of plugins configurations
    $pConfig = $cPlugin->getConfig();

    $ULIB = new UrlLibrary();
    $ULIB->loadConfig();
    $plug = array();
    $conf = array();
    if (isset($listActive['active']) and is_array($listActive['active'])) {
        foreach($listActive['active'] as $key => $row) {
            $plug[] = $key;
            $conf[$key][] = 'active'; } }
    if (isset($listActive['actions']) and is_array($listActive['actions'])) {
        foreach($listActive['actions'] as $key => $row) {
            if (!is_array($row)) continue;
            foreach($row as $kkey => $rrow) {
                if (!in_array($kkey, $plug)) $plug[] = $kkey;
                if (!in_array('actions', $conf[$kkey])) $conf[$kkey][] = 'actions'; } } }
    if (isset($listActive['installed']) and is_array($listActive['installed'])) {
        foreach($listActive['installed'] as $key => $row) {
            if (!in_array($key, $plug)) $plug[] = $key;
            $conf[$key][] = 'installed'; } }
    if (isset($listActive['libs']) and is_array($listActive['libs'])) {
        foreach($listActive['libs'] as $key => $row) {
            if (!in_array($key, $plug)) $plug[] = $key;
            $conf[$key][] = 'libs'; } }
    if (is_array($pConfig)) {
        foreach($pConfig as $key => $row) {
            if (!in_array($key, $plug)) $plug[] = $key;
            $conf[$key][] = 'config'; } }
    if (isset($ULIB->CMD) and is_array($ULIB->CMD)) {
        foreach($ULIB->CMD as $key => $row) {
            if ($key != 'core' and $key != 'static' and $key != 'search' and $key != 'news' and !in_array($key, $plug)) $plug[] = $key;
            $conf[$key][] = 'urlcmd'; } }
    $tpath = locatePluginTemplates(array('conf.list', 'conf.list.row'), 'clear_config');
    $output = '';
    sort($plug);
    foreach($plug as $key => $row) {
        $pvars = array();
        $pvars['id'] = $row;
        $pvars['conf'] = '';
        foreach($conf[$row] as $kkey => $rrow) {
            if ( $pvars['id'] == 'auth_basic' )
                continue;
            $pvars['conf'] .= 
            '<a href="#" title="'.__('с_с:'.$rrow). '"' .
            ' onclick="confirmIt(\'' . home . '/engine/admin.php?mod=extra-config&plugin=clear_config&action=delete&id='.$row.
            '&conf='.$rrow.'\', \''.sprintf(__('с_с:confirm'), __('с_с:'.$rrow), $row).'\');">'.
            '<img src="' . home . '/engine/plugins/clear_config/tpl/images/'.$rrow.'.png" /></a>&#160;';
        }
        $tvars['entries'][] = $pvars;
    }
    
    $xt = $twig->loadTemplate('plugins/clear_config/tpl/conf.list.tpl');
    echo $xt->render($tvars);
}

function delete() {
    global $PLUGINS;

    // Load CORE Plugin
    $cPlugin = CPlugin::instance();
    // Load list of active plugins
    $listActive = $cPlugin->getListActive();
    // Load of plugins configurations
    $pConfig = $cPlugin->getConfig();

    if (!isset($_REQUEST['id']) and !isset($_REQUEST['conf'])) {
        msg(array('type' => 'danger', 'message' => __('с_с:error')));
        showlist();
        return false;
    }
    $id = secure_html($_REQUEST['id']);
    $conf = secure_html($_REQUEST['conf']);
    switch ($conf){
        case 'active':
            if (isset($listActive['active'][$id])){
                unset($PLUGINS['active']['active'][$id]);
                msg(array('message' => sprintf(__('с_с:del_ok'), 'active', $id)));}
            else msg(array('type' => 'danger', 'message' => sprintf(__('с_с:del_er'), 'active', $id)));
            break;
        case 'actions':
            $if_delete = false;
            if (isset($listActive['actions']) and is_array($listActive['actions'])) {
                foreach($listActive['actions'] as $key => $row) {
                    if (isset($listActive['actions'][$key][$id])) {
                        unset($PLUGINS['active']['actions'][$key][$id]);
                        $if_delete = true;} } }
            if ($if_delete) msg(array('message' => sprintf(__('с_с:del_ok'), 'actions', $id)));
            else msg(array('type' => 'danger', 'message' => sprintf(__('с_с:del_er'), 'actions', $id)));
            break;
        case 'installed':
            if (isset($listActive['installed'][$id])) {
                unset($PLUGINS['active']['installed'][$id]);
                msg(array('message' => sprintf(__('с_с:del_ok'), 'installed', $id))); }
            else msg(array('type' => 'danger', 'message' => sprintf(__('с_с:del_er'), 'installed', $id)));
            break;
        case 'libs':
            if (isset($listActive['libs'][$id])) {
                unset($PLUGINS['active']['libs'][$id]);
                msg(array('message' => sprintf(__('с_с:del_ok'), 'libs', $id))); }
            else msg(array('type' => 'danger', 'message' => sprintf(__('с_с:del_er'), 'libs', $id)));
            break;
        case 'config':
            if (isset($pConfig[$id])) {
                unset($PLUGINS['config'][$id]);
                msg(array('message' => sprintf(__('с_с:del_ok'), 'config', $id))); }
            else msg(array('type' => 'danger', 'message' => sprintf(__('с_с:del_er'), 'config', $id)));
            break;
        case 'urlcmd':
            $ULIB = new UrlLibrary();
            $ULIB->loadConfig();
            if (isset($ULIB->CMD[$id])) {
                unset($ULIB->CMD[$id]);
                msg(array('message' => sprintf(__('с_с:del_ok'), 'urlcmd', $id))); }
            else msg(array('type' => 'danger', 'message' => sprintf(__('с_с:del_er'), 'urlcmd', $id)));
            $ULIB->saveConfig();
            break;
    }
    $cPlugin->saveListActive();
    // Save configuration parameters of plugins
    $cPlugin->saveConfig();
    showlist();
}