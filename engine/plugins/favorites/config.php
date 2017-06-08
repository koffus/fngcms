<?php

//
// Configuration file for plugin
//

// Protect against hack attempts
if (!defined('NGCMS')) die ('HAL');

// Preload config file
pluginsLoadConfig();

// Load lang files
Lang::loadPlugin($plugin, 'config', '', '', ':');

// Fill configuration parameters
$cfg = array('description' => 'Плагин отображает новости, для которых выставлен флаг "добавить в закладки"');

$cfgX = array();
	array_push($cfgX, array(
		'name' => 'number',
		'title' => 'Кол-во новостей для отображения',
		'descr' => 'Сколько новостей будет отображаться в блоке "закладки"',
		'type' => 'input',
		'value' => intval(pluginGetVariable($plugin, 'number')) ? pluginGetVariable($plugin, 'number') : 10,
		));
	array_push($cfgX, array(
		'name' => 'maxlength',
		'title' => 'Ограничение длины названия новости',
		'descr' => 'Если название превышает указанные пределы, то оно будет урезано',
		'type' => 'input',
		'value' => intval(pluginGetVariable($plugin, 'maxlength')) ? pluginGetVariable($plugin, 'maxlength') : 100,
		));
	array_push($cfgX, array(
		'name' => 'counter',
		'title' => 'Отображать счетчик просмотров',
		'descr' => '<code>Да</code> - счетчик будет отображаться<br /><code>Нет</code> - счетчик не будет отображаться',
		'type' => 'select',
		'values' => array(0 => __('noa'), 1 => __('yesa')),
		'value' => intval(pluginGetVariable($plugin, 'counter')),
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

$cfgX = array();
	array_push($cfgX, array(
		'name' => 'cache',
		'title' => __('cache'),
		'descr' => __('cache#desc'),
		'type' => 'select',
		'values' => array('1' => __('yesa'), '0' => __('noa')),
		'value' => intval(pluginGetVariable($plugin, 'cache')) ? intval(pluginGetVariable($plugin, 'cache')) : 1,
		));
	array_push($cfgX, array(
		'name' => 'cacheExpire',
		'title' => __('cacheExpire'),
		'descr' => __('cacheExpire#desc'),
		'type' => 'input',
		'value' => intval(pluginGetVariable($plugin, 'cacheExpire')) ? intval(pluginGetVariable($plugin, 'cacheExpire')) : 60,
		));
array_push($cfg, array(
	'mode' => 'group',
	'title' => __('group.cache'),
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
