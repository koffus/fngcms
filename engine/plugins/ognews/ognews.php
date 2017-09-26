<?php

// Protect against hack attempts
if (!defined('BBCMS')) die ('HAL');

class OGNEWSNewsFilter extends NewsFilter
{
    function showNews($newsID, $SQLnews, &$tvars, $mode = array())
    {
        global $CurrentHandler, $config;

        if (($CurrentHandler['handlerName'] == 'news') 
            and $SQLnews['alt_name'] == $CurrentHandler['params']['altname']
            and 'full' == $mode['style']
            ) {
                // Load CORE Plugin
                $cPlugin = CPlugin::instance();
                $alink = checkLinkAvailable('uprofile', 'show')?
                    generateLink('uprofile', 'show', array('name' => $SQLnews['author'], 'id' => $SQLnews['author_id']), array(), '', true):
                    generateLink('core', 'plugin', array('plugin' => 'uprofile', 'handler' => 'show'), array('name' => $SQLnews['author'], 'id' => $SQLnews['author_id']), '', true);
                $cPlugin->regHtmlVar('plain','<meta property="og:site_name" content="'.$config["home_title"].'">');
                $cPlugin->regHtmlVar('plain','<meta property="og:type" content="article">');
                $cPlugin->regHtmlVar('plain','<meta property="og:title" content="'.$SQLnews["title"].'">');
                $cPlugin->regHtmlVar('plain','<meta property="og:url" content="'. News::generateLink($SQLnews).'">');
                if (false) {
                    // only to facebook profile
                    $cPlugin->regHtmlVar('plain','<meta property="article:author" content="'.$alink.'">');
                } else {
                    $cPlugin->regHtmlVar('plain','<meta name="author" content="'.$SQLnews['author'].'">');
                }
                if (!empty($SQLnews['catid'])) {
                    $cPlugin->regHtmlVar('plain','<meta property="article:section" content="'.explode(',', GetCategories($SQLnews['catid'], true))[0].'">');
                }
                if (!empty($SQLnews['description'])) {
                    $cPlugin->regHtmlVar('plain','<meta property="og:description" content="'.$SQLnews['description'].'">');
                }
                if (!empty($SQLnews['keywords'])) {
                    $cPlugin->regHtmlVar('plain','<meta property="article:tag" content="'.$SQLnews['keywords'].'">');
                }
                if (!empty($SQLnews['postdate'])) {
                    $cPlugin->regHtmlVar('plain','<meta property="article:published_time" content="'.gmdate("Y-m-d\TH:i:s\Z", $SQLnews['postdate']).'">');
                }
                if (!empty($SQLnews['editdate'])) {
                    $cPlugin->regHtmlVar('plain','<meta property="article:modified_time" content="'.gmdate("Y-m-d\TH:i:s\Z", $SQLnews['editdate']).'">');
                }
                if($tvars['vars']['news']['embed']['imgCount'] > 0) {
                    $cPlugin->regHtmlVar('plain','<meta property="og:image" content="'.$tvars['vars']['news']['embed']['images'][0].'" />');
                } elseif(!empty($SQLnews['#images'])) {
                    foreach($SQLnews['#images'] as $img_item) {
                        $cPlugin->regHtmlVar('plain','<meta property="og:image" content="'.home.'/uploads/dsn/'.$img_item['folder'].'/'.$img_item['name'].'" />');
                    }
                }
        }
        return 1;
    }
}

pluginRegisterFilter('news', 'ognews', new OGNEWSNewsFilter);
