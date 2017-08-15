<?php

// #====================================================================================#
// # Наименование плагина: nsched [ News SCHEDuller ] #
// # Разрешено к использованию с: Next Generation CMS #
// # Автор: Vitaly A Ponomarev, vp7@mail.ru #
// #====================================================================================#

// #====================================================================================#
// # Инсталл скрипт плагина #
// #====================================================================================#
// Protect against hack attempts
if (!defined('NGCMS')) die ('HAL');

Lang::loadPlugin('nsched', 'install');

$db_update = array(
 array(
 'table' => 'news',
 'action' => 'modify',
 'fields' => array(
 array('action' => 'drop', 'name' => 'nsched_activate'),
 array('action' => 'drop', 'name' => 'nsched_deactivate'),
 )
 ),
);

// RUN
if ('commit' == $action) {
	// If submit requested, do config save
	if (fixdb_plugin_install('nsched', $db_update, 'deinstall')) {
		plugin_mark_deinstalled('nsched');
	}	
} else {
	$text = 'При удалении плагина <b>nsched</b> вся информация о расписании размещения/удаления новостей будет потеряна!<br><br>';
	generate_install_page('nsched', $text, 'deinstall');
}