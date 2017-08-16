<?php

//
// Configuration file for plugin
//

// Protect against hack attempts
if (!defined('NGCMS')) die ('HAL');

// Load lang files
Lang::loadPlugin($plugin, 'config', '', ':');

// Prepare configuration parameters
$jcRowCount = $mysql->result("select count(*) from ".prefix."_jchat");

// Fill configuration parameters
$cfg = array(
    'description' => __($plugin.':description'),
    'submit' => array(
        array('type' => 'default'),
    )
    );

$cfgX = array();
	array_push($cfgX, array('name' => 'jcRowCount','title' => 'Всего записей','descr' => '','type' => 'input','html_flags' => 'readonly','value' => intval($jcRowCount),));
	array_push($cfgX, array('name' => 'purge_save','title' => 'Удалить старые записи, оставив последние','descr' => '','type' => 'manual','input' => '<div class="input-group"><span class="input-group-addon"><input type="checkbox" name="purge" value="1" /></span><input type="text" name="purge_save" value="50" size="3" class="form-control" /></div>',));
	array_push($cfgX, array('type' => 'manual','input' => '<input type="checkbox" name="reload" value="1" /> Перезагрузить страницу у всех посетителей',));
array_push($cfg, array('mode' => 'group','title' => __($plugin.':conf.stat'),'entries' => $cfgX,));

$cfgX = array();
	array_push($cfgX, array('name' => 'access', 'title' => __($plugin.':access'), 'descr' => __($plugin.':access#desc'), 'type' => 'select', 'values' => array ('0' => __($plugin.':access.off'), '1' => __($plugin.':access.ro'), '2' => __($plugin.':access.rw')), 'value' => pluginGetVariable($plugin,'access')));
	array_push($cfgX, array('name' => 'rate_limit', 'title' => __($plugin.':rate_limit'), 'descr' => __($plugin.':rate_limit#desc'), 'type' => 'input', 'value' => pluginGetVariable($plugin,'rate_limit')));
	array_push($cfgX, array('name' => 'maxwlen', 'title' => __($plugin.':maxwlen'), 'descr' => __($plugin.':maxwlen#desc'), 'type' => 'input', 'value' => pluginGetVariable($plugin,'maxwlen')));
	array_push($cfgX, array('name' => 'maxlen', 'title' => __($plugin.':maxlen'), 'descr' => __($plugin.':maxlen#desc'), 'type' => 'input', 'value' => pluginGetVariable($plugin,'maxlen')));
	array_push($cfgX, array('name' => 'format_time', 'title' => __($plugin.':format_time'), 'descr' => __($plugin.':format_time#desc'), 'type' => 'input', 'value' => pluginGetVariable($plugin,'format_time')));
	array_push($cfgX, array('name' => 'format_date', 'title' => __($plugin.':format_date'), 'descr' => __($plugin.':format_date#desc'), 'type' => 'input', 'value' => pluginGetVariable($plugin,'format_date')));
array_push($cfg, array(
	'mode' => 'group',
	'title' => __('group.config'),
	'entries' => $cfgX,
	));

$cfgX = array();
	array_push($cfgX, array('name' => 'enable_panel', 'title' => __($plugin.':enable.panel'), 'descr' => __($plugin.':enable.panel#desc'), 'type' => 'select', 'values' => array('0' => __('noa'), '1' => __('yesa')), 'value' => pluginGetVariable($plugin,'enable_panel')));
	array_push($cfgX, array('name' => 'refresh', 'title' => __($plugin.':refresh'), 'descr' => __($plugin.':refresh#desc'), 'type' => 'input', 'value' => pluginGetVariable($plugin,'refresh')));
	array_push($cfgX, array('name' => 'history', 'title' => __($plugin.':history'), 'descr' => __($plugin.':history#desc'), 'type' => 'input', 'value' => pluginGetVariable($plugin,'history')));
	array_push($cfgX, array('name' => 'maxidle', 'title' => __($plugin.':maxidle'), 'descr' => __($plugin.':maxidle#desc'), 'type' => 'input', 'value' => pluginGetVariable($plugin,'maxidle')));
	array_push($cfgX, array('name' => 'order', 'title' => __($plugin.':order'), 'descr' => __($plugin.':order#desc'), 'type' => 'select', 'values' => array('0' => __($plugin.':order.asc'), '1' => __($plugin.':order.desc')), 'value' => pluginGetVariable($plugin,'order')));
array_push($cfg, array('mode' => 'group', 'title' => __($plugin.':conf.panel'), 'entries' => $cfgX));

$cfgX = array();
	array_push($cfgX, array('name' => 'enable_win', 'title' => __($plugin.':enable.win'), 'descr' => __($plugin.':enable.win#desc'), 'type' => 'select', 'values' => array('0' => __('noa'), '1' => __('yesa')), 'value' => pluginGetVariable($plugin,'enable_win')));
	array_push($cfgX, array('name' => 'win_mode', 'title' => __($plugin.':win.mode'), 'descr' => __($plugin.':win.mode#desc'), 'type' => 'select', 'values' => array('0' => __($plugin.':win.mode.internal'), '1' => __($plugin.':win.mode.external')), 'value' => pluginGetVariable($plugin,'win_mode')));
	array_push($cfgX, array('name' => 'win_refresh', 'title' => __($plugin.':refresh'), 'type' => 'input', 'value' => pluginGetVariable($plugin,'win_refresh')));
	array_push($cfgX, array('name' => 'win_history', 'title' => __($plugin.':history'), 'type' => 'input', 'value' => pluginGetVariable($plugin,'win_history')));
	array_push($cfgX, array('name' => 'win_maxidle', 'title' => __($plugin.':maxidle'), 'type' => 'input', 'value' => pluginGetVariable($plugin,'win_maxidle')));
	array_push($cfgX, array('name' => 'win_order', 'title' => __($plugin.':order'), 'type' => 'select', 'values' => array('0' => __($plugin.':order.asc'), '1' => __($plugin.':order.desc')), 'value' => pluginGetVariable($plugin,'win_order')));
array_push($cfg, array('mode' => 'group', 'title' => __($plugin.':conf.window'), 'entries' => $cfgX));

$cfgX = array();
	array_push($cfgX, array(
		'name' => 'localSource',
		'title' => __('localSource'),
		'descr' => __('localSource#desc'),
		'type' => 'select',
		'values' => array('0' => __('localSource_0'), '1' => __('localSource_1'),),
		'value' => intval(pluginGetVariable($plugin, 'localSource')) ? intval(pluginGetVariable($plugin, 'localSource')) : 0,
		));
array_push($cfg, array(
	'mode' => 'group',
	'title' => __('group.source'),
	'entries' => $cfgX,
	));

// RUN
if ('commit' == $action) {
	// Check if we need to purge old messages
	if ($_REQUEST['purge']) {
		// Delete all extra records
		$dc = $jcRowCount - intval($_REQUEST['purge_save']);
		if (($_REQUEST['purge_save'] != '')&&($dc > 0)) {
			$mysql->query("delete from ".prefix."_jchat order by id limit ".$dc);
		}
	}

	// Check if we need to reload page
	if ($_REQUEST['reload']) {
		$mysql->query("insert into ".prefix."_jchat_events (chatid, postdate, type) values (1, unix_timestamp(now()), 3)");
		$lid = $mysql->result("select LAST_INSERT_ID()");
		$mysql->query("delete from ".prefix."_jchat_events where type=3 and id <> ".db_squote($lid));
	}

	// If submit requested, do config save
	commit_plugin_config_changes($plugin, $cfg);
}

generate_config_page($plugin, $cfg);
