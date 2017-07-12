<?php

//
// Copyright (C) 2006-2008 Next Generation CMS (http://ngcms.ru/)
// Name: extraconf.inc.php
// Description: Plugin configuration manager
// Author: Vitaly Ponomarev
//

// Protect against hack attempts
if (!defined('NGCMS')) die ('HAL');

//
// Switch plugin ON/OFF
//
function pluginSwitch($pluginID, $mode = 'on') {

	// Load list of active plugins
    $cPlugin = CPlugin::instance();
    $plugins = $cPlugin->getList();

	// Decide what to do
	switch ($mode) {
		// TURN _ON_
		case 'on':
			// Load plugin list
            $extras = $cPlugin->getInfo();
			if (!is_array($extras)) {
                return false;
            }
			if (!$extras[$pluginID]) {
                return false;
            }

			// Mark module as active
			$plugins['active'][$pluginID] = $extras[$pluginID]['dir'];

			// Mark module to be activated in all listed actions
			if (isset($extras[$pluginID]['acts']) and isset($extras[$pluginID]['file'])) {
				foreach (explode(',',$extras[$pluginID]['acts']) as $act) {
						$plugins['actions'][$act][$pluginID] = $extras[$pluginID]['dir'].'/'.$extras[$pluginID]['file'];
				}
			}

			foreach ($extras[$pluginID]['actions'] as $act => $file) {
				$plugins['actions'][$act][$pluginID] = $extras[$pluginID]['dir'].'/'.$file;
			}

			if (count($extras[$pluginID]['library']))
				$plugins['libs'][$pluginID] = $extras[$pluginID]['library'];

			// update active extra list in memory: SET and SAVEACTIVE
            return ($cPlugin->setList($plugins) and $cPlugin->saveListActive());

		// TURN _OFF_
		case 'off':
			unset($plugins['active'][$pluginID]);
			unset($plugins['libs'][$pluginID]);

			foreach ($plugins['actions'] as $key => $value) {
				if (isset($plugins['actions'][$key][$pluginID])) {
						unset($plugins['actions'][$key][$pluginID]);
				}
			}

            // update active extra list in memory: SET and SAVEACTIVE
            return ($cPlugin->setList($plugins) and $cPlugin->saveListActive());
	}
	return false;
}

//
// Mark plugin as installed
//
function plugin_mark_installed($plugin) {
	// Load list of active plugins
    $cPlugin = CPlugin::instance();
    $plugins = $cPlugin->getList();

	// return if already installed
	if (isset($plugins['installed'][$plugin])) {
		return 1;
	}

	$plugins['installed'][$plugin] = 1;
	// update active extra list in memory: SET and SAVEACTIVE
    return ($cPlugin->setList($plugins) and $cPlugin->saveListActive());
}

//
// Mark plugin as deinstalled
//
function plugin_mark_deinstalled($plugin) {

	// Load list of active plugins
    $cPlugin = CPlugin::instance();
    $plugins = $cPlugin->getList();

	// return if already installed
	if (!$plugins['installed'][$plugin]) {
		return 1;
	}

	unset($plugins['installed'][$plugin]);
	unset($plugins['active'][$plugin]);
	foreach ($plugins['actions'] as $k => $v) {
		unset($plugins['actions'][$k][$plugin]);
	}

	// update active extra list in memory: SET and SAVEACTIVE
    return ($cPlugin->setList($plugins) and $cPlugin->saveListActive());
}