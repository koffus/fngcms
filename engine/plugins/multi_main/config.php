<?php

//
// Configuration file for plugin
//

// Protect against hack attempts
if (!defined('NGCMS')) die ('HAL');

// Load lang files
Lang::loadPlugin($plugin, 'config', '', ':');

// Prepare configuration parameters
switch ($action) {
	case 'list_menu': showlist(); break;
	case 'add_form': add(); break;
	case 'move_up': move('up'); showlist(); break;
	case 'move_down': move('down'); showlist(); break;
	case 'dell': delete(); break;
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

function general_submit(){

	$if_error = false;
	if (!validate($_POST['main'])) {
		msg(array('type' => 'danger', 'title' => __('multi_main:error_val_title'), 'message' => sprintf(__('multi_main:error_validate'), __('multi_main:label_main'))));
		$if_error = true;
	}
	if (!validate($_POST['guest'])) {
		msg(array('type' => 'danger', 'title' => __('multi_main:error_val_title'), 'message' => sprintf(__('multi_main:error_validate'), __('multi_main:label_guest'))));
		$if_error = true;
	}
	if (!validate($_POST['coment'])) {
		msg(array('type' => 'danger', 'title' => __('multi_main:error_val_title'), 'message' => sprintf(__('multi_main:error_validate'), __('multi_main:label_coment'))));
		$if_error = true;
	}
	if (!validate($_POST['journ'])) {
		msg(array('type' => 'danger', 'title' => __('multi_main:error_val_title'), 'message' => sprintf(__('multi_main:error_validate'), __('multi_main:label_journ'))));
		$if_error = true;
	}
	if (!validate($_POST['moder'])) {
		msg(array('type' => 'danger', 'title' => __('multi_main:error_val_title'), 'message' => sprintf(__('multi_main:error_validate'), __('multi_main:label_moder'))));
		$if_error = true;
	}
	if (!validate($_POST['admin'])) {
		msg(array('type' => 'danger', 'title' => __('multi_main:error_val_title'), 'message' => sprintf(__('multi_main:error_validate'), __('multi_main:label_admin'))));
		$if_error = true;
	}
	if (!$if_error){
		pluginSetVariable('multi_main', 'main', $_POST['main']);
		pluginSetVariable('multi_main', 'guest', $_POST['guest']);
		pluginSetVariable('multi_main', 'coment', $_POST['coment']);
		pluginSetVariable('multi_main', 'journ', $_POST['journ']);
		pluginSetVariable('multi_main', 'moder', $_POST['moder']);
		pluginSetVariable('multi_main', 'admin', $_POST['admin']);
        // Load CORE Plugin
        $cPlugin = CPlugin::instance();
        // Save configuration parameters of plugins
        if($cPlugin->saveConfig()) {
            msg(array('message' => __('commited')));
        } else {
            msg(array('type' => 'danger', 'message' => __('commited_fail')));
        }
	}
}

function main(){
	global $tpl;
	$tpath = locatePluginTemplates(array('conf.main', 'conf.general.form'), 'multi_main', 1);
	$ttvars['vars']['main'] = isset($_POST['main'])?$_POST['main']:pluginGetVariable('multi_main', 'main');
	$ttvars['vars']['guest'] = isset($_POST['guest'])?$_POST['guest']:pluginGetVariable('multi_main', 'guest');
	$ttvars['vars']['coment'] = isset($_POST['coment'])?$_POST['coment']:pluginGetVariable('multi_main', 'coment');
	$ttvars['vars']['journ'] = isset($_POST['journ'])?$_POST['journ']:pluginGetVariable('multi_main', 'journ');
	$ttvars['vars']['moder'] = isset($_POST['moder'])?$_POST['moder']:pluginGetVariable('multi_main', 'moder');
	$ttvars['vars']['admin'] = isset($_POST['admin'])?$_POST['admin']:pluginGetVariable('multi_main', 'admin');
	$ttvars['vars']['action'] = __('multi_main:button_general');
	$tpl->template('conf.general.form', $tpath['conf.general.form']);
	$tpl->vars('conf.general.form', $ttvars);
	$tvars['vars']['entries'] = $tpl->show('conf.general.form');
	$tvars['vars']['action'] = __('multi_main:button_general');
	$tpl->template('conf.main', $tpath['conf.main']);
	$tpl->vars('conf.main', $tvars);
	print $tpl->show('conf.main');
}

function showlist(){
	global $tpl, $catz;
	$tpath = locatePluginTemplates(array('conf.main', 'conf.list', 'conf.list.row'), 'multi_main', 1);
	$category = pluginGetVariable('multi_main', 'category');
	$output = '';
	foreach ($category as $cat=>$tpll) {
		$pvars['vars']['cat'] = $cat;
		$pvars['vars']['cat_name'] = $catz[$cat]['name'];
		$pvars['vars']['tpl'] = $tpll;
		$tpl->template('conf.list.row', $tpath['conf.list.row']);
		$tpl->vars('conf.list.row', $pvars);
		$output .= $tpl->show('conf.list.row');
	}
	$ttvars['vars']['entries'] = $output;
	$tpl->template('conf.list', $tpath['conf.list']);
	$tpl->vars('conf.list', $ttvars);
	$tvars['vars']['entries'] = $tpl->show('conf.list');
	$tvars['vars']['action'] = __('multi_main:button_list');
	$tpl->template('conf.main', $tpath['conf.main']);
	$tpl->vars('conf.main', $tvars);
	print $tpl->show('conf.main');
}

function add(){
	global $tpl, $catz;
	$category = pluginGetVariable('multi_main', 'category');
	$if_add = true;
	$cat = '';
	$tpll = '';
	if (isset($_GET['cat'])){
		if (!array_key_exists($_GET['cat'], $category)){
			msg(array('type' => 'danger', 'title' => __('multi_main:error_val_title'), 'message' => __('multi_main:error_not_exists')));
			showlist();
			return;
		}
		$cat = $_GET['cat'];
		$tpll = $category[$cat];
		$if_add = false;
	}
	if (isset($_POST['cat']) and isset($_POST['tpl'])) {
		$cat = $_POST['cat'];
		$tpll = $_POST['tpl'];
		$if_error = false;
		if (!$cat or !validate($cat)){
			msg(array('type' => 'danger', 'title' => __('multi_main:error_val_title'), 'message' => sprintf(__('multi_main:error_validate'), __('multi_main:label_cat'))));
			$if_error = true;
		}
		if (!$tpll or !validate($tpll)){
			msg(array('type' => 'danger', 'title' => __('multi_main:error_val_title'), 'message' => sprintf(__('multi_main:error_validate'), __('multi_main:label_tpl'))));
			$if_error = true;
		}
		if ($if_add and array_key_exists($cat, $category)){
			msg(array('type' => 'danger', 'title' => __('multi_main:error_val_title'), 'message' => sprintf(__('multi_main:error_exists'), $catz[$cat]['name'])));
			$if_error = true;
		}
		if (!$if_error){
			$category[$cat] = $tpll;
			pluginSetVariable('multi_main', 'category', $category);
            // Load CORE Plugin
            $cPlugin = CPlugin::instance();
            // Save configuration parameters of plugins
            if($cPlugin->saveConfig()) {
                msg(array('message' => __('commited')));
            } else {
                msg(array('type' => 'danger', 'message' => __('commited_fail')));
            }
			showlist();
			return;
		}
	}
	$cat_list = array();
	foreach($catz as $key=>$val){
		if(array_key_exists($key, $category) and ($if_add or $key != $cat)) continue;
		$cat_list[$key] = $val['name'];
	}
	
	$tpath = locatePluginTemplates(array('conf.main', 'conf.add_edit.form'), 'multi_main', 1);
	$ttvars['vars']['cat'] = $cat;
	$ttvars['vars']['cat_list'] = MakeDropDown($cat_list, 'cat', $cat);
	$ttvars['vars']['tpl'] = $tpll;		
	$ttvars['regx']['/\[add\](.*?)\[\/add\]/si'] = $if_add?'$1':'';
	$ttvars['regx']['/\[edit\](.*?)\[\/edit\]/si'] = $if_add?'':'$1';
	$tpl->template('conf.add_edit.form', $tpath['conf.add_edit.form']);
	$tpl->vars('conf.add_edit.form', $ttvars);
	$tvars['vars']['entries'] = $tpl->show('conf.add_edit.form');
	$tvars['vars']['action'] = $if_add?__('multi_main:button_add_submit'):__('multi_main:button_edit_submit');
	$tpl->template('conf.main', $tpath['conf.main']);
	$tpl->vars('conf.main', $tvars);
	print $tpl->show('conf.main');
}

function delete() {
	global $tpl, $catz;
	$category = pluginGetVariable('multi_main', 'category');

	if (!isset($_REQUEST['cat']) or !array_key_exists($_REQUEST['cat'], $category)) {
		msg(array('type' => 'danger', 'title' => __('multi_main:error_val_title'), 'message' => __('multi_main:error_not_exists')));
		showlist();
		return;
	}
	$cat = $_REQUEST['cat'];
	if (isset($_POST['commit'])) {
		if ($_POST['commit'] == 'yes'){
			unset($category[$cat]);
			pluginSetVariable('multi_main', 'category', $category);
			 // Load CORE Plugin
            $cPlugin = CPlugin::instance();
            // Save configuration parameters of plugins
            if($cPlugin->saveConfig()) {
                msg(array('message' => __('commited')));
            } else {
                msg(array('type' => 'danger', 'message' => __('commited_fail')));
            }
		}
		showlist();
		return;
	}
	
	$tpath = locatePluginTemplates(array('conf.main', 'conf.commit.form'), 'multi_main', 1);
	$tvars['vars']['cat'] = $cat;
	$tvars['vars']['commit'] = sprintf(__('multi_main:desc_commit'), $catz[$cat]['name']);
	$tpl->template('conf.commit.form', $tpath['conf.commit.form']);
	$tpl->vars('conf.commit.form', $tvars);
	$tvars['vars']['entries'] = $tpl->show('conf.commit.form');
	$tvars['vars']['action'] = __('multi_main:title_commit');
	$tpl->template('conf.main', $tpath['conf.main']);
	$tpl->vars('conf.main', $tvars);
	print $tpl->show('conf.main');
}