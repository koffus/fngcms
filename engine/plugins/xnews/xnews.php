<?php

// Plugin `xnews` (c) Vitaly Ponomarev
// Redesined original plugin top_news by Alexey N. Zhukov (http://digitalplace.ru) [ email: zhukov.alexei@gmail.com ]

# Protect against hack attempts
if (!defined('BBCMS')) die ('HAL');

// Load shared library
include_once root.'includes/inc/libnews.php';

//
// Show data block for xnews plugin
// Params:
//   * id - if specified, all params will be preloaded for specified configured entity
//   * categoryMode - way how to choose categories for this block
//   * pinMode - way how to work with "pinned" news [ 0 - does not matter, 1 - only pinned, 2 - only not pinned ]
//   * favMode - way how to work with "favorite" news [ 0 - does not matter, 1 - only favorite, 2 - only not favorite ]
//   * categories - list of categories for categoryMode
//   * visibilityMode - way how to decide if this block should be displayed
//   * visibilityCList - list of categories where block should be displayed (in case if block is displayed only in specific categories)
//   * mainMode - how to manage paged marked as "On mainpage" [ 0 - does not matter, 1 - not on main page, 2 - on main page ]
//   * count - number of news to show
//   * skip - number of news to skip before show
//   * maxAge - maximum age of news
//   * order - how to sort [ viewed, commented, last, random ]
//   * showNoNews - flag if block should be showed even if there're no news
//   * skipCurrent - flag if we should not show current news in block content (if we're currently in news)
//   * skin - ID/directory of template to use (directory from engine/plugins/xnews/tpl/
//   * extractImages - flag if news engine should extract embedded (via <img src=".."/>) images into array news.embed.images
//   * cacheAge - age of cache [in seconds]

function xNewsShowBlock($params = [])
{
    global $CurrentHandler, $twig, $config;

    if (isset($params['id'])) {
        // Scan blocks
        $isFound = false;

        for ($i = 1; $i < 50; $i++) {
            if (pluginGetVariable('xnews', $i.'_name') == $params['id']) {
                // BLOCK FOUND. MAKE PRESETS !!!
                $isFound = true;

                if (pluginGetVariable('xnews', 'cache') and (!isset($params['cacheAge'])))
                    $params['cacheAge'] = pluginGetVariable('xnews', 'cache_expire');

                foreach (array('categoryMode', 'categories', 'visibilityMode', 'visibilityCList', 'mainMode', 'pinMode', 'favMode', 'count', 'skip', 'maxAge', 'order', 'showNoNews', 'skipCurrent', 'extractImages') as $k)
                    if (!isset($params[$k]))
                        $params[$k] = pluginGetVariable('xnews', $i.'_'.$k);

                // Load template only if it's defined
                if (!isset($params['skin']) and (pluginGetVariable('xnews', $i.'_skin') != ''))
                    $params['skin'] = pluginGetVariable('xnews', $i.'_skin');

                break;
            }
        }
        if (!$isFound) {
            return '[XNEWS: No profile found "'.$params['id'].'"]';
        }

    }
    
    foreach (array(
        'categoryMode',
        'categories',
        'visibilityMode',
        'visibilityCList',
        'mainMode',
        'pinMode',
        'favMode',
        'count',
        'skip',
        'maxAge',
        'order',
        'showNoNews',
        'skipCurrent',
        'extractImages') as $k) {
        if (empty($params[$k])) {
            if (is_null(pluginGetVariable('xnews', '1_'.$k))) {
                $params[$k] = null;
            } else {
                $params[$k] = pluginGetVariable('xnews', '1_'.$k);
            }
        }
    }
    if (empty($params['cacheAge'])) {
        if (is_null(pluginGetVariable('xnews', 'cache_expire'))) {
            $params['cacheAge'] = 60;
        } else {
            $params['cacheAge'] = pluginGetVariable('xnews', 'cache_expire');
        }
    }

    // Convert category list into array
    if (!is_array($params['categories'])) {
        $params['categories'] = preg_split('# *, *#', $params['categories'], -1, PREG_SPLIT_NO_EMPTY);
    }

    if (isset($params['visibilityCList']) and (!is_array($params['visibilityCList']))) {
        $params['visibilityCList'] = preg_split('# *, *#', $params['visibilityCList'], -1, PREG_SPLIT_NO_EMPTY);
    }

    $currentCategory = getCurrentNewsCategory();

    //print "<pre>".var_export($CurrentHandler, true)."</pre>";
    //print "Call params: <pre>".var_export($params, true)."</pre>";
    // Check if block should be displayed
    if (isset($params['visibilityMode']) and $params['visibilityMode'] > 0) {
        if ($CurrentHandler['pluginName'] != 'news')
            return;

        switch ($params['visibilityMode']) {
            // Everywhere
            case 0:
                break;

            // Only on category page
            case 1:
                if ($CurrentHandler['handlerName'] != 'by.category')
                    return false;

                break;
            // Only on news page
            case 2:
                if (($CurrentHandler['handlerName'] != 'news') and ($CurrentHandler['handlerName'] != 'print'))
                    return false;

                break;
            // On category/news page
            case 3:
                if (($CurrentHandler['handlerName'] != 'news') and ($CurrentHandler['handlerName'] != 'print') and ($CurrentHandler['handlerName'] != 'by.category'))
                    return false;

                break;
        }

        // Check if we're now in allowed category
        if (count($params['visibilityCList']) > 0) {
            $catched = false;

            foreach ($params['visibilityCList'] as $cx) {
                if (trim($cx) == $currentCategory[1]) {
                    $catched = true;
                    break;
                }
            }
            if (!$catched)
                return false;
        }
    }

    // ** Prepare a list of categories where this block should be displayed
    $categoryList = array();

    // Category display mode
    $params['categoryMode'] = isset($params['categoryMode']) ? $params['categoryMode'] : 0;
    $params['categories'] = isset($params['categories']) ? $params['categories'] : 0;
    switch ($params['categoryMode']) {
        // Only selected categories
        case 0:
            $categoryList = $params['categories'];
            break;

        // Only current category
        case 1:
            if (!is_array($currentCategory))
                return false;

            $categoryList = array($currentCategory[1]);
            break;

        // Selected + current categories
        case 2:
        $categoryList = $params['categories'];
        if (is_array($currentCategory) and !array_search($currentCategory[1], $categoryList))
            $categoryList []= $currentCategory[1];
        break;
    }

    // Prepare filters
    $filterList = array();

    // Prepare keys for cacheing
    $cacheKeys = array();
    $cacheDisabled = false;

    // mainMode
    if ($params['mainMode'] > 0) {
        $filterList []= '(mainpage = '.(($params['mainMode'] > 1)?'0':'1').')';
        $cacheKeys []= '|mainMode='.$params['mainMode'];
    }

    // pinMode
    if (isset($params['pinMode']) and ($params['pinMode'] > 0)) {
        $filterList []= '(pinned = '.(($params['pinMode'] > 1)?'0':'1').')';
        $cacheKeys []= '|pinMode='.$params['pinMode'];
    }

    // favMode
    if (isset($params['favMode']) and ($params['favMode'] > 0)) {
        $filterList []= '(favorite = '.(($params['favMode'] > 1)?'0':'1').')';
        $cacheKeys []= '|favMode='.$params['favMode'];
    }

    // skipCurrent
    if (isset($params['skipCurrent']) and $params['skipCurrent'] and (($CurrentHandler['handlerName'] == 'news') or ($CurrentHandler['handlerName'] == 'print'))) {
        list($xm, $null, $xi) = getCurrentNewsCategory();
        if (($xm == 'full') and ($xi > 0)) {
            $filterList []= '(id <> '.intval($xi).')';
            $cacheDisabled = true;
        }
    }

    $showCount = (!empty($params['count']) and $params['count'] > 0) ? intval($params['count']) : 10;
    $showSkip = (!empty($params['skip']) and $params['skip'] > 0) ? intval($params['skip']) : 0;
    $showAge = (!empty($params['maxAge']) and $params['maxAge'] > 0) ? intval($params['maxAge']) : 0;

    $cacheKeys []= '|count='.$showCount;
    $cacheKeys []= '|skip='.$showSkip;
    $cacheKeys []= '|maxAge='.$showAge;
    $cacheKeys []= '|embed='.intval($params['extractImages']);

    $cacheKeys []= '|categoryMode='.$params['categoryMode'];

    if ($showAge > 0) {
        $filterList []= '((unix_timestamp(now()) - postdate) < '.($showAge * 86400).')';
    }

    if((is_array($categoryList) and count($categoryList))) {
        
        $cacheKeys []= '|categories='.join(",",$categoryList);
        //print "categoryList [".var_export($categoryList, true)."]";

        $catFilter = array();
        foreach ($categoryList as $cat) {
            $catFilter [] = "(catid regexp '[[:<:]](".trim($cat).")[[:>:]]')";
        }
        $filterList [] = '('.join(' OR ', $catFilter).')';
    }

    $orderAllowed = array('viewed' => 'views desc', 'commented' => 'com desc', 'last' => 'postdate desc', 'random' => 'rand()');

    if ($params['order'] and isset($orderAllowed[$params['order']])) {
        $orderBy = $orderAllowed[$params['order']];
    } else {
        $orderBy = $orderAllowed['viewed'];
    }

    $cacheKeys []= '|order='.$orderBy;

    // Show only published news
    $filterList []= '(approve > 0)';

    // Prepare SQL query
    $requestQuery = "SELECT * FROM ".prefix."_news ".((count($filterList)>0)?"WHERE ".implode(" AND ", $filterList):"")." ORDER BY {$orderBy} LIMIT {$showSkip}, {$showCount}";

    $cacheKeys []= '|skin='.$params['skin'];

    // Generate cache file name [ we should take into account SWITCHER plugin ]
    if (!$cacheDisabled and ($params['cacheAge'] > 0)) {
        $cacheFileName = md5('xnews'.$config['theme'].$config['default_lang'].join('', $cacheKeys)).'.txt';
        $cacheData = cacheRetrieveFile($cacheFileName, $params['cacheAge'], 'xnews');
        if ($cacheData != false) {
            // We got data from cache. Return it and stop
            return $cacheData;
        }
    }

    // Desired template path and template name
    $tPath = plugin_locateTemplates('xnews', array('xnews'), $params['skin']);
    $tName = $tPath['xnews'] . 'xnews.tpl';

    // Generate main block
    $tVars = array(
        'items' => news_showlist(array(), array(), array(
                        'plugin' => 'xnews',
                        'extractImages' => $params['extractImages'],
                        'overrideSQLquery' => $requestQuery,
                        'extendedReturnArray' => true,
                        'twig' => true,
                    )),
    );

    // Manage `showNoNews` flag
    if (empty($tVars['items']) and $params['showNoNews']) {
        $xOut = '';
    } else {
        $xOut = $twig->render($tName, $tVars);
    }

    if (!$cacheDisabled and ($params['cacheAge'] > 0)) {
        cacheStoreFile($cacheFileName, $xOut, 'xnews');
    }

    return $xOut;
}

twigRegisterFunction('xnews', 'show', 'xNewsShowBlock');
