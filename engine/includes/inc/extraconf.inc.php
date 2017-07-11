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
	global $PLUGINS;

	// Load list of active plugins
    $cPlugin = CPlugin::instance();
    $listActive = $cPlugin->getListActive();

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
			$listActive['active'][$pluginID] = $extras[$pluginID]['dir'];

			// Mark module to be activated in all listed actions
			if (isset($extras[$pluginID]['acts']) and isset($extras[$pluginID]['file'])) {
				foreach (explode(',',$extras[$pluginID]['acts']) as $act) {
						$listActive['actions'][$act][$pluginID] = $extras[$pluginID]['dir'].'/'.$extras[$pluginID]['file'];
				}
			}

			foreach ($extras[$pluginID]['actions'] as $act => $file) {
				$listActive['actions'][$act][$pluginID] = $extras[$pluginID]['dir'].'/'.$file;
			}

			if (count($extras[$pluginID]['library']))
				$listActive['libs'][$pluginID] = $extras[$pluginID]['library'];

			// update active extra list in memory
			$PLUGINS['active'] = $listActive;
			return $cPlugin->saveListActive();

		// TURN _OFF_
		case 'off':
			unset($listActive['active'][$pluginID]);
			unset($listActive['libs'][$pluginID]);

			foreach ($listActive['actions'] as $key => $value) {
				if (isset($listActive['actions'][$key][$pluginID])) {
						unset($listActive['actions'][$key][$pluginID]);
				}
			}

			$PLUGINS['active'] = $listActive;
			return $cPlugin->saveListActive();
	}
	return false;
}

//
// Mark plugin as installed
//
function plugin_mark_installed($plugin) {
	global $PLUGINS;

	// Load list of active plugins
    $cPlugin = CPlugin::instance();
    $listActive = $cPlugin->getListActive();

	// return if already installed
	if (isset($listActive['installed'][$plugin])) {
		return 1;
	}

	$listActive['installed'][$plugin] = 1;
	$PLUGINS['active'] = $listActive;
	return $cPlugin->saveListActive();
}

//
// Mark plugin as deinstalled
//
function plugin_mark_deinstalled($plugin) {
	global $PLUGINS;

	// Load list of active plugins
    $cPlugin = CPlugin::instance();
    $listActive = $cPlugin->getListActive();

	// return if already installed
	if (!$listActive['installed'][$plugin]) {
		return 1;
	}

	unset($listActive['installed'][$plugin]);
	unset($listActive['active'][$plugin]);
	foreach ($listActive['actions'] as $k => $v) {
		unset($listActive['actions'][$k][$plugin]);
	}

	$PLUGINS['active'] = $listActive;
	return $cPlugin->saveListActive();
}