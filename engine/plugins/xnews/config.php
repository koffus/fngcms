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
    msg(array('type' => 'danger', 'message' => __('msg.no_skin')));
}

$count = intval(pluginGetVariable($plugin, 'count'));
if ($count < 1 or $count > 50)
    $count = 1;

// Fill configuration parameters
$cfg = array(
    'description' => __($plugin.':description'),
    'submit' => array(
        array('type' => 'default'),
        array('type' => 'clearCacheFiles'),
    )
    );

$cfgX = array();
    array_push($cfgX, array(
        'name' => 'count',
        'title' => __($plugin.':count_title'),
        'type' => 'input',
        'value' => $count,
        ));
array_push($cfg, array(
    'mode' => 'group',
    'title' => __('group.config'),
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
        'name' => 'cache_expire',
        'title' => __('cache_expire'),
        'descr' => __('cache_expire#desc'),
        'type' => 'input',
        'value' => intval(pluginGetVariable($plugin, 'cache_expire')) ? intval(pluginGetVariable($plugin, 'cache_expire')) : 60,
        ));
array_push($cfg, array(
    'mode' => 'group',
    'title' => __('group.cache'),
    'entries' => $cfgX,
    ));

for ($i = 1; $i <= $count; $i++) {

    $currentVar = "{$i}";
    $blockName = pluginGetVariable($plugin, "{$currentVar}_name") ? pluginGetVariable('xnews', "{$currentVar}_name") : '# '.$currentVar;
    $orderby = array(
        'viewed' => __($plugin.':orderby_views'),
        'commented' => __($plugin.':orderby_comments'),
        'random' => __($plugin.':orderby_random'),
        'last' => __($plugin.':orderby_last'),
        );

    $cfgX = array();

        array_push($cfgX, array(
            'name' => "{$currentVar}_name",
            'title' => 'Идентификатор блока<br/><small>По данному ID можно будет формировать данный блок через вызов <b>TWIG</b> функции <b>callPlugin()</b></small>',
            'type' => 'input',
            'value' => pluginGetVariable($plugin, "{$currentVar}_name"),
            ));

        array_push($cfgX, array(
            'name' => "{$currentVar}_skin",
            'title' => __('skin'),
            'descr' => __('skin#desc'),
            'type' => 'select',
            'values' => $skList,
            'value' => pluginGetVariable($plugin, "{$currentVar}_skin"),
            ));

        array_push($cfgX, array(
            'name' => "{$currentVar}_visibilityMode",
            'title' => 'Область видимости<br/><small>Укажите на каких страницах будет отображаться данный блок</small>',
            'type' => 'select',
            'values' => array('0' => 'Везде', 1 => 'На странице категорий', 2 => 'На странице новостей', 3 => 'Страница категорий + новостей'),
            'value' => pluginGetVariable($plugin, "{$currentVar}_visibilityMode"))
            );

        array_push($cfgX, array(
            'name' => "{$currentVar}_visibilityCList",
            'title' => 'Список категорий на которых отображается блок<br/><small>Можно указать конкретные категории при выборе <b>категории/новости</b> в предыдущем пункте</small>',
            'type' => 'input',
            'value' => pluginGetVariable($plugin, "{$currentVar}_visibilityCList"),
            ));

        array_push($cfgX, array(
            'name' => "{$currentVar}_categoryMode",
            'title' => 'Из каких категорий генерируется лента новостей',
            'type' => 'select',
            'values' => array('0' => 'Список категорий', 1 => 'Текущая категория', 2 => 'Список + текущая'),
            'value' => pluginGetVariable($plugin, "{$currentVar}_categoryMode"),
            ));

        array_push($cfgX, array(
            'name' => "{$currentVar}_categories",
            'title' => 'Список категорий для генерации ленты<br/><small>Задаётся список категорий (через запятую) при выборе <b>список</b> в предыдущем поле. Оставьте поле пустым для генерации ленты по всем категориям</small>',
            'type' => 'input',
            'value' => pluginGetVariable($plugin, "{$currentVar}_categories"),
            ));

        array_push($cfgX, array(
            'name' => "{$currentVar}_mainMode",
            'title' => "Отображение новостей с главной страницы<br/><small>Выберите тип новостей, которые будут отображаться в блоке</small>",
            'type' => 'select',
            'value' => pluginGetVariable($plugin, "{$currentVar}_mainMode"),
            'values' => array('0' => 'Все', 1 => 'С главной', 2 => 'Не с главной'),
            ));

        array_push($cfgX, array(
            'name' => "{$currentVar}_pinMode",
            'title' => "Отображение прикрепленных новостей<br/><small>Выберите тип новостей, которые будут отображаться в блоке</small>",
            'type' => 'select',
            'value' => pluginGetVariable($plugin, "{$currentVar}_pinMode"),
            'values' => array('0' => 'Все', 1 => 'Прикрепленные', 2 => 'Не прикрепленные'),
            ));

        array_push($cfgX, array(
            'name' => "{$currentVar}_favMode",
            'title' => "Отображение новостей из закладок<br/><small>Выберите тип новостей, которые будут отображаться в блоке</small>",
            'type' => 'select',
            'value' => pluginGetVariable($plugin, "{$currentVar}_favMode"),
            'values' => array('0' => 'Все', 1 => 'Только из закладок', 2 => 'Не добавленные в закладки'),
            ));

        array_push($cfgX, array(
            'name' => "{$currentVar}_skipCurrent",
            'title' => "Не отображать в блоке текущую новость<br/><small>Данный режим не позволяет использовать кеширование блоков и повышает нагрузку на систему</small>",
            'type' => 'select',
            'value' => pluginGetVariable($plugin, "{$currentVar}_skipCurrent"),
            'values' => array('0' => 'Нет', 1 => 'Да'),
            ));

        array_push($cfgX, array(
            'name' => "{$currentVar}_extractImages",
            'title' => "Извлекать изображения из новости",
            'descr' => "<code>Да</code> - все изображения из текста новости, если они есть, будут доступны в массиве переменных <code>{{ news.embed.images }}</code>",
            'type' => 'select',
            'value' => pluginGetVariable($plugin, "{$currentVar}_extractImages"),
            'values' => array('0' => 'Нет', 1 => 'Да'),
            ));

        array_push($cfgX, array(
            'name' => "{$currentVar}_showNoNews",
            'title' => 'Выводить блок если в нём нет новостей',
            'type' => 'checkbox',
            'value' => pluginGetVariable($plugin ,"{$currentVar}_showNoNews"),
            ));

        array_push($cfgX, array(
            'name' => "{$currentVar}_count",
            'title' => __($plugin.':number_title'),
            'type' => 'input',
            'value' => intval(pluginGetVariable($plugin, "{$currentVar}_count")) ? pluginGetVariable($plugin, "{$currentVar}_count") : '10',
            ));

        array_push($cfgX, array(
            'name' => "{$currentVar}_skip",
            'title' => 'Пропустить первые <b>X</b> новостей при показе блока<br/><small>Значение по умолчанию: 0</small>',
            'type' => 'input',
            'value' => intval(pluginGetVariable($plugin, "{$currentVar}_skip")) ? pluginGetVariable($plugin , "{$currentVar}_skip") : '0',
            ));

        array_push($cfgX, array(
            'name' => "{$currentVar}_maxAge",
            'title' => __($plugin.':date'),
            'type' => 'input',
            'value' => intval(pluginGetVariable($plugin, "{$currentVar}_maxAge")),
            ));

        array_push($cfgX, array(
            'name' => "{$currentVar}_order",
            'type' => 'select',
            'title' => __($plugin.':orderby_title'),
            'values' => $orderby,
            'value' => pluginGetVariable($plugin, "{$currentVar}_order"),
            ));

            /*
        array_push($cfgX, array(
            'name' => "{$currentVar}_content",
            'title' => __($plugin.':content'),
            'type' => 'checkbox',
            'value' => pluginGetVariable($plugin ,"{$currentVar}_content"),
            ));

        array_push($cfgX, array(
            'name' => "{$currentVar}_img",
            'title' => __($plugin.':img'),
            'type' => 'checkbox',
            'value' => pluginGetVariable('xnews',"{$currentVar}_img"),
            ));
            */

    array_push($cfg, array(
        'mode' => 'group',
        'title' => __($plugin.':group').$blockName,
        'toggle' => 'hide',
        'entries' => $cfgX,
        ));
}

// RUN
if ('commit' == $action) {
    // If submit requested, do config save
    commit_plugin_config_changes($plugin, $cfg);
}

generate_config_page($plugin, $cfg);
