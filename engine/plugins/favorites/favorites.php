<?php

// Protect against hack attempts
if (!defined('NGCMS')) die ('HAL');

// Load lang file
Lang::loadPlugin('favorites', 'main', '', '', ':');

function plugin_favorites()
{
    global $config, $mysql, $twig, $template, $parse;

    $number = intval(pluginGetVariable('favorites', 'number'));
    $maxlength = intval(pluginGetVariable('favorites', 'maxlength'));
    $counter = pluginGetVariable('favorites', 'counter') ? intval(pluginGetVariable('favorites', 'counter')) : false;
    $cache = pluginGetVariable('favorites', 'cache');
    $cacheExpire = pluginGetVariable('favorites', 'cacheExpire');

    if (!$number) {
        $number = 10;
    }
    if (!$maxlength) {
        $maxlength = 100;
    }

    $templateName = 'favorites';

    // Generate cache file name [ we should take into account SWITCHER plugin ]
    $cacheFileName = md5('favorites' . $config['theme'] . $templateName . $config['default_lang'] . $number . $maxlength) . '.txt';
    if ($cache and $cacheExpire > 0) {
        $cacheData = cacheRetrieveFile($cacheFileName, pluginGetVariable('favorites', 'cacheExpire'), 'favorites');
        if ($cacheData != false) {
            // We got data from cache. Return it and stop
            $template['vars']['plugin_favorites'] = $cacheData;
            return;
        }
    }

    // Determine paths for all template files
    $tpath = locatePluginTemplates(array('entries', 'favorites'), 'favorites', pluginGetVariable('favorites', 'localSource'));

    foreach ($mysql->select("select id, alt_name, postdate, title, views, catid from " . prefix . "_news where favorite = '1' and approve = '1' limit 0," . $number) as $row) {
        if (mb_strlen($row['title'], 'UTF-8') > $maxlength) {
            $title = $parse->truncateHTML(secure_html($row['title']), 0, $maxlength);
        } else {
            $title = secure_html($row['title']);
        }

        $tVars['entries'][] = array(
            'link' => News::generateLink($row),
            'title' => $title,
            'views' => $counter ? (int)$row['views'] : '',
        );
    }

    $xt = $twig->loadTemplate($tpath[$templateName] . $templateName . '.tpl');
    $output = $xt->render($tVars);
    $template['vars']['plugin_favorites'] = $output;

    if ($cache and $cacheExpire > 0) {
        cacheStoreFile($cacheFileName, $output, 'favorites');
    }
}

registerActionHandler('index', 'plugin_favorites');
