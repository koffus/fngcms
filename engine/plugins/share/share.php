<?php

// Protect against hack attempts
if (!defined('BBCMS')) die ('HAL');

class ShareNewsFilter extends NewsFilter {

    function showNews($newsID, $SQLnews, &$tvars, $mode = array()) {
        global $twig;

        // Load CORE Plugin
        $cPlugin = CPlugin::instance();

        Lang::loadPlugin('share', 'site');

        $tpath = plugin_locateTemplates('share', array('share', ':share.css', ':share.js'));
        $cPlugin->regHtmlVar('css', $tpath['url::share.css'].'/share.css'); 
        //$cPlugin->regHtmlVar('js', $tpath['url::share.js'].'/share.js'); 

        $tVars = array(
            'home' => home,
            'news' => array(
                'url' => $tvars['vars']['news']['url']['full'],
                'title' => $tvars['vars']['news']['title'],
                ),
            );
            
        $templateName = 'share';

        $tvars['vars']['plugin_share'] = $twig->render($tpath[$templateName] . $templateName . '.tpl', $tVars);
        
        return 1;
    }
}

pluginRegisterFilter('news','share', new ShareNewsFilter);
