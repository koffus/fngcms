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
    'description' => __($plugin.':description'),
    'submit' => array(
        array('type' => 'default'),
        array('type' => 'clearCacheFiles'),
    )
    );

$cfgX = array();
    array_push($cfgX, array(
        'name' => 'extform',
        'title' => "Режим отображения формы",
        'descr' => "<code>Новость</code> - форма отчёта выводится в самой новости<br/><code>Отдельная страница</code> - в новости выводится только ссылка, форма же показывается на отдельной странице",
        'type' => 'select',
        'values' => array('0' => 'Новость', '1' => 'Отдельная страница'),
        'value' => intval(pluginGetVariable($plugin,'extform')),
        ));
    array_push($cfgX, array(
        'name' => 'errlist',
        'title' => "Список ошибок",
        'descr' => "Записывается в формате:<br/>КОД_ОШИБКИ<b>|</b>ТЕКСТ_ОШИБКИ<br/><code>КОД_ОШИБКИ</code> - уникальный цифровой идентификатор (от 1 до 255) ошибки<br/><code>ТЕКСТ_ОШИБКИ</code> - текст ошибки, показываемый пользователю.<br/>Пользователю и администратору будет отображаться текст, но в БД будет храниться только код",
        'type' => 'text',
        'html_flags' => 'rows=8',
        'value' => pluginGetVariable($plugin,'errlist'),
        ));
    array_push($cfgX, array(
        'name' => 'inform_author',
        'title' => "Оповещать автора новости по email о проблеме",
        'descr' => "<code>Да</code> - на каждый отчёт об ошибке будет сформировано email сообщение<br/><code>Нет</code> - email сообщение отправляться не будет",
        'type' => 'select',
        'values' => array('0' => 'Нет', '1' => 'Да'),
        'value' => intval(pluginGetVariable($plugin,'inform_author')),
        ));
    array_push($cfgX, array(
        'name' => 'inform_admin',
        'title' => "Оповещать <b>администраторов сайта</b> по email о проблеме",
        'descr' => "<code>Да</code> - на каждый отчёт об ошибке будет сформировано email сообщение<br/><code>Нет</code> - email сообщение отправляться не будет",
        'type' => 'select',
        'values' => array('0' => 'Нет', '1' => 'Да'),
        'value' => intval(pluginGetVariable($plugin,'inform_admin')),
        ));
    array_push($cfgX, array(
        'name' => 'inform_reporter',
        'title' => "Оповещать о решении проблемы автора отчёта",
        'descr' => "<code>Да</code> - автор будет получаеть email сообщение при реакции администрации на его отчёт<br/><code>Нет</code> - email сообщение отправляться не будет<br/><code>По запросу</code> - email сообщение будет отправляться, если оно запрошено автором",
        'type' => 'select',
        'values' => array('0' => 'Нет', '1' => 'Да', '2' => 'По запросу'),
        'value' => intval(pluginGetVariable($plugin,'inform_reporter')),
        ));
    array_push($cfgX, array(
        'name' => 'allow_unreg',
        'title' => "Разрешить незарегистрированным оставлять отчёты",
        'descr' => "<code>Да</code> - незарегистрированный пользователь сможет оставлять отчёт<br/><code>Нет</code> - отчёт сможет оставить только зарегистрированный пользователь",
        'type' => 'select',
        'values' => array('0' => 'Нет', '1' => 'Да'),
        'value' => intval(pluginGetVariable($plugin,'allow_unreg')),
        ));
    array_push($cfgX, array(
        'name' => 'allow_unreg_inform',
        'title' => "Разрешить незарегистрированным получать оповещения",
        'descr' => "<code>Да</code> - незарегистрированный пользователь сможет указать свой email адрес для получения писем о реакции администрации на отчёт<br/><code>Нет</code> - получить email оповещение незарегистрированный пользователь не сможет",
        'type' => 'select',
        'values' => array('0' => 'Нет', '1' => 'Да'),
        'value' => intval(pluginGetVariable($plugin,'allow_unreg_inform')),
        ));
    array_push($cfgX, array(
        'name' => 'allow_text',
        'title' => "Разрешить добавлять текстовое сообщение к отчёту об ошибке",
        'descr' => "<code>Да</code> - текст могут добавлять все<br/><code>Нет</code> - добавление текста запрещено<br/><code>Только зарегистрированные</code> - текст могут добавлять только зарегистрированные пользователи",
        'type' => 'select',
        'values' => array('0' => 'Нет', '1' => 'Только зарегистрированные', '2' => 'Да'),
        'value' => intval(pluginGetVariable($plugin,'allow_text')),
        ));
array_push($cfg, array(
    'mode' => 'group',
    'title' => 'Настройки оповещений',
    'entries' => $cfgX,
    ));

$cfgX = array();
    array_push($cfgX, array(
        'name' => 'admins',
        'title' => "Список назначенных администраторов",
        'descr' => "Укажите список логинов пользователей (по одному логину в строке), которым будут выданы административные права для работы с данным плагином<br/><i>Пробелы в конце строк недопустимы!</i>",
        'type' => 'text',
        'html_flags' => 'rows="4"',
        'value' => pluginGetVariable($plugin,'admins'),
        ));
    array_push($cfgX, array(
        'name' => 'inform_admins',
        'title' => "Оповещать <b>назначенных администраторов</b> по email о проблеме",
        'descr' => "<code>Да</code> - на каждый отчёт об ошибке будет сформировано email сообщение<br/><code>Нет</code> - email сообщение отправляться не будет",
        'type' => 'select',
        'values' => array('0' => 'Нет', '1' => 'Да'),
        'value' => intval(pluginGetVariable($plugin,'inform_admin')),
        ));
array_push($cfg, array(
    'mode' => 'group',
    'title' => 'Управление доступом',
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

$cfgX = array();
    array_push($cfgX, array(
        'name' => 'cache',
        'title' => __('cache'),
        'descr' => __('cache#desc'),
        'type' => 'select',
        'values' => array('1' => __('yesa'), '0' => __('noa')),
        'value' => intval(pluginGetVariable($plugin, 'cache')) ? intval(pluginGetVariable($plugin, 'cache')) : 1,
        ));
    /*array_push($cfgX, array(
        'name' => 'cache_expire',
        'title' => __('cache_expire'),
        'descr' => __('cache_expire#desc'),
        'type' => 'input',
        'value' => intval(pluginGetVariable($plugin, 'cache_expire')) ? intval(pluginGetVariable($plugin, 'cache_expire')) : 60,
        ));*/
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
