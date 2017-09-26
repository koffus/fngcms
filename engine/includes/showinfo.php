<?php

//
// Copyright (C) 2008 BixBite CMS (http://bixbite.site/)
// Name: showinfo.php
// Description: Show different informational blocks
// Author: Vitaly Ponomarev
//

if ('plugin' == $_REQUEST['mode']) {

    header('Content-Type: text/html; charset=UTF-8');
    include_once "../core.php";

    // Protect against hack attempts
    if (!defined('BBCMS')) die ('HAL');

    // Load CORE Plugin
    $cPlugin = CPlugin::instance();
    // Load plugin list
    $extras = $cPlugin->getInfo();

    $plugin = str_replace(array('/', '\\', '..'), '', secure_html($_REQUEST['plugin']));
    if (!is_array($extras[$plugin]))
        return;

    if ('readme' == $_REQUEST['item']) {
        if (file_exists(root.'plugins/'.$plugin.'/readme')) {
            print "<pre>";
            print file_get_contents(root.'plugins/'.$plugin.'/readme');
            print "</pre>";
        }
    }
    if ('history' == $_REQUEST['item']) {
        if (file_exists(root . 'plugins/' . $plugin . '/history')) {
            print "<pre>";
            print file_get_contents(root . 'plugins/' . $plugin . '/history');
            print "</pre>";
        }
    }
    
    /*?>
    <script src="<?php echo scriptLibrary ?>/js/showdown-1.7.1.js"></script>
    <script>
        var converter = new showdown.Converter();
        document.getElementsByTagName("pre")[0].innerHTML = converter.makeHtml(document.getElementsByTagName("pre")[0].innerHTML);
    </script>
    <?php*/
}
