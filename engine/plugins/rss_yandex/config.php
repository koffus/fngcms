<?php

//
// Configuration file for plugin
//

// Protect against hack attempts
if (!defined('NGCMS')) die ('HAL');

// Load lang files
Lang::loadPlugin($plugin, 'config', '', ':');

//
// Prepare configuration parameters
$xfEnclosureValues = array( '' => '');

// IF plugin 'XFIELDS' is enabled - load it to prepare `enclosure` integration
if (pluginIsActive('xfields')) {
    include_once(root."/plugins/xfields/xfields.php");

    // Load XFields config
    if (is_array($xfc=xf_configLoad())) {
        foreach ($xfc['news'] as $fid => $fdata) {
            $xfEnclosureValues[$fid] = $fid.' ('.$fdata['title'].')';
        }
    }
}

// For example - find 1st category with news for demo URL
$demoCategory = '';
foreach ($catz as $scanCat) {
    if ($scanCat['posts'] > 0) {
        $demoCategory = $scanCat['alt'];
        break;
    }
}

// Fill configuration parameters
$cfg = array('description' => 'Плагин экспорта ленты новостей для поисковой системы Яndex<br>Полная лента новостей доступна по адресу: <b>'.generatePluginLink('rss_yandex', '', array(), array(), true, true).(($demoCategory != '') ? '</b><br/>Лента новостей для категории <i>'.$catz[$demoCategory]['name'].'</i>: <b>'.generatePluginLink('rss_yandex', 'category', array('category' => $demoCategory), array(), true, true).'</b>' : ''));

$cfgX = array();
array_push($cfgX, array(
        'name' => 'feed_title',
        'title' => 'Название RSS потока для полной ленты',
        'descr' => 'Допустимые переменные:<br/><code>{{siteTitle}}</code> - название сайта<br/>Значение по умолчанию: <code>{{siteTitle}}</code>',
        'type' => 'input',
        'value' => pluginGetVariable('rss_yandex','feed_title')?pluginGetVariable('rss_yandex','feed_title'):'{{siteTitle}}',
        ));
array_push($cfgX, array(
        'name' => 'news_title',
        'title' => 'Заголовок (название) новости',
        'descr' => 'Допустимые переменные:<br/><code>{{siteTitle}}</code> - название сайта<br/><code>{{newsTitle}}</code> - заголовок новости<br/><code>{{masterCategoryName}}</code> - название <b>главной</b> категории новости<br/>Значение по умолчанию: <code>{% if masterCategoryName %}{{masterCategoryName}} :: {% endif %}{{newsTitle}}</code>', 
        'type' => 'text',
        'value' => pluginGetVariable('rss_yandex','news_title')?pluginGetVariable('rss_yandex','news_title'):'{% if masterCategoryName %}{{masterCategoryName}} :: {% endif %}{{newsTitle}}'));
array_push($cfgX, array(
        'name' => 'full_format',
        'title' => 'Формат генерации полного текста новости для ленты Яndex',
        'descr' => '<code>Полная</code> - выводится только полная часть новости<br><code>Короткая+полная</code> - выводится короткая + полная часть новости',
        'type' => 'select',
        'values' => array('0' => 'Полная', '1' => 'Полная+короткая'), value => pluginGetVariable('rss_yandex','full_format'),
        ));
array_push($cfgX, array(
        'name' => 'news_age',
        'title' => 'Максимальный срок давности новостей для публикации в ленте',
        'descr' => 'Яndex индексирует новости не старше 8 суток.<br/>Значение по умолчанию: <code>10 суток</code>',
        'type' => 'input',
        'value' => pluginGetVariable('rss_yandex','news_age'),
        ));
array_push($cfgX, array(
        'name' => 'delay',
        'title' => 'Отсрочка вывода новостей в ленту',
        'descr' => 'Вы можете задать время (<b>в минутах</b>) на которое будет откладываться вывод новостей в RSS ленту',
        'type' => 'input',
        'value' => pluginGetVariable('rss_yandex','delay'),
        ));
array_push($cfg, array(
    'mode' => 'group',
    'title' => __('group.config'),
    'entries' => $cfgX,
    ));

$cfgX = array();
    array_push($cfgX, array(
        'name' => 'feed_image_title',
        'title' => 'Заголовок (title) для логотипа',
        'type' => 'input',
        'value' => pluginGetVariable('rss_yandex','feed_image_title'),
        ));
    array_push($cfgX, array('type' => 'input', 'name' => 'feed_image_link',
        'title' => 'URL с изображением логотипа',
        'descr' => 'Желательный размер логотипа - 100 пикселей по максимальной стороне',
        'value' => pluginGetVariable('rss_yandex','feed_image_link'),
        ));
    array_push($cfgX, array(
        'name' => 'feed_image_url',
        'title' => 'Ссылка (link) для перехода по клику на логотип',
        'descr' => 'Обычно - URL вашего сайта',
        'type' => 'input',
        'value' => pluginGetVariable('rss_yandex','feed_image_url'),
        ));
array_push($cfg, array(
    'mode' => 'group',
    'title' => 'Отображение логотипа',
    'entries' => $cfgX,
    ));

$cfgX = array();
array_push($cfgX, array(
        'name' => 'xfEnclosureEnabled',
        'title' => "Генерация поля 'Enclosure' используя данные плагина xfields",
        'descr' => "<code>Да</code> - включить генерацию<br /><code>Нет</code> - отключить генерацию</small>",
        'type' => 'select',
        'values' => array(0 => __('noa'), 1 => __('yesa')),
        'value' => intval(pluginGetVariable($plugin,'xfEnclosureEnabled')),
        ));
array_push($cfgX, array(
        'name' => 'xfEnclosure',
        'title' => "ID поля плагина <b>xfields</b>, которое будет использоваться для генерации поля <b>Enclosure</b>",
        'type' => 'select',
        'values' => $xfEnclosureValues,
        'value' => pluginGetVariable($plugin,'xfEnclosure'),
        ));
array_push($cfg, array(
    'mode' => 'group',
    'title' => 'Генерация поля <b>enclosure</b> из поля xfields',
    'entries' => $cfgX,
    ));

$cfgX = array();
array_push($cfgX, array(
        'name' => 'textEnclosureEnabled',
        'title' => "Вывод в поле 'Enclosure' всех изображений из текста новости (используя HTML тег &lt;img&gt;)",
        'descr' => "<code>Да</code> - выводить все изображения<br /><code>Нет</code> - не выводить</small>",
        'type' => 'select',
        'values' => array(0 => __('noa'), 1 => __('yesa')),
        'value' => intval(pluginGetVariable($plugin,'textEnclosureEnabled')),
        ));
array_push($cfg, array(
    'mode' => 'group',
    'title' => '<b>Генерация поля <b>enclosure</b> из текста новости</b>',
    'entries' => $cfgX,
    ));

$cfgX = array();
    array_push($cfgX, array(
        'name' => 'cache',
        'title' => __('cache'),
        'descr' => __('cache#desc'),
        'type' => 'select',
        'values' => array(0 => __('noa'), 1 => __('yesa')),
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
if ('commit' == $action) {
    // If submit requested, do config save
    commit_plugin_config_changes($plugin, $cfg);
}

generate_config_page($plugin, $cfg);
