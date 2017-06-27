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
    $active = $cPlugin->getListActive();

	// Decide what to do
	switch ($mode) {
		// TURN _ON_
		case 'on':
			// Load plugin list
            $extras = $cPlugin->getList();
			if (!is_array($extras)) { return false; }
			if (!$extras[$pluginID]) { return false; }

			// Mark module as active
			$active['active'][$pluginID] = $extras[$pluginID]['dir'];

			// Mark module to be activated in all listed actions
			if (isset($extras[$pluginID]['acts']) and isset($extras[$pluginID]['file'])) {
				foreach (explode(',',$extras[$pluginID]['acts']) as $act) {
						$active['actions'][$act][$pluginID] = $extras[$pluginID]['dir'].'/'.$extras[$pluginID]['file'];
				}
			}

			foreach ($extras[$pluginID]['actions'] as $act => $file) {
				$active['actions'][$act][$pluginID] = $extras[$pluginID]['dir'].'/'.$file;
			}

			if (count($extras[$pluginID]['library']))
				$active['libs'][$pluginID] = $extras[$pluginID]['library'];

			// update active extra list in memory
			$PLUGINS['active'] = $active;
			return savePluginsActiveList();

		// TURN _OFF_
		case 'off':
			unset($active['active'][$pluginID]);
			unset($active['libs'][$pluginID]);

			foreach ($active['actions'] as $key => $value) {
				if ($active['actions'][$key][$pluginID]) {
						unset($active['actions'][$key][$pluginID]);
				}
			}

			$PLUGINS['active'] = $active;
			return savePluginsActiveList();
	}
	return false;
}

//
// Save list of active plugins & required files
//

function savePluginsActiveList(){
	global $PLUGINS;

	if (!is_file(conf_pactive))
		return false;

	if (!($file = fopen(conf_pactive, "w")))
		return false;

	$content = '<?php $array = '.var_export($PLUGINS['active'], true).'; ?>';
	fwrite($file, $content);
	fclose($file);

	return true;
}

//
// Mark plugin as installed
//
function plugin_mark_installed($plugin) {
	global $PLUGINS;

	// Load list of active plugins
    $cPlugin = CPlugin::instance();
    $active = $cPlugin->getListActive();

	// return if already installed
	if ($active['installed'][$plugin]) {
		return 1;
	}

	$active['installed'][$plugin] = 1;
	$PLUGINS['active'] = $active;
	return savePluginsActiveList();
}

//
// Mark plugin as deinstalled
//
function plugin_mark_deinstalled($plugin) {
	global $PLUGINS;

	// Load list of active plugins
    $cPlugin = CPlugin::instance();
    $active = $cPlugin->getListActive();

	// return if already installed
	if (!$active['installed'][$plugin]) {
		return 1;
	}

	unset($active['installed'][$plugin]);
	unset($active['active'][$plugin]);
	foreach ($active['actions'] as $k => $v) {
		unset($active['actions'][$k][$plugin]);
	}

	$PLUGINS['active'] = $active;
	return savePluginsActiveList();
}