<?php

/*
 * Main file for plugin
 */

// Protect against hack attempts
if (!defined('BBCMS')) die ('HAL');

function plugin_rss_import($params = [])
{
    if (!is_array($widgets = pluginGetVariable('rss_import', 'widgets'))) return;

    global $config, $twig, $template, $parse;

    // Load lang files
    Lang::loadPlugin('rss_import', 'admin', '', ':');

    foreach($widgets as $id => $widget) {
        if (!$widget['active']) continue;

        $widgetName = 'plugin_rss_import_'.$widget['name'];

        // Generate cache file name [ we should take into account SWITCHER plugin ]
        if ($widget['cache']) {
            $cacheFileName = md5($config['theme'] . $config['default_lang'] . 'rss_import' . $widget['skin'] . $widgetName) . '.txt';
            $cacheData = cacheRetrieveFile($cacheFileName, $widget['cache_expire'], 'rss_import');
            if ($cacheData != false) {
                // We got data from cache. Return it and stop
                $template['vars'][$widgetName] = $cacheData;
                continue;
            }
        }

        // Get content RSS-chanel
        if (!$rss = @simplexml_load_file($widget['url'])) {
            $template['vars'][$widgetName] = __('rss_import:rss_n_a');
            continue;
        }

        // Get info for each item
        $j = 1;
        $tVars['widget_title'] = $widget['title'];
        foreach ($rss->channel->item as $item) {
            $tVars['items'][] = [
                'link' => $item->link,
                'title' => $parse->truncateHTML($item->title, $widget['title_length']),
                'dateStamp' => strtotime($item->pubDate),
                'embed' => ['images' => $widget['extract_images'] ? $parse->extractImages($item->description) : NULL],
                'description' => $widget['description_enable'] ? $parse->truncateHTML($item->description, $widget['description_length']) : NULL,
            ];
            if ($j == $widget['items_count']) break;
            $j++;
        }

        // Desired template path
        $tPath = plugin_locateTemplates('rss_import', 'rss', $widget['skin']);

        // Return widget content
        $template['vars'][$widgetName] = $twig->render($tPath['rss'] . 'rss.tpl', $tVars);

        // Store to cache, if enabled
        if ($widget['cache']) {
            cacheStoreFile($cacheFileName, $template['vars'][$widgetName], 'rss_import');
        }
    }
}

registerActionHandler('index', 'plugin_rss_import');
