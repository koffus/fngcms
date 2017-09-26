<?php

/*
 * Configuration file for plugin
 */

// Protect against hack attempts
if (!defined('BBCMS')) die ('HAL');

// Load lang files
Lang::loadPlugin($plugin, 'admin', '', ':');

// Load CORE Plugin
$cPlugin = CPlugin::instance();

// Prepare configuration parameters
if (empty($skList = $cPlugin->getThemeSkin($plugin))) {
    msg(array( 'type' => 'danger', 'message' => __('msg.no_skin')));
}

// Fill configuration parameters
$cfg = array(
    'description' => __($plugin.':description'),
    'submit' => array(
        array('type' => 'default'),
    )
    );

$cfgX = array();
	array_push($cfgX, array(
		'name' => 'syscurrency',
		'title' => __('finance:syscurrency'),
		'descr' => __('finance:syscurrency.descr'),
		'type' => 'select',
		'values' => array('RUR' => 'RUR', 'EUR' => 'EUR', 'USD' => 'USD'),
		'value' => pluginGetVariable('finance','syscurrency'),
		));
array_push($cfg, array('mode' => 'group', 'title' => 'Общие настройки', 'entries' => $cfgX));

$b = array();
$select = $mysql->select("select * from ".prefix."_balance_manager order by id");
foreach ($select as $row) {
	$b[$row['id']] = array('monetary' => $row['monetary'], 'type' => $row['type'], 'description' => $row['description']);
}

for ($i = 1; $i < 5; $i++) {
	$cfgX = array();
		//array_push($cfgX, array('title' => '== <b>Настройки баланса №'.$i.'</b> =='));
		array_push($cfgX, array(
			'nosave' => 1,
			'name' => 'balance'.$i.'_monetary',
			'title' => __('finance:balance.monetary'),
			'descr' => __('finance:balance.monetary.descr'),
			'type' => 'select',
			'values' => array('1' => 'Да', '0' => 'Нет'),
			'value' => $b[$i]['monetary'],
			));
		array_push($cfgX, array(
			'nosave' => 1,
			'name' => 'balance'.$i.'_type',
			'title' => __('finance:balance.type'),
			'descr' => __('finance:balance.type.descr'),
			'type' => 'input',
			'value' => $b[$i]['type'],
			));
		array_push($cfgX, array(
			'nosave' => 1,
			'name' => 'balance'.$i.'_description',
			'title' => __('finance:balance.descr'),
			'descr' => __('finance:balance.descr.descr'),
			'type' => 'input',
			'value' => $b[$i]['description'],
			));
	array_push($cfg, array(
		'mode' => 'group',
		'title' => __('finance:balance.header').$i,
		'entries' => $cfgX,
		));
}

$cfgX = array();
	array_push($cfgX, array(
        'name' => 'skin',
        'title' => __('skin'),
        'descr' => __('skin#desc'),
        'type' => 'select',
        'values' => $skList,
        'value' => pluginGetVariable($plugin, 'skin'),
    ));
	array_push($cfgX, array(
		'name' => 'extends',
		'title' => 'Расположение блока',
		'descr' => 'Расположение блока в админ. панели при добавлении/редактировании новости',
		'type' => 'select',
		'values' => array (
			'main' => 'Основное содержание',
			'additional' => 'Дополнительно',
			'owner' => 'Собственный отдельный блок',
			),
		'value' => intval(pluginGetVariable($plugin,'extends')),
		));
array_push($cfg, array(
	'mode' => 'group',
	'title' => __($plugin.':group.source'),
	'entries' => $cfgX,
	));

// RUN
if ('commit' == $action) {

	// Save changes into DB
	for ($i = 1; $i < 5; $i++) {
		if ($row = $mysql->record("select * from ".prefix."_balance_manager where id = $i")) {
			$query = "update ".prefix."_balance_manager set monetary = ".db_squote($_POST['balance'.$i.'_monetary']).", type = ".db_squote($_POST['balance'.$i.'_type']).", description = ".db_squote($_POST['balance'.$i.'_description'])." where id = $i";
		} else {
			$query = "insert into ".prefix."_balance_manager (id, monetary, type, description) values ($i,".db_squote($_POST['balance'.$i.'_monetary']).",".db_squote($_POST['balance'.$i.'_type']).",".db_squote($_POST['balance'.$i.'_description']).")";
		}
		$mysql->query($query);
	}

	// If submit requested, do config save
	commit_plugin_config_changes($plugin, $cfg);
}

generate_config_page($plugin, $cfg);
