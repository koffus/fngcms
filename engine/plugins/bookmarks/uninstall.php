<?php

# protect against hack attempts
if (!defined('NGCMS')) die ('HAL');

# preload config file


$db_update = array(
	array(
		'table' => 'bookmarks',
		'action' => 'drop',
	),
);

if (isset($_REQUEST['action']) and $_REQUEST['action'] == 'commit') {
	if (fixdb_plugin_install($plugin, $db_update, 'deinstall')) {
		plugin_mark_deinstalled($plugin);
	}
} else {
	generate_install_page($plugin, 'Плагин успешно удален', 'deinstall');
}

?>