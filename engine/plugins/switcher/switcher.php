<?php

// Protect against hack attempts
if (!defined('BBCMS')) die ('HAL');

// Load lang file
Lang::loadPlugin('switcher', 'site', '', ':');

registerActionHandler('core', 'plugin_switcher');
registerActionHandler('index', 'plugin_switcher_menu');
register_plugin_page('switcher', '', 'switcher_redirector', 0);

function plugin_switcher() {
    global $config;

    // Get chosen template
    $sw_template = isset($_COOKIE['sw_template']) ? intval($_COOKIE['sw_template']): 0;
    $sw_count = intval(pluginGetVariable('switcher', 'count'));
    if ( empty($sw_count) )
        $sw_count = 3;

    if ( ($sw_template> 0) and ($sw_template <= $sw_count) and pluginGetVariable('switcher','profile'.$sw_template.'_active') ) {
        if (pluginGetVariable('switcher','profile'.$sw_template.'_template')) {
            $config['theme'] = pluginGetVariable('switcher','profile'.$sw_template.'_template');
        }
        if (pluginGetVariable('switcher','profile'.$sw_template.'_lang')) {
            $config['default_lang'] = pluginGetVariable('switcher','profile'.$sw_template.'_lang');
        }
    }
}

function plugin_switcher_menu() {
    global $template, $twig;

    $list = '';
    $sw_count = intval(pluginGetVariable('switcher', 'count'));
    if ( empty($sw_count) )
        $sw_count = 3;

    $profile = (isset($_COOKIE['sw_template'])) ? intval($_COOKIE['sw_template']) : 1;
    for ( $i=1; $i <= $sw_count ; $i++ ) {
        if ( pluginGetVariable('switcher', 'profile'.$i.'_active') ) {
            $list .= '<option value="'.$i.'"'.($i === $profile ? ' selected' : '').'>'.pluginGetVariable('switcher', 'profile'.$i.'_name').'</option>';
        }
    }
    $tVars = array(
        'list' => $list,
        );
    $tpath = plugin_locateTemplates('switcher', array('switcher'));
    $template['vars']['plugin_switcher'] = $twig->render($tpath['switcher'] . 'switcher.tpl', $tVars);
}

function switcher_redirector() {
    $templateID = $_REQUEST['profile'];

    // Scan for template with this ID
    $sw_count = intval(pluginGetVariable('switcher','count'));
    if ( empty($sw_count) )
        $sw_count = 3;
    $templateNum = 0;
    for ( $i=1; $i <= $sw_count ; $i++ ) {
        if ( pluginGetVariable('switcher','profile'.$i.'_id') == $templateID ) {
            $templateNum = $i;
            break;
        }
    }
    // Set cookie with template ID
    @setcookie('sw_template', $templateNum, time() + 365 * 24 * 60 * 60, '/');
    // Redirect user:
    // if `redirect` is set - to specified URL
    // if `redirect` is not set - to root directory of the site
    coreRedirectAndTerminate((pluginGetVariable('switcher', 'profile'.$i.'_redirect') ? pluginGetVariable('switcher', 'profile'.$i.'_redirect') : home));
}
