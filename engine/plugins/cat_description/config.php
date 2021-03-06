<?php

/*
 * Configuration file for plugin
 */

// Protect against hack attempts
if (!defined('BBCMS')) die ('HAL');

// Load lang files
Lang::loadPlugin($plugin, 'admin', '', ':');

// Prepare configuration parameters
switch ($action) {
    case 'edit': editform(); break;
    case 'confirm': confirm(); showlist(); break;
    case 'delete': delete(); showlist(); break;
    default: showlist();
}

function showlist()
{
    global $mysql, $tpl;
    $tpath = plugin_locateTemplates('cat_description', array('conf.main', 'conf.button', 'conf.list', 'conf.list.row', 'conf.add_edit.form'));
    
    $is_null = false;
    foreach ($mysql->select('select '.prefix.'_cat_description.id, '.prefix.'_cat_description.catid, '.prefix.'_cat_description.is_on, '.prefix.'_category.name from '.prefix.'_cat_description left join '.prefix.'_category on '.prefix.'_cat_description.catid='.prefix.'_category.id order by `catid`') as $row)
    {
        $pvars['vars'] = array (
            'id' => $row['id'],
            'catid' => $row['catid'],
            'is_on' => $row['is_on']?__('cat_description:is_on_on'):__('cat_description:is_on_off'),
            'name' => $row['name']?$row['name']:__('cat_description:main')
            );
        
        $pvars['vars']['tpl_url'] = $tpath['url:conf.list.row'];
        $tpl->template('conf.list.row', $tpath['conf.list.row']);
        $tpl->vars('conf.list.row', $pvars);
        $output .= $tpl->show('conf.list.row');
        if (!$row['catid']) $is_null = true;
    }

    $category_list = array();
    if (!$is_null) $category_list[0] = __('cat_description:main');
    foreach ($mysql->select('select '.prefix.'_category.id, '.prefix.'_category.name from '.prefix.'_category left join '.prefix.'_cat_description on '.prefix.'_category.id = '.prefix.'_cat_description.catid where '.prefix.'_cat_description.catid is null order by `id`') as $row) $category_list[$row['id']] = $row['name']; 

    $ttvars['regx']['/\[add\](.*?)\[\/add\]/si'] = '$1';
    $ttvars['regx']['/\[edit\](.*?)\[\/edit\]/si'] = '';
    $ttvars['vars']['is_on_list'] = MakeDropDown(array(0 => __('noa'), 1 => __('yesa')), 'is_on', $selected = 1);
    $ttvars['vars']['category_list'] = MakeDropDown($category_list, 'catid', $selected = 0);

    $tpl->template('conf.add_edit.form', $tpath['conf.add_edit.form']);
    $tpl->vars('conf.add_edit.form', $ttvars);
    $tvars['vars']['add_edit_form'] = $tpl->show('conf.add_edit.form');
    
    unset($ttvars);
    $ttvars['vars']['entries'] = isset($output)?$output:'';
    
    $tpl->template('conf.list', $tpath['conf.list']);
    $tpl->vars('conf.list', $ttvars);
    $tvars['vars']['list'] = $tpl->show('conf.list');
    
    $tpl->template('conf.button', $tpath['conf.button']);
    $tpl->vars('conf.button', array());
    $tvars['vars']['button'] = $tpl->show('conf.button');

    $tpl->template('conf.main', $tpath['conf.main']);
    $tpl->vars('conf.main', $tvars);
    print $tpl->show('conf.main');
}

function editform()
{
    global $mysql, $tpl;

    $id = intval($_REQUEST['id']);

    $tpath = plugin_locateTemplates('cat_description', array('conf.main', 'conf.add_edit.form'));
    
    $qwer = $mysql->select('select `id`, `catid`, `is_on`, `description` from '.prefix.'_cat_description where `id`='.db_squote($id).' limit 1');
    if (!$qwer) return; 

    $category_list = array();
    if ($qwer[0]['catid'] == 0 or !$mysql->select('select `id` from '.prefix.'_cat_description where `catid`=\'0\' limit 1'))
        $category_list[0] = __('cat_description:main');
    foreach ($mysql->select('select '.prefix.'_category.id, '.prefix.'_category.name from '.prefix.'_category left join '.prefix.'_cat_description on '.prefix.'_category.id ='.prefix.'_cat_description.catid where '.prefix.'_cat_description.catid is null or '.prefix.'_cat_description.id='.db_squote($id).' order by `id`') as $row) $category_list[$row['id']] = $row['name'];
    
    foreach ($qwer as $row)
    {
        $ttvars['vars']['id'] = $row['id'];
        $ttvars['vars']['is_on_list'] = MakeDropDown(array(0 => __('noa'), 1 => __('yesa')), 'is_on', $selected = $row['is_on']);
        $ttvars['vars']['category_list'] = MakeDropDown($category_list, 'catid', $selected = $row['catid']);
        $ttvars['vars']['description'] = $row['description'];
    }

    $ttvars['regx']['/\[add\](.*?)\[\/add\]/si'] = '';
    $ttvars['regx']['/\[edit\](.*?)\[\/edit\]/si'] = '$1';

    $tpl->template('conf.add_edit.form', $tpath['conf.add_edit.form']);
    $tpl->vars('conf.add_edit.form', $ttvars);
    $tvars['vars']['add_edit_form'] = $tpl->show('conf.add_edit.form');

    $tvars['vars']['button'] = '';
    $tvars['vars']['list'] = '';
    
    $tpl->template('conf.main', $tpath['conf.main']);
    $tpl->vars('conf.main', $tvars);
    print $tpl->show('conf.main');
}

function confirm() {
    global $mysql, $parse;

    $id = intval($_REQUEST['id']);
    $catid = intval($_REQUEST['catid']);
    $is_on = intval($_REQUEST['is_on']);
    $description = $_REQUEST['description'];

    if ($mysql->select('select `id` from '.prefix.'_cat_description where `id`='.db_squote($id))) {
        $mysql->query('update '.prefix.'_cat_description set '.
            '`catid`='.db_squote($catid).', '.
            '`is_on`='.db_squote($is_on).', '.
            '`description`='.db_squote($description).' '.
            'where `id` = '.db_squote($id).' limit 1');
        msg(array('type' => 'info', 'message' => __('cat_description:edit_info')));
        clearCacheFiles($plugin);
    } else if (!$mysql->select('select `id` from '.prefix.'_cat_description where `catid`='.db_squote($catid))) {
        $mysql->query('insert '.prefix.'_cat_description '.
            '(`catid`, `is_on`, `description`) values '.
            '('.db_squote($catid).', '.db_squote($is_on).', '.db_squote($description).')');
        msg(array('type' => 'info', 'message' => __('cat_description:add_info')));
        clearCacheFiles($plugin);
    } else {
        msg(array('type' => 'info', 'message' => __('cat_description:info_error_add_edit')));
    }
}

function delete()
{
    global $mysql;

    $id = intval($_REQUEST['id']);
    $mysql->query('delete from '.prefix.'_cat_description where `id`='.db_squote($id));
    msg(array('type' => 'info', 'message' => sprintf(__('cat_description:info_delete') ,$id)));
    clearCacheFiles($plugin);
}
