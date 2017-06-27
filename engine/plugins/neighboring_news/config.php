<?php

//
// Configuration file for plugin
//

// Protect against hack attempts
if (!defined('NGCMS')) die ('HAL');

// Load lang files
Lang::loadPlugin($plugin, 'config', '', '', ':');

// Fill configuration parameters
$cfg = array('description' => 'Плагин генерирует ссылки на следующую и предыдущую новости.');

$cfgX = array();
	array_push($cfgX, array(
		'name' => 'full_mode',
		'title' => 'Выводить в полной новости',
		'type' => 'checkbox',
		'value' => pluginGetVariable('neighboring_news', 'full_mode'),
		));
	array_push($cfgX, array(
		'name' => 'short_mode',
		'title' => 'Выводить в краткой новости<br /><small>Не рекомендуется, т.к. количество запросов к БД увеличится на (2*количество новостей на главной странице)</small>',
		'type' => 'checkbox',
		'value' => pluginGetVariable('neighboring_news', 'short_mode'),
		));
	array_push($cfgX, array(
		'name' => 'compare',
		'title' => 'Параметр выборки из категорий',
		'type' => 'select',
		'values' => array('1' => '1 - Учитываем только главную', '2' => '2 - Полное соответствие'),
		'value' => intval(pluginGetVariable('neighboring_news', 'compare')),
		));
array_push($cfg, array(
	'mode' => 'group',
	'title' => __('group.config'),
	'entries' => $cfgX,
	));

$cfgX = array();
	array_push($cfgX, array(
		'name' => 'localSource',
		'title' => __('localSource'),
		'descr' => __('localSource#desc'),
		'type' => 'select',
		'values' => array('0' => __('localSource_0'), '1' => __('localSource_1'),),
		'value' => intval(pluginGetVariable($plugin, 'localSource')) ? intval(pluginGetVariable($plugin, 'localSource')) : 0,
		));
array_push($cfg, array(
	'mode' => 'group',
	'title' => __('group.source'),
	'entries' => $cfgX,
	));

// RUN
if ($_REQUEST['action'] == 'commit') {
	// If submit requested, do config save
	commit_plugin_config_changes($plugin, $cfg);
	print_commit_complete($plugin, $cfg);
} else {
	generate_config_page($plugin, $cfg);
}
