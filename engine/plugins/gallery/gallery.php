<?php

// Protect against hack attempts
if (!defined('NGCMS')) die ('HAL');

Lang::loadPlugin('gallery', 'main', '', ':');
Lang::loadPlugin('comments', 'main', '', ':');

function plugin_gallery_category()
{
    global $twig, $mysql, $template;

    if (pluginGetVariable('gallery', 'cache')) {
        $cacheFileName = md5('gallery' . 'category') . '.txt';
        $cacheData = cacheRetrieveFile($cacheFileName, pluginGetVariable('gallery', 'cacheExpire'), 'gallery');
        if ($cacheData != false) {
            return $template['vars']['plugin_gallery_category'] = $cacheData;
        }
    }

    $galleries = [];
    $rows = $mysql->select('SELECT id, name, title, icon, (SELECT count(*) FROM '.prefix.'_images where folder='.prefix.'_gallery.name) as count FROM '.prefix.'_gallery WHERE if_active=1 ORDER BY position');
    foreach($rows as $row) {
        $id = (int)$row['id'];
        $name = $folder = secure_html($row['name']);
        $icon = secure_html($row['icon']);
        $galleries[] = [
            'id' => $id,
            'name' => $name,
            'title' => secure_html($row['title']),
            'count' => $row['count'],
            'url' => generatePluginLink('gallery', 'gallery', array('id' => $id, 'name' => $name)),
            'src_thumb' => file_exists(images_dir . '/' . $folder . '/thumb/' . $icon)
                ? images_url . '/' . $folder . '/thumb/' . $icon
                : images_url . '/' . $folder . '/' . $icon,
        ];
    }

    $tpath = locatePluginTemplates(array('category'), 'gallery', pluginGetVariable('gallery', 'localSource'), pluginGetVariable('gallery', 'localSkin'));
    $tVars = [
        'url_tpl' => $tpath['url:category'],
        'url_main' => generatePluginLink('gallery', null),
        'galleries' => $galleries,
        ];
    $template['vars']['plugin_gallery_category'] = $twig->loadTemplate($tpath['category'] . 'category.tpl')->render($tVars);

    if (pluginGetVariable('gallery', 'cache')) {
        cacheStoreFile($cacheFileName, $template['vars']['plugin_gallery_category'], 'gallery');
    }
}

function plugin_gallery_main($params = [])
{
    global $userROW, $template, $twig, $mysql, $TemplateCache, $SYSTEM_FLAGS;

    $page = isset($params['page']) ? intval($params['page']) : 1;
    if ($page < 1) $page = 1;

    $SYSTEM_FLAGS['info']['title']['group'] = __('gallery:title');
    $SYSTEM_FLAGS['info']['title']['item'] = __('gallery:page').' ' . $page;
    $SYSTEM_FLAGS['info']['breadcrumbs'] = array(array('link' => generatePluginLink('gallery', null),'text' => __('gallery:title')),);

    if (pluginGetVariable('gallery', 'cache')) {
        $cacheFileName = md5('gallery'.'mainblock' . $page) . '.txt';
        $cacheData = cacheRetrieveFile($cacheFileName, pluginGetVariable('gallery', 'cacheExpire'), 'gallery');
        if ($cacheData != false) {
            return $template['vars']['mainblock'] = $cacheData;
        }
    }

    $galleries = [];
    $limit = 'limit ' . ($page - 1) * pluginGetVariable('gallery', 'image_count') .', ' . pluginGetVariable('gallery', 'image_count');
    $rows = $mysql->select('SELECT *, (SELECT count(*) FROM '.prefix.'_images WHERE folder='.prefix.'_gallery.name) AS count FROM '.prefix.'_gallery WHERE if_active=1 ORDER BY position '.$limit);
    foreach($rows as $row) {
        $id = (int)$row['id'];
        $name = $folder = secure_html($row['name']);
        $icon = secure_html($row['icon']);
        $galleries[] = [
            'id' => $id,
            'name' => $name,
            'title' => secure_html($row['title']),
            'url' => generatePluginLink('gallery', 'gallery', array('id' => $id, 'name' => $name)),
            'count' => $row['count'],
            'description' => secure_html($row['description']),
            'keywords' => secure_html($row['keywords']),
            'src' => images_url . '/' . $folder . '/' . $icon,
            'src_thumb' => file_exists(images_dir . '/' . $folder . '/thumb/' . $icon)
                ? images_url . '/' . $folder . '/thumb/' . $icon
                : images_url . '/' . $folder . '/' . $icon,
        ];
    }

    $pagesss = '';
    if (pluginGetVariable('gallery', 'image_count')) {
        $count = 0;
        if (is_array($pcnt = $mysql->record('SELECT count(*) AS cnt FROM '.prefix.'_gallery WHERE if_active=1')))
            $count = $pcnt['cnt'];
        $page_count = ceil($count / pluginGetVariable('gallery', 'image_count'));
        if ($page_count > 1) {
            $paginationParams = array('pluginName' => 'gallery', 'pluginHandler' => '', 'params' => array(), 'xparams' => array(), 'paginator' => array('page', 0, false));
            templateLoadVariables(true); 
            $navigations = $TemplateCache['site']['#variables']['navigation'];
            $pagesss .= ($page > 1)?str_replace('%page%', __('gallery:paginator.prev'),str_replace('%link%',generatePageLink($paginationParams, $page - 1), $navigations['prevlink'])):'';
            $pagesss .= generatePagination($page, 1, $page_count, 10, $paginationParams, $navigations);
            $pagesss .= ($page < $page_count)?str_replace('%page%', __('gallery:paginator.next'),str_replace('%link%',generatePageLink($paginationParams, $page + 1), $navigations['nextlink'])):'';
        }
    }
    
    $tpath = locatePluginTemplates(array('page_main'), 'gallery', pluginGetVariable('gallery', 'localSource'), pluginGetVariable('gallery', 'localSkin'));
    $tVars = [
        'url_tpl' => $tpath['url:page_main'],
        'url_main' => generatePluginLink('gallery', null),
        'galleries' => $galleries,
        'pagesss' => $pagesss,
        ];

    $template['vars']['mainblock'] = $output = $twig->loadTemplate($tpath['page_main'] . 'page_main.tpl')->render($tVars);

    if (pluginGetVariable('gallery', 'cache')){
        cacheStoreFile($cacheFileName, $output, 'gallery');
    }
}

function plugin_gallery_gallery($params = [])
{
    global $userROW, $template, $twig, $mysql, $TemplateCache, $SYSTEM_FLAGS;

    $page = isset($params['page']) ? intval($params['page']) : 1;
    if ($page < 1) $page = 1;
    $gallery['name'] = isset($params['name']) ? secure_html($params['name']) : (isset($_REQUEST['name']) ? secure_html($_REQUEST['name']): false);

    if (!$gallery['name']) {
        msg(array('type' => 'danger', 'message' => 'Не все параметры заданы'));
        return false;
    }

    $gallery = $mysql->record('SELECT * FROM '.prefix.'_gallery WHERE name=' . db_squote($gallery['name']) . ' LIMIT 1');
    if (!is_array($gallery)) {
        msg(array('type' => 'danger', 'message' => 'Не все параметры заданы'));
        return false;
    }

    $gallery['id'] = (int)$gallery['id'];
    $gallery['name'] = secure_html($gallery['name']); // Reload name of gallery
    $gallery['title'] = secure_html($gallery['title']);
    $gallery['description'] = secure_html($gallery['description']);
    $gallery['keywords'] = secure_html($gallery['keywords']);

    $SYSTEM_FLAGS['info']['title']['group'] = __('gallery:title').' '. $gallery['title'];
    $SYSTEM_FLAGS['info']['title']['item'] = __('gallery:page').' ' . $page;
    if (pluginGetVariable('gallery', 'if_description')) $SYSTEM_FLAGS['meta']['description'] = $gallery['description'];
    if (pluginGetVariable('gallery', 'if_keywords')) $SYSTEM_FLAGS['meta']['keywords'] = $gallery['keywords'];
    $SYSTEM_FLAGS['info']['breadcrumbs'] = array(
            array('link' => generatePluginLink('gallery', null),'text' => __('gallery:title'),),
            array('link' => generatePluginLink('gallery', 'gallery', array('id' => $gallery['id'], 'name' => $gallery['name'])),'text' => $gallery['title']),
        );

    if (pluginGetVariable('gallery', 'cache')) {
        $cacheFileName = md5('gallery' . $gallery['id'] . $gallery['name'] . $page) . '.txt';
        $cacheData = cacheRetrieveFile($cacheFileName, pluginGetVariable('gallery', 'cacheExpire'), 'gallery');
        if ($cacheData != false) {
            return $template['vars']['mainblock'] = $cacheData;
        }
    }

    $images = [];
    $limit = 'LIMIT ' . ($page - 1) * $gallery['image_count'] .', ' . $gallery['image_count'];
    $rows = $mysql->select('SELECT id, name, description, folder, views, com FROM '.prefix.'_images WHERE folder='.db_squote($gallery['name']).' ORDER BY date asc, id asc '.$limit);
    foreach($rows as $row) {
        $id = (int)$row['id'];
        $name = secure_html($row['name']);
        $folder = secure_html($row['folder']);
        $images[] = [
            'id' => $id,
            'name' => $name,
            'com' => (int)$row['com'],
            'views' => (int)$row['views'],
            'description' => secure_html($row['description']),
            'url' => generatePluginLink('gallery', 'image', array('gallery' => $folder,'id' => $id,'name' => $name)),
            'src' => images_url . '/' . $folder . '/' . $name,
            'src_thumb' => file_exists(images_url . '/' . $folder . '/thumb/' . $name)
                ? images_url . '/' . $folder . '/thumb/' . $folder
                : images_url . '/' . $folder . '/' . $name,
        ];
    }

    $pagesss = '';
    if ($gallery['image_count']) {
        $count = 0;
        if (is_array($pcnt = $mysql->record('select count(*) as cnt from '.prefix.'_images where folder='.db_squote($gallery['name']))))
            $count = $pcnt['cnt'];
        $page_count = ceil($count / $gallery['image_count']);
        if ($page_count > 1) {
            $paginationParams = array('pluginName' => 'gallery', 'pluginHandler' => 'gallery', 'params' => array('id' => $gallery['id'], 'name' => $gallery['name']), 'xparams' => array(), 'paginator' => array('page', 0, false));
            templateLoadVariables(true); 
            $navigations = $TemplateCache['site']['#variables']['navigation'];
            $pagesss .= ($page > 1)?str_replace('%page%', __('gallery:paginator.prev'),str_replace('%link%',generatePageLink($paginationParams, $page - 1), $navigations['prevlink'])):'';
            $pagesss .= generatePagination($page, 1, $page_count, 10, $paginationParams, $navigations);
            $pagesss .= ($page < $page_count)?str_replace('%page%', __('gallery:paginator.next'),str_replace('%link%',generatePageLink($paginationParams, $page + 1), $navigations['nextlink'])):'';
        }
    }

    $tpath = locatePluginTemplates(array('page_gallery'), 'gallery', pluginGetVariable('gallery', 'localSource'), $gallery['skin']);
    $tVars = [
        'url_tpl' => $tpath['url:page_gallery'],
        'url_main' => generatePluginLink('gallery', null),
        'images' => $images,
        'gallery' => [
            'url' => generatePluginLink('gallery', 'gallery', array('id' => $gallery['id'], 'name' => $gallery['name'])),
            'title' => $gallery['title'],
            'description' => $gallery['description'],
            'keywords' => $gallery['keywords'],
            ],
        'pagesss' => $pagesss,
        ];

    $template['vars']['mainblock'] = $output = $twig->loadTemplate($tpath['page_gallery'] . 'page_gallery.tpl')->render($tVars);
    if (pluginGetVariable('gallery', 'cache')){
        cacheStoreFile($cacheFileName, $output, 'gallery');
    }
}

function plugin_gallery_image($params = [])
{
    global $userROW, $template, $tpl, $twig, $mysql, $TemplateCache, $SYSTEM_FLAGS;

    // Load CORE Plugin
    $cPlugin = CPlugin::instance();

    $imageName = !empty($params['name']) ? secure_html($params['name']) : false;
    $gallery['name'] = !empty($params['gallery']) ? secure_html($params['gallery']) : false;

    if (!$imageName or !$gallery['name']) {
        error404();
        return false;
    }

    $gallery = $mysql->record('SELECT * from '.prefix.'_gallery WHERE name=' . db_squote($gallery['name']) . ' limit 1');

    if (!is_array($gallery)) {
        error404();
        return false;
    }

    $gallery['id'] = (int)$gallery['id'];
    $gallery['name'] = secure_html($gallery['name']); // Reload name of gallery
    $gallery['title'] = secure_html($gallery['title']);
    $gallery['description'] = secure_html($gallery['description']);
    $gallery['keywords'] = secure_html($gallery['keywords']);

    $SYSTEM_FLAGS['info']['title']['group'] = __('gallery:title').' '. $gallery['title'];
    $SYSTEM_FLAGS['info']['title']['item'] = $imageName;
    if (pluginGetVariable('gallery', 'if_keywords')) $SYSTEM_FLAGS['meta']['keywords'] = $gallery['keywords'];
    if (pluginGetVariable('gallery', 'if_description')) $SYSTEM_FLAGS['meta']['description'] = $gallery['description'];
    $SYSTEM_FLAGS['info']['breadcrumbs'] = array(
        array('link' => generatePluginLink('gallery', null),'text' => __('gallery:title')),
        array('link' => generatePluginLink('gallery', 'gallery', array('id' => $gallery['id'], 'name' => $gallery['name'])),'text' => $gallery['title']),
        array('link' => generatePluginLink('gallery', 'image', array('gallery' => $gallery['name'], 'name' => $imageName)),'text' => $imageName),
        );

    // Need to update count views
    $mysql->query('UPDATE '.prefix.'_images SET views=views+1 WHERE name='.db_squote($imageName));

    if (pluginGetVariable('gallery', 'cache')) {
        $cacheFileName = md5('gallery' . $gallery['id'] . $gallery['name'] . $imageName) . '.txt';
        $cacheData = cacheRetrieveFile($cacheFileName, pluginGetVariable('gallery', 'cacheExpire'), 'gallery');
        if ($cacheData != false) {
            return $template['vars']['mainblock'] = $cacheData;
        }
    }

    $row = $mysql->record('SELECT * FROM '.prefix.'_images WHERE folder='.db_squote($gallery['name']).' and name='.db_squote($imageName).' ORDER BY date LIMIT 1');

    $row['id'] = (int)$row['id'];
    $row['com'] = (int)$row['com'];
    $row['views'] = (int)$row['views'];
    $row['width'] = (int)$row['width'];
    $row['height'] = (int)$row['height'];
    $row['name'] = secure_html($row['name']);
    $row['description'] = secure_html($row['description']);


    // Reload meta-description of page
    if (pluginGetVariable('gallery', 'if_description')) $SYSTEM_FLAGS['meta']['description'] = $row['description'];

    // Prepare date to generate output Prev and Next
    templateLoadVariables(true); 
    $nav = $TemplateCache['site']['#variables']['navigation'];

    // Prev image, if isset
    $pimage = $mysql->select('SELECT name FROM '.prefix.'_images WHERE folder='.db_squote($gallery['name']).' AND id<'.db_squote($row['id']).' order by `id` desc limit 1');
    if (isset($pimage[0])) {
        $imageName = secure_html($pimage[0]['name']);
        $paginationParams = array(
            'pluginName' => 'gallery','pluginHandler' => 'image',
            'params' => array('id' => $gallery['id'],'gallery' => $gallery['name'],'name' => $imageName),'xparams' => array(),
            'paginator' => array('page', 0, false));
        $prevlink = str_replace('%page%', __('gallery:prevlink'), str_replace('%link%', generatePageLink($paginationParams, 0), $nav['prevlink']));
    } else {
        $prevlink = '';
    }

    // Next image, if isset
    $nimage = $mysql->select('select name from '.prefix.'_images where folder='.db_squote($gallery['name']).' and id>'.db_squote($row['id']).' order by `id` asc limit 1');
    if (isset($nimage[0])) {
        $imageName = secure_html($nimage[0]['name']);
        $paginationParams = array(
            'pluginName' => 'gallery','pluginHandler' => 'image',
            'params' => array('galleryID' => $gallery['id'],'gallery' => $gallery['name'],'name' => $imageName),'xparams' => array(),
            'paginator' => array('page', 0, false));
        $nextlink = str_replace('%page%', __('gallery:nextlink'), str_replace('%link%', generatePageLink($paginationParams, 0), $nav['nextlink']));
    } else {
        $nextlink = '';
    }

    // Комментарии не тронуты
    // Вернуться и доделать. Эй, куда пошел
    if ($cPlugin->isActive('comments')) {
        // Prepare params for call
        // module - DB table images
        $callingCommentsParams = array('outprint' => true, 'total' => $row['com'], 'module' => 'images');

        include_once(root."/plugins/comments/inc/comments.show.php");

        $tcvars = array();
        // Show comments [ if not skipped ]
        $tcvars['vars']['entries'] = comments_show($row['id'], 0, 0, $callingCommentsParams);
        $tcvars['vars']['comnum'] = $row['com'];

        $tcvars['vars']['more_comments'] = '';
        $tcvars['regx']['#\[more_comments\](.*?)\[\/more_comments\]#is'] = '';

        // Show form for adding comments
        if (!pluginGetVariable('comments', 'regonly') or is_array($userROW)) {
            $tcvars['vars']['form'] = comments_showform($row['id'], $callingCommentsParams);
            $tcvars['regx']['#\[regonly\](.*?)\[\/regonly\]#is'] = '';
            $tcvars['regx']['#\[commforbidden\](.*?)\[\/commforbidden\]#is'] = '';
        } else {
            $tcvars['vars']['form'] = '';
            $tcvars['regx']['#\[regonly\](.*?)\[\/regonly\]#is'] = '$1';
            $tcvars['regx']['#\[commforbidden\](.*?)\[\/commforbidden\]#is'] = '';
        }
        $tcvars['regx']['#\[comheader\](.*)\[/comheader\]#is'] = ($row['com']) ? '$1' : '';

        $tPath = locatePluginTemplates('comments.internal', 'comments', pluginGetVariable('comments', 'localSource'));

        $tpl->template('comments.internal', $tPath['comments.internal']);
        $tpl->vars('comments.internal', $tcvars);
        //$tvars['vars']['plugin_comments'] = $tpl->show('comments.internal');
    }

    $tpath = locatePluginTemplates(array('page_image'), 'gallery', pluginGetVariable('gallery', 'localSource'), $gallery['skin']);
    $tVars = [
        'url_tpl' => $tpath['url:page_image'],
        'url_main' => generatePluginLink('gallery', null),
        'gallery' => [
            'url' => generatePluginLink('gallery', 'gallery', array('id' => $gallery['id'], 'name' => $gallery['name'])),
            'title' => $gallery['title'],
            'description' => $gallery['description'],
            'keywords' => $gallery['keywords'],
            ],
        'img' => [
            'name' => $row['name'],
            'src' => images_url . '/' . $gallery['name'] . '/' . $row['name'],
            'description' => $row['description'],
            'date' => Lang::retDate('j.m.Y - H:i', $row['date']),
            'dateStamp' => $row['date'],
            'width' => $row['width'],
            'height' => $row['height'],
            'com' => $row['com'],
            'views' => $row['views'],
            ],
        'nextlink' => $nextlink,
        'gallerylink' => str_replace('%page%', $gallery['title'], str_replace('%link%', generatePluginLink('gallery', 'gallery', array('name' => $gallery['name'])), $nav['link_page'])),
        'prevlink' => $prevlink,
        'plugin_comments' => $tpl->show('comments.internal'),
    ];

    $template['vars']['mainblock'] = $output = $twig->loadTemplate($tpath['page_image'] . 'page_image.tpl')->render($tVars);
    if (pluginGetVariable('gallery', 'cache')){
        cacheStoreFile($cacheFileName, $output, 'gallery');
    }
}

function plugin_gallery_widget($params = [])
{
    global $template, $twig, $mysql, $TemplateCache, $SYSTEM_FLAGS;

    if (!is_array($widgets = pluginGetVariable('gallery', 'widgets')))
        return;

    foreach($widgets as $id=>$widg){
        if (!$widg['if_active'])
            continue;

        if (pluginGetVariable('gallery', 'cache')) {
            $cacheFileName = md5('gallerywidget'.$id).'.txt';
            $cacheData = cacheRetrieveFile($cacheFileName, pluginGetVariable('gallery', 'cacheExpire'), 'gallery');
            if ($cacheData != false) {
                return $template['vars']['plugin_gallery_' . $widg['name']] = $cacheData;
            }
        }

        $where = '';
        if ($widg['gallery']){
            $t_gallery_array = explode(',', $widg['gallery']);
            $t_gallery = '';
            foreach($t_gallery_array as $gal){
                if ($t_gallery)
                    $t_gallery .= ', ';
                $t_gallery .= '\'' . trim($gal) . '\'';
            }
            $where .= ' where folder in (' . $t_gallery . ')';
        }

        $limit = 'limit '. $widg['image_count'];
        if ($widg['if_rand'] == 1){
            $image_key = $mysql->select('select id from '.prefix.'_images '.$where);
            if (count($image_key)){
                shuffle($image_key);
                if ($limit) $image_key = array_slice($image_key, 0, $widg['image_count']);
                $where .= ' and ';
                $t_key_list = '';
                foreach($image_key as $img){
                    if ($t_key_list)
                        $t_key_list .= ', ';
                    $t_key_list .= $img['id'];
                }
                $where .= ' id in ('.$t_key_list.')';
            }
        } elseif ($widg['if_rand'] == 2) {
            $where .= ' order by views desc';
        } elseif ($widg['if_rand'] == 3) {
            $where .= ' order by com desc';
        }

        $images = [];
        $rows = $mysql->select('SELECT id, name, folder, description, views, com FROM '.prefix.'_images ' . $where . ' ' . $limit);
        foreach($rows as $row) {
            $id = (int)$row['id'];
            $name = secure_html($row['name']);
            $folder = secure_html($row['folder']);
            $images[] = [
                'id' => $id,
                'title' => $name,
                'com' => (int)$row['com'],
                'views' => (int)$row['views'],
                'description' => secure_html($row['description']),
                'url' => generatePluginLink('gallery', 'image', array('gallery' => $folder,'id' => $id,'name' => $name)),
                'src' => file_exists(images_dir . '/' . $folder . '/' . $name)
                    ? images_url . '/' . $folder . '/' . $name
                    : '',
                'src_thumb' => file_exists(images_dir . '/' . $folder . '/thumb/' . $name)
                    ? images_url . '/' . $folder . '/thumb/' . $name
                    : images_url . '/' . $folder . '/' . $name,
            ];
        }

        $tpath = locatePluginTemplates(array('widget'), 'gallery', pluginGetVariable('gallery', 'localSource'), $widg['skin']);
        $tVars = [
            'url_tpl' => $tpath['url:widget'],
            'url_main' => generatePluginLink('gallery', null),
            'images' => $images,
            'widget_title' => $widg['title'],
            ];

        $template['vars']['plugin_gallery_' . $widg['name']] = $output = $twig->loadTemplate($tpath['widget'] . 'widget.tpl')->render($tVars);
        if (pluginGetVariable('gallery', 'cache')){
            cacheStoreFile($cacheFileName, $output, 'gallery');
        }
    }
}

register_plugin_page('gallery','','plugin_gallery_main');
register_plugin_page('gallery','gallery','plugin_gallery_gallery');
register_plugin_page('gallery','image','plugin_gallery_image');
register_plugin_page('gallery','widget','plugin_gallery_widget');
registerActionHandler('index', 'plugin_gallery_category');
registerActionHandler('index_post', 'plugin_gallery_widget', 1, 9999);
