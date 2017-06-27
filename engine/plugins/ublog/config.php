<?php

//
// Configuration file for plugin
//

// Protect against hack attempts
if (!defined('NGCMS')) die ('HAL');

// Load lang files
Lang::loadPlugin($plugin, 'config', '', '', ':');

// Prepare configuration parameters
$personalCount = intval(pluginGetVariable($plugin,'personalCount'));
if (($personalCount < 2)||($personalCount > 100))
	$personalCount = 10;

// Fill configuration parameters
$cfg = array('description' => 'Плагин показывает новости конкретного пользователя');

$cfgX = array();
	array_push($cfgX, array(
		'name' => 'replaceCount',
		'title' => 'Заменять значение переменной {news} на активную ссылку на блог пользователя',
		'descr' => '<code>Да</code> - будет заменяться значение переменной<br /><code>Нет</code> - значение переменной заменяться не будет',
		'type' => 'select',
		'values' => array('1' => __('yesa'), '0' => __('noa')),
		'value' => intval(pluginGetVariable($plugin,'replaceCount')),
		));
array_push($cfg, array(
	'mode' => 'group',
	'title' => 'Страница просмотра профиля пользователя',
	'entries' => $cfgX,
	));

$cfgX = array();
	array_push($cfgX, array(
		'name' => 'personalPages',
		'title' => 'Включить персональную ленту для новостей пользователей',
		'descr' => '<code>Да</code> - по адресу <code>/plugin/ublog/?id=ID_пользователя</code> будет доступен список его новостей<br /><code>Нет</code> - список новостей пользователя выводиться не будет',
		'type' => 'select',
		'values' => array('1' => __('yesa'), '0' => __('noa')),
		'value' => intval(pluginGetVariable($plugin,'personalPages')),
		));
	array_push($cfgX, array(
		'name' => 'personalCount',
		'title' => 'Кол-во новостей, отображаемых на странице',
		'descr' => 'Значение по умолчанию: <code>10</code>',
		'type' => 'input',
		'value' => $personalCount,
		));
array_push($cfg, array(
	'mode' => 'group',
	'title' => 'Собственная страница с лентой новостей пользователя',
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
