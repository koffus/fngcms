<?php

//
// Copyright (C) 2006-2017 BixBite CMS (http://bixbite.site/)
// Name: libnews.php
// Description: News engine shared functions
// Author: Vitaly A Ponomarev, vp7@mail.ru
//

//
// Show one news
// Params (newsID or alt_name should be filled)
// $newsID - ID of the news
// $alt_name - alt name of the news
// $callingParams
//		'style'	 => mode for which we're called
//			* short		- short new display
//			* full		- full news display
//			* export	- export data [ for plugins or so on. No counters are updated ]
//			* exportVars	- export $tvars['vars'] array [ for plugins ... ]
//			* export_body	- export ONLY BODY short+full [ for plugins or so on... ]
//			* export_short	- export ONLY BODY short [ for plugins or so on... ]
//			* export_full	- export ONLY BODY full [ for plugins or so on... ]
// 	'emulate' => array with "fake" emulated row [ used for preview or so ... ]
//		'plugin' => if is called from plugin - ID of plugin
//		'overrideTemplateName' => alternative template for display
//		'overrideTemplatePath' => alternative path for searching of template
//		'customCategoryTemplate' => automatically override custom category templates
//		'setCurrentCategory' => update Current Category in system flags
//		'setCurrentNews' => update Current News in system flags
//		'addCanonicalLink' => if specified, rel="canonical" will be added into {htmlvars}
//		'validateCategoryID' => if specified, check if content represents correct category ID(s) for this news
//		'validateCategoryAlt' => if specified, check if content represents correct category altname(s) for this news
//		'extractImages'	- Extract embedded images/files from news body
//		Returns:
//			false - when news is not found
//			data - when news is found and export is used
//			news row - when news is found
function news_showone($newsID, $alt_name, $callingParams = array())
{
    global $mysql, $tpl, $userROW, $catz, $catmap, $config, $template, $parse, $SYSTEM_FLAGS, $PFILTERS, $EXTRA_HTML_VARS;
    global $year, $month, $day;

    $timer = MicroTimer::instance();

    // Calculate exec time
    $tX0 = $timer->stop(4);

    if (isset($callingParams['emulate']) and is_array($callingParams['emulate'])) {
        $row = $callingParams['emulate'];
        $callingParams['emulateMode'] = 1;
    } else {

        if ($newsID) {
            $filter = array('id=' . db_squote($newsID));
        } elseif ($alt_name) {
            $filter = array('alt_name=' . db_squote($alt_name));
        } else {
            return false;
        }

        if ($year) {
            array_push($filter, 'postdate >= ' . db_squote(mktime(0, 0, 0, $month ? $month : 1, $day ? $day : 1, $year)));
            array_push($filter, 'postdate <= ' . db_squote(mktime(23, 59, 59, $month ? $month : 12, $day ? $day : 31, $year)));
        }

        // Load news from DB
        if (!is_array($row = $mysql->record("select * from " . prefix . "_news where approve=1" . (count($filter) ? ' and ' . implode(" and ", $filter) : '')))) {
            error404();
            return false;
        }

        // Check if canonical link should be added
        if ($callingParams['addCanonicalLink']) {
            $EXTRA_HTML_VARS [] = array('type' => 'plain', 'data' => '<link rel="canonical" href="' . News::generateLink($row, false, 0, true) . '"/>');
        }

        // Check if correct categories were specified [ only for SINGLE category display
        if ((isset($callingParams['validateCategoryID']) or isset($callingParams['validateCategoryAlt']) or 1) and $config['news_multicat_url']) {
            if (getIsSet($row['catid'])) {
                $nci = explode(',', $row['catid']);
                $nci = intval(array_shift($nci));
            }

            $nca = (getIsSet($nci)) ? $catmap[$nci] : 'none';

            if ((isset($callingParams['validateCategoryID']) and ($callingParams['validateCategoryID'] != $nci)) or (isset($callingParams['validateCategoryAlt']) and ($callingParams['validateCategoryAlt'] != $nca))) {
                $redirectURL = News::generateLink($row, false, 0, true);
                coreRedirectAndTerminate($redirectURL);
            }
        }

        // Fetch attached images/files (if any)
        if ($row['num_files']) {
            $row['#files'] = $mysql->select("select * from " . prefix . "_files where linked_ds = 1 and linked_id = " . db_squote($row['id']));
        } else {
            $row['#files'] = array();
        }

        if ($row['num_images']) {
            $row['#images'] = $mysql->select("select * from " . prefix . "_images where linked_ds = 1 and linked_id = " . db_squote($row['id']));
        } else {
            $row['#images'] = array();
        }

    }

    if (isset($callingParams['setCurrentCategory'])) {
        // Fetch category ID from news
        if (getIsSet($row['catid'])) {
            $nci = explode(',', $row['catid']);
            $nci = intval(array_shift($nci));
        }

        if (getIsSet($cid) and isset($catmap[$cid])) {
            // Save current category identifier
            $SYSTEM_FLAGS['news']['currentCategory.alt'] = $catz[$catmap[$cid]]['alt'];
            $SYSTEM_FLAGS['news']['currentCategory.id'] = $catz[$catmap[$cid]]['id'];
            $SYSTEM_FLAGS['news']['currentCategory.name'] = $catz[$catmap[$cid]]['name'];
            $SYSTEM_FLAGS['news']['currentCategory.record'] = $catz[$catmap[$cid]];

            $currentCategory = $catz[$catmap[$cid]];
        }
    }

    if (isset($callingParams['setCurrentNews'])) {
        // Save some significant news flags for plugin processing
        $SYSTEM_FLAGS['news']['db.id'] = $row['id'];
        $SYSTEM_FLAGS['news']['db.categories'] = array();
        foreach (explode(',', $row['catid']) as $cid) {
            if (isset($catmap[$cid]))
                array_push($SYSTEM_FLAGS['news']['db.categories'], intval($cid));
        }

        $SYSTEM_FLAGS['news']['db.alt'] = $row['alt_name'];
        $SYSTEM_FLAGS['news']['db.title'] = $row['title'];
        $SYSTEM_FLAGS['news']['db.record'] = $row;
    }

    // preload plugins
    loadActionHandlers('news:show');
    loadActionHandlers('news:show:one');
    loadActionHandlers('news_full');

    // Calculate exec time
    $tX1 = $timer->stop(4);

    // Execute filters
    if (isset($PFILTERS['news']) and is_array($PFILTERS['news']))
        foreach ($PFILTERS['news'] as $k => $v) {
            $v->showNewsPre($row['id'], $row, $callingParams);
            $timer->registerEvent('[FILTER] News->showNewsPre: call plugin [' . $k . ']');
        }

    $tX2 = $timer->stop(4);
    $tvars = News::fillVariables($row, 1, isset($_REQUEST['page']) ? intval($_REQUEST['page']) : 0, (substr($callingParams['style'], 0, 6) == 'export') ? 1 : 0);
    $tX3 = $timer->stop(4);
    $timer->registerEvent('call News::fillVariables() for [ ' . ($tX3 - $tX2) . ' ] sec');

    // Prepare list of linked files and images
    $callingParams['linkedFiles'] = array();
    $tvars['vars']['_files'] = array();
    if(isset($row['#files']) and is_array($row['#files']) and count($row['#files'])) {
        foreach ($row['#files'] as $k => $v) {
            if ($v['linked_id'] == $row['id']) {
                $callingParams['linkedFiles']['ids'] [] = $v['id'];
                $callingParams['linkedFiles']['data'] [] = $v;
                $tvars['vars']['_files'] [] = array(
                    'plugin' => $v['plugin'],
                    'pidentity' => $v['pidentity'],
                    'url' => ($v['storage'] ? $config['attach_url'] : $config['files_url']) . '/' . $v['folder'] . '/' . $v['name'],
                    'name' => $v['name'],
                    'origName' => secure_html($v['orig_name']),
                    'description' => secure_html($v['description']),
                    'size' => formatSize(filesize(($v['storage'] ? $config['attach_dir'] : $config['files_dir']) . '/' . $v['folder'] . '/' . $v['name'])),
                );
            }
        }
    }

    $callingParams['linkedImages'] = array();
    $tvars['vars']['_images'] = array();
    if(isset($row['#images']) and is_array($row['#images']) and count($row['#images'])) {
        foreach ($row['#images'] as $k => $v) {
            if ($v['linked_id'] == $row['id']) {
                $callingParams['linkedImages']['ids'] [] = $k;
                $callingParams['linkedImages']['data'] [] = $v;
                $tvars['vars']['_images'] [] = array(
                    'plugin' => $v['plugin'],
                    'pidentity' => $v['pidentity'],
                    'url' => ($v['storage'] ? $config['attach_url'] : $config['images_url']) . '/' . $v['folder'] . '/' . $v['name'],
                    'purl' => $v['preview'] ? (($v['storage'] ? $config['attach_url'] : $config['images_url']) . '/' . $v['folder'] . '/thumb/' . $v['name']) : null,
                    'width' => $v['width'],
                    'height' => $v['height'],
                    'pwidth' => $v['p_width'],
                    'pheight' => $v['p_height'],
                    'name' => $v['name'],
                    'origName' => secure_html($v['orig_name']),
                    'description' => secure_html($v['description']),
                    'size' => formatSize(filesize(($v['storage'] ? $config['attach_dir'] : $config['images_dir']) . '/' . $v['folder'] . '/' . $v['name'])),
                    'flags' => array(
                        'hasPreview' => $v['preview'],
                    ),
                );
            }
        }
    }

    // Show icon of `MAIN` category for current news
    $masterCatID = 0;
    if (getIsSet($row['catid'])) {
        $masterCatID = explode(',', $row['catid']);
        $masterCatID = intval(array_shift($masterCatID));
    }

    if ($masterCatID and isset($catmap[$masterCatID]) and trim($catz[$catmap[$masterCatID]]['icon'])) {
        $tvars['vars']['icon'] = trim($catz[$catmap[$masterCatID]]['icon']);
        $tvars['vars']['[icon]'] = '';
        $tvars['vars']['[/icon]'] = '';
    } else {
        $tvars['vars']['icon'] = '';
        $tvars['regx']["'\[icon\].*?\[/icon\]'si"] = '';
    }

    // Show edit/detele news buttons
    if (is_array($userROW) and ($row['author_id'] == $userROW['id'] or $userROW['status'] == '1' or $userROW['status'] == '2')) {
        $tvars['vars']['news']['flags']['canEdit'] = true;
        $tvars['vars']['news']['flags']['canDelete'] = true;
        $tvars['vars']['news']['url']['edit'] = admin_url . '/admin.php?mod=news&amp;action=edit&amp;id=' . $row['id'];
        $tvars['vars']['news']['url']['delete'] = admin_url . '/admin.php?mod=news&amp;action=manage&amp;subaction=mass_delete&amp;token=' . genUToken('admin.news.edit') . '&amp;selected_news[]=' . $row['id'];

        $tvars['vars']['[edit-news]'] = '<a href="' . admin_url . '/admin.php?mod=news&amp;action=edit&amp;id=' . $row['id'] . '" target="_blank">';
        $tvars['vars']['[/edit-news]'] = '</a>';
        $tvars['vars']['[del-news]'] = '<a onclick="confirmIt(\'' . admin_url . '/admin.php?mod=news&amp;subaction=do_mass_delete&amp;token=' . genUToken('admin.news.edit') . '&amp;selected_news[]=' . $row['id'] . '\', \'' . __('sure_del') . '\')" target="_blank" style="cursor: pointer;">';
        $tvars['vars']['[/del-news]'] = '</a>';
    } else {
        $tvars['regx']["'\\[edit-news\\].*?\\[/edit-news\\]'si"] = '';
        $tvars['regx']["'\\[del-news\\].*?\\[/del-news\\]'si"] = '';
    }

    $newsid = $row['id'];
    $row['views'] = $row['views'] + 1;

    // Extract embedded images/files if requested
    // news.embed.images	- list of URL's
    // news.embed.imgCount	- count of extracted URL's
    $tvars['vars']['news']['embed'] = array('images' => array());
    if (!empty($callingParams['extractImages'])) {
        $tvars['vars']['news']['embed']['images'] = $parse->extractImages($tvars['vars']['news']['short'] . $tvars['vars']['news']['full']);
    }
    $tvars['vars']['news']['embed']['imgCount'] = count($tvars['vars']['news']['embed']['images']);

    // Calculate exec time
    $tX1 = $timer->stop(4);

    // Execute filters
    if (isset($PFILTERS['news']) and is_array($PFILTERS['news'])) {
        foreach ($PFILTERS['news'] as $k => $v) {
            $timer->registerEvent('[FILTER] News->showNews: call plugin [' . $k . ']');
            $v->showNews($row['id'], $row, $tvars, $callingParams);
        }
    }

    $tX2 = $timer->stop(4);
    $timer->registerEvent('Show single news: full exec time [ ' . ($tX2 - $tX0) . ' ] sec');

    // Check if we need only to export body
    if ($callingParams['style'] == 'export_body')
        return $tvars['vars']['short-story'] . ' ' . $tvars['vars']['full-story'];

    if ($callingParams['style'] == 'export_short')
        return $tvars['vars']['short-story'];

    if ($callingParams['style'] == 'export_full')
        return $tvars['vars']['full-story'];

    if ($callingParams['style'] == 'exportVars')
        return $tvars['vars'];

    // Update visits counter if we're not in emulation mode
    if (empty($callingParams['emulate']) and ($callingParams['style'] == 'full') and (getIsSet($_REQUEST['page']) < 2)) {
        $cmode = intval($config['news_view_counters']);
        if ($cmode > 1) {
            // Delayed update of counters
            $mysql->query("insert into " . prefix . "_news_view (id, cnt) values (" . db_squote($row['id']) . ", 1) on duplicate key update cnt = cnt + 1");
        } else if ($cmode > 0) {
            $mysql->query("update " . prefix . "_news set views=views+1 where id = " . db_squote($row['id']));
        }
    }

    // Make temlate procession - auto/manual overriding
    //->calling style
    if (!$callingParams['style']) $callingParams['style'] = 'full';

    //->desired template - override template if needed
    if (getIsSet($callingParams['overrideTemplateName'])) {
        $templateName = $callingParams['overrideTemplateName'];
    } else {
        //->generate template name for selected style
        switch ($callingParams['style']) {
            case 'short' :
                $templateName = 'news.short';
                break;
            case 'full' :
                $templateName = 'news.full';
                break;
            case 'print' :
                $templateName = 'news.print';
                break;
            default :
                $templateName = '';
        }
    }

    // Set default template path
    $templatePath = tpl_site;

    //->desired template path - override path if needed
    if (getIsSet($callingParams['overrideTemplatePath'])) {
        $templatePath = $callingParams['overrideTemplatePath'];
    } elseif ($callingParams['customCategoryTemplate']) {
        // Check if isset custom template for category in news
        $templatePath = getCatTemplate($row['catid'], $templateName);
        if ('full' === $callingParams['style'] and file_exists($templatePath . DS . 'main.tpl')) {
            $SYSTEM_FLAGS['template.main.path'] = $templatePath;
        }
    }

    // Load & configure template
    $tpl->template($templateName, $templatePath);
    $tpl->vars($templateName, $tvars);

    // No comments/meta in emulation or export mode
    if (is_array(getIsSet($callingParams['emulate'])) or ($callingParams['style'] == 'export'))
        return $tpl->show($templateName);

    // Set meta tags for news page
    if (getIsSet($row['description']) != '') {
        $SYSTEM_FLAGS['meta']['description'] = $row['description'];
    } else {
        $SYSTEM_FLAGS['meta']['description'] = secure_html($row['title'] . '. ' . GetCategories($row['catid'], true) . '. ' . home_title);
    }
    if (getIsSet($row['keywords']) != '') {
        $SYSTEM_FLAGS['meta']['keywords'] = $row['keywords'];
    } else {
        // Удаляем все слова меньше 3-х символов
        $row['keywords'] = preg_replace('#\b[\d\w]{1,3}\b#iu', '', secure_html($row['title']) . ' ' . GetCategories($row['catid'], true) . ' ' . home_title);
        // Удаляем знаки препинания
        $row['keywords'] = preg_replace('#[^\d\w ]+#iu', '', $row['keywords']);
        // Удаляем лишние пробельные символы
        $row['keywords'] = preg_replace('#[\s]+#iu', ' ', $row['keywords']);
        // Заменяем пробелы на запятые
        $row['keywords'] = preg_replace('#[\s]#iu', ',', $row['keywords']);
        // Выводим для леньтяев
        $SYSTEM_FLAGS['meta']['keywords'] = mb_strtolower(trim($row['keywords'], ','));
    }

    // Prepare title
    //$SYSTEM_FLAGS['info']['title']['group']	= $config["category_link"]?GetCategories($row['catid'], true):Lang::retDate(timestamp, $row['postdate']);
    $SYSTEM_FLAGS['info']['title']['group'] = $row['catid'] ? GetCategories($row['catid'], true) : __('no_cat');
    $SYSTEM_FLAGS['info']['title']['item'] = secure_html($row['title']);

    // We are in short or full mode. Add data into {mainblock}
    $template['vars']['mainblock'] .= $tpl->show($templateName);

    return $row;
}

//
// Show news list
// [PROCESS FILTER]
function newsProcessFilter($conditions)
{
    $conditions = array_filter($conditions);

    if (empty($conditions))
        return '';

    switch (strtoupper($conditions[0])) {
        case 'AND' :
        case 'OR' :
            $list = array();
            for ($i = 1; $i < count($conditions); $i++) {
                $rec = newsProcessFilter($conditions[$i]);
                if ($rec != '')
                    $list [] = '(' . $rec . ')';
            }
            return join(' ' . strtoupper($conditions[0]) . ' ', $list);
        case 'DATA':
            if ($conditions[1] == 'category') {
                switch ($conditions[2]) {
                    case '=':
                        return "`catid` regexp '[[:<:]](" . intval($conditions[3]) . ")[[:>:]]'";
                    default:
                        return '';
                }
            } else {
                switch (strtoupper($conditions[2])) {
                    case '=':
                    case '>=':
                    case '<=':
                    case '>':
                    case '<':
                    case 'LIKE':
                        return '`' . $conditions[1] . '` ' . $conditions[2] . ' ' . db_squote($conditions[3]);
                    case 'IN':
                        if (is_array($conditions[3])) {
                            $xt = array();
                            foreach ($conditions[3] as $r)
                                $xt[] = db_squote($r);

                            return '`' . $conditions[1] . '` IN (' . join(',', $xt) . ') ';
                        }
                        return '';
                    case 'BETWEEN':
                        if (is_array($conditions[3])) {
                            return '`' . $conditions[1] . '` BETWEEN ' . db_squote($conditions[3][0]) . ' AND ' . db_squote($conditions[3][1]);
                        }
                        return '';
                }
            }
            break;
        case 'SQL' :
            return '(' . $conditions[1] . ')';
        default:
            return '';
    }
}

// Params (newsID or alt_name should be filled)
// $filterConditions - conditions for filtering
// $paginationParams - config params for page display
//		'pluginName'	- name of plugin for call
//		'pluginHandler'	- handler for call
//		'params'		- standart param list for generateLink() call
//		'xparams'		- standart param list for generateLink() call
//		'paginator'		- set up for pagination
//			[0]		- variable name
//			[1]		- variable location 0 - params / 1 - xparams
//			[2]		- zero show flag 0 - don't show if zero / 1 - show anytime
// $callingParams
//		'style'	 => mode for which we're called
//			* short		- short new display
//			* full		- full news display
//			* export	- export data [ for plugins or so on. No counters are updated ]
//		'twig' => [FLAG] Use TWIG template engine if set
//		'plugin' => if is called from plugin - ID of plugin
//		'overrideTemplateName' => alternative template for display [ this param overrides `customCategoryTemplate` field ]
//		'overrideTemplatePath' => alternative path for searching of template
//		'customCategoryTemplate' => flag if we need to have custom templates for this news
//			* 0 - no
//			* 1 - use template from master category of the news (1st category)
//			* 2 - use template from current category
//		'currentCategoryId' => ID of current category, required only for `customCategoryTemplate` == 2
//		'regenShortNews' =>
//			'mode' 		=> If we should generate `on the fly` short news from long one
//				* ''		- Leave short news as is [ default ]
//				* 'auto'	- Generate ShortNews from long only if ShortNews is empty
//				* 'force'	- Generate ShortNews from long in any case
//			'len' 			=> Length in chars of part of LongNews that will be used for regeneration (in case if regeneration is active)
//			'finisher' => chars that will be added into the end to indicate that this is truncated line ( default = '...' )
//		'showNumber' => set number of news to show per page
//		'newsOrder' => set news order
//		'overrideSQLquery' => array - sets if PLUGIN wants to run it's own query
//		'page' => page number to show
//		'extendedReturn' => flag if we need to return an extended array:
//			'count' - count of found news
//			'data' - data to be showed
//		'extendedReturnData' => flag if 'data' should be returned as array of separate entries
//		'extendedReturnSQL' => flag if we need to return original SQL fetched data in extendedReturn answer as 'sql' field
//		'entendedReturnPagination' => flag if pagination should be returned within extendedReturn array as a variable 'pagination'
//		'searchFlag' => flag if we want to use non-mondatory template 'news.search.tpl' [!!only for style = 'short' !!]
//		'pin'	-	Way of sorting for PINNED news
//			0	-	`pinned` (for MainPage)
//			1	-	`catpinned`	(for Categories page)
//			2	-	without taking PIN into account
//		'disablePagination'	- Disable generation of page information
//		'extractImages'	- Extract embedded images/files from news body
//		'paginationCategoryID'	- IF function is called in 'by.category' we can specify categoryID here, this will optimize 'page count' query
//
function news_showlist($filterConditions = array(), $paginationParams = array(), $callingParams = array())
{
    global $mysql, $tpl, $userROW, $catz, $catmap, $config, $vars, $parse, $template, $PFILTERS, $twig, $parse;
    global $year, $month, $day;
    global $SYSTEM_FLAGS, $TemplateCache;

    $timer = MicroTimer::instance();

    $categoryList = array();

    // Generate SQL filter for 'WHERE' using filterConditions parameter
    $query['filter'] = newsProcessFilter(array('AND', array('DATA', 'approve', '=', '1'), $filterConditions));

    // Set default template path 
    //tpl_dir . $config['theme']
    $templatePath = tpl_site;

    // Make temlate procession - auto/manual overriding
    if (empty($callingParams['style'])) {
        $callingParams['style'] = 'short';
    }

    // desired template - override template if needed
    if (isset($callingParams['overrideTemplateName']) and $callingParams['overrideTemplateName']) {
        $templateName = $callingParams['overrideTemplateName'];
    } else {
        //->generate template name for selected style
        switch ($callingParams['style']) {
            case 'short':
                $templateName = 'news.short';
                break;
            case 'full':
                $templateName = 'news.full';
                break;
            default:
                $templateName = '';
        }
    }

    $cstart = $start_from = isset($callingParams['page']) ? intval($callingParams['page']) : 0;

    if ($cstart < 1)
        $cstart = 1;

    $i = $start_from ? $start_from : 0;

    $showNumber = ($config['number'] >= 1) ? $config['number'] : 5;
    if (isset($callingParams['showNumber']) and (intval($callingParams['showNumber']) > 0))
        $showNumber = intval($callingParams['showNumber']);

    $limit_start = $cstart ? ($cstart - 1) * $showNumber : 0;
    $limit_count = $showNumber;

    $orderBy = isset($callingParams['newsOrder']) ? $callingParams['newsOrder'] : 'id desc';
    if (!in_array($orderBy, array('id desc', 'id asc', 'com desc', 'com asc', 'postdate desc', 'postdate asc', 'title desc', 'title asc', 'views desc', 'views asc')))
        $orderBy = 'id desc';

    if ($orderBy == 'postdate desc')
        $orderBy = 'editdate desc, postdate desc';

    if (!empty($callingParams['pin'])) {
        switch ($callingParams['pin']) {
            case 1:
                $orderBy = 'catpinned desc, ' . $orderBy;
                break;
            case 2:
                break;
            default:
                $orderBy = 'pinned desc, ' . $orderBy;
                break;
        }
    }

    $query['orderby'] = " order by " . $orderBy . " limit " . $limit_start . "," . $limit_count;

    // Make select / news counting queries
    $nCount = 0;
    $output = '';
    $outputList = array();

    // Call `SELECT` query
    // * Check if we need to override query
    if (isset($callingParams['overrideSQLquery']) and ($callingParams['overrideSQLquery'] != '')) {
        $query['result'] = $callingParams['overrideSQLquery'];
        // ** FORCE TO DISABLE PAGINATION !!
        $callingParams['disablePagination'] = true;
    } else {
        $query['result'] = "SELECT * FROM " . prefix . "_news WHERE " . $query['filter'] . $query['orderby'];
    }

    $selectResult = $mysql->select($query['result'], 1);

    if (isset($callingParams['disablePagination']) and ($callingParams['disablePagination'])) {
        $newsCount = count($selectResult);
        $pages_count = 1;
    } else {
        if (isset($callingParams['paginationCategoryID']) and ($callingParams['paginationCategoryID'] > 0)) {
            $query['count'] = 'SELECT count(*) FROM ' . prefix . '_news_map where categoryID = ' . db_squote($callingParams['paginationCategoryID']);
        } else {
            $query['count'] = "SELECT count(*) as count FROM " . prefix . "_news WHERE " . $query['filter'];
        }
        $newsCount = $mysql->result($query['count']);
        $pages_count = ceil($newsCount / $showNumber);
    }

    // Prepare TOTAL data for plugins
    // = count		- count of fetched news
    // = totalCount	- total count of news
    // = pagesCount	- total count of pages that will be displayed
    // = result		- result of the query (array)
    // = ids		- array with IDs of fetched news

    $callingParams['query'] = array(
        'count' => count($selectResult),
        'result' => $selectResult,
        'totalCount' => $newsCount,
        'pagesCount' => $pages_count,
    );

    // Reference for LINKED images and files
    $callingParams['linkedImages'] = array(
        'ids' => array(),
        'data' => array(),
    );

    $callingParams['linkedFiles'] = array(
        'ids' => array(),
        'data' => array(),
    );

    // List of news that have linked images
    $nilink = array();
    $nflink = array();

    foreach ($selectResult as $row) {
        $callingParams['query']['ids'][] = $row['id'];
        if ($row['num_images'])
            $nilink [] = $row['id'];
        if ($row['num_files'])
            $nflink [] = $row['id'];
    }

    // Load linked images
    $linkedImages = array();
    if (count($nilink)) {
        foreach ($mysql->select("select * from " . prefix . "_images where (linked_ds = 1) and (linked_id in (" . join(", ", $nilink) . "))", 1) as $nirow) {
            $linkedImages['ids'] [] = $nirow['id'];
            $linkedImages['data'][$nirow['id']] = $nirow;
        }
    }

    // Load linked files
    $linkedFiles = array();
    if (count($nflink)) {
        foreach ($mysql->select("select * from " . prefix . "_files where (linked_ds = 1) and (linked_id in (" . join(", ", $nflink) . "))", 1) as $nirow) {
            $linkedFiles['ids'] [] = $nirow['id'];
            $linkedFiles['data'][$nirow['id']] = $nirow;
        }
    }

    // ===================================================================
    // All information is retrieved from database.
    // Preload plugins and take care of them.
    loadActionHandlers('news:show');
    loadActionHandlers('news:show:list');
    loadActionHandlers('news_short');

    // Execute filters
    if (isset($PFILTERS['news']) and is_array($PFILTERS['news'])) {
        // Special handler for linked images/files
        $callingParams['linkedImages'] = $linkedImages;
        $callingParams['linkedFiles'] = $linkedFiles;

        foreach ($PFILTERS['news'] as $k => $v)
            $v->onBeforeShowlist($callingParams);
    }

    //->desired template path - override path if needed
    if (isset($callingParams['overrideTemplatePath'])) {
        $templatePath = $callingParams['overrideTemplatePath'];
    } elseif (isset($callingParams['customCategoryTemplate']) and isset($callingParams['currentCategoryId'])) {
        // Check if isset custom template for category in news
        $templatePath = getCatTemplate($callingParams['currentCategoryId'], $templateName);
    } else {
        // Set default template path
        $templatePath = tpl_site;
    }

    // Hack for 'automatic search mode'
    $currentTemplateName = $templateName;
    // switch to `search` template if no templateName was overrided AND style is search AND searchFlag is set AND search template file exists
    if (isset($callingParams['searchFlag']) and ($callingParams['searchFlag']) and (!isset($callingParams['overrideTemplatePath'])) and ($callingParams['style'] == 'short') and (@file_exists($templatePath . '/news.search.tpl'))) {
        $currentTemplateName = 'news.search';
    }

    // Main processing cycle
    $outputArray = [];
    foreach ($selectResult as $row) {
        $i++;
        $nCount++;

        // Give 'news in order' field to plugins
        $callingParams['nCount'] = $nCount;

        // Execute filters
        if (isset($PFILTERS['news']) and is_array($PFILTERS['news'])) {
            foreach ($PFILTERS['news'] as $k => $v) {
                $v->showNewsPre($row['id'], $row, $callingParams);
            }
        }

        $tvars = News::fillVariables($row, 0, isset($_REQUEST['page']) ? intval($_REQUEST['page']) : 0, 0, isset($callingParams['regenShortNews']) ? $callingParams['regenShortNews'] : array());

        $tvars['vars']['alternating'] = ($i % 2) ? 'odd' : 'even';

        // Prepare list of linked files and images
        $callingParams['linkedFiles'] = array();
        $tvars['vars']['_files'] = array();

        if (isset($linkedFiles['data']) and is_array($linkedFiles['data']))
            foreach ($linkedFiles['data'] as $k => $v) {
                if ($v['linked_id'] == $row['id']) {
                    $callingParams['linkedFiles']['ids'] [] = $v['id'];
                    $callingParams['linkedFiles']['data'] [] = $v;
                    $tvars['vars']['_files'] [] = array(
                        'plugin' => $v['plugin'],
                        'pidentity' => $v['pidentity'],
                        'url' => ($v['storage'] ? $config['attach_url'] : $config['files_url']) . '/' . $v['folder'] . '/' . $v['name'],
                        'name' => $v['name'],
                        'origName' => secure_html($v['orig_name']),
                        'description' => secure_html($v['description']),
                        'size' => formatSize(filesize(($v['storage'] ? $config['attach_dir'] : $config['files_dir']) . '/' . $v['folder'] . '/' . $v['name'])),
                    );
                }
            }

        $callingParams['linkedFiles'] = array();
        $tvars['vars']['_images'] = array();

        if (isset($linkedImages['data']) and is_array($linkedImages['data']))
            foreach ($linkedImages['data'] as $k => $v) {
                if ($v['linked_id'] == $row['id']) {
                    $callingParams['linkedImages']['ids'] [] = $v['id'];
                    $callingParams['linkedImages']['data'] [] = $v;
                    $tvars['vars']['_images'] [] = array(
                        'plugin' => $v['plugin'],
                        'pidentity' => $v['pidentity'],
                        'url' => ($v['storage'] ? $config['attach_url'] : $config['images_url']) . '/' . $v['folder'] . '/' . $v['name'],
                        'purl' => $v['preview'] ? (($v['storage'] ? $config['attach_url'] : $config['images_url']) . '/' . $v['folder'] . '/thumb/' . $v['name']) : null,
                        'width' => $v['width'],
                        'height' => $v['height'],
                        'pwidth' => $v['p_width'],
                        'pheight' => $v['p_height'],
                        'name' => $v['name'],
                        'origName' => secure_html($v['orig_name']),
                        'description' => secure_html($v['description']),
                        'size' => formatSize(filesize(($v['storage'] ? $config['attach_dir'] : $config['images_dir']) . '/' . $v['folder'] . '/' . $v['name'])),
                        'flags' => array(
                            'hasPreview' => $v['preview'],
                        ),
                    );
                }
            }

        //print "LinkedFiles (".$row['id']."): <pre>".var_export($tvars['vars']['#files'], true)."</pre>";
        //print "LinkedImages (".$row['id']."): <pre>".var_export($tvars['vars']['#images'], true)."</pre>";

        // Extract embedded images/files if requested
        // news.embed.images	- list of URL's
        // news.embed.imgCount	- count of extracted URL's

        $tvars['vars']['news']['embed'] = array('images' => array());
        if (!empty($callingParams['extractImages'])) {
            $tvars['vars']['news']['embed']['images'] = $parse->extractImages($tvars['vars']['news']['short'] . $tvars['vars']['news']['full']);
        }
        $tvars['vars']['news']['embed']['imgCount'] = count($tvars['vars']['news']['embed']['images']);

        // Print icon if only one parent category
        if (!empty($row['catid']) and !stristr(",", $row['catid']) and isset($catmap[$row['catid']]) and ($catalt = $catmap[$row['catid']]) and isset($catz[$catalt]['icon']) and $catz[$catalt]['icon']) {
            // [TWIG] news.flags.hasCategoryIcon
            $tvars['news']['flags']['hasCategoryIcon'] = true;
            $tvars['vars']['icon'] = $catz[$catalt]['icon'];
            $tvars['vars']['[icon]'] = '';
            $tvars['vars']['[/icon]'] = '';
        } else {
            $tvars['flags']['hasCategoryIcon'] = false;
            $tvars['vars']['icon'] = '';
            $tvars['regx']["'\\[icon\\].*?\\[/icon\\]'si"] = '';
        }

        if (is_array($userROW) and ($userROW['id'] == $row['author_id'] or ($userROW['status'] == 1 or $userROW['status'] == 2))) {
            // [TWIG] news.flags.canEdit, news.flags.canDelete, news.url.edit, news.url.delete
            $tvars['vars']['news']['flags']['canEdit'] = true;
            $tvars['vars']['news']['flags']['canDelete'] = true;
            $tvars['vars']['news']['url']['edit'] = admin_url . "/admin.php?mod=news&amp;action=edit&amp;id=" . $row['id'];
            $tvars['vars']['news']['url']['delete'] = admin_url . "/admin.php?mod=news&amp;action=manage&amp;subaction=mass_delete&amp;token=" . genUToken('admin.news.edit') . "&amp;selected_news[]=" . $row['id'];

            $tvars['vars']['editNewsLink'] = admin_url . "/admin.php?mod=news&amp;action=edit&amp;id=" . $row['id'];
            $tvars['vars']['[edit-news]'] = "<a href=\"" . admin_url . "/admin.php?mod=news&amp;action=edit&amp;id=" . $row['id'] . "\" target=\"_blank\">";
            $tvars['vars']['[/edit-news]'] = '</a>';
            $tvars['vars']['[del-news]'] = "<a onclick=\"confirmIt('" . admin_url . "/admin.php?mod=news&amp;action=manage&amp;subaction=mass_delete&amp;token=" . genUToken('admin.news.edit') . "&amp;selected_news[]=" . $row['id'] . "', '" . __('sure_del') . "')\" target=\"_blank\" style=\"cursor: pointer;\">";
            $tvars['vars']['deleteNewsLink'] = admin_url . "/admin.php?mod=news&amp;action=manage&amp;subaction=mass_delete&amp;token=" . genUToken('admin.news.edit') . "&amp;selected_news[]=" . $row['id'];
            $tvars['vars']['[/del-news]'] = '</a>';
        } else {
            $tvars['news']['flags']['canEdit'] = false;
            $tvars['news']['flags']['canDelete'] = false;
            $tvars['regx']["'\[edit-news\].*?\[/edit-news\]'si"] = "";
            $tvars['regx']["'\[del-news\].*?\[/del-news\]'si"] = "";
        }
        //executeActionHandler('news_short');

        // Execute filters
        if (isset($PFILTERS['news']) and is_array($PFILTERS['news'])) {
            foreach ($PFILTERS['news'] as $k => $v) {
                $v->showNews($row['id'], $row, $tvars, $callingParams);
            }
        }

        // If only collection array entries
        // Example for xnews plugin
        if (!empty($callingParams['extendedReturnArray'])) {
            $tempVars = $tvars['vars']['news'];
            unset($tvars['vars']['news']);
            $outputArray [] = array_merge($tvars['vars'], $tempVars);
            continue;
        }

        // Populate variables
        $tVars = $tvars['vars'];

        // Provide information about used template
        $tVars['templateName'] = $currentTemplateName;
        $tVars['templatePath'] = $templatePath;

        // Render template
        $res = $twig->render($templatePath . '/' . $currentTemplateName . '.tpl', $tVars);

        $outputList [] = $res;
    }

    unset($tvars, $tVars);

    // Return output if need only array entries
    // Example for xnews plugin
    if (!empty($callingParams['extendedReturnArray'])) {
        return $outputArray;
    }

    $output = join('', $outputList);

    // Return output if we're in export mode
    if ($callingParams['style'] == 'export') {
        return $output;
    }

    // Print "no news" if we didn't find any news [ DON'T PRINT IN EXTENDED MODE ]
    if (!$nCount) {
        if (!isset($callingParams['extendedReturn']) or !$callingParams['extendedReturn']) {
            msg(array('type' => 'info', 'message' => __('msgi_no_news')));
        }
        $limit_start = 2;
    }

    // Generate pagination/navigation if it's not disabled
    $paginationOutput = '';
    if (!(isset($callingParams['disablePagination']) and ($callingParams['disablePagination']))) {
        templateLoadVariables(true);
        $navigations = $TemplateCache['site']['#variables']['navigation'];
        $tpl->template('pages', tpl_site);

        // Prev page link
        if ($limit_start and $nCount) {
            $prev = floor($limit_start / $showNumber);
            $tvars['regx']["'\[prev-link\](.*?)\[/prev-link\]'si"] = str_replace('%page%', "$1", str_replace('%link%', generatePageLink($paginationParams, $prev), $navigations['prevlink']));
        } else {
            $tvars['regx']["'\[prev-link\](.*?)\[/prev-link\]'si"] = "";
            $prev = 0;
            $no_prev = true;
        }

        $maxNavigations = $config['newsNavigationsCount'];
        if ($maxNavigations < 1)
            $maxNavigations = 10;

        $tvars['vars']['pages'] = generatePagination($cstart, 1, $pages_count, $maxNavigations, $paginationParams, $navigations);

        // Next page link
        if (($prev + 2 <= $pages_count) and $nCount) {
            $tvars['regx']["'\[next-link\](.*?)\[/next-link\]'si"] = str_replace('%page%', "$1", str_replace('%link%', generatePageLink($paginationParams, $prev + 2), $navigations['nextlink']));
        } else {
            $tvars['regx']["'\[next-link\](.*?)\[/next-link\]'si"] = "";
            $no_next = true;
        }

        if ($nCount and ($pages_count > 1)) {
            $tpl->vars('pages', $tvars);
            $paginationOutput = $tpl->show('pages');
        }

        if (empty($callingParams['entendedReturnPagination']) and empty($callingParams['extendedReturnPagination']))
            $output .= $paginationOutput;
    }

    // Return result
    if ((isset($callingParams['extendedReturn']) and $callingParams['extendedReturn'])) {
        $returnData = array(
            'count' => $newsCount,
            'data' => (isset($callingParams['extendedReturnData']) and $callingParams['extendedReturnData']) ? $outputList : $output,
            'pages' => array(
                'current' => $cstart,
                'total' => $pages_count,
                'output' => $paginationOutput,
            )
        );

        if (isset($callingParams['entendedReturnPagination']) and $callingParams['entendedReturnPagination']) {
            $returnData['pagination'] = $paginationOutput;
        }

        return $returnData;
    }
    return $output;
}
