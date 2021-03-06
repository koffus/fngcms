<?php

//
// Copyright (C) 2006-2017 BixBite CMS (http://bixbite.site/)
// Name: news.php
// Description: News display sub-engine
// Author: Vitaly Ponomarev
//

// Protect against hack attempts
if (!defined('BBCMS')) die ('HAL');

Lang::load('news', 'site');

// Load shared library
include_once root . 'includes/inc/libnews.php';

// ================================================================= //
// Module code //
// ================================================================= //

// Default "show news" function
function showNews($handlerName, $params)
{

    global $catz, $catmap, $template, $config, $userROW, $PFILTERS, $SYSTEM_FLAGS, $SUPRESS_TEMPLATE_SHOW, $tpl, $parse, $currentCategory, $twig, $twigLoader, $TemplateCache;

    $timer = MicroTimer::instance();
    Lang::load('news', 'site'); // !!!

    // preload plugins
    loadActionHandlers('news');
    $timer->registerEvent("All [news] plugins are preloaded");

    // Init array with configuration parameters
    $callingParams = array('customCategoryTemplate' => 1, 'setCurrentCategory' => 1, 'setCurrentNews' => 1);
    $callingCommentsParams = array();

    // Preload template configuration variables
    templateLoadVariables();

    // Check if template requires extracting embedded images
    if (!empty($config['extract_images'])) {
        $callingParams['extractImages'] = true;
    }

    // Set default template path
    $templatePath = tpl_site;

    // Check for FULL NEWS mode
    if (($handlerName == 'news') or ($handlerName == 'print')) {
        $flagPrint = ($handlerName == 'print') ? true : false;
        if ($flagPrint)
            $SUPRESS_TEMPLATE_SHOW = true;

        $callingParams['style'] = $flagPrint ? 'print' : 'full';

        // Execute filters [ onBeforeShow ] ** ONLY IN 'news' mode. In print mode we don't use it
        if (!$flagPrint and isset($PFILTERS['news']) and is_array($PFILTERS['news'])) {
            foreach ($PFILTERS['news'] as $k => $v) {
                $v->onBeforeShow('full');
            }
        }

        // Determine passed params
        $vars = array('id' => 0, 'altname' => '');
        if (isset($params['id'])) {
            $vars['id'] = $params['id'];
        } else if (isset($params['zid'])) {
            $vars['id'] = $params['zid'];
        } else if (isset($params['altname'])) {
            $vars['altname'] = $params['altname'];
        } else if (isset($_REQUEST['id'])) {
            $vars['id'] = intval($_REQUEST['id']);
        } else if (isset($_REQUEST['zid'])) {
            $vars['id'] = intval($_REQUEST['zid']);
        } else {
            $vars['altname'] = $_REQUEST['altname'];
        }

        if (isset($params['category'])) {
            $callingParams['validateCategoryAlt'] = $params['category'];
        }
        if (isset($params['catid'])) {
            $callingParams['validateCategoryID'] = $params['catid'];
        }

        $callingParams['addCanonicalLink'] = true;

        // Try to show news
        if (($row = news_showone($vars['id'], $vars['altname'], $callingParams)) !== false) {
            // Execute filters [ onAfterShow ] ** ONLY IN 'news' mode. In print mode we don't use it
            if (!$flagPrint and isset($PFILTERS['news']) and is_array($PFILTERS['news'])) {
                foreach ($PFILTERS['news'] as $k => $v) {
                    $v->onAfterNewsShow($row['id'], $row, array('style' => 'full'));
                }
            }
        }

    } else {

        $callingParams['style'] = 'short';
        $callingParams['page'] = (isset($params['page']) and intval($params['page'])) ? intval($params['page']) : (isset($_REQUEST['page']) ? intval($_REQUEST['page']) : 0);

        // Execute filters [ onBeforeShow ]
        if (isset($PFILTERS['news']) and is_array($PFILTERS['news'])) {
            foreach ($PFILTERS['news'] as $k => $v)
                $v->onBeforeShow('short');
        }

        $tableVars = array();

        $ntTemplateName = 'news.table.tpl';

        $callingParams['extendedReturn'] = true;
        $callingParams['extendedReturnData'] = true;
        $callingParams['entendedReturnPagination'] = true;

        switch ($handlerName) {
            case 'main':
                $SYSTEM_FLAGS['info']['title']['group'] = !empty($config['meta_title']) ? $config['meta_title'] : __('mainpage');
                $paginationParams = checkLinkAvailable('news', 'main') ?
                    array('pluginName' => 'news', 'pluginHandler' => 'main', 'params' => array(), 'xparams' => array(), 'paginator' => array('page', 0, false)) :
                    array('pluginName' => 'core', 'pluginHandler' => 'plugin', 'params' => array('plugin' => 'news', 'handler' => 'main'), 'xparams' => array(), 'paginator' => array('page', 1, false));

                if ($config['default_newsorder'] != '')
                    $callingParams['newsOrder'] = $config['default_newsorder'];

                $tableVars = news_showlist(array('DATA', 'mainpage', '=', '1'), $paginationParams, $callingParams);

                break;

            case 'all':
                $SYSTEM_FLAGS['info']['title']['group'] = __('allnews');
                $paginationParams = checkLinkAvailable('news', 'all') ?
                    array('pluginName' => 'news', 'pluginHandler' => 'all', 'params' => array(), 'xparams' => array(), 'paginator' => array('page', 0, false)) :
                    array('pluginName' => 'core', 'pluginHandler' => 'plugin', 'params' => array('plugin' => 'news', 'handler' => 'all'), 'xparams' => array(), 'paginator' => array('page', 1, false));

                if ($config['default_newsorder'] != '')
                    $callingParams['newsOrder'] = $config['default_newsorder'];

                $tableVars = news_showlist(array(), $paginationParams, $callingParams);

                break;

            case 'by.category':
                $category = '';
                if (isset($params['catid'])) {
                    $category = $params['catid'];
                } elseif (isset($params['category']) and isset($catz[$params['category']])) {
                    $category = $catz[$params['category']]['id'];
                } elseif (isset($_REQUEST['catid'])) {
                    $category = $params['catid'];
                } elseif (isset($_REQUEST['category']) and isset($catz[$_REQUEST['category']])) {
                    $category = $catz[$_REQUEST['category']]['id'];
                }

                // We can't show unexisted categories
                if (!$category or !isset($catmap[$category])) {
                    if (!$params['FFC']) {
                        error404();
                    }
                    return false;
                }
                $currentCategory = $catz[$catmap[$category]];

                // Save current category identifier
                $SYSTEM_FLAGS['news']['currentCategory.alt'] = $currentCategory['alt'];
                $SYSTEM_FLAGS['news']['currentCategory.id'] = $currentCategory['id'];
                $SYSTEM_FLAGS['news']['currentCategory.name'] = $currentCategory['name'];

                // Set title
                $SYSTEM_FLAGS['info']['title']['group'] = $currentCategory['name'];

                // Check if `default template` for this category is set to "current category"
                $cct = intval(substr($currentCategory['flags'], 2, 1));
                if ($cct < 1) {
                    $cct = intval($config['template_mode']);
                    if (!$cct)
                        $cct = 1;
                }
                $callingParams['customCategoryTemplate'] = $cct;
                $callingParams['currentCategoryId'] = $currentCategory['id'];

                // Set meta tags for category page
                if (getIsSet($currentCategory['description']) != '') {
                    $SYSTEM_FLAGS['meta']['description'] = $currentCategory['description'];
                } else {
                    $SYSTEM_FLAGS['meta']['description'] = $currentCategory['name'] . '. ' . home_title;
                }
                if (getIsSet($currentCategory['keywords']) != '') {
                    $SYSTEM_FLAGS['meta']['keywords'] = $currentCategory['keywords'];
                } else {
                    // Удаляем все слова меньше 3-х символов
                    $currentCategory['keywords'] = preg_replace('#\b[\d\w]{1,3}\b#iu', '', $currentCategory['name'] . ' ' . home_title);
                    // Удаляем знаки препинания
                    $currentCategory['keywords'] = preg_replace('#[^\d\w ]+#iu', '', $currentCategory['keywords']);
                    // Удаляем лишние пробельные символы
                    $currentCategory['keywords'] = preg_replace('#[\s]+#iu', ' ', $currentCategory['keywords']);
                    // Заменяем пробелы на запятые
                    $currentCategory['keywords'] = preg_replace('#[\s]#iu', ',', $currentCategory['keywords']);
                    // Выводим для леньтяев
                    $SYSTEM_FLAGS['meta']['keywords'] = mb_strtolower(trim($currentCategory['keywords'], ','));
                }

                // Set number of `news per page` if this parameter is filled in category
                if ($currentCategory['number'])
                    $callingParams['showNumber'] = $currentCategory['number'];

                // Set personal `order by` for category
                if ($currentCategory['orderby'])
                    $callingParams['newsOrder'] = $currentCategory['orderby'];
                if (isset($_COOKIE['newsOrder']))
                    $callingParams['newsOrder'] = $_COOKIE['newsOrder'];

                $paginationParams = checkLinkAvailable('news', 'by.category') ?
                    array('pluginName' => 'news', 'pluginHandler' => 'by.category', 'params' => array('category' => $catmap[$category]), 'xparams' => array(), 'paginator' => array('page', 0, false)) :
                    array('pluginName' => 'core', 'pluginHandler' => 'plugin', 'params' => array('plugin' => 'news', 'handler' => 'by.category'), 'xparams' => array('category' => $catmap[$category]), 'paginator' => array('page', 1, false));

                // Sort news for `category` mode
                $callingParams['pin'] = 1;

                // Notify that we use 'pagination category' mode
                $callingParams['paginationCategoryID'] = $currentCategory['id'];

                // Generate news content
                $tableVars = news_showlist(array('DATA', 'category', '=', $category), $paginationParams, $callingParams);

                // TABLE - prepare information about category
                
                $tableVars['category'] = makeCategoryInfo($currentCategory['id']);
                $tableVars['category'] = array_shift($tableVars['category']);

                // TABLE - prepare information about sorting block
                $sortDefault = array('id desc',
                    'postdate desc', 'title desc', 'views desc', 'com desc');
                //if ( isset($_COOKIE['newsOrder']) and in_array($callingParams['newsOrder'], $sortDefault) ) {
                $sortKey = array_search($callingParams['newsOrder'], $sortDefault);
                $sortDefault = array_diff($sortDefault, [$callingParams['newsOrder']]);
                $sortDefault = $sortDefault + [$sortKey => str_replace('desc', 'asc', $callingParams['newsOrder'])];
                ksort($sortDefault);
                //}

                $tableVars['newsOrder'] = '';
                $sortOrder = explode(' ', $callingParams['newsOrder']);
                foreach ($sortDefault as $key => $value) {
                    $pieces = explode(' ', $value);
                    if ($pieces[0] == 'com' and !pluginIsActive('comments'))
                        continue;
                    if ($pieces[0] == $sortOrder[0]) {
                        $tableVars['newsOrder'] .= '<span onclick="newsorder(\'' . $value . '\'); return false;" class="news-order-link active ' . $pieces[1] . '">' . __('news.order.' . $pieces[0]) . '</span>';
                    } else {
                        $tableVars['newsOrder'] .= '<span onclick="newsorder(\'' . $value . '\'); return false;" class="news-order-link">' . __('news.order.' . $pieces[0]) . '</span>';
                    }
                }

                // Check if template 'news.table.tpl' exists [first check custom category template (if set), after that - common template for the whole site
                if ($currentCategory['tpl'])
                    if (file_exists(tpl_site . '/ncustom/' . $currentCategory['tpl'] . '/news.table.tpl')) {
                        $ntTemplateName = 'ncustom/' . $currentCategory['tpl'] . '/' . $ntTemplateName;
                        if (file_exists(tpl_site . '/ncustom/' . $currentCategory['tpl'] . '/main.tpl')) {
                            $SYSTEM_FLAGS['template.main.path'] = tpl_site . '/ncustom/' . $currentCategory['tpl'];
                        }
                    }

                break;

            case 'by.day':
                $year = intval(isset($params['year']) ? $params['year'] : $_REQUEST['year']);
                $month = intval(isset($params['month']) ? $params['month'] : $_REQUEST['month']);
                $day = intval(isset($params['day']) ? $params['day'] : $_REQUEST['day']);

                if (($year < 1970) or ($year > 2100) or ($month < 1) or ($month > 12) or ($day < 1) or ($day > 31))
                    return false;

                $tableVars['year'] = $year;
                $tableVars['month'] = $month;
                $tableVars['day'] = $day;
                $tableVars['dateStamp'] = mktime("0", "0", "0", $month, $day, $year);

                $SYSTEM_FLAGS['info']['title']['group'] = Lang::retDate("j F Y", mktime("0", "0", "0", $month, $day, $year));
                $paginationParams = checkLinkAvailable('news', 'by.day') ?
                    array(
                        'pluginName' => 'news',
                        'pluginHandler' => 'by.day',
                        'params' => array(
                            'day' => sprintf('%02u', $day),
                            'month' => sprintf('%02u', $month),
                            'year' => $year
                        ),
                        'xparams' => array(),
                        'paginator' => array('page', 0, false)
                    ) :
                    array(
                        'pluginName' => 'core',
                        'pluginHandler' => 'plugin',
                        'params' => array(
                            'plugin' => 'news',
                            'handler' => 'by.day'),
                        'xparams' => array(
                            'day' => sprintf('%02u', $day),
                            'month' => sprintf('%02u', $month),
                            'year' => $year
                        ),
                        'paginator' => array('page', 1, false)
                    );

                // Use extended return mode
                $callingParams['extendedReturn'] = true;
                $tableVars = news_showlist(array('DATA', 'postdate', 'BETWEEN', array(mktime(0, 0, 0, $month, $day, $year), mktime(23, 59, 59, $month, $day, $year))), $paginationParams, $callingParams);

                // Check if there're output data
                if ($tableVars['count'] <= 0) {
                    // No data, stop execution
                    if (!$params['FFC']) {
                        error404();
                    }
                    return false;
                }
                break;

            case 'by.month':
                $year = intval(isset($params['year']) ? $params['year'] : $_REQUEST['year']);
                $month = intval(isset($params['month']) ? $params['month'] : $_REQUEST['month']);

                if (($year < 1970) or ($year > 2100) or ($month < 1) or ($month > 12))
                    return false;

                $tableVars['year'] = $year;
                $tableVars['month'] = $month;
                $tableVars['dateStamp'] = mktime("0", "0", "0", $month, 1, $year);

                $SYSTEM_FLAGS['info']['title']['group'] = Lang::retDate("F Y", mktime(0, 0, 0, $month, 1, $year));
                $paginationParams = checkLinkAvailable('news', 'by.month') ?
                    array('pluginName' => 'news', 'pluginHandler' => 'by.month', 'params' => array('month' => sprintf('%02u', $month), 'year' => $year), 'xparams' => array(), 'paginator' => array('page', 0, false)) :
                    array('pluginName' => 'core', 'pluginHandler' => 'plugin', 'params' => array('plugin' => 'news', 'handler' => 'by.month'), 'xparams' => array('month' => sprintf('%02u', $month), 'year' => $year), 'paginator' => array('page', 1, false));

                // Use extended return mode
                $callingParams['extendedReturn'] = true;
                $tableVars = news_showlist(array('DATA', 'postdate', 'BETWEEN', array(mktime(0, 0, 0, $month, 1, $year), mktime(23, 59, 59, $month, date("t", mktime(0, 0, 0, $month, 1, $year)), $year))), $paginationParams, $callingParams);

                // Check if there're output data
                if ($tableVars['count'] <= 0) {
                    // No data, stop execution
                    if (!$params['FFC']) {
                        error404();
                    }
                    return false;
                }
                break;

            case 'by.year':
                $year = intval(isset($params['year']) ? $params['year'] : $_REQUEST['year']);

                if (($year < 1970) or ($year > 2100))
                    return false;

                $tableVars['year'] = $year;
                $tableVars['dateStamp'] = mktime("0", "0", "0", 1, 1, $year);

                $SYSTEM_FLAGS['info']['title']['group'] = Lang::retDate("Y", mktime(0, 0, 0, 1, 1, $year));
                $paginationParams = checkLinkAvailable('news', 'by.year') ?
                    array('pluginName' => 'news', 'pluginHandler' => 'by.year', 'params' => array('year' => $year), 'xparams' => array(), 'paginator' => array('page', 0, false)) :
                    array('pluginName' => 'core', 'pluginHandler' => 'plugin', 'params' => array('plugin' => 'news', 'handler' => 'by.year'), 'xparams' => array('year' => $year), 'paginator' => array('page', 1, false));

                // Use extended return mode
                $callingParams['extendedReturn'] = true;
                $tableVars = news_showlist(array('DATA', 'postdate', 'BETWEEN', array(mktime(0, 0, 0, 1, 1, $year), mktime(23, 59, 59, 12, 31, $year))), $paginationParams, $callingParams);

                // Check if there're output data
                if ($tableVars['count'] <= 0) {
                    // No data, stop execution
                    if (!$params['FFC']) {
                        error404();
                    }
                    return false;
                }
                break;
        }

        $tableVars['handler'] = $handlerName;

        // Prepare news table
        //print "[TABLE VARS]<pre>".var_export($tableVars, true)."</pre>";
        $twigLoader->setDefaultContent($ntTemplateName, '{% for entry in data %}{{ entry }}{% else %}' . __('msgi_no_news') . '{% endfor %} {{ pagination }}');
        $template['vars']['mainblock'] .= $twig->render($ntTemplateName, $tableVars);

        // Execute filters [ onAfterShow ]
        if (isset($PFILTERS['news']) and is_array($PFILTERS['news'])) {
            foreach ($PFILTERS['news'] as $k => $v) {
                $v->onAfterShow('short');
            }
        }
    }
}
