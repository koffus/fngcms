<?php

//
// Configuration file for plugin
//

// Protect against hack attempts
if (!defined('NGCMS')) die ('HAL');

// Load lang files
Lang::loadPlugin($plugin, 'config', '', ':');

// Prepare configuration parameters
include_once('inc/similar.php');

// Fill configuration parameters
$cfg = array('description' => __($plugin.':description'));

$cfgX = array();
array_push($cfgX, array(
		'name' => 'similar_enabled',
		'title' => __('similar:similar_enabled'),
		'descr' => __('similar:similar_enabled#desc'),
		'type' => 'select',
		'values' => array(0 => __('noa'), 1 => __('yesa')),
		'value' => pluginGetVariable($plugin, 'similar_enabled'),
		));
array_push($cfgX, array(
		'name' => 'count',
		'title' => __('similar:similar_count'),
		'descr' => __('similar:similar_count#desc'),
		'type' => 'input',
		'value' => pluginGetVariable($plugin, 'count'),
		));
array_push($cfg, array(
	'mode' => 'group',
	'title' => __('similar:cfg_similar'),
	'entries' => $cfgX,
	));

/*$cfgX = array();
array_push($cfgX, array(
		'name' => 'samecat_enabled',
		'title' => __('similar:samecat_enabled'),
		'descr' => __('similar:samecat_enabled#desc'),
		'type' => 'select',
		'values' => array(0 => __('noa'), 1 => __('yesa')),
		'value' => pluginGetVariable($plugin, 'samecat_enabled'),
		));
array_push($cfgX, array(
		'name' => 'samecat_count',
		'title' => __('similar:samecat_count'),
		'descr' => __('similar:samecat_count#desc'),
		'type' => 'input',
		'value' => pluginGetVariable($plugin, 'samecat_count'),
		));
array_push($cfg, array(
	'mode' => 'group',
	'title' => '<b>'.__('similar:cfg_samecateg').'</b>',
	'entries' => $cfgX,
	));*/

$cfgX = array();
array_push($cfgX, array(
		'name' => 'pcall',
		'title' => 'Интеграция с новостными плагинами',
		'descr' => '<code>Да</code> - в плагине появится возможность испольвать переменные других плагинов<br /><code>Нет</code> - переменные других плагинов использовать нельзя',
		'type' => 'select',
		'values' => array('1' => 'Да', '0' => 'Нет'),
		'value' => intval(pluginGetVariable($plugin,'pcall')),
		));
array_push($cfgX, array(
		'name' => 'pcall_mode',
		'title' => 'Режим вызова',
		'descr' => 'Вам необходимо выбрать какой из режимов отображения новостей будет эмулироваться<br/><code>экспорт</code> - экспорт данных в другие плагины (<font color="red">рекомендуется</font>)<br /><code>короткая</code> - короткая новость<br><code>полная</code> - полная новость</small>',
		'type' => 'select',
		'values' => array('0' => 'экспорт', '1' => 'короткая', '2' => 'полная'),
		'value' => intval(pluginGetVariable($plugin,'pcall_mode')),
		));
array_push($cfg, array(
	'mode' => 'group',
	'title' => 'Интеграция',
	'entries' => $cfgX));

$cfgX = array();
array_push($cfgX, array(
		'name' => 'countX',
		'title' => __('similar:similarity'),
		));
array_push($cfg, array(
	'mode' => 'group',
	'title' => __('similar:cfg_similarity'),
	'entries' => $cfgX,
	));

$cfgX = array();
	array_push($cfgX, array(
		'name' => 'localSource',
		'title' => __('localSource'),
		'descr' => __('localSource#desc'),
		'type' => 'select',
		'values' => array('0' => __('localSource_0'), '1' => __('localSource_1'),),
		'value' => intval(pluginGetVariable($plugin, 'localSource')) ? intval(pluginGetVariable($plugin, 'localSource')) : '0',
		));
array_push($cfg, array(
	'mode' => 'group',
	'title' => __('group.source'),
	'entries' => $cfgX,
	));

$cfgX = array();
array_push($cfgX, array(
	'name' => 'rebuild', 
	'title' => __('rebuild'),
	'descr' => __('rebuild#desc'),
	'type' => 'select', 
	'value' => 0, 
	'values' => array('1' => __('yesa'), '0' => __('noa')),
	'nosave' => 1
	));
array_push($cfg, array(
	'mode' => 'group',
	'title' => __('group.rebuild'),
	'entries' => $cfgX,
	));

// RUN
if (isset($_REQUEST['action']) and $_REQUEST['action'] == 'commit') {
	// Rebuild index table
	if ($_REQUEST['rebuild']) {
		// * Truncate index
		// * Mark all news to have broken index
		if($mysql->query("truncate table ".prefix."_similar_index") and $mysql->query("update ".prefix."_news set similar_status = 0"))
            msg(array('message' => __('rebuild.done'),));
	}

    // If submit requested, do config save
    commit_plugin_config_changes($plugin, $cfg);
}

generate_config_page($plugin, $cfg);

