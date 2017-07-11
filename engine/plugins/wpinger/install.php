<?php

// Protect against hack attempts
if (!defined('NGCMS')) die ('HAL');

//
// Install script for plugin.
// $action: possible action modes
// 	confirm		- screen for installation confirmation
//	apply		- apply installation, with handy confirmation
//	autoapply - apply installation in automatic mode [INSTALL script]
//

function plugin_wpinger_install($action) {

	if ($action != 'autoapply')
		Lang::loadPlugin('wpinger', 'config', '', ':');

	// Apply requested action
	switch ($action) {
		case 'confirm':
			generate_install_page('wpinger', __('wpinger:install_text'));
			break;
		case 'autoapply':
		case 'apply':
			// Now we need to set some default params
			$params = array(
				'proxy' => 1,
				'urls' => "http://ping.blogs.yandex.ru/RPC2\nhttp://blogsearch.google.ru/ping/RPC2",
			);

			foreach ($params as $k => $v) {
				pluginSetVariable('wpinger', $k, $v);
			}
			
			plugin_mark_installed('wpinger');
            // Load CORE Plugin
            $cPlugin = CPlugin::instance();
            // Save configuration parameters of plugins
            $cPlugin->saveConfig();

			break;
	}
	return true;
}
