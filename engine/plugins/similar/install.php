<?php

// Protect against hack attempts
if (!defined('BBCMS')) die ('HAL');

//
// Configuration file for plugin
//



//
// Install script for plugin.
// $action: possible action modes
// 	confirm		- screen for installation confirmation
//	apply		- apply installation, with handy confirmation
//	autoapply - apply installation in automatic mode [INSTALL script]
//
function plugin_similar_install($action) {

	if ($action != 'autoapply')
        Lang::loadPlugin('similar', 'admin', '', ':');

	// Fill DB_UPDATE configuration scheme
	$db_update = array(
		array(
			'table' => 'news',
			'action' => 'cmodify',
			'fields' => array(
			// Status of similar news:
			// 0 - BROKEN (data should be rebuilded)
			// 1 - No SIMILAR data
			// 2 - Have SIMILAR data
			array('action' => 'cmodify', 'name' => 'similar_status', 'type' => 'int', 'params' => "default 0"),
			)
		),
		array(
			'table' => 'similar_index',
			'action' => 'cmodify',
			'key' => 'primary key(`id`), key `newsID` (`newsID`) ',
			'fields' => array(
				array('action' => 'cmodify', 'name' => 'id', 'type' => 'int', 'params' => 'not null auto_increment'),
				array('action' => 'cmodify', 'name' => 'dimension', 'type' => 'int', 'params' => 'default 0'),
				array('action' => 'cmodify', 'name' => 'newsID', 'type' => 'int'),
				array('action' => 'cmodify', 'name' => 'refNewsID', 'type' => 'int'),
				array('action' => 'cmodify', 'name' => 'refNewsQuantaty', 'type' => 'int'),
				array('action' => 'cmodify', 'name' => 'refNewsTitle', 'type' => 'varchar(120)', 'params' => ''),
				array('action' => 'cmodify', 'name' => 'refNewsDate', 'type' => 'int'),
			)
		),
	);

	// Apply requested action
	switch ($action) {
		case 'confirm':
			generate_install_page('similar', __('similar:install#desc'));
			break;
		case 'autoapply':
		case 'apply':
			if (fixdb_plugin_install('similar', $db_update, 'install', ($action=='autoapply')?true:false)) {
				plugin_mark_installed('similar');
			} else {
				return false;
			}

			// Now we need to set some default params
			$params = array(
				'count' => 5,
				'samecat_count' => 5,
			);

			foreach ($params as $k => $v) {
				pluginSetVariable('similar', $k, $v);
			}
            // Load CORE Plugin
            $cPlugin = CPlugin::instance();
            // Save configuration parameters of plugins
            $cPlugin->saveConfig();

			break;
	}
	return true;
}

