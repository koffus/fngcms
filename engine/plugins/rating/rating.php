<?php

// Protect against hack attempts
if (!defined('BBCMS')) die ('HAL');

function rating_show($newsID, $rating, $votes)
{
    global $twig, $userROW;

    // Load CORE Plugin
    $cPlugin = CPlugin::instance();

    Lang::loadPlugin('rating', 'site');

    $tpath = plugin_locateTemplates('rating', array('rating', 'rating.form', ':rating.css'));
    $cPlugin->regHtmlVar('css', $tpath['url::rating.css'].'rating.css'); 
    
    $tVars = array(
        'tpl_url' => $tpath['url::rating.css'],
        'home' => home,
        'ajax_url' => generateLink('core', 'plugin', array('plugin' => 'rating'), array()),
        'post_id' => $newsID,
        'rating' => (!$rating or !$votes) ? 0 : round(($rating / $votes), 0),
        'votes' => $votes,
        );

    if (isset($_COOKIE['rating'.$newsID]) or (pluginGetVariable('rating','regonly') and !is_array($userROW))) {
        // Show
        $templateName = 'rating';
    } else {
        // Edit
        $templateName = 'rating.form';
    }
    return $twig->render($tpath[$templateName] . $templateName . '.tpl', $tVars);
}

class RatingNewsFilter extends NewsFilter {
    function showNews($newsID, $SQLnews, &$tvars, $mode = array()) {
        global $mysql, $userROW;

        $tvars['vars']['rating'] = rating_show($SQLnews['id'], $SQLnews['rating'], $SQLnews['votes']);
    }
}

pluginRegisterFilter('news','rating', new RatingNewsFilter);
