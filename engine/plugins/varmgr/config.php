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
    'description' => 'Добавляет новые переменные управления датой новости, а также позволяет заменить формат отображения даты. Идеален для графического отображения даты.',
    'submit' => array(
        array('type' => 'default'),
    )
    );

$cfgX = array();
	array_push($cfgX, array(
		'name' => 'extdate',
		'title' => 'Дополнительные переменные для управления датой',
		'descr' => 'Доступны переменные:<br><code>{day}</code> - день (1 - 31)<br><code>{day0}</code> - день (01 - 31)<br><code>{month}</code> - месяц (1 - 12)<br><code>{month0}</code> - месяц (01 - 12)<br><code>{year}</code> - год (00 - 99)<br><code>{year2}</code> - год (1980 - 2100)<br><code>{month_s}</code> - текст месяца (Янв, Фев,...)<br><code>{month_l}</code> - текст месяца (Январь, Февраль,...)',
		'type' => 'select',
		'values' => array('0' => 'выкл', '1' => 'вкл'),
		'value' => pluginGetVariable($plugin,'extdate'),
		));
	array_push($cfgX, array(
		'name' => 'newdate',
		'title' => 'Изменить формат даты',
		'descr' => 'При заполнении данного параметра изменяется формат отображения даты в новостях на указанный',
		'type' => 'input',
		'value' => pluginGetVariable($plugin,'newdate'),
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
