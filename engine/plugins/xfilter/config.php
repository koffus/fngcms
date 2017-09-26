<?php

/*
 * Configuration file for plugin
 */

// Protect against hack attempts
if (!defined('BBCMS')) die ('HAL');

// Load lang files
Lang::loadPlugin($plugin, 'admin', '', ':');

// Load CORE Plugin
$cPlugin = CPlugin::instance();

/*// Check to dependence plugin
$dependence = [];
if (!$cPlugin->isActive('xfields')) {
    $dependence[] = 'xfields';
}*/

// Prepare configuration parameters
if (empty($skList = $cPlugin->getThemeSkin($plugin))) {
    msg(array('type' => 'danger', 'message' => __('msg.no_skin')));
}

$orderby = array(
	'id_desc' => __($plugin.':orderby_iddesc'), 
	'id_asc' => __($plugin.':orderby_idasc'), 
	'postdate_desc' => __($plugin.':orderby_postdatedesc'), 
	'postdate_asc' => __($plugin.':orderby_postdateasc'), 
	'title_desc' => __($plugin.':orderby_titledesc'), 
	'title_asc' => __($plugin.':orderby_titleasc'),
	);

// Fill configuration parameters
$cfg = array(
    'description' => __($plugin.':description'),
    //'dependence' => $dependence,
    'submit' => array(
        array('type' => 'default'),
    )
    );

$cfgX = array();
	array_push($cfgX, array(
		'name' => "{$currentVar}_skipcat", 
		'title' => __($plugin.':skipcat'), 
		'descr' => __($plugin.':skipcat#desc'), 
		'type' => 'input',
		'value' => pluginGetVariable($plugin, "{$currentVar}_skipcat"),
		));
	array_push($cfgX, array(
		'name' => "{$currentVar}_showAllCat",
		'type' => 'select',
		'title' => __($plugin.':showAllCat'),
		'values' => array(1 => __('yesa'), 0 => __('noa')), 
		'value' => pluginGetVariable($plugin, "{$currentVar}_showAllCat"),
		));
	array_push($cfgX, array(
		'name' => "{$currentVar}_order",
		'type' => 'select',
		'title' => __($plugin.':orderby_title'),
		'values' => $orderby,
		'value' => pluginGetVariable($plugin, "{$currentVar}_order"),
		));
	array_push($cfgX, array(
		'name' => "{$currentVar}_showNumber",
		'title' => __($plugin.':number_title'),
		'descr' => __($plugin.':number_title#desc'),
		'type' => 'input',
		'value' => intval(pluginGetVariable($plugin, "{$currentVar}_showNumber")) ? pluginGetVariable($plugin, "{$currentVar}_showNumber") : 10,
		));
array_push($cfg, array(
	'mode' => 'group', 
	'title' => __($plugin.':group'), 
	'entries' => $cfgX,
	));

$cfgX = array();
	array_push($cfgX, array(
        'name' => 'skin',
        'title' => __('skin'),
        'descr' => __('skin#desc'),
        'type' => 'select',
        'values' => $skList,
        'value' => pluginGetVariable($plugin, 'skin'),
    ));
array_push($cfg, array(
	'mode' => 'group',
	'title' => __('group.source'),
	'entries' => $cfgX,
	));

// RUN
if ('commit' == $action) {
	// If submit requested, do config save
	commit_plugin_config_changes($plugin, $cfg);
}

generate_config_page($plugin, $cfg);
