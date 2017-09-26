<?php

/*
 * Configuration file for plugin
 */

// Protect against hack attempts
if (!defined('BBCMS')) die ('HAL');

// Load lang files
Lang::loadPlugin($plugin, 'admin', '', ':');

switch ($action) {
    case 'list_user': show_list_user(); break;
    case 'list_category': show_list_category(); break;
    case 'add_user': add_user(); break;
    case 'add_category': add_category(); break;
    case 'move_up': move('up'); showlist(); break;
    case 'move_down': move('down'); showlist(); break;
    case 'dell_user': delete_user(); break;
    case 'dell_category': delete_category(); break;
    case 'general_submit': general_submit(); main(); break;
    default: main();
}

function validate($string){
    $chars = 'abcdefghijklmnopqrstuvwxyz_.0123456789';
    if ($string == '') return true;
    foreach(str_split($string) as $char)
        if (stripos($chars, $char) === false)
            return false;
    return true;
}

function main(){
    global $tpl;
    $tpath = plugin_locateTemplates('category_access', array('conf.main', 'conf.general.form'));
    
    $guest = pluginGetVariable('category_access', 'guest');
    $coment = pluginGetVariable('category_access', 'coment');
    $journ = pluginGetVariable('category_access', 'journ');
    $moder = pluginGetVariable('category_access', 'moder');
    $admin = pluginGetVariable('category_access', 'admin');
    $message = pluginGetVariable('category_access', 'message');
    
    $ttvars['vars']['guest_list'] = MakeDropDown(array(0 => __('category_access:label_close'), 1 => __('category_access:label_protect'), 2 => __('category_access:label_open')), 'guest', $guest);
    $ttvars['vars']['coment_list'] = MakeDropDown(array(0 => __('category_access:label_close'), 1 => __('category_access:label_protect'), 2 => __('category_access:label_open')), 'coment', $coment);
    $ttvars['vars']['journ_list'] = MakeDropDown(array(0 => __('category_access:label_close'), 1 => __('category_access:label_protect'), 2 => __('category_access:label_open')), 'journ', $journ);
    $ttvars['vars']['moder_list'] = MakeDropDown(array(0 => __('category_access:label_close'), 1 => __('category_access:label_protect'), 2 => __('category_access:label_open')), 'moder', $moder);
    $ttvars['vars']['admin_list'] = MakeDropDown(array(0 => __('category_access:label_close'), 1 => __('category_access:label_protect'), 2 => __('category_access:label_open')), 'admin', $admin);
    $ttvars['vars']['message'] = $message;
    
    $ttvars['vars']['action'] = __('category_access:button_general');
    $tpl->template('conf.general.form', $tpath['conf.general.form']);
    $tpl->vars('conf.general.form', $ttvars);
    $tvars['vars']['entries'] = $tpl->show('conf.general.form');
    $tvars['vars']['action'] = __('category_access:button_general');
    $tpl->template('conf.main', $tpath['conf.main']);
    $tpl->vars('conf.main', $tvars);
    print $tpl->show('conf.main');
}

function show_list_user(){
    global $tpl, $catz, $catmap;
    $tpath = plugin_locateTemplates('category_access', array('conf.main', 'conf.list.user', 'conf.list.user.row'));
    $users = pluginGetVariable('category_access', 'users');
    $output = '';
    foreach ($users as $user=>$category) {
        $pvars['vars']['user'] = $user;
        $pvars['vars']['category'] = $catz[$catmap[$category]]['name'];
        $tpl->template('conf.list.user.row', $tpath['conf.list.user.row']);
        $tpl->vars('conf.list.user.row', $pvars);
        $output .= $tpl->show('conf.list.user.row');
    }
    $ttvars['vars']['entries'] = $output;
    $tpl->template('conf.list.user', $tpath['conf.list.user']);
    $tpl->vars('conf.list.user', $ttvars);
    $tvars['vars']['entries'] = $tpl->show('conf.list.user');
    $tvars['vars']['action'] = __('category_access:button_list_user');
    $tpl->template('conf.main', $tpath['conf.main']);
    $tpl->vars('conf.main', $tvars);
    print $tpl->show('conf.main');
}

function show_list_category(){
    global $tpl, $catz, $catmap;
    $tpath = plugin_locateTemplates('category_access', array('conf.main', 'conf.list', 'conf.list.row'));
    $categorys = pluginGetVariable('category_access', 'categorys');
    $output = '';
    foreach ($categorys as $cat) {
        $pvars['vars']['category'] = $cat;
        $pvars['vars']['category_name'] = $catz[$catmap[$cat]]['name'];
        $tpl->template('conf.list.row', $tpath['conf.list.row']);
        $tpl->vars('conf.list.row', $pvars);
        $output .= $tpl->show('conf.list.row');
    }
    $ttvars['vars']['entries'] = $output;
    $tpl->template('conf.list', $tpath['conf.list']);
    $tpl->vars('conf.list', $ttvars);
    $tvars['vars']['entries'] = $tpl->show('conf.list');
    $tvars['vars']['action'] = __('category_access:button_list_category');
    $tpl->template('conf.main', $tpath['conf.main']);
    $tpl->vars('conf.main', $tvars);
    print $tpl->show('conf.main');
}


function general_submit(){

    // Load CORE Plugin
    $cPlugin = CPlugin::instance();

    $guest = isset($_POST['guest'])?intval($_POST['guest']):0;
    $coment = isset($_POST['coment'])?intval($_POST['coment']):0;
    $journ = isset($_POST['journ'])?intval($_POST['journ']):0;
    $moder = isset($_POST['moder'])?intval($_POST['moder']):0;
    $admin = isset($_POST['admin'])?intval($_POST['admin']):0;
    $message = isset($_POST['message'])?$_POST['message']:'';
    if (!$if_error){
        pluginSetVariable('category_access', 'guest', $guest);
        pluginSetVariable('category_access', 'coment', $coment);
        pluginSetVariable('category_access', 'journ', $journ);
        pluginSetVariable('category_access', 'moder', $moder);
        pluginSetVariable('category_access', 'admin', $admin);
        pluginSetVariable('category_access', 'message', $message);

        // Save configuration parameters of plugins
        if ($cPlugin->saveConfig()) {
            msg(array('type' => 'info', 'message' => __('category_access:info_save_general')));
        } else {
            msg(array('type' => 'danger', 'message' => __('commited_fail')));
        }
    }
}

function add_user(){
    global $tpl, $catz, $catmap, $mysql;

    // Load CORE Plugin
    $cPlugin = CPlugin::instance();
    $users = pluginGetVariable('category_access', 'users');
    $if_add = true;
    $user = '';
    $category = 0;
    if (isset($_GET['user'])){
        if (!array_key_exists($_GET['user'], $users)){
            msg(array('type' => 'danger', 'title' => __('category_access:error_val_title'), 'message' => __('category_access:error_not_exists')));
            show_list_user();
            return;
        }
        $user = $_GET['user'];
        $category = $users[$user];
        $if_add = false;
    }
    if (isset($_POST['user']) and isset($_POST['category']))
    {
        $user = $_POST['user'];
        $category = $_POST['category'];
        $if_error = false;
        if (!$user or !validate($user)){
            msg(array('type' => 'danger', 'title' => __('category_access:error_val_title'), 'message' => sprintf(__('category_access:error_validate'), __('category_access:label_user_name'))));
            $if_error = true;
        }
        if (!array_key_exists($category, $catmap)){
            msg(array('type' => 'danger', 'title' => __('category_access:error_val_title'), 'message' => __('category_access:error_category')));
            $if_error = true;
        }
        if ($if_add and array_key_exists($user, $users)){
            msg(array('type' => 'danger', 'title' => __('category_access:error_val_title'), 'message' => sprintf(__('category_access:error_exists'), $user)));
            $if_error = true;
        }
        if (!$if_error){
            $users[$user] = $category;
            pluginSetVariable('category_access', 'users', $users);
            // Save configuration parameters of plugins
            if($cPlugin->saveConfig()) {
                msg(array('type' => 'info', 'message' => __('category_access:info_save_general')));
            } else {
                msg(array('type' => 'danger', 'message' => __('commited_fail')));
            }
            show_list_user();
            return;
        }
    }
    $category_list = array();
    foreach($catmap as $key=>$val){
        if(array_key_exists($key, $category) and ($if_add or $key != $cat)) continue;
        $category_list[$key] = $catz[$val]['name'];
    }
    $user_list = array();
    foreach ($mysql->select('select '.prefix.'_users.name from '.prefix.'_users order by '.prefix.'_users.name asc') as $row)
    {
        if(array_key_exists($row['name'], $users) and ($if_add or $row['name'] != $user)) continue;
        $user_list[$row['name']] = $row['name'];
    }
    
    $tpath = plugin_locateTemplates('category_access', array('conf.main', 'conf.add_edit_user.form'));
    $ttvars['vars']['user'] = $user;
    $ttvars['vars']['user_list'] = MakeDropDown($user_list, 'user', $user);
    $ttvars['vars']['category_list'] = MakeDropDown($category_list, 'category', $category);
    $ttvars['regx']['/\[add\](.*?)\[\/add\]/si'] = $if_add?'$1':'';
    $ttvars['regx']['/\[edit\](.*?)\[\/edit\]/si'] = $if_add?'':'$1';
    $tpl->template('conf.add_edit_user.form', $tpath['conf.add_edit_user.form']);
    $tpl->vars('conf.add_edit_user.form', $ttvars);
    $tvars['vars']['entries'] = $tpl->show('conf.add_edit_user.form');
    $tvars['vars']['action'] = $if_add?__('category_access:button_add_user'):__('category_access:button_edit_user');
    $tpl->template('conf.main', $tpath['conf.main']);
    $tpl->vars('conf.main', $tvars);
    print $tpl->show('conf.main');
}

function add_category()
{
    global $tpl, $catz, $catmap;

    // Load CORE Plugin
    $cPlugin = CPlugin::instance();
    $categorys = pluginGetVariable('category_access', 'categorys');

    if (isset($_POST['category']) and is_array($_POST['category']))
    {
        foreach ($_POST['category'] as $category)
        {
            if (!array_key_exists($category, $catmap)){
                msg(array('type' => 'danger', 'title' => __('category_access:error_val_title'), 'message' => sprintf(__('category_access:error_category_not_add'), $category)));
                continue;
            }
            if (in_array($category, $categorys)){
                msg(array('type' => 'danger', 'title' => __('category_access:error_val_title'), 'message' => sprintf(__('category_access:error_category_not_add'), $catz[$catmap[$category]]['name'])));
                continue;
            }
            $categorys[] = $category;
        }
        pluginSetVariable('category_access', 'categorys', $categorys);
       // Save configuration parameters of plugins
        if($cPlugin->saveConfig()) {
            msg(array('type' => 'info', 'message' => __('category_access:info_save_general')));
        } else {
            msg(array('type' => 'danger', 'message' => __('commited_fail')));
        }
        show_list_category();
        return;
    }

    $tpath = plugin_locateTemplates('category_access', array('conf.main', 'conf.add_edit.category', 'conf.add_edit.category.row'));
    
    $entries = '';
    foreach($catmap as $key=>$val){
        if(in_array($key, $categorys)) continue;
        $pvars['vars']['category'] = $key;
        $pvars['vars']['category_name'] = $catz[$val]['name'];
        $tpl->template('conf.add_edit.category.row', $tpath['conf.add_edit.category.row']);
        $tpl->vars('conf.add_edit.category.row', $pvars);
        $entries .= $tpl->show('conf.add_edit.category.row');
    }
    
    $ttvars['vars']['entries'] = $entries;
    $tpl->template('conf.add_edit.category', $tpath['conf.add_edit.category']);
    $tpl->vars('conf.add_edit.category', $ttvars);
    $tvars['vars']['entries'] = $tpl->show('conf.add_edit.category');
    $tvars['vars']['action'] = __('category_access:button_add_category');
    $tpl->template('conf.main', $tpath['conf.main']);
    $tpl->vars('conf.main', $tvars);
    print $tpl->show('conf.main');
}

function delete_user()
{
    global $tpl;

    // Load CORE Plugin
    $cPlugin = CPlugin::instance();
    $users = pluginGetVariable('category_access', 'users');

    if (!isset($_REQUEST['user']) or !array_key_exists($_REQUEST['user'], $users)) {
        msg(array('type' => 'danger', 'title' => __('category_access:error_val_title'), 'message' => __('category_access:error_not_exists_user')));
        show_list_user();
        return;
    }
    $user = $_REQUEST['user'];
    if (isset($_POST['commit'])) {
        if ($_POST['commit'] == 'yes'){
            unset($users[$user]);
            pluginSetVariable('category_access', 'users', $users);
            // Save configuration parameters of plugins
            if($cPlugin->saveConfig()) {
                msg(array('type' => 'info', 'message' => __('category_access:info_save_general')));
            } else {
                msg(array('type' => 'danger', 'message' => __('commited_fail')));
            }
        }
        show_list_user();
        return;
    }
    
    $tpath = plugin_locateTemplates('category_access', array('conf.main', 'conf.commit.user'));
    $tvars['vars']['user'] = $user;
    $tvars['vars']['commit'] = sprintf(__('category_access:desc_commit_user'), $user);
    $tpl->template('conf.commit.user', $tpath['conf.commit.user']);
    $tpl->vars('conf.commit.user', $tvars);
    $tvars['vars']['entries'] = $tpl->show('conf.commit.user');
    $tvars['vars']['action'] = __('category_access:title_commit');
    $tpl->template('conf.main', $tpath['conf.main']);
    $tpl->vars('conf.main', $tvars);
    print $tpl->show('conf.main');
}

function delete_category()
{
    global $tpl, $catz, $catmap;

    // Load CORE Plugin
    $cPlugin = CPlugin::instance();
    $categorys = pluginGetVariable('category_access', 'categorys');

    if (!isset($_REQUEST['category']) or !in_array($_REQUEST['category'], $categorys)) {
        msg(array('type' => 'danger', 'title' => __('category_access:error_val_title'), 'message' => __('category_access:error_not_exists')));
        show_list_category();
        return;
    }
    $category = $_REQUEST['category'];
    if (isset($_POST['commit'])) {
        if ($_POST['commit'] == 'yes') {
            unset($categorys[array_search($category, $categorys)]);
            pluginSetVariable('category_access', 'categorys', $categorys);
            // Save configuration parameters of plugins
            if($cPlugin->saveConfig()) {
                msg(array('type' => 'info', 'message' => __('category_access:info_save_general')));
            } else {
                msg(array('type' => 'danger', 'message' => __('commited_fail')));
            }
        }
        show_list_category();
        return;
    }
    
    $tpath = plugin_locateTemplates('category_access', array('conf.main', 'conf.commit.form'));
    $tvars['vars']['category'] = $category;
    $tvars['vars']['commit'] = sprintf(__('category_access:desc_commit_category'), $catz[$catmap[$category]]['name']);
    $tpl->template('conf.commit.form', $tpath['conf.commit.form']);
    $tpl->vars('conf.commit.form', $tvars);
    $tvars['vars']['entries'] = $tpl->show('conf.commit.form');
    $tvars['vars']['action'] = __('category_access:title_commit');
    $tpl->template('conf.main', $tpath['conf.main']);
    $tpl->vars('conf.main', $tvars);
    print $tpl->show('conf.main');
}