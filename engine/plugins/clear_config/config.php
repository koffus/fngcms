<?php

//
// Configuration file for plugin
//

// Protect against hack attempts
if (!defined('NGCMS')) die ('HAL');

// Load lang files
Lang::load('users', 'admin');
Lang::loadPlugin('clear_config', 'config', '', 'с_с', ':');

switch ($_REQUEST['action']) {
	case 'delete': delete(); break;
	default: showlist();
}

function showlist() {
	global $twig, $PLUGINS;

	
	$ULIB = new urlLibrary();
	$ULIB->loadConfig();
	$plug = array();
	$conf = array();
	if (isset($PLUGINS['active']['active']) and is_array($PLUGINS['active']['active'])) {
		foreach($PLUGINS['active']['active'] as $key => $row) {
			$plug[] = $key;
			$conf[$key][] = 'active'; } }
	if (isset($PLUGINS['active']['actions']) and is_array($PLUGINS['active']['actions'])) {
		foreach($PLUGINS['active']['actions'] as $key => $row) {
			if (!is_array($row)) continue;
			foreach($row as $kkey => $rrow) {
				if (!in_array($kkey, $plug)) $plug[] = $kkey;
				if (!in_array('actions', $conf[$kkey])) $conf[$kkey][] = 'actions'; } } }
	if (isset($PLUGINS['active']['installed']) and is_array($PLUGINS['active']['installed'])) {
		foreach($PLUGINS['active']['installed'] as $key => $row) {
			if (!in_array($key, $plug)) $plug[] = $key;
			$conf[$key][] = 'installed'; } }
	if (isset($PLUGINS['active']['libs']) and is_array($PLUGINS['active']['libs'])) {
		foreach($PLUGINS['active']['libs'] as $key => $row) {
			if (!in_array($key, $plug)) $plug[] = $key;
			$conf[$key][] = 'libs'; } }
	if (isset($PLUGINS['config']) and is_array($PLUGINS['config'])) {
		foreach($PLUGINS['config'] as $key => $row) {
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
	if (!isset($_REQUEST['id']) or !isset($_REQUEST['conf'])) {
		msg(array('type' => 'danger', 'message' => __('с_с:error')));
		showlist();	return false; }
	$id = secure_html(convert($_REQUEST['id']));
	$conf = secure_html(convert($_REQUEST['conf']));
	switch ($conf){
		case 'active':
			if (isset($PLUGINS['active']['active'][$id])){
				unset($PLUGINS['active']['active'][$id]);
				msg(array('message' => sprintf(__('с_с:del_ok'), 'active', $id)));}
			else msg(array('type' => 'danger', 'message' => sprintf(__('с_с:del_er'), 'active', $id)));
			break;
		case 'actions':
			$if_delete = false;
			if (isset($PLUGINS['active']['actions']) and is_array($PLUGINS['active']['actions'])) {
				foreach($PLUGINS['active']['actions'] as $key => $row) {
					if (isset($PLUGINS['active']['actions'][$key][$id])) {
						unset($PLUGINS['active']['actions'][$key][$id]);
						$if_delete = true;} } }
			if ($if_delete) msg(array('message' => sprintf(__('с_с:del_ok'), 'actions', $id)));
			else msg(array('type' => 'danger', 'message' => sprintf(__('с_с:del_er'), 'actions', $id)));
			break;
		case 'installed':
			if (isset($PLUGINS['active']['installed'][$id])) {
				unset($PLUGINS['active']['installed'][$id]);
				msg(array('message' => sprintf(__('с_с:del_ok'), 'installed', $id))); }
			else msg(array('type' => 'danger', 'message' => sprintf(__('с_с:del_er'), 'installed', $id)));
			break;
		case 'libs':
			if (isset($PLUGINS['active']['libs'][$id])) {
				unset($PLUGINS['active']['libs'][$id]);
				msg(array('message' => sprintf(__('с_с:del_ok'), 'libs', $id))); }
			else msg(array('type' => 'danger', 'message' => sprintf(__('с_с:del_er'), 'libs', $id)));
			break;
		case 'config':
			if (isset($PLUGINS['config'][$id])) {
				unset($PLUGINS['config'][$id]);
				msg(array('message' => sprintf(__('с_с:del_ok'), 'config', $id))); }
			else msg(array('type' => 'danger', 'message' => sprintf(__('с_с:del_er'), 'config', $id)));
			break;
		case 'urlcmd':
			$ULIB = new urlLibrary();
			$ULIB->loadConfig();
			if (isset($ULIB->CMD[$id])) {
				unset($ULIB->CMD[$id]);
				msg(array('message' => sprintf(__('с_с:del_ok'), 'urlcmd', $id))); }
			else msg(array('type' => 'danger', 'message' => sprintf(__('с_с:del_er'), 'urlcmd', $id)));
			$ULIB->saveConfig();
			break;
	}
	pluginsSaveConfig();
	savePluginsActiveList();
	showlist();
}