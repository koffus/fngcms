<?php

// Protect against hack attempts
if (!defined('NGCMS')) die ('HAL');

//
// Configuration file for plugin
//

pluginsLoadConfig();
Lang::loadPlugin($plugin, 'main', '', 'similar', ':');
include_once('inc/similar.php');

$cfg = array();
array_push($cfg, array('name' => 'rebuild', 'title' => __('similar:rebuild'), 'descr' => __('similar:rebuild_desc'), 'type' => 'select', 'value' => 0, 'values' => array ( 0 => __('noa'), 1 => __('yesa')), 'nosave' => 1));

$cfgX = array();
array_push($cfgX, array('name' => 'localsource', 'title' => __('similar:localsource'), 'descr' => __('similar:localsource'), 'type' => 'select', 'values' => array ( '0' => __('similar:lsrc_site'), '1' => __('similar:lsrc_plugin')), 'value' => intval(pluginGetVariable($plugin,'localsource'))));
array_push($cfg, array('mode' => 'group', 'title' => '<b>'.__('similar:cfg_display').'</b>', 'entries' => $cfgX));

$cfgX = array();
array_push($cfgX, array('name' => 'similar_enabled', 'title' => __('similar:similar_enabled'), 'descr' => __('similar:similar_enabled_desc'), 'type' => 'select', 'values' => array(0 => __('noa'), 1 => __('yesa')), 'value' => pluginGetVariable($plugin, 'similar_enabled')));
array_push($cfgX, array('name' => 'count', 'title' => __('similar:similar_count'), 'descr' => __('similar:similar_count_desc'), 'type' => 'input', 'html_flags' => 'size="4"', 'value' => pluginGetVariable($plugin, 'count')));
array_push($cfg, array('mode' => 'group', 'title' => '<b>'.__('similar:cfg_similar').'</b>', 'entries' => $cfgX));

//$cfgX = array();
//array_push($cfgX, array('name' => 'samecat_enabled', 'title' => __('similar:samecat_enabled'), 'descr' => __('similar:samecat_enabled_desc'), 'type' => 'select', 'values' => array(0 => __('noa'), 1 => __('yesa')), 'value' => pluginGetVariable($plugin, 'samecat_enabled')));
//array_push($cfgX, array('name' => 'samecat_count', 'title' => __('similar:samecat_count'), 'descr' => __('similar:samecat_count_desc'), 'type' => 'input', 'html_flags' => 'size="4"', 'value' => pluginGetVariable($plugin, 'samecat_count')));
//array_push($cfg, array('mode' => 'group', 'title' => '<b>'.__('similar:cfg_samecateg').'</b>', 'entries' => $cfgX));

$cfgX = array();
array_push($cfgX, array('name' => 'pcall', 'title' => "Интеграция с новостными плагинами<br /><small><b>Да</b> - в плагине появится возможность испольвать переменные других плагинов<br /><b>Нет</b> - переменные других плагинов использовать нельзя</small>", 'type' => 'select', 'values' => array ( '1' => 'Да', '0' => 'Нет'), 'value' => intval(pluginGetVariable($plugin,'pcall'))));
array_push($cfgX, array('name' => 'pcall_mode', 'title' => "Режим вызова", 'descr' => "Вам необходимо выбрать какой из режимов отображения новостей будет эмулироваться<br/><b>экспорт</b> - экспорт данных в другие плагины (<font color=\"red\">рекомендуется</font>)<br /><b>короткая</b> - короткая новость<br><b>полная</b> - полная новость</small>", 'type' => 'select', 'values' => array ( '0' => 'экспорт', '1' => 'короткая', '2' => 'полная'), 'value' => intval(pluginGetVariable($plugin,'pcall_mode'))));
array_push($cfg, array('mode' => 'group', 'title' => '<b>Интеграция</b>', 'entries' => $cfgX));

$cfgX = array();
array_push($cfgX, array('name' => 'countX', 'title' => __('similar:similarity')));
array_push($cfg, array('mode' => 'group', 'title' => '<b>'.__('similar:cfg_similarity').'</b>', 'entries' => $cfgX));

if (!$_REQUEST['action']) {
	generate_config_page($plugin, $cfg);
}
elseif ($_REQUEST['action'] == 'commit') {
	commit_plugin_config_changes($plugin, $cfg);
	if ($_REQUEST['rebuild']) {
		// Rebuild index table

		// * Truncate index
		$mysql->query("truncate table ".prefix."_similar_index");

		// * Mark all news to have broken index
		$mysql->query("update ".prefix."_news set similar_status = 0");

		msg(array('message' => __($plugin.':rebuild_done')));
	}
	print_commit_complete($plugin, $cfg);
}
