<?php

//
// Configuration file for plugin
//

// Protect against hack attempts
if (!defined('NGCMS')) die ('HAL');

// Load lang files
Lang::loadPlugin($plugin, $plugin, '', ':');

// Fill configuration parameters
$cfg = array('description' => __($plugin.':description'));
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
if (isset($_REQUEST['action']) and $_REQUEST['action'] == 'commit') {
	// If submit requested, do
	messaging($_REQUEST['subject'], $_REQUEST['content']);
} else {
	generate_config_page('messaging', $cfg);
}
