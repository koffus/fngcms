<?php

//
// Configuration file for plugin
//

// Protect against hack attempts
if (!defined('NGCMS')) die ('HAL');

// Preload config file
pluginsLoadConfig();

// Fill configuration parameters
$cfg = array('description' => 'Плагин отображает "календарь" - отображает данные о новостях по выбранному месяцу подсвечивая дни когда были размещены новостиПри клике на день будут отображаться новости за этот день');

$cfgX = array();
	array_push($cfgX, array(
		'name' => 'mode',
		'title' => 'В каком режиме генерируется вывод плагина<br /><small><code>Автоматически</code> - при включении плагина автоматически генерируется блок {plugin_comments}<br /><code>TWIG</code> - вывод плагина генерируется только через TWIG функцию <code>callPlugin()</code></small>',
		'type' => 'select',
		'values' => array ( '0' => 'Автоматически', '1' => 'TWIG'),
		'value' => intval(pluginGetVariable($plugin,'mode')),
		));
array_push($cfg, array(
	'mode' => 'group',
	'title' => 'Режим запуска',
	'entries' => $cfgX,
	));

$cfgX = array();
	array_push($cfgX, array(
		'name' => 'localsource',
		'title' => 'Выберите каталог из которого плагин будет брать шаблоны для отображения<br /><small><code>Шаблон сайта</code> - плагин будет пытаться взять шаблоны из общего шаблона сайта; в случае недоступности - шаблоны будут взяты из собственного каталога плагина<br /><code>Плагин</code> - шаблоны будут браться из собственного каталога плагина</small>',
		'type' => 'select',
		'values' => array ( '0' => 'Шаблон сайта', '1' => 'Плагин'),
		'value' => intval(pluginGetVariable($plugin,'localsource')),
		));
array_push($cfg, array(
	'mode' => 'group',
	'title' => 'Настройки отображения',
	'entries' => $cfgX,
	));

$cfgX = array();
	array_push($cfgX, array(
		'name' => 'cache',
		'title' => "Использовать кеширование данных<br /><small><code>Да</code> - кеширование используется<br /><code>Нет</code> - кеширование не используется</small>",
		'type' => 'select',
		'values' => array ( '1' => 'Да', '0' => 'Нет'),
		'value' => intval(pluginGetVariable($plugin,'cache')),
		));
	array_push($cfgX, array(
		'name' => 'cacheExpire',
		'title' => "Период обновления кеша<br /><small>(через сколько секунд происходит обновление кеша. Значение по умолчанию: <code>60</code>)</small>",
		'type' => 'input',
		'value' => intval(pluginGetVariable($plugin,'cacheExpire'))?pluginGetVariable($plugin,'cacheExpire'):'60',
		));
array_push($cfg, array(
	'mode' => 'group',
	'title' => 'Настройки кеширования',
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
