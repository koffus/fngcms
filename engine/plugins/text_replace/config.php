<?php

/*
 * Configuration file for plugin
 */

// Protect against hack attempts
if (!defined('BBCMS')) die ('HAL');

// Load lang files
Lang::loadPlugin($plugin, 'admin', '', ':');

// Fill configuration parameters
$cfg = array(
    'description' => 'Замена слов на адрес страниц',
    'submit' => array(
        array('type' => 'default'),
    )
    );

$cfgX = array();
	array_push($cfgX, array(
		'name' => 'p_count',
		'title' => "Количество замен одной ссылке в одной новости",
		'type' => 'input',
		'value' => intval(pluginGetVariable($plugin,'p_count')),
		));
	array_push($cfgX, array(
		'name' => 'c_replace',
		'title' => "Режим поиска",
		'type' => 'select',
		'values' => array('0' => 'Не точное совпадение', '1' => 'Точное совпадени без учета регистра', '2' => 'Точное совпадение с учетом регистра'),
		'value' => intval(pluginGetVariable($plugin,'c_replace')),
		));
	array_push($cfgX, array(
		'name' => 'replace',
		'title' => 'Списки',
		'descr' => 'Укажите слова через разделить <code>|</code> и переводом строк<br />Шаблон:<br /><code>Что_искать|На_что_заменить|Количество_в_одной_новости</code><br />Примеры:<br /><code>test|http://test|2<br />test2|http://test2</code>',
		'type' => 'text',
		'html_flags' => 'rows="8"',
		'value' => pluginGetVariable($plugin,'replace'),
		));
	array_push($cfgX, array(
		'name' => 'str_url',
		'title' => 'Шаблон подмены',
		'descr' => 'Ключи:<br /><code>%search%</code> - искомое слово<br /><code>%replace%</code> - заменяемое слово<br /><code>%scriptLibrary%</code> - путь до библиотек http://site/lib<br /><code>%home%</code> - адрес сайта http://ngcms<br />Пример:<br /><code>&lt;a href="%replace%"&gt;%search%&lt;/a&gt;</code>',
		'type' => 'input',
		'value' => pluginGetVariable($plugin,'str_url'),
		));
array_push($cfg, array(
	'mode' => 'group',
	'title' => __('group.config'),
	'entries' => $cfgX,
	));

// RUN
if ('commit' == $action) {
	// If submit requested, do config save
	commit_plugin_config_changes($plugin, $cfg);
}

generate_config_page($plugin, $cfg);

