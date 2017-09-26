<?php

/*
 * Configuration file for plugin
 */

// Protect against hack attempts
if (!defined('BBCMS')) die ('HAL');

// Load lang files
Lang::loadPlugin($plugin, 'admin', '', ':');

// Load CORE Plugin
$cPlugin = CPlugin::instance();

// Prepare configuration parameters
if (empty($skList = $cPlugin->getThemeSkin($plugin))) {
    msg(array( 'type' => 'danger', 'message' => __('msg.no_skin')));
}

// Fill configuration parameters
$cfg = array(
    'description' => 'Плагин генерирует ссылки на следующую и предыдущую новости.',
    'submit' => array(
        array('type' => 'default'),
    )
    );

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
        'name' => 'skin',
        'title' => __('skin'),
        'descr' => __('skin#desc'),
        'type' => 'select',
        'values' => $skList,
        'value' => pluginGetVariable($plugin, 'skin'),
    ));
array_push($cfg, array(
    'mode' => 'group',
    'title' => __('group.source'),
    'entries' => $cfgX,
    ));

// RUN
if ('commit' == $action) {
    // If submit requested, do config save
    commit_plugin_config_changes($plugin, $cfg);
}

generate_config_page($plugin, $cfg);
