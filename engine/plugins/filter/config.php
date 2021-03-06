<?php

/*
 * Configuration file for plugin
 */

// Protect against hack attempts
if (!defined('BBCMS')) die ('HAL');

// Load lang files
Lang::loadPlugin($plugin, 'admin', '', ':');

// Fill configuration parameters
$cfg = array(
    'description' => __($plugin.':description'),
    'submit' => array(
        array('type' => 'default'),
    )
    );

$cfgX = array();
	array_push($cfgX, array(
		'name' => 'replace',
		'title' => __($plugin.':replace'),
		'descr' => __($plugin.':replace#desc'),
		'type' => 'text',
		'html_flags' => 'rows="8"',
		'value' => pluginGetVariable('filter','replace'),
		));
	array_push($cfgX, array(
		'name' => 'block',
		'title' => __($plugin.':block'),
		'descr' => __($plugin.':block#desc'),
		'type' => 'text',
		'html_flags' => 'rows="8"',
		'value' => pluginGetVariable('filter','block'),
		));
array_push($cfg, array(
	'mode' => 'group',
	'title' => __('group.config'),
	'entries' => $cfgX,
	));

// RUN
if ('commit' == $action) {
	// If submit requested, do config save
	commit_plugin_config_changes($plugin, $cfg);
}

generate_config_page($plugin, $cfg);
