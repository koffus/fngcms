<?php

/*
 * Configuration file for plugin
 */

// Protect against hack attempts
if (!defined('BBCMS')) die ('HAL');

function plugin_pm_install($action) {
	
	if ($action != 'autoapply')
		Lang::loadPlugin('pm', 'admin', '', ':');
		
	$db_create = array(
		array(
			'table' => 'pm',
			'action' => 'cmodify',
			'key' => 'primary key (`id`)',
			'fields' => array(
				array('action' => 'cmodify', 'name' => 'id', 'type' => 'int(10)', 'params' => 'UNSIGNED NOT NULL AUTO_INCREMENT'),
				array('action' => 'cmodify', 'name' => 'subject', 'type' => 'varchar(255)', 'params' => 'NOT NULL'),
				array('action' => 'cmodify', 'name' => 'message', 'type' => 'text', 'params' => 'NOT NULL'),
				array('action' => 'cmodify', 'name' => 'from_id', 'type' => 'int(10)', 'params' => 'NOT NULL'),
				array('action' => 'cmodify', 'name' => 'to_id', 'type' => 'int(10)', 'params' => 'NOT NULL'),
				array('action' => 'cmodify', 'name' => 'date', 'type' => 'int(10)', 'params' => 'NOT NULL'),
				array('action' => 'cmodify', 'name' => 'viewed', 'type' => 'tinyint(1)', 'params' => 'NOT NULL'),
				array('action' => 'cmodify', 'name' => 'folder', 'type' => 'varchar(10)', 'params' => 'NOT NULL')
			)
		),

		array(
			 'table' => 'users',
			 'action' => 'cmodify',
			 'fields' => array(
				array('action' => 'cmodify', 'name' => 'pm_all', 'type' => 'smallint(5)', 'params' => "default '0'"),
				array('action' => 'cmodify', 'name' => 'pm_unread', 'type' => 'smallint(5)', 'params' => "default '0'"),
				array('action' => 'cmodify', 'name' => 'pm_sync', 'type' => 'tinyint(1)', 'params' => "default '0'"),
				array('action' => 'cmodify', 'name' => 'pm_email', 'type' => 'tinyint(1)', 'params' => "default '1'"),
			)
		),
	);

	switch ($action) {
		case 'confirm': 
			 generate_install_page('pm', __('pm:install'));
			 break;
		case 'autoapply':
		case 'apply':
			if (fixdb_plugin_install('pm', $db_create, 'install', ($action=='autoapply')?true:false)) {
				plugin_mark_installed('pm');
			} else {
				return false;
			}
			break;
	}
	return true;
}
