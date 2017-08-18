<?php

//
// Configuration file for plugin
//

// Protect against hack attempts
if (!defined('NGCMS')) die ('HAL');

// Load lang files
Lang::loadPlugin($plugin, 'config', '', ':');

// Micro sRouter =)
switch ($action) {
    case 'list':
    case 'update':
    case 'dell':
    case 'edit_submit':
    case 'move_up':
    case 'move_down':
        showList($plugin, $action);
        break;

    case 'edit':
        edit($plugin, $action);
        break;

    case 'widget_list':
    case 'widget_edit_submit':
    case 'widget_dell':
        showWidgetList($plugin, $action);
        break;

    case 'widget_add':
        editWidget($plugin, $action);
        break;

    default:
        main($plugin, $action);
        break;
}

function main($plugin, $action)
{

    // Load CORE Plugin
    $cPlugin = CPlugin::instance();

    // Prepare configuration parameters
    if (!$skList = $cPlugin->getFoldersSkin($plugin)) {
        $skList = [];
        msg(array( 'type' => 'danger', 'message' => __('gallery:msg.no_skin')));
    }

    // Check to dependence plugin
    $dependence = [];
    if (!$cPlugin->isActive('comments')) {
        $dependence['comments'] = 'comments';
    }

    // Fill configuration parameters
    $cfg = array(
        'description' => __('gallery:description'),
        'dependence' => $dependence,
        'navigation' => array(
            array('class' => 'active','href' => 'admin.php?mod=extra-config&plugin=gallery','title' => __('group.config')),
            array('href' => 'admin.php?mod=extra-config&plugin=gallery&action=list','title' => __('gallery:button_list')),
            array('href' => 'admin.php?mod=extra-config&plugin=gallery&action=widget_list','title' => __('gallery:button_widget_list')),
        ),
        'submit' => array(array('type' => 'default'),array('type' => 'clearCacheFiles'))
    );

    $cfgX = array();
        array_push($cfgX, array(
            'name' => 'if_description',
            'title' => __('gallery:label_if_description'),
            'descr' => __('gallery:desc_if_description'),
            'type' => 'select',
            'values' => array('1' => __('yesa'), '0' => __('noa')),
            'value' => pluginGetVariable($plugin, 'if_description'),
            ));
        array_push($cfgX, array(
            'name' => 'if_keywords',
            'title' => __('gallery:label_if_keywords'),
            'descr' => __('gallery:desc_if_keywords'),
            'type' => 'select',
            'values' => array('1' => __('yesa'), '0' => __('noa')),
            'value' => pluginGetVariable($plugin, 'if_keywords'),
            ));
        array_push($cfgX, array(
            'name' => 'image_count',
            'title' => __('gallery:label_image_count'),
            'descr' => __('gallery:desc_image_count'),
            'type' => 'input',
            'value' => pluginGetVariable($plugin, 'image_count'),
            ));
    array_push($cfg, array(
        'mode' => 'group',
        'title' => __('group.config'),
        'entries' => $cfgX,
        ));

    $cfgX = array();
        array_push($cfgX, array(
            'name' => 'localSource',
            'title' => __('localSource'),
            'descr' => __('localSource#desc'),
            'type' => 'select',
            'values' => array('0' => __('localSource_0'), '1' => __('localSource_1'),),
            'value' => pluginGetVariable($plugin, 'localSource'),
            ));
        array_push($cfgX, array(
            'name' => 'localSkin',
            'title' => __('localSkin'),
            'descr' => __('localSkin#desc'),
            'type' => 'select',
            'values' => $skList,
            'value' => pluginGetVariable($plugin,'localSkin'),
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
            'value' => pluginGetVariable($plugin, 'cache'),
            ));
        array_push($cfgX, array(
            'name' => 'cacheExpire',
            'title' => __('cacheExpire'),
            'descr' => __('cacheExpire#desc'),
            'type' => 'input',
            'value' => pluginGetVariable($plugin, 'cacheExpire') ? intval(pluginGetVariable($plugin, 'cacheExpire')) : 60,
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

function showList($plugin, $action)
{
    global $twig, $mysql, $parse;

    // Fill configuration parameters
    $cfg = array(
        'description' => __('gallery:description'),
        'navigation' => array(
            array('href' => 'admin.php?mod=extra-config&plugin=gallery','title' => __('group.config')),
            array('class' => 'active','href' => 'admin.php?mod=extra-config&plugin=gallery&action=list','title' => __('gallery:button_list')),
            array('href' => 'admin.php?mod=extra-config&plugin=gallery&action=widget_list','title' => __('gallery:button_widget_list')),
        ),
        'submit' => array(
            array('class' => 'btn btn-primary','href' => 'admin.php?mod=extra-config&plugin=gallery&action=update','title' => __('gallery:button_update')),
            )
    );

    // RUN
    do {
        if ('update' == $action) {
            $gallery = $mysql->select('select name from '.prefix.'_gallery');
            $next_order = count($gallery) + 1;
            if ($dir = opendir(images_dir)) {
                while($file = readdir($dir)) {
                    if (!is_dir(images_dir."/".$file) or $file == "." or $file == ".." or GetKeyFromName($file, $gallery) !== false)
                        continue;
                    $mysql->query('insert '.prefix.'_gallery '.
                        '(name, title, position) values '.
                        '('.db_squote($file).', '.db_squote($file).', '.db_squote($next_order).')');
                        $next_order ++;
                }
                closedir($dir);
                msg(array('message' => __('gallery:info_update_record')));
            }

        } elseif ('edit_submit' == $action) {

            if (!isset($_POST['id']) or !isset($_POST['title']) or !isset($_POST['if_active']) or !isset($_POST['skin']) or !isset($_POST['icon']) or !isset($_POST['description']) or !isset($_POST['keywords']) or !isset($_POST['image_count'])) {
                msg(array('type' => 'danger', 'message' => 'Не все параметры заданы'));
                break;
            }
            $id = intval($_POST['id']);
            
            $gallery = $mysql->record('select * from '.prefix.'_gallery where `id`='.db_squote($id).' limit 1');
            
            if (!$gallery) {
                msg(array('type' => 'danger', 'message' => ''));
                break;
            }

            $title = secure_html($_POST['title']);
            $skin = $parse->translit(trim($_POST['skin']), 1);
            $image_count = intval($_POST['image_count']);
            $if_active = intval($_POST['if_active']);
            $icon = secure_html($_POST['icon']);
            $description = trim($_POST['description']);
            $keywords = trim(secure_html($_POST['keywords']));

            $t_update = '';
            if ($title != $gallery['title']) 
                $t_update .= (($t_update?', ':'').'`title`='.db_squote($title));
            if ($skin != $gallery['skin']) 
                $t_update .= (($t_update?', ':'').'`skin`='.db_squote($skin));
            if ($image_count != $gallery['image_count']) 
                $t_update .= (($t_update?', ':'').'`image_count`='.db_squote($image_count));
            if ($if_active != $gallery['if_active']) 
                $t_update .= (($t_update?', ':'').'`if_active`='.db_squote($if_active));
            if ($icon != $gallery['icon']) 
                $t_update .= (($t_update?', ':'').'`icon`='.db_squote($icon));
            if ($description != $gallery['description']) 
                $t_update .= (($t_update?', ':'').'`description`='.db_squote($description));
            if ($keywords != $gallery['keywords']) 
                $t_update .= (($t_update?', ':'').'`keywords`='.db_squote($keywords));
            
            if ($t_update){
                $mysql->query('update '.prefix.'_gallery set '.$t_update.' where id = '.db_squote($id).' limit 1');
                msg(array('message' => __('gallery:info_update_record')));
            } else {
                msg(array('type' => 'info', 'message' => 'Изменений нет'));
            }

        } elseif ('move_up' == $action or 'move_down' == $action) {

            if (!isset($_REQUEST['id'])) {
                msg(array('type' => 'danger', 'message' => __('gallery:msg.no_gallery')));
                break;
            }
            $id = intval($_REQUEST['id']);

            $gallery = $mysql->record('select id, position from '.prefix.'_gallery where `id`='.db_squote($id).' limit 1');
            if (!$gallery) {
                msg(array('type' => 'danger', 'message' => __('gallery:msg.no_gallery')));
                break;
            }
            $count = 0;
            if (is_array($pcnt = $mysql->record('select count(*) as cnt from '.prefix.'_gallery')))
                $count = $pcnt['cnt'];

            if ($action == 'move_up') {
                if ($gallery['position'] == 1) {
                    msg(array('type' => 'danger', 'message' => __('gallery:info_update_record')));
                    break;
                }

                $gallery2 = $mysql->record('select id, position from '.prefix.'_gallery where position='.db_squote($gallery['position'] - 1).' limit 1');

                $mysql->query('update '.prefix.'_gallery set position='.db_squote($gallery['position']).'where `id`='.db_squote($gallery2['id']).' limit 1');
                $mysql->query('update '.prefix.'_gallery set position='.db_squote($gallery2['position']).'where `id`='.db_squote($gallery['id']).' limit 1');
            } elseif ($action == 'move_down') {
                if ($gallery['position'] == $count) {
                    msg(array('type' => 'danger', 'message' => __('gallery:info_update_record')));
                    break;
                }

                $gallery2 = $mysql->record('select id, position from '.prefix.'_gallery where position='.db_squote($gallery['position'] + 1).' limit 1');

                $mysql->query('update '.prefix.'_gallery set position='.db_squote($gallery['position']).'where `id`='.db_squote($gallery2['id']).' limit 1');
                $mysql->query('update '.prefix.'_gallery set position='.db_squote($gallery2['position']).'where `id`='.db_squote($gallery['id']).' limit 1');
            }
            msg(array('type' => 'info', 'message' => __('gallery:info_update_record')));

        } elseif ('dell' == $action) {
            if (!isset($_REQUEST['id'])) {
                msg(array('type' => 'danger', 'message' => __('gallery:msg.no_gallery')));
                break;
            }
            $id = intval($_REQUEST['id']);
            $gallery = $mysql->record('select `title` from '.prefix.'_gallery where `id`='.db_squote($id).' limit 1');
            if (!$gallery) {
                msg(array('type' => 'danger', 'message' => __('gallery:msg.no_gallery')));
                break;
            }
            $mysql->query('delete from '.prefix.'_gallery where `id`='.db_squote($id));
            $next_order = 1;
            foreach($mysql->select('select id from '.prefix.'_gallery order by position') as $row) {
                $dir = opendir(images_dir);
                $mysql->query('update '.prefix.'_gallery set position='.db_squote($next_order).'where `id`='.db_squote($row['id']).' limit 1');
                $next_order ++;
            }
            msg(array('type' => 'info', 'message' => __('gallery:info_delete')));
        }

        if ('list' != $action and pluginGetVariable('gallery', 'cache')) {
            clearCacheFiles($plugin);}

    } while (0);

    $items = array();
    $rows = $mysql->select('select * from '.prefix.'_gallery order by position');
    foreach($rows as $row) {
        // Prepare data for template
        $items[] = [
            'isActive' => $row['if_active'],
            'id' => $row['id'],
            'name' => $row['name'],
            'title' => $row['title'],
            'url' => generatePluginLink('gallery', 'gallery', array('id' => $row['id'], 'name' => $row['name'])),
            'skin' => $row['skin'],
        ];
    }
    $tVars['items'] = $items;
    $tpath = locatePluginTemplates(array('gallery.list'), 'gallery', 1, '', 'admin');
    array_push($cfg, array(
            'type' => 'flat',
            'input' => $twig->loadTemplate($tpath['gallery.list'] . 'gallery.list.tpl')->render($tVars)
            ));
    generate_config_page($plugin, $cfg);
}

function edit($plugin, $action)
{
    global $mysql;

    // Load CORE Plugin
    $cPlugin = CPlugin::instance();

    // Prepare configuration parameters
    if (!$skList = $cPlugin->getFoldersSkin($plugin)) {
        $skList = [];
        msg(array( 'type' => 'danger', 'message' => __('gallery:msg.no_skin')));
    }
    if (!isset($_REQUEST['id'])) {
        msg(array('type' => 'danger', 'message' => __('gallery:msg.no_gallery')));
        return;
    }
    $id = intval($_REQUEST['id']);
    $gallery = $mysql->record('select * from '.prefix.'_gallery where `id`='.db_squote($id).' limit 1');
    if (!$gallery) {
        msg(array('type' => 'danger', 'message' => __('gallery:msg.no_gallery')));
        return;
    }
    $icon_list = array();
    foreach($mysql->select('select name from '.prefix.'_images where folder='.db_squote($gallery['name'])) as $row)
        $icon_list[$row['name']] = $row['name'];

    // Fill configuration parameters
    $cfg = array(
        'description' => __('gallery:description'),
        'navigation' => array(
            array('href' => 'admin.php?mod=extra-config&plugin=gallery','title' => __('group.config')),
            array('class' => 'active','href' => 'admin.php?mod=extra-config&plugin=gallery&action=list','title' => __('gallery:button_list')),
            array('href' => 'admin.php?mod=extra-config&plugin=gallery&action=widget_list','title' => __('gallery:button_widget_list')),
        ),
        'action' => 'admin.php?mod=extra-config&plugin=gallery&action=edit_submit', // !!!
        'submit' => array(array('type' => 'default')),
        array(
            'name' => 'id',
            'type' => 'hidden',
            'value' => $id,
            )
    );

    $cfgX = array();
        array_push($cfgX, array(
            'name' => 'name',
            'title' => __('gallery:label_name'),
            'descr' => __('gallery:desc_name'),
            'type' => 'input',
            'html_flags' => 'readonly',
            'value' => $gallery['name'],
            ));
        array_push($cfgX, array(
            'name' => 'title',
            'title' => __('gallery:label_title'),
            'descr' => __('gallery:desc_title'),
            'type' => 'input',
            'value' => $gallery['title'],
            ));
        array_push($cfgX, array(
            'name' => 'if_active',
            'title' => __('gallery:label_if_active'),
            'descr' => __('gallery:desc_if_active'),
            'type' => 'select',
            'values' => array('1' => __('yesa'), '0' => __('noa')),
            'value' => $gallery['if_active'],
            ));
    array_push($cfg, array(
        'mode' => 'group',
        'title' => __('gallery:legend_general'),
        'entries' => $cfgX,
        ));

    $cfgX = array();
        array_push($cfgX, array(
            'name' => 'icon',
            'title' => __('gallery:label_icon'),
            'descr' => __('gallery:desc_icon'),
            'type' => 'select',
            'values' => $icon_list,
            'value' => $gallery['icon'],
            ));
        array_push($cfgX, array(
            'name' => 'skin',
            'title' => __('gallery:label_skin'),
            'descr' => __('gallery:desc_skin'),
            'type' => 'select',
            'values' => $skList,
            'value' => $gallery['skin'],
            ));
        array_push($cfgX, array(
            'name' => 'image_count',
            'title' => __('gallery:label_image_count_gallery'),
            'descr' => __('gallery:desc_image_count_gallery'),
            'type' => 'input',
            'value' => $gallery['image_count'],
            ));
    array_push($cfg, array(
        'mode' => 'group',
        'title' => __('gallery:legend_gallery_one'),
        'entries' => $cfgX,
        ));

    $cfgX = array();
        array_push($cfgX, array(
            'name' => 'description',
            'title' => __('gallery:label_description'),
            'descr' => __('gallery:desc_description'),
            'type' => 'text',
            'html_flags' => 'rows="3"',
            'value' => $gallery['description'],
            ));
        array_push($cfgX, array(
            'name' => 'keywords',
            'title' => __('gallery:label_keywords'),
            'descr' => __('gallery:desc_keywords'),
            'type' => 'text',
            'html_flags' => 'rows="3"',
            'value' => $gallery['keywords'],
            ));
    array_push($cfg, array(
        'mode' => 'group',
        'title' => __('gallery:legend_description'),
        'entries' => $cfgX,
        ));

    generate_config_page($plugin, $cfg);
}

function showWidgetList($plugin, $action)
{
    global $twig, $mysql, $parse;

    // Fill configuration parameters
    $cfg = array(
        'description' => __('gallery:description'),
        'navigation' => array(
            array('href' => 'admin.php?mod=extra-config&plugin=gallery','title' => __('group.config')),
            array('href' => 'admin.php?mod=extra-config&plugin=gallery&action=list','title' => __('gallery:button_list')),
            array('class' => 'active','href' => 'admin.php?mod=extra-config&plugin=gallery&action=widget_list','title' => __('gallery:button_widget_list')),
        ),
        'submit' => array(
            array('class' => 'btn btn-primary','href' => 'admin.php?mod=extra-config&plugin=gallery&action=widget_add','title' => __('gallery:button_widget_add')),
            )
    );

    // RUN
    do {
        if ('widget_edit_submit' == $action) {
            if (empty($_POST['name']) or empty($_POST['title']) or empty($_POST['if_active'])  or empty($_POST['skin']) or empty($_POST['image_count']) or empty($_POST['if_rand'])) {
                msg(array('type' => 'danger', 'message' => 'Не все параметры заданы' . '<br><a href="#" onClick="history.back(1);" class="alert-link">Вернуться назад</a>'));
                break;
            }

            $widgets = pluginGetVariable('gallery', 'widgets');

            $id = isset($_POST['id']) ? intval($_POST['id']) : count($widgets);
            $name = $parse->translit(trim($_POST['name']), 1);
            $title = secure_html($_POST['title']);
            $if_active = intval($_POST['if_active']);
            $skin = secure_html(trim($_POST['skin']));
            $image_count = intval($_POST['image_count']);
            $if_rand = intval($_POST['if_rand']);
            $gallery = secure_html($_POST['gallery']);

            $widgets[$id]['name'] = $name;
            $widgets[$id]['title'] = $title;
            $widgets[$id]['if_active'] = $if_active;
            $widgets[$id]['skin'] = $skin;
            $widgets[$id]['image_count'] = $image_count;
            $widgets[$id]['if_rand'] = $if_rand;
            $widgets[$id]['gallery'] = $gallery;

            pluginSetVariable('gallery', 'widgets', $widgets);
            // Load CORE Plugin
            $cPlugin = CPlugin::instance();
            // Save configuration parameters of plugins
            $cPlugin->saveConfig();

            msg(array('message' => __('gallery:info_update_record')));
        } elseif ('widget_dell' == $action) {
            if (!isset($_REQUEST['id'])) {
                msg(array('type' => 'danger', 'message' => __('gallery:msg.no_widget')));
                break;
            }
            $id = intval($_REQUEST['id']);
            $widgets = pluginGetVariable('gallery', 'widgets');
            if (empty($widgets[$id])){
                msg(array('type' => 'danger', 'message' => __('gallery:msg.no_widget')));
                break;
            }
            if (isset($widgets[$id])){
                unset($widgets[$id]);
                pluginSetVariable('gallery', 'widgets', $widgets);
                // Load CORE Plugin
                $cPlugin = CPlugin::instance();
                // Save configuration parameters of plugins
                $cPlugin->saveConfig();
            }
            msg(array('type' => 'info', 'message' => __('gallery:info_delete')));
        }

        if ('widget_list' != $action and pluginGetVariable('gallery', 'cache')) {
            clearCacheFiles($plugin);}

    } while (0);

    $items = array();
    if (is_array($widgets = pluginGetVariable('gallery', 'widgets'))){
        foreach($widgets as $id=>$row) {
            // Prepare data for template
            $items[] = [
                'isActive' => $row['if_active'],
                'id' => $id,
                'name' => $row['name'],
                'title' => $row['title'],
                'gallery' => $row['gallery'],
                'skin' => $row['skin'],
                'rand' => $row['if_rand'] ? __('gallery:label_yes') : __('gallery:label_no'),
            ];
        }
    }
    $tVars['items'] = $items;
    $tpath = locatePluginTemplates(array('widget.list'), 'gallery', 1, '', 'admin');
    array_push($cfg, array(
            'type' => 'flat',
            'input' => $twig->loadTemplate($tpath['widget.list'] . 'widget.list.tpl')->render($tVars)
            ));
    generate_config_page($plugin, $cfg);
}

function editWidget($plugin, $action)
{
    global $tpl;

    // Load CORE Plugin
    $cPlugin = CPlugin::instance();

    // Prepare configuration parameters
    if (!$skList = $cPlugin->getFoldersSkin($plugin)) {
        $skList = [];
        msg(array( 'type' => 'danger', 'message' => __('gallery:msg.no_skin')));
    }

    $id = -1;
    $if_active = 1;
    $name = '';
    $title = '';
    $skin = '';
    $image_count = 12;
    $if_rand = 0;
    $gallery = '';
    if (isset($_REQUEST['id'])){
        $id = intval($_REQUEST['id']);
        $widgets = pluginGetVariable('gallery', 'widgets');
        if (empty($widgets[$id])) {
            $id = -1;
        } else {
            $if_active = $widgets[$id]['if_active'];
            $name = $widgets[$id]['name'];
            $title = $widgets[$id]['title'];
            $skin = $widgets[$id]['skin'];
            $image_count = $widgets[$id]['image_count'];
            $if_rand = $widgets[$id]['if_rand'];
            $gallery = $widgets[$id]['gallery'];
        }
    }

    // Fill configuration parameters
    $cfg = array(
        'description' => __('gallery:description'),
        'navigation' => array(
            array('href' => 'admin.php?mod=extra-config&plugin=gallery','title' => __('group.config')),
            array('href' => 'admin.php?mod=extra-config&plugin=gallery&action=list','title' => __('gallery:button_list')),
            array('class' => 'active','href' => 'admin.php?mod=extra-config&plugin=gallery&action=widget_list','title' => __('gallery:button_widget_list')),
        ),
        'action' => 'admin.php?mod=extra-config&plugin=gallery&action=widget_edit_submit', // !!!
        'submit' => array(array('type' => 'default')),
        array(
            'name' => 'id',
            'type' => 'hidden',
            'value' => $id,
            )
    );

    $cfgX = array();
        array_push($cfgX, array(
            'name' => 'name',
            'title' => __('gallery:label_widget_name'),
            'descr' => __('gallery:desc_widget_name') . '<br><code>{{ plugin_gallery_ID }}</code>',
            'type' => 'input',
            'value' => $name,
            ));
        array_push($cfgX, array(
            'name' => 'title',
            'title' => __('gallery:label_widget_title'),
            'descr' => __('gallery:desc_widget_title'),
            'type' => 'input',
            'value' => $title,
            ));
        array_push($cfgX, array(
            'name' => 'if_active',
            'title' => __('gallery:label_widget_if_active'),
            'descr' => __('gallery:desc_widget_if_active'),
            'type' => 'select',
            'values' => array('1' => __('yesa'), '0' => __('noa')),
            'value' => $if_active,
            ));
        array_push($cfgX, array(
            'name' => 'gallery',
            'title' => __('gallery:label_gallery'),
            'descr' => __('gallery:desc_gallery'),
            'type' => 'input',
            'value' => $gallery,
            ));
    array_push($cfg, array(
        'mode' => 'group',
        'title' => __('gallery:legend_general'),
        'entries' => $cfgX,
        ));

    $cfgX = array();
        array_push($cfgX, array(
            'name' => 'skin',
            'title' => __('gallery:label_skin'),
            'descr' => __('gallery:desc_skin'),
            'type' => 'select',
            'values' => $skList,
            'value' => $skin,
            ));
        array_push($cfgX, array(
            'name' => 'image_count',
            'title' => __('gallery:label_image_count_widget'),
            'descr' => __('gallery:desc_image_count_widget'),
            'type' => 'input',
            'value' => $image_count,
            ));
        array_push($cfgX, array(
            'name' => 'if_rand',
            'title' => 'Сортировка',
            'descr' => 'Порядок вывода изображений',
            'type' => 'select',
            'values' => array(0 => 'по умолчанию', 1 => 'случайно', 2 => 'просмотры', 3 => 'комментарии'),
            'value' => $if_rand,
            ));
    array_push($cfg, array(
        'mode' => 'group',
        'title' => __('gallery:legend_widget_one'),
        'entries' => $cfgX,
        ));

    generate_config_page($plugin, $cfg);
}

function GetKeyFromName($name, $array)
{
    $count = count($array);
    for ($i = 0; $i < $count; $i ++)
        if ($array[$i]['name'] == $name)
            return $i;
    return false;
}
