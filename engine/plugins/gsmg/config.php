<?php

//
// Configuration file for plugin
//

// Protect against hack attempts
if (!defined('NGCMS')) die ('HAL');

// Load lang files
Lang::loadPlugin($plugin, 'config', '', ':');

// Load CORE Plugin
$cPlugin = CPlugin::instance();

// Check to dependence plugin
$dependence = [];
if (!$cPlugin->isActive('rss_export')) {
    $dependence['rss_export'] = 'rss_export';
}

// Fill configuration parameters
$cfg = array(
    'description' => __($plugin.':description') . ' <a href="' . home . '/gsmg.xml" target="_blank">' . home . '/gsmg.xml</a>',
    'dependence' => $dependence,
    'submit' => array(
        array('type' => 'default'),
        array('type' => 'clearCacheFiles'),
    )
    );

$cfgX = array();
    array_push($cfgX, array(
        'name' => 'main',
        'title' => "Добавлять головную страницу в карту сайта",
        'descr' => "<code>Да</code> - страница будет добавляться в карту сайта<br /><code>Нет</code> - страница не будет добавляться в карту сайта",
        'type' => 'select',
        'values' => array(0 => __('noa'), 1 => __('yesa')),
        'value' => intval(pluginGetVariable($plugin,'main')),
        ));
    array_push($cfgX, array(
        'name' => 'main_pr',
        'title' => "Приоритет головной страницы",
        'descr' => 'значение от <b>0.0</b> до <b>1.0</b>',
        'type' => 'input',
        'value' => (pluginGetVariable($plugin,'main_pr') == '')?'1.0':pluginGetVariable($plugin,'main_pr'),
        ));
    array_push($cfgX, array(
        'name' => 'mainp',
        'title' => "Добавлять постраничку головной страницы в карту сайта",
        'descr' => "<code>Да</code> - страница будет добавляться в карту сайта<br /><code>Нет</code> - страница не будет добавляться в карту сайта",
        'type' => 'select',
        'values' => array(0 => __('noa'), 1 => __('yesa')),
        'value' => intval(pluginGetVariable($plugin,'mainp')),
        ));
    array_push($cfgX, array(
        'name' => 'mainp_pr',
        'title' => "Приоритет постранички головной страницы",
        'descr' => 'значение от <b>0.0</b> до <b>1.0</b>',
        'type' => 'input',
        'value' => (pluginGetVariable($plugin,'mainp_pr') == '')?'0.5':pluginGetVariable($plugin,'mainp_pr'),
        ));
array_push($cfg, array(
    'mode' => 'group',
    'title' => 'Настройки для головной страницы сайта',
    'entries' => $cfgX,
    ));

$cfgX = array();
    array_push($cfgX, array(
        'name' => 'cat',
        'title' => "Добавлять страницы категорий в карту сайта",
        'type' => 'select',
        'values' => array(0 => __('noa'), 1 => __('yesa')),
        'value' => intval(pluginGetVariable($plugin,'cat')),
        ));
    array_push($cfgX, array(
        'name' => 'cat_pr',
        'title' => "Приоритет страниц категорий",
        'type' => 'input',
        'value' => (pluginGetVariable($plugin,'cat_pr') == '')?'0.5':pluginGetVariable($plugin,'cat_pr'),
        ));
    array_push($cfgX, array(
        'name' => 'catp',
        'title' => "Добавлять постраничку страниц категорий в карту сайта",
        'type' => 'select',
        'values' => array(0 => __('noa'), 1 => __('yesa')),
        'value' => intval(pluginGetVariable($plugin,'catp')),
        ));
    array_push($cfgX, array(
        'name' => 'catp_pr',
        'title' => "Приоритет постранички категорий",
        'type' => 'input',
        'value' => (pluginGetVariable($plugin,'catp_pr') == '')?'0.5':pluginGetVariable($plugin,'catp_pr'),
        ));
array_push($cfg, array(
    'mode' => 'group',
    'title' => 'Настройки для страниц категорий',
    'entries' => $cfgX,
    ));

$cfgX = array();
    array_push($cfgX, array(
        'name' => 'news',
        'title' => "Добавлять страницы новостей в карту сайта",
        'type' => 'select',
        'values' => array(0 => __('noa'), 1 => __('yesa')),
        'value' => intval(pluginGetVariable($plugin,'news')),
        ));
    array_push($cfgX, array(
        'name' => 'news_pr',
        'title' => "Приоритет страниц новостей",
        'type' => 'input',
        'value' => (pluginGetVariable($plugin,'news_pr') == '')?'0.3':pluginGetVariable($plugin,'news_pr'),
        ));
array_push($cfg, array(
    'mode' => 'group',
    'title' => 'Настройки для страниц новостей',
    'entries' => $cfgX,
    ));

$cfgX = array();
    array_push($cfgX, array(
        'name' => 'static',
        'title' => "Добавлять статические страницы в карту сайта",
        'type' => 'select',
        'values' => array(0 => __('noa'), 1 => __('yesa')),
        'value' => intval(pluginGetVariable($plugin,'static')),
        ));
    array_push($cfgX, array(
        'name' => 'static_pr',
        'title' => "Приоритет статических страниц",
        'type' => 'input',
        'value' => (pluginGetVariable($plugin,'static_pr') == '')?'0.3':pluginGetVariable($plugin,'static_pr'),
        ));
array_push($cfg, array(
    'mode' => 'group',
    'title' => 'Настройки для статических страниц',
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
        'descr' => __($plugin.':cacheExpire#desc'),
        'type' => 'input',
        'value' => intval(pluginGetVariable($plugin, 'cacheExpire')) ? intval(pluginGetVariable($plugin, 'cacheExpire')) : 10800,
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
