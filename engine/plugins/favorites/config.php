<?php

//
// Configuration file for plugin
//

// Protect against hack attempts
if (!defined('NGCMS')) die ('HAL');

// Load lang files
Lang::loadPlugin($plugin, 'config', '', ':');

// Fill configuration parameters
$cfg = array('description' => 'Плагин отображает новости, для которых выставлен флаг "добавить в закладки"');

$cfgX = array();
	array_push($cfgX, array(
		'name' => 'number',
		'title' => 'Кол-во новостей для отображения',
		'descr' => 'Сколько новостей будет отображаться в блоке "закладки"',
		'type' => 'input',
		'value' => (int)pluginGetVariable($plugin, 'number') ? (int)pluginGetVariable($plugin, 'number') : 10,
		));
	array_push($cfgX, array(
		'name' => 'maxlength',
		'title' => 'Ограничение длины названия новости',
		'descr' => 'Если название превышает указанные пределы, то оно будет урезано',
		'type' => 'input',
		'value' => (int)pluginGetVariable($plugin, 'maxlength') ? (int)pluginGetVariable($plugin, 'maxlength') : 100,
		));
	array_push($cfgX, array(
		'name' => 'counter',
		'title' => 'Отображать счетчик просмотров',
		'descr' => '<code>Да</code> - счетчик будет отображаться<br /><code>Нет</code> - счетчик не будет отображаться',
		'type' => 'select',
		'values' => array(0 => __('noa'), 1 => __('yesa')),
		'value' => (int)pluginGetVariable($plugin, 'counter') ? (int)pluginGetVariable($plugin, 'counter') : 0,
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
		'value' => (int)pluginGetVariable($plugin, 'localSource') ? (int)pluginGetVariable($plugin, 'localSource') : 0,
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
		'values' => array('0' => __('noa'), '1' => __('yesa')),
		'value' => (int)pluginGetVariable($plugin, 'cache') ? (int)pluginGetVariable($plugin, 'cache') : 1,
		));
	array_push($cfgX, array(
		'name' => 'cacheExpire',
		'title' => __('cacheExpire'),
		'descr' => __('cacheExpire#desc'),
		'type' => 'input',
		'value' => (int)pluginGetVariable($plugin, 'cacheExpire') ? (int)pluginGetVariable($plugin, 'cacheExpire') : 60,
		));
array_push($cfg, array(
	'mode' => 'group',
	'title' => __('group.cache'),
	'entries' => $cfgX,
	));

// RUN
if ('commit' == $action) {
	// If submit requested, do config save
	commit_plugin_config_changes($plugin, $cfg);
}

generate_config_page($plugin, $cfg);
