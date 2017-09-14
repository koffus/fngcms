<?php

// Protect against hack attempts
if (!defined('NGCMS')) die ('HAL');

function rss_import($params = [])
{
    global $config, $twig, $template, $parse;

    if (is_array($widgets = pluginGetVariable('rss_import', 'widgets'))){
        foreach($widgets as $id => $widget) {

            $widgetName = 'plugin_rss_import_'.$widget['name'];

            // Generate cache file name [ we should take into account SWITCHER plugin ]
            if ($widget['cache']) {
                $cacheFileName = md5($config['theme'] . $config['default_lang'] . $widgetName) . '.txt';
                $cacheData = cacheRetrieveFile($cacheFileName, $widget['cacheExpire'], 'rss_import');
                if ($cacheData != false) {
                    // We got data from cache. Return it and stop
                    $template['vars'][$widgetName] = $cacheData;
                    continue;
                }
            }

            // Desired template path and template name
            $tPath = locatePluginTemplates('rss', 'rss_import', $widget['localSource'], $widget['localSkin']);
            $tName = $tPath['rss'] . 'rss.tpl';

            // Get content RSS-chanel
            $rss = simplexml_load_file($widget['url']);
            if (empty($rss)) {
                $template['vars'][$widgetName] = 'RSS не доступен';
                continue;
            }

            // Get info for each item
            $j = 1;
            $items = [];
            foreach ($rss->channel->item as $item) {

                $images = [];

                if ($widget['images_enable']) {
                    $tempLine = $item->description;
                    // Scan for <img> tag
                    if (preg_match_all('/\<img (.+?)(?: *\/?)\>/i', $tempLine, $m)) {
                        // Analyze all found <img> tags for parameters
                        foreach ($m[1] as $kl) {
                            $klp = $parse->parseBBCodeParams($kl);
                            // Add record if src="" parameter is set
                            if (isset($klp['src'])) {
                                $images[]['src'] = $klp['src'];
                            }
                        }
                    }
                } else {
                    $description = preg_replace('/\<img (.+?)(?: *\/?)\>/i', '', $item->description);
                }

                if ($widget['description_enable']) {
                    $description = $parse->truncateHTML($item->description, $widget['description_length']);
                } else {
                    $description = NULL;
                }

                $items[] = [
                    'link' => $item->link,
                    'title' => $parse->truncateHTML($item->title, $widget['title_length']),
                    'dateStamp' => strtotime($item->pubDate),
                    'images' => $images,
                    'description' => $description,
                ];

                if ($j == $widget['items_count']) {
                    break;
                }
                $j++;
            }

            $tVars = [
                'widget_title' => $widget['title'],
                'items' => $items,
                ];
            $template['vars'][$widgetName] = $twig->render($tName, $tVars);

            if ($widget['cache']) {
                cacheStoreFile($cacheFileName, $template['vars'][$widgetName], 'rss_import');
            }
        }
    }
}

registerActionHandler('index', 'rss_import');
