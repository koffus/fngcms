<?php

// Protect against hack attempts
if (!defined('BBCMS')) die ('HAL');

function MDNews() {
    $body  = '<script src="' . scriptLibrary . '/js/showdown-1.7.1.js"></script>';
    $body .= '<script src="' . admin_url . '/plugins/md/inc/markdown.init.js"></script>';
    return $body;
}

class MDNewsFilter extends NewsFilter {
    
    function addNewsForm(&$tvars) {
        $extends = 'js';
        $tvars['extends'][$extends][] = array(
            'header_title' => __('autokeys:header_title'),
            'body' => MDNews(),
            );
        return 1;
    }
    
    function editNewsForm($newsID, $SQLold, &$tvars) {
        $extends = 'js';
        $tvars['extends'][$extends][] = array(
            'header_title' => __('autokeys:header_title'),
            'body' => MDNews(),
            );
        return 1;
    }
}

pluginRegisterFilter('news','md', new MDNewsFilter);
