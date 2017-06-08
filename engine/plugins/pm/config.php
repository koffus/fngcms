<?php

/*
 * Plugin's "Private message" configuration file for NextGeneration CMS (http://ngcms.ru/)
 * Copyright (C) 2011 Alexey N. Zhukov (http://digitalplace.ru)
 * http://digitalplace.ru
 * 
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or (at
 * your option) any later version.
 * 
 * This program is distributed in the hope that it will be useful, but
 * WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU
 * General Public License for more details.
 * 
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA 02111-1307, USA.
 *
 */

//
// Configuration file for plugin
//

// Protect against hack attempts
if (!defined('NGCMS')) die ('HAL');

// Preload config file
pluginsLoadConfig();

// Load lang files
Lang::loadPlugin($plugin, 'config', '', '', ':');

// Fill configuration parameters
$cfg = array('description' => __($plugin.':description'));

$cfgX = array();
	array_push($cfgX, array(
		'name' => 'msg_per_page', 
		'title' => "Количество сообщений на странице<br /><small>По умолчанию: <code>10</code></small>", 
		'type' => 'input', 
		'value' => intval(pluginGetVariable($plugin, 'msg_per_page') ? intval(pluginGetVariable($plugin, 'msg_per_page')) : 10)
		));
	array_push($cfgX, array(
		'name' => 'title_length', 
		'title' => "Максимальная длина темы сообщения<br /><small>По умолчанию: <code>50</code></small>", 
		'type' => 'input', 
		'value' => intval(pluginGetVariable($plugin, 'title_length') ? intval(pluginGetVariable($plugin, 'title_length')) : 50)
		));
	array_push($cfgX, array(
		'name' => 'message_length', 
		'title' => "Максимальная длина сообщения<br /><small>По умолчанию: <code>3000</code></small>", 
		'type' => 'input', 
		'value' => intval(pluginGetVariable($plugin, 'message_length') ? intval(pluginGetVariable($plugin, 'message_length')) : 3000)
		));
array_push($cfg, array(
	'mode' => 'group',
	'title' => __('group.config'),
	'entries' => $cfgX,
	));

$cfgX = array();
	array_push($cfgX, array(
		'name' => 'localSource',
		'title' => __('localSource'),
		'descr' => __('localSource#desc'),
		'type' => 'select',
		'values' => array('0' => __('localSource_0'), '1' => __('localSource_1'),),
		'value' => intval(pluginGetVariable($plugin, 'localSource')) ? intval(pluginGetVariable($plugin, 'localSource')) : '0',
		));
array_push($cfg, array(
	'mode' => 'group',
	'title' => __('group.source'),
	'entries' => $cfgX,
	));

$cfgX = array();
array_push($cfgX, array(
	'name' => 'rebuild', 
	'title' => __('rebuild'),
	'descr' => __('rebuild#desc'),
	'type' => 'select', 
	'value' => 0, 
	'values' => array('1' => __('yesa'), '0' => __('noa')),
	'nosave' => 1
	));
array_push($cfg, array(
	'mode' => 'group',
	'title' => __('group.rebuild'),
	'entries' => $cfgX,
	));

// RUN
if ($_REQUEST['action'] == 'commit') {
	// Rebuild index table
	if ($_REQUEST['rebuild']) {
		if($mysql->query('UPDATE '.prefix.'_users SET `pm_sync` = 0'))
			msg(array('message' => __('rebuild.done')));
		generate_config_page($plugin, $cfg);
	} else {
		// If submit requested, do config save
		commit_plugin_config_changes($plugin, $cfg);
		print_commit_complete($plugin, $cfg);
	}
} else {
	generate_config_page($plugin, $cfg);
}