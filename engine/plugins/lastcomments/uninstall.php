<?php

// Protect against hack attempts
if (!defined('NGCMS')) die ('HAL');

$ULIB = new UrlLibrary();
$ULIB->loadConfig();
$ULIB->removeCommand('lastcomments', '');
$ULIB->removeCommand('lastcomments', 'rss');

if (isset($_REQUEST['action']) and $_REQUEST['action'] == 'commit') {
	plugin_mark_deinstalled('lastcomments');
	$ULIB->saveConfig();
} else {
	generate_install_page('lastcomments', "Bye-Bye!", 'deinstall');
}