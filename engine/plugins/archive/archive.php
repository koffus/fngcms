<?php

// Protect against hack attempts
if (!defined('BBCMS')) die ('HAL');

//
// Show data block for archive plugin
// Params:
//  * maxnum - Max num entries for archive
//  * counter - Show counter in the entries
//  * tcounter - Show text counter in the entries
//  * template - Personal template for plugin
//  * cache_expire - age of cache [in seconds]
function plugin_archive()
{
    global $config, $mysql, $twig, $template;

    // Load lang file
    Lang::loadPlugin('archive', 'site', '', ':');

    // Prepare configuration parameters
    $maxnum = pluginGetVariable('archive', 'maxnum');
    $counter = pluginGetVariable('archive', 'counter');
    $tcounter = pluginGetVariable('archive', 'tcounter');
    $cache = pluginGetVariable('archive', 'cache');
    $cache_expire = pluginGetVariable('archive', 'cache_expire');

    if (($maxnum < 1) or ($maxnum > 50))
        $maxnum = 12;

    // Generate cache file name [ we should take into account SWITCHER plugin ]
    if ($cache and $cache_expire > 0) {
        $cacheFileName = md5('archive' . $config['theme'] . $config['default_lang']) . $maxnum . '.txt';
        $cacheData = cacheRetrieveFile($cacheFileName, $cache_expire, 'archive');
        if ($cacheData != false) {
            // We got data from cache. Return it and stop
            return $template['vars']['plugin_archive'] = $cacheData;
        }
    }

    // Load list
    $items = [];
    $rows = $mysql->select("
        SELECT month(from_unixtime(postdate)) as month, year(from_unixtime(postdate)) as year, COUNT(id) AS cnt 
        FROM " . prefix . "_news 
        WHERE approve = '1' 
        GROUP BY year, month 
        ORDER BY year DESC, month DESC 
        limit " . $maxnum);
    foreach ($rows as $row) {

        $items[] = [
            'link' => generateLink('news', 'by.month', array('year' => $row['year'], 'month' => sprintf('%02u', $row['month']))),
            'title' => Lang::$months[$row['month'] - 1] . ' ' . $row['year'],
            'cnt' => $row['cnt'],
            'counter' => $counter,
            'ctext' => $tcounter ? ' ' . Padeg($row['cnt'], __('news.counter_case')) : '',
            ];
    }

    // Determine paths for all template files
    $tpath = plugin_locateTemplates('archive');

    // Collection of variables
    $tVars = [
        'widget_title' => __('archive:plugin_title'),
        'items' => $items,
    ];

    $template['vars']['plugin_archive'] = $twig->render($tpath['archive'] . 'archive.tpl', $tVars);

    // Store to cache file
    if ($cache and $cache_expire > 0) {
        cacheStoreFile($cacheFileName, $template['vars']['plugin_archive'], 'archive');
    }

}

registerActionHandler('index', 'plugin_archive');
