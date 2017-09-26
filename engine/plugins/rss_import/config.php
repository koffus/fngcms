<?php

/*
 * Configuration file for plugin
 */

// Protect against hack attempts
if (!defined('BBCMS')) die ('HAL');

// Load lang files
Lang::loadPlugin($plugin, 'admin', '', ':');

// Set default values if values are not set [for new variables]
foreach ([
    'items_count' => 10,
    'title_length' => 120,
    'description_length' => 120,
    'description_enable' => 1,
    'extract_images' => 1,
    'skin' => 'basic',
    'cache' => 1,
    'cache_expire' => 60,
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
    if (empty($skList = $cPlugin->getThemeSkin($plugin))) {
        msg(array( 'type' => 'danger', 'message' => __('msg.no_skin')));
    }

    // Fill configuration parameters
    $cfg = array(
        'description' => __($plugin.':description'),
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
            'name' => 'extract_images',
            'title' => 'Извлекать изображения из новости',
            'descr' => '<code>Да</code> - все изображения из текста новости, если они есть, будут доступны в массиве переменных <code>{{ item.embed.images }}</code>',
            'type' => 'select',
            'values' => array('0' => __('noa'), '1' => __('yesa')),
            'value' => intval(pluginGetVariable($plugin, 'extract_images')),
            ));
    array_push($cfg, array(
        'mode' => 'group',
        'title' => __('group.general'),
        'entries' => $cfgX,
        ));


    // Skin
    $cfgX = array();
    array_push($cfgX, array(
        'name' => 'skin',
        'title' => __('skin'),
        'descr' => __('skin#desc'),
        'type' => 'select',
        'values' => $skList,
        'value' => pluginGetVariable($plugin, 'skin'),
    ));
    array_push($cfgX, array(
        'name' => 'skinLoad',
        'title' => __('skinLoad'),
        'descr' => __('skinLoad#desc'),
        'type' => 'file',
        'nosave' => true,
    ));
    array_push($cfg, array(
        'mode' => 'group',
        'title' => __('group.source'),
        'entries' => $cfgX,
    ));

    // cache and cache_expire
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
        'name' => 'cache_expire',
        'title' => __('cache_expire'),
        'descr' => __('cache_expire#desc'),
        'type' => 'input',
        'value' => intval(pluginGetVariable($plugin, 'cache_expire')),
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

// 
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

            if (isset($_POST['id'])) {
                $id = intval($_POST['id']);
            } elseif (is_array($widgets)) {
                $id = end(array_keys($widgets)) + 1;
            } else {
                $id = 1;
            }

            $widgets[$id] = [
                'name' => $parse->translit($_POST['name']),
                'title' => secure_html($_POST['title']),
                'url' => secure_html($_POST['url']),
                'active' => intval($_POST['active']),
                'items_count' => intval($_POST['items_count']),
                'title_length' => intval($_POST['title_length']),
                'description_enable' => intval($_POST['description_enable']),
                'description_length' => intval($_POST['description_length']),
                'extract_images' => intval($_POST['extract_images']),
                'skin' => secure_html($_POST['skin']),
                'cache' => intval($_POST['cache']),
                'cache_expire' => intval($_POST['cache_expire']),
                ];

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
                'active' => $widgets[$id]['active'],
                'title_length' => $widgets[$id]['title_length'],
                'description_enable' => $widgets[$id]['description_enable'],
                'description_length' => $widgets[$id]['description_length'],
                'extract_images' => $widgets[$id]['extract_images'],
                'skin' => $widgets[$id]['skin'],
                'cache' => $widgets[$id]['cache'],
                'cache_expire' => $widgets[$id]['cache_expire'],
            ];
        }
    }
    $tVars['items'] = $items;
    $tpath = plugin_locateTemplates($plugin, array('widget.list'));
    array_push($cfg, array(
            'type' => 'flat',
            'input' => $twig->render($tpath['widget.list'] . 'widget.list.tpl', $tVars)
            ));
    generate_config_page($plugin, $cfg);
}

// 
function pluginWidgetEditAction($plugin, $action)
{

    // Load CORE Plugin
    $cPlugin = CPlugin::instance();

    // Prepare configuration parameters
    if (empty($skList = $cPlugin->getThemeSkin($plugin))) {
        msg(array( 'type' => 'danger', 'message' => __('msg.no_skin')));
    }

    // Loli - pop
    $text = ['географии','техники','всего мира','биологии','культуры'];

    // Get all widgets array
    $widgets = pluginGetVariable('rss_import', 'widgets');

    if (is_array($widgets)) {
        $id = end(array_keys($widgets)) + 1;
    } else {
        $id = 1;
    }
    $name = '';
    $title = '';
    $url = '';
    $active = 1;
    $items_count = pluginGetVariable('rss_import', 'items_count');
    $title_length = pluginGetVariable('rss_import', 'title_length');
    $description_enable = pluginGetVariable('rss_import', 'description_enable');
    $description_length = pluginGetVariable('rss_import', 'description_length');
    $extract_images = pluginGetVariable('rss_import', 'extract_images');
    $skin = pluginGetVariable('rss_import', 'skin');
    $cache = pluginGetVariable('rss_import', 'cache');
    $cache_expire = pluginGetVariable('rss_import', 'cache_expire');
    if (isset($_REQUEST['id']) and !empty($widgets[$_REQUEST['id']])) {
        $id = intval($_REQUEST['id']);
        $name = $widgets[$id]['name'];
        $title = $widgets[$id]['title'];
        $url = $widgets[$id]['url'];
        $active = $widgets[$id]['active'];
        $items_count = $widgets[$id]['items_count'];
        $title_length = $widgets[$id]['title_length'];
        $description_enable = $widgets[$id]['description_enable'];
        $description_length = $widgets[$id]['description_length'];
        $extract_images = $widgets[$id]['extract_images'];
        $skin = $widgets[$id]['skin'];
        $cache = $widgets[$id]['cache'];
        $cache_expire = $widgets[$id]['cache_expire'];
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

    // Main configuration
    $cfgX = array();
        array_push($cfgX, array(
            'name' => 'active',
            'title' => __('rss_import:widget_active'),
            'descr' => __('rss_import:widget_active#desc'),
            'type' => 'select',
            'values' => array('0' => __('noa'), '1' => __('yesa')),
            'value' => $active,
            ));
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
            'name' => 'extract_images',
            'title' => 'Извлекать изображения из новости',
            'descr' => '<code>Да</code> - все изображения из текста новости, если они есть, будут доступны в массиве переменных <code>{{ item.embed.images }}</code>',
            'type' => 'select',
            'values' => array('0' => __('noa'), '1' => __('yesa')),
            'value' => $extract_images,
            ));
    array_push($cfg, array(
        'mode' => 'group',
        'title' => __('group.widget_config'),
        'entries' => $cfgX,
        ));


    // Skin
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

    // cache and cache_expire
    $cfgX = array();
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
        'name' => 'cache_expire',
        'title' => __('cache_expire'),
        'descr' => __('cache_expire#desc'),
        'type' => 'input',
        'value' => $cache_expire,
    ));
    array_push($cfg, array(
        'mode' => 'group',
        'title' => __('group.cache'),
        'entries' => $cfgX,
    ));

    generate_config_page($plugin, $cfg);
}
