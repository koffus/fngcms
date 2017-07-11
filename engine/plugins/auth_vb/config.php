<?php

//
// Configuration file for plugin
//

// Load lang files
Lang::loadPlugin('auth_vb', 'config', 'auth');

// Fill configuration parameters
$cfg = array();

$cfgX = array();
array_push($cfg, array('descr' => __('auth_description')));
array_push($cfgX, array('descr' => __('auth_extdb_fulldesc')));
array_push($cfgX, array('name' => 'extdb', 'title' => __('auth_extdb_extdb'), 'descr' => __('auth_extdb_extdb_desc'), 'type' => 'select', 'values' => array('1' => __('yesa'), '0' => __('noa')), 'value' => pluginGetVariable('auth_vb','extdb')));
array_push($cfgX, array('name' => 'dbhost', 'title' => __('auth_extdb_dbhost'), 'type' => 'input', value => pluginGetVariable('auth_vb','dbhost')));
array_push($cfgX, array('name' => 'dbname', 'title' => __('auth_extdb_dbname'), 'type' => 'input', value => pluginGetVariable('auth_vb','dbname')));
array_push($cfgX, array('name' => 'dblogin', 'title' => __('auth_extdb_dblogin'), 'type' => 'input', value => pluginGetVariable('auth_vb','dblogin')));
array_push($cfgX, array('name' => 'dbpass', 'title' => __('auth_extdb_dbpass'), 'type' => 'input', value => pluginGetVariable('auth_vb','dbpass')));
array_push($cfg, array('mode' => 'group', 'title' => __('auth_extdb'), 'entries' => $cfgX));

$cfgX = array();
array_push($cfgX, array('name' => 'dbprefix', 'title' => __('auth_params_prefix'), 'descr' => __('auth_params_prefix_desc'), 'type' => 'input', value => pluginGetVariable('auth_vb','dbprefix')));
array_push($cfgX, array('name' => 'ipcheck', 'title' => 'Параметр &quot;<b>ipcheck</b>&quot; - Длина IP адреса для проверки сессии', 'descr' => 'Этот параметр необходимо взять из настроек форума, он указывает какая глубина проверки IP адреса будет делаться при проверке правильности сессии пользователя.<br/><b>*</b> Раздел: "Опции vBulletin" => "Настройки сервера и параметры оптимизации"','type' => 'select', 'values' => array ('0' => '0 | 255.255.255.255', '1' => '1 | 255.255.255.0', '2' => '2 | 255.255.0.0'), value => pluginGetVariable('auth_vb','ipcheck')));
array_push($cfgX, array('name' => 'cookietimeout', 'title' => 'Параметр &quot;<b>cookietimeout</b>&quot; - время жизни для сессии в cookie', 'descr' => 'Этот параметр необходимо взять из настроек форума.<br/><b>*</b> Раздел: "Опции vBulletin" => "Настройки сервера и параметры оптимизации"','type' => 'input', value => pluginGetVariable('auth_vb','cookietimeout')));
array_push($cfgX, array('name' => 'cookie_security_hash', 'title' => 'Значение переменной &quot;<b>cookie_security_hash</b>&quot; - параметр шифрации Cookie', 'descr' => 'Этот параметр необходимо взять из файла-конфигурации форума includes/config.php<br/><b>*</b>Значение по умолчанию <u>отсутствует</u>','type' => 'input', value => pluginGetVariable('auth_vb','cookie_security_hash')));
array_push($cfgX, array('name' => 'cookie_domain', 'title' => __('auth_params_domain'), 'descr' => __('auth_params_domain_desc'),'type' => 'input', value => pluginGetVariable('auth_vb','cookie_domain')));
array_push($cfgX, array('name' => 'setremember', 'title' => __('auth_setremember'), 'descr' => __('auth_setremember_desc'), 'type' => 'select', 'values' => array('0' => __('auth_mauto'), '1' => __('yesa'), '2' => __('noa')), 'value' => pluginGetVariable('auth_vb','setremember')));
array_push($cfg, array('mode' => 'group', 'title' => __('auth_params'), 'entries' => $cfgX));

$cfgX = array();
array_push($cfgX, array('name' => 'userjoin', 'title' => __('auth_auto_join'), 'descr' => __('auth_auto_join_desc'), 'type' => 'select', 'values' => array('1' => __('yesa'), '0' => __('noa')), 'value' => pluginGetVariable('auth_vb','userjoin')));
array_push($cfgX, array('name' => 'autocreate_ng', 'title' => __('auth_auto_ng'), 'descr' => __('auth_auto_ng_desc'), 'type' => 'select', 'values' => array('1' => __('yesa'), '0' => __('noa')), 'value' => pluginGetVariable('auth_vb','autocreate_ng')));
array_push($cfg, array('mode' => 'group', 'title' => __('auth_auto'), 'entries' => $cfgX));

// RUN
if (isset($_REQUEST['action']) and $_REQUEST['action'] == 'commit') {
	// If submit requested, do config save
	commit_plugin_config_changes($plugin, $cfg);
}

generate_config_page($plugin, $cfg);
