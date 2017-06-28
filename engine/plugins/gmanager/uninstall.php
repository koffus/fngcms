<?php

// Protect against hack attempts
if (!defined('NGCMS')) die ('HAL');
Lang::loadPlugin('gmanager', 'config', '', '', ':');
$ULIB = new UrlLibrary();
$ULIB->loadConfig();
$ULIB->removeCommand('gmanager', '');
$ULIB->removeCommand('gmanager', 'gallery');

if (isset($_REQUEST['action']) and $_REQUEST['action'] == 'commit') {
	plugin_mark_deinstalled('gmanager');
	$ULIB->saveConfig();
} else {
	$text = __('gmanager:desc_deinstall');
	generate_install_page('gmanager', $text, 'deinstall');
}