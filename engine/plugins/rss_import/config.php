<?php

//
// Configuration file for plugin
//

// Protect against hack attempts
if (!defined('NGCMS')) die ('HAL');

// Load lang files
Lang::loadPlugin($plugin, 'config', '', ':');

// Set default values if values are not set [for new variables]
foreach ([
    'items_count' => 10,
    'title_length' => 120,
    'description_length' => 120,
    'description_enable' => 1,
    'images_enable' => 1,
    'cache' => 1,
    'cacheExpire' => 60,
    ] as $k => $v ) {
    if (pluginGetVariable($plugin, $k) == null) {
        pluginSetVariable($plugin, $k, $v);
    }
}

// Micro sRouter =)
switch ($action)
{
    case 'widget_list':
    case 'widget_edit_submit':
    case 'widget_dell':
        pluginWidgetListAction($plugin, $action);
        break;

    case 'widget_add':
    case 'widget_edit':
        pluginWidgetEditAction($plugin, $action);
        break;

    default:
        pluginConfigAction($plugin, $action);
        break;
}

// Configuration page for plugin
function pluginConfigAction($plugin, $action)
{

    // Load CORE Plugin
    $cPlugin = CPlugin::instance();

    // Prepare configuration parameters
    if (!$skList = $cPlugin->getFoldersSkin($plugin)) {
        $skList = [];
        msg(array( 'type' => 'danger', 'message' => __('msg.no_skin')));
    }

    // Fill configuration parameters
    $cfg = array(
        'description' => 'Плагин позволяет создавать на сайте виджеты (информеры) с отображением RSS-каналов (лент новостей) с других сайтов.',
        'navigation' => array(
            array('class' => 'active','href' => 'admin.php?mod=extra-config&plugin=rss_import','title' => __('config')),
            array('href' => 'admin.php?mod=extra-config&plugin=rss_import&action=widget_list','title' => __('widgetList')),
        ),
        'submit' => array(
            array('type' => 'default'),
            array('type' => 'clearCacheFiles'),
        )
    );

    // Default configuration
    $cfgX = array();
        array_push($cfgX, array(
            'name' => 'items_count',
            'title' => 'Количество новостей для отображения',
            'descr' => 'Значение по умолчанию: <code>10</code>',
            'type' => 'input',
            'value' => intval(pluginGetVariable($plugin, 'items_count')),
            ));
        array_push($cfgX, array(
            'name' => 'title_length',
            'title' => 'Ограничение длины заголовка новости',
            'descr' => 'Если заголовок новости превышает указанные пределы, то оно будет урезано. Значение по умолчанию: <code>120</code>.<br><code>0</code> - выводить полностью',
            'type' => 'input',
            'value' => intval(pluginGetVariable($plugin, 'title_length')),
            ));
        array_push($cfgX, array(
            'name' => 'description_enable',
            'title' => 'Генерировать описание новости',
            'descr' => '<code>Да</code> - в шаблоне будет доступна переменная <code>{{&nbsp;item.description&nbsp;}}</code><br/><code>Нет</code> - обработка описания будет игнорироваться',
            'type' => 'select',
            'values' => array('0' => __('noa'), '1' => __('yesa')),
            'value' => intval(pluginGetVariable($plugin, 'description_enable')),
            ));
        array_push($cfgX, array(
            'name' => 'description_length',
            'title' => 'Ограничение длины описания новости',
            'descr' => 'Если описание новости превышает указанные пределы, то оно будет урезано. Значение по умолчанию: <code>120</code>.<br><code>0</code> - выводить полностью',
            'type' => 'input',
            'value' => intval(pluginGetVariable($plugin, 'description_length')),
            ));
        array_push($cfgX, array(
            'name' => 'images_enable',
            'title' => 'Извлекать изображения, если они есть',
            'descr' => '<code>Да</code> - все изображения из описания новости будут доступны в массиве переменных <code>{{&nbsp;item.images&nbsp;}}</code><br/><code>Нет</code> - изображения будут удаляться',
            'type' => 'select',
            'values' => array('0' => __('noa'), '1' => __('yesa')),
            'value' => intval(pluginGetVariable($plugin, 'images_enable')),
            ));
    array_push($cfg, array(
        'mode' => 'group',
        'title' => __('group.general'),
        'entries' => $cfgX,
        ));


    // localSource
    $cfgX = array();
    array_push($cfgX, array(
        'name' => 'localSource',
        'title' => __('localSource'),
        'descr' => __('localSource#desc'),
        'type' => 'select',
        'values' => array('0' => __('localSource_0'), '1' => __('localSource_1'),),
        'value' => intval(pluginGetVariable($plugin, 'localSource')) ? intval(pluginGetVariable($plugin, 'localSource')) : '0',
    ));
    array_push($cfgX, array(
        'name' => 'localSkin',
        'title' => __('localSkin'),
        'descr' => __('localSkin#desc'),
        'type' => 'select',
        'values' => $skList,
        'value' => pluginGetVariable($plugin,'localSkin') ? pluginGetVariable($plugin,'localSkin') : 'basic',
    ));
    array_push($cfg, array(
        'mode' => 'group',
        'title' => __('group.source'),
        'entries' => $cfgX,
    ));

    // cache and cacheExpire
    $cfgX = array();
    array_push($cfgX, array(
        'name' => 'cache',
        'title' => __('cache'),
        'descr' => __('cache#desc'),
        'type' => 'select',
        'values' => array('1' => __('yesa'), '0' => __('noa')),
        'value' => intval(pluginGetVariable($plugin, 'cache')),
    ));
    array_push($cfgX, array(
        'name' => 'cacheExpire',
        'title' => __('cacheExpire'),
        'descr' => __('cacheExpire#desc'),
        'type' => 'input',
        'value' => intval(pluginGetVariable($plugin, 'cacheExpire')),
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
}

function pluginWidgetListAction($plugin, $action)
{
    global $twig, $mysql, $parse;

    // Fill configuration parameters
    $cfg = array(
        'navigation' => array(
            array('href' => 'admin.php?mod=extra-config&plugin=rss_import','title' => __('config')),
            array('class' => 'active','href' => 'admin.php?mod=extra-config&plugin=rss_import&action=widget_list','title' => __('widgetList')),
        ),
        'submit' => array(
            array('class' => 'btn btn-primary','href' => 'admin.php?mod=extra-config&plugin=rss_import&action=widget_add','title' => __('btn.widget_add')),
        )
    );

    // RUN
    do {
        if ('widget_edit_submit' == $action) {
            if (empty($_POST['name']) or empty($_POST['title']) or empty($_POST['url'])) {
                msg(array('type' => 'danger', 'message' => __('msg.no_save_params')));
                break;
            }

            // Get all widgets array
            $widgets = pluginGetVariable('rss_import', 'widgets');

            $id = isset($_POST['id']) ? intval($_POST['id']) : end(array_keys($widgets)) + 1;
            $widgets[$id]['name'] = $parse->translit($_POST['name']);
            $widgets[$id]['title'] = secure_html($_POST['title']);
            $widgets[$id]['url'] = secure_html($_POST['url']);
            $widgets[$id]['items_count'] = intval($_POST['items_count']);
            $widgets[$id]['title_length'] = intval($_POST['title_length']);
            $widgets[$id]['description_enable'] = intval($_POST['description_enable']);
            $widgets[$id]['description_length'] = intval($_POST['description_length']);
            $widgets[$id]['images_enable'] = intval($_POST['images_enable']);
            $widgets[$id]['localSource'] = intval($_POST['localSource']);
            $widgets[$id]['localSkin'] = secure_html($_POST['localSkin']);
            $widgets[$id]['cache'] = intval($_POST['cache']);
            $widgets[$id]['cacheExpire'] = intval($_POST['cacheExpire']);

            pluginSetVariable($plugin, 'widgets', $widgets);
            // Load CORE Plugin
            $cPlugin = CPlugin::instance();
            // Save configuration parameters of plugins
            $cPlugin->saveConfig();

            msg(array('message' => __('commited')));
        } elseif ('widget_dell' == $action) {
            if (empty($_REQUEST['id'])) {
                msg(array('type' => 'danger', 'message' => __($plugin.':msg.widget_no')));
                break;
            }
            $id = intval($_REQUEST['id']);
            $widgets = pluginGetVariable($plugin, 'widgets');
            if (empty($widgets[$id])){
                msg(array('type' => 'danger', 'message' => __($plugin.':msg.widget_no')));
                break;
            }
            if (isset($widgets[$id])){
                unset($widgets[$id]);
                pluginSetVariable($plugin, 'widgets', $widgets);
                // Load CORE Plugin
                $cPlugin = CPlugin::instance();
                // Save configuration parameters of plugins
                $cPlugin->saveConfig();
            }
            msg(array('message' => __($plugin.':msg.widget_delete')));
        }

        if ('widget_list' != $action and pluginGetVariable($plugin, 'cache')) {
            clearCacheFiles($plugin);}

    } while (0);

    $items = [];
    if (is_array($widgets = pluginGetVariable($plugin, 'widgets'))){
        foreach($widgets as $id => $widget) {
            // Prepare data for template
            $items[] = [
                'id' => $id,
                'name' => $widgets[$id]['name'],
                'title' => $widgets[$id]['title'],
                'url' => $widgets[$id]['url'],
                'items_count' => $widgets[$id]['items_count'],
                'title_length' => $widgets[$id]['title_length'],
                'description_enable' => $widgets[$id]['description_enable'],
                'description_length' => $widgets[$id]['description_length'],
                'images_enable' => $widgets[$id]['images_enable'],
                'localSource' => $widgets[$id]['localSource'],
                'localSkin' => $widgets[$id]['localSkin'],
                'cache' => $widgets[$id]['cache'],
                'cacheExpire' => $widgets[$id]['cacheExpire'],
            ];
        }
    }
    $tVars['items'] = $items;
    $tpath = locatePluginTemplates(array('widget.list'), $plugin, 1, '', 'admin');
    array_push($cfg, array(
            'type' => 'flat',
            'input' => $twig->loadTemplate($tpath['widget.list'] . 'widget.list.tpl')->render($tVars)
            ));
    generate_config_page($plugin, $cfg);
}

function pluginWidgetEditAction($plugin, $action)
{

    // Load CORE Plugin
    $cPlugin = CPlugin::instance();

    // Prepare configuration parameters
    if (!$skList = $cPlugin->getFoldersSkin($plugin)) {
        $skList = [];
        msg(array( 'type' => 'danger', 'message' => __('msg.no_skin')));
    }

    // Loli - pop
    $text = ['географии','техники','всего мира','биологии','культуры'];

    // Get all widgets array
    $widgets = pluginGetVariable('rss_import', 'widgets');

    $id = end(array_keys($widgets)) + 1;
    $name = '';
    $title = '';
    $url = '';
    $items_count = pluginGetVariable('rss_import', 'items_count');
    $title_length = pluginGetVariable('rss_import', 'title_length');
    $description_enable = pluginGetVariable('rss_import', 'description_enable');
    $description_length = pluginGetVariable('rss_import', 'description_length');
    $images_enable = pluginGetVariable('rss_import', 'images_enable');
    $localSource = pluginGetVariable('rss_import', 'localSource');
    $localSkin = pluginGetVariable('rss_import', 'localSkin');
    $cache = pluginGetVariable('rss_import', 'cache');
    $cacheExpire = pluginGetVariable('rss_import', 'cacheExpire');
    if (isset($_REQUEST['id']) and !empty($widgets[$_REQUEST['id']])) {
        $id = intval($_REQUEST['id']);
        $name = $widgets[$id]['name'];
        $title = $widgets[$id]['title'];
        $url = $widgets[$id]['url'];
        $items_count = $widgets[$id]['items_count'];
        $title_length = $widgets[$id]['title_length'];
        $description_enable = $widgets[$id]['description_enable'];
        $description_length = $widgets[$id]['description_length'];
        $images_enable = $widgets[$id]['images_enable'];
        $localSource = $widgets[$id]['localSource'];
        $localSkin = $widgets[$id]['localSkin'];
        $cache = $widgets[$id]['cache'];
        $cacheExpire = $widgets[$id]['cacheExpire'];
    }

    // Fill configuration parameters
    $cfg = array(
        'navigation' => array(
            array('href' => 'admin.php?mod=extra-config&plugin=rss_import','title' => __('config')),
            array('class' => 'active','href' => 'admin.php?mod=extra-config&plugin=rss_import&action=widget_list','title' => __('widgetList')),
        ),
        'action' => 'admin.php?mod=extra-config&plugin=rss_import&action=widget_edit_submit', // !!!
        'submit' => array(array('type' => 'default')),
        array(
            'name' => 'id',
            'type' => 'hidden',
            'value' => $id,
            )
    );

    // Default configuration
    $cfgX = array();
               array_push($cfgX, array(
            'name' => 'name',
            'title' => __('rss_import:widget_name'),
            'descr' => __('rss_import:widget_name#desc') . ' <code>{{ plugin_rss_import_ID }}</code>',
            'type' => 'input',
            'value' => $name,
            ));
        array_push($cfgX, array(
            'name' => 'title',
            'title' => __('rss_import:widget_title'),
            'descr' => __('rss_import:widget_title#desc'),
            'type' => 'input',
            'value' => $title,
            ));
        array_push($cfgX, array(
            'name' => 'url',
            'title' => 'Адрес новостей для отображения',
            'descr' => 'Например: <code>http://bixbite.site/rss.xml</code>',
            'type' => 'input',
            'value' => $url,
        ));
        array_push($cfgX, array(
            'name' => 'items_count',
            'title' => 'Количество новостей для отображения',
            'descr' => 'Значение по умолчанию: <code>10</code>',
            'type' => 'input',
            'value' => $items_count,
            ));
        array_push($cfgX, array(
            'name' => 'title_length',
            'title' => 'Ограничение длины заголовка новости',
            'descr' => 'Если заголовок новости превышает указанные пределы, то оно будет урезано. Значение по умолчанию: <code>120</code>.<br><code>0</code> - выводить полностью',
            'type' => 'input',
            'value' => $title_length,
            ));
        array_push($cfgX, array(
            'name' => 'description_enable',
            'title' => 'Генерировать описание новости',
            'descr' => '<code>Да</code> - в шаблоне будет доступна переменная <code>{{&nbsp;item.description&nbsp;}}</code><br/><code>Нет</code> - обработка описания будет игнорироваться',
            'type' => 'select',
            'values' => array('0' => __('noa'), '1' => __('yesa')),
            'value' => $description_enable,
            ));
        array_push($cfgX, array(
            'name' => 'description_length',
            'title' => 'Ограничение длины описания новости',
            'descr' => 'Если описание новости превышает указанные пределы, то оно будет урезано. Значение по умолчанию: <code>120</code>.<br><code>0</code> - выводить полностью',
            'type' => 'input',
            'value' => $description_length,
            ));
        array_push($cfgX, array(
            'name' => 'images_enable',
            'title' => 'Извлекать изображения, если они есть',
            'descr' => '<code>Да</code> - все изображения из описания новости будут доступны в массиве переменных <code>{{&nbsp;item.images&nbsp;}}</code><br/><code>Нет</code> - изображения будут удаляться',
            'type' => 'select',
            'values' => array('0' => __('noa'), '1' => __('yesa')),
            'value' => $images_enable,
            ));
    array_push($cfg, array(
        'mode' => 'group',
        'title' => __('group.widget_config'),
        'entries' => $cfgX,
        ));


    // localSource
    $cfgX = array();
    array_push($cfgX, array(
        'name' => 'localSource',
        'title' => __('localSource'),
        'descr' => __('localSource#desc'),
        'type' => 'select',
        'values' => array('0' => __('localSource_0'), '1' => __('localSource_1'),),
        'value' => $localSource,
    ));
    array_push($cfgX, array(
        'name' => 'localSkin',
        'title' => __('localSkin'),
        'descr' => __('localSkin#desc'),
        'type' => 'select',
        'values' => $skList,
        'value' => $localSkin,
    ));
    array_push($cfg, array(
        'mode' => 'group',
        'title' => __('group.source'),
        'entries' => $cfgX,
    ));

    // cache and cacheExpire
    $cfgX = array();
    array_push($cfgX, array(
        'name' => 'cache',
        'title' => __('cache'),
        'descr' => __('cache#desc'),
        'type' => 'select',
        'values' => array('1' => __('yesa'), '0' => __('noa')),
        'value' => $cache,
    ));
    array_push($cfgX, array(
        'name' => 'cacheExpire',
        'title' => __('cacheExpire'),
        'descr' => __('cacheExpire#desc'),
        'type' => 'input',
        'value' => $cacheExpire,
    ));
    array_push($cfg, array(
        'mode' => 'group',
        'title' => __('group.cache'),
        'entries' => $cfgX,
    ));

    generate_config_page($plugin, $cfg);
}

for ($i = 1; $i <= $count; $i++) {
    $cfgX = array();
    array_push($cfgX, array(
        'name' => 'rss' . $i . '_name',
        'title' => 'Заголовок новостей для отображения',
        'descr' => 'Например: <code>BixBite CMS</code>',
        'type' => 'input',
        'value' => pluginGetVariable($plugin, 'rss' . $i . '_name'),
    ));
    array_push($cfgX, array(
        'name' => 'rss' . $i . '_url',
        'title' => 'Адрес новостей для отображения',
        'descr' => 'Например: <code>http://bixbite.site</code>',
        'type' => 'input',
        'value' => pluginGetVariable($plugin, 'rss' . $i . '_url'),
    ));
    array_push($cfgX, array(
        'name' => 'rss' . $i . '_number',
        'title' => 'Количество новостей для отображения',
        'descr' => 'Значение по умолчанию: <code>10</code>',
        'type' => 'input',
        'value' => intval(pluginGetVariable($plugin, 'rss' . $i . '_number')) ? intval(pluginGetVariable($plugin, 'rss' . $i . '_number')) : 10,
    ));
    array_push($cfgX, array(
        'name' => 'rss' . $i . '_maxlength',
        'title' => 'Ограничение длины названия новости',
        'descr' => 'Если название превышает указанные пределы, то оно будет урезано<br />Значение по умолчанию: <code>100</code>',
        'type' => 'input',
        'value' => intval(pluginGetVariable($plugin, 'rss' . $i . '_maxlength')) ? intval(pluginGetVariable($plugin, 'rss' . $i . '_maxlength')) : 100,
    ));
    array_push($cfgX, array(
        'name' => 'rss' . $i . '_newslength',
        'title' => 'Ограничение длины короткой новости',
        'descr' => 'Если название превышает указанные пределы, то оно будет урезано<br />Значение по умолчанию: <code>100</code>',
        'type' => 'input',
        'value' => intval(pluginGetVariable($plugin, 'rss' . $i . '_newslength')) ? intval(pluginGetVariable($plugin, 'rss' . $i . '_newslength')) : 100,
    ));
    array_push($cfgX, array(
        'name' => 'rss' . $i . '_description',
        'title' => 'Генерировать переменную {description}',
        'type' => 'checkbox',
        'value' => pluginGetVariable($plugin, 'rss' . $i . '_description'),
    ));
    array_push($cfgX, array(
        'name' => 'rss' . $i . '_img',
        'title' => 'Удалить все картинки из {description}',
        'type' => 'checkbox',
        'value' => pluginGetVariable($plugin, 'rss' . $i . '_img'),
    ));
}
