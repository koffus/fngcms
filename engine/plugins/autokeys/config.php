<?php

//
// Configuration file for plugin
//

// Protect against hack attempts
if (!defined('NGCMS')) die ('HAL');

// Preload config file
pluginsLoadConfig();
Lang::loadPlugin($plugin, 'config', '', '', ':');

global $config;

if ( file_exists($stopFile = GetPluginDir($plugin).'/config/stop-words/' . $config['default_lang'] . '.sw.txt') )
	$stopText  = file_get_contents( $stopFile );
if ( file_exists($allowFile = GetPluginDir($plugin).'/config/allow-words.txt') )
	$allowText  = file_get_contents( $allowFile );

// Fill configuration parameters
$cfg = array('description' => __($plugin.':description'));

array_push($cfg, array(
	'name' => 'activate_add',
	'title' => __($plugin.':activate_add'),
	'type' => 'select',
	'values' => array(0 => __('noa'), 1 => __('yesa')),
	'value' => pluginGetVariable($plugin, 'activate_add'),
	));
array_push($cfg, array(
	'name' => 'activate_edit',
	'title' => __($plugin.':activate_edit'),
	'type' => 'select',
	'values' => array(0 => __('noa'), 1 => __('yesa')),
	'value' => pluginGetVariable($plugin, 'activate_edit'),
	));
array_push($cfg, array(
	'name' => 'length',
	'title' => __($plugin.':length'),
	'descr' => __($plugin.':length#desc'),
	'type' => 'input',
	'value' => pluginGetVariable($plugin, 'length'),
	));
array_push($cfg, array(
	'name' => 'sub',
	'title' => __($plugin.':sub'),
	'descr' => __($plugin.':sub#desc'),
	'type' => 'input',
	'value' => pluginGetVariable($plugin, 'sub'),
	));
array_push($cfg, array(
	'name' => 'occur',
	'title' => __($plugin.':occur'),
	'descr' => __($plugin.':occur#desc'),
	'type' => 'input',
	'value' => pluginGetVariable($plugin, 'occur'),
	));
array_push($cfg, array(
	'name' => 'add_title',
	'title' => __($plugin.':add_title'),
	'descr' => __($plugin.':add_title#desc'),
	'type' => 'input',
	'value' => pluginGetVariable($plugin, 'add_title'),
	));
array_push($cfg, array(
	'name' => 'sum',
	'title' => __($plugin.':sum'),
	'descr' => __($plugin.':sum#desc'),
	'type' => 'input',
	'value' => pluginGetVariable($plugin, 'sum'),
	));
array_push($cfg, array(
	'name' => 'count',
	'title' => __($plugin.':count'),
	'descr' => __($plugin.':count#desc'),
	'type' => 'input',
	'value' => pluginGetVariable($plugin, 'count'),
	));
array_push($cfg, array(
	'name' => 'good_b',
	'title' => __($plugin.':good_b'),
	'descr' => __($plugin.':good_b#desc'),
	'type' => 'select',
	'values' => array(0 => __('noa'), 1 => __('yesa')),
	'value' => pluginGetVariable($plugin, 'good_b'),
	));
array_push($cfg, array(
	'name' => 'block_y',
	'title' => __($plugin.':block_y'),
	'descr' => __($plugin.':block_y#desc'),
	'type' => 'select',
	'values' => array(0 => __('noa'), 1 => __('yesa')),
	'value' => pluginGetVariable($plugin, 'block_y'),
	));
array_push($cfg, array(
	'name' => 'block',
	'title' => __($plugin.':block'),
	'descr' => __($plugin.':block#desc'),
	'type' => 'text',
	'html_flags' => 'rows="8"',
	'value' => $stopText,
	));
array_push($cfg, array(
	'name' => 'good_y',
	'title' => __($plugin.':good_y'),
	'descr' => __($plugin.':good_y#desc'),
	'type' => 'select',
	'values' => array(0 => __('noa'), 1 => __('yesa')),
	'value' => pluginGetVariable($plugin, 'good_y'),
	));
array_push($cfg, array(
	'name' => 'good',
	'title' => __($plugin.':good'),
	'descr' => __($plugin.':good#desc'),
	'type' => 'text',
	'html_flags' => 'rows="8"',
	'value' => $allowText,
	));

// RUN
if ($_REQUEST['action'] == 'commit') {
	if (($fs = fopen($stopFile, 'w')) !== FALSE) {
		fwrite($fs, $_REQUEST['block'] );
		fclose($fs);
	} else {
		msg(array('type' => 'danger', 'message' => 'Ошибка записи файла стоп-слов!'));
	}
	if (($fa = fopen($allowFile, 'w')) !== FALSE) {
		fwrite($fa, $_REQUEST['good'] );
		fclose($fa);
	} else {
		msg(array('type' => 'danger', 'message' => 'Ошибка записи файла желательных слов!'));
	}

	// If submit requested, do config save
	commit_plugin_config_changes($plugin, $cfg);
	print_commit_complete($plugin, $cfg);
} else {
	generate_config_page($plugin, $cfg);
}
