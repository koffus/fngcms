<?php

//
// Configuration file for plugin
//

// Protect against hack attempts
if (!defined('NGCMS')) die ('HAL');

// Load lang files
Lang::loadPlugin($plugin, 'config', '', '', ':');

//
// Prepare configuration parameters
$count = pluginGetVariable($plugin, 'count');
if ( (intval($count) < 1) or (intval($count) > 20) )
	$count = 1;

// Fill configuration parameters
$cfg = array('description' => 'Плагин RSS новостей.');

$cfgX = array();
array_push($cfgX, array(
	'name' => 'count',
	'title' => 'Количество блоков с RSS новостями',
	'type' => 'input',
	'value' => $count,
	));
array_push($cfg, array(
	'mode' => 'group',
	'title' => __('group.config'),
	'entries' => $cfgX,
	));

for ($i = 1; $i <= $count; $i++) {
	$cfgX = array();
		array_push($cfgX, array(
		'name' => 'rss'.$i.'_name',
		'title' => 'Заголовок новостей для отображения',
		'descr' => 'Например: <code>Next Generation CMS</code>',
		'type' => 'input',
		'value' => pluginGetVariable($plugin,'rss'.$i.'_name'),
		));
		array_push($cfgX, array(
		'name' => 'rss'.$i.'_url',
		'title' => 'Адрес новостей для отображения',
		'descr' => 'Например: <code>http://ngcms.ru</code>',
		'type' => 'input',
		'value' => pluginGetVariable($plugin,'rss'.$i.'_url'),
		));
		array_push($cfgX, array(
		'name' => 'rss'.$i.'_number',
		'title' => 'Количество новостей для отображения',
		'descr' => 'Значение по умолчанию: <code>10</code>',
		'type' => 'input',
		'value' => intval(pluginGetVariable($plugin,'rss'.$i.'_number')) ? intval(pluginGetVariable($plugin, 'rss'.$i.'_number')) : 10,
		));
		array_push($cfgX, array(
		'name' => 'rss'.$i.'_maxlength',
		'title' => 'Ограничение длины названия новости',
		'descr' => 'Если название превышает указанные пределы, то оно будет урезано<br />Значение по умолчанию: <code>100</code>',
		'type' => 'input',
		'value' => intval(pluginGetVariable($plugin, 'rss'.$i.'_maxlength')) ? intval(pluginGetVariable($plugin ,'rss'.$i.'_maxlength')) : 100,
		));
		array_push($cfgX, array(
		'name' => 'rss'.$i.'_newslength',
		'title' => 'Ограничение длины короткой новости',
		'descr' => 'Если название превышает указанные пределы, то оно будет урезано<br />Значение по умолчанию: <code>100</code>',
		'type' => 'input',
		'value' => intval(pluginGetVariable($plugin, 'rss'.$i.'_newslength')) ? intval(pluginGetVariable($plugin ,'rss'.$i.'_newslength')):100,
		));
		array_push($cfgX, array(
		'name' => 'rss'.$i.'_content',
		'title' => 'Генерировать переменную {short_news}',
		'type' => 'checkbox',
		'value' => pluginGetVariable($plugin,'rss'.$i.'_content'),
		));
		array_push($cfgX, array(
		'name' => 'rss'.$i.'_img',
		'title' => 'Удалить все картинки из {short_news}',
		'type' => 'checkbox',
		'value' => pluginGetVariable($plugin,'rss'.$i.'_img'),
		));
	array_push($cfg, array(
	'mode' => 'group',
	'title' => 'Настройки блока № <b>'.$i.'</b> {rss'.$i.'}',
	'entries' => $cfgX));
}

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