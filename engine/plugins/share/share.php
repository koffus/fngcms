<?php

// Protect against hack attempts
if (!defined('NGCMS')) die ('HAL');

class ShareNewsFilter extends NewsFilter {

    function showNews($newsID, $SQLnews, &$tvars, $mode = array()) {
        global $twig;

        // Load CORE Plugin
        $cPlugin = CPlugin::instance();

        Lang::loadPlugin('share', 'site');
        $localSkin = pluginGetVariable('share', 'localSkin');
        if (!$localSkin)
            $localSkin='basic';

        $tpath = locatePluginTemplates(array('share', ':share.css', ':share.js'), 'share', pluginGetVariable('share', 'localSource'), $localSkin);
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
        $xt = $twig->loadTemplate($tpath[$templateName] . $templateName . '.tpl');

        $tvars['vars']['plugin_share'] = $xt->render($tVars);
        
        return 1;
    }
}

pluginRegisterFilter('news','share', new ShareNewsFilter);
