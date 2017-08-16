<?php

//
// Configuration file for plugin
//

// Protect against hack attempts
if (!defined('NGCMS')) die ('HAL');

// Load lang files
Lang::loadPlugin($plugin, $plugin, '', ':');

// Fill configuration parameters
$cfg = array(
    'description' => __($plugin.':description'),
    'submit' => array(
        array('type' => 'default'),
    )
    );

array_push($cfg, array(
    'name' => 'subject',
    'title' => __($plugin.':subject'),
    'type' => 'input',
    'value' => '',
    ));
array_push($cfg, array(
    'name' => 'content',
    'title' => __($plugin.':content'),
    'type' => 'text',
    'html_flags' => 'rows="10" name="content" id="content"',
    'value' => '',
    ));

// RUN
if ('commit' == $action) {
    // If submit requested, do
    messaging($_REQUEST['subject'], $_REQUEST['content']);
}

// This plugin always generated config page
generate_config_page($plugin, $cfg);
