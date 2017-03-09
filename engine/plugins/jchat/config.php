<?php

// Protect against hack attempts
if (!defined('NGCMS')) die ('HAL');

//
// Configuration file for plugin
//

// Preload config file
pluginsLoadConfig();

Lang::loadPlugin('jchat', 'config', '', '', ':');

// Calculate row count
$jcRowCount = $mysql->result("select count(*) from ".prefix."_jchat");
// Fill configuration parameters
$cfg = array();
array_push($cfg, array('descr' => __('jchat:desc')));

$cfgX = array();
array_push($cfgX, array('type' => 'flat', 'input' => '<tr><td class="contentEntry1" valign="top" colspan="2">Всего записей: '.$jcRowCount.'</td></tr>'));
array_push($cfgX, array('type' => 'flat', 'input' => '<tr><td class="contentEntry1" valign="top" colspan="2"><input type="checkbox" name="purge" value="1"/> Удалить старые записи, оставив последние <input type="text" name="purge_save" size="3" value="50"/></td></tr>'));
array_push($cfgX, array('type' => 'flat', 'input' => '<tr><td class="contentEntry1" valign="top" colspan="2"><input type="checkbox" name="reload" value="1"/> Перезагрузить страницу у всех посетителей</td></tr>'));
array_push($cfg, array('mode' => 'group', 'title' => '<b>'.__('jchat:conf.stat').'</b>', 'entries' => $cfgX));

$cfgX = array();
array_push($cfgX, array('name' => 'localsource', 'title' => __('jchat:localsource'), 'descr' => __('jchat:localsource#desc'), 'type' => 'select', 'values' => array ( '0' => __('jchat:lsrc.site'), '1' => __('jchat:lsrc.plugin')), 'value' => intval(pluginGetVariable($plugin,'localsource'))));
array_push($cfgX, array('name' => 'access', 'title' => __('jchat:access'), 'descr' => __('jchat:access#desc'), 'type' => 'select', 'values' => array ('0' => __('jchat:access.off'), '1' => __('jchat:access.ro'), '2' => __('jchat:access.rw')), 'value' => pluginGetVariable($plugin,'access')));
array_push($cfgX, array('name' => 'rate_limit', 'title' => __('jchat:rate_limit'), 'descr' => __('jchat:rate_limit#desc'), 'type' => 'input', 'value' => pluginGetVariable($plugin,'rate_limit')));
array_push($cfgX, array('name' => 'maxwlen', 'title' => __('jchat:maxwlen'), 'descr' => __('jchat:maxwlen#desc'), 'type' => 'input', 'value' => pluginGetVariable($plugin,'maxwlen')));
array_push($cfgX, array('name' => 'maxlen', 'title' => __('jchat:maxlen'), 'descr' => __('jchat:maxlen#desc'), 'type' => 'input', 'value' => pluginGetVariable($plugin,'maxlen')));
array_push($cfgX, array('name' => 'format_time', 'title' => __('jchat:format_time'), 'descr' => __('jchat:format_time#desc'), 'type' => 'input', 'value' => pluginGetVariable($plugin,'format_time')));
array_push($cfgX, array('name' => 'format_date', 'title' => __('jchat:format_date'), 'descr' => __('jchat:format_date#desc'), 'type' => 'input', 'value' => pluginGetVariable($plugin,'format_date')));
array_push($cfg, array('mode' => 'group', 'title' => '<b>'.__('jchat:conf.main').'</b>', 'entries' => $cfgX));

$cfgX = array();
array_push($cfgX, array('name' => 'enable_panel', 'title' => __('jchat:enable.panel'), 'descr' => __('jchat:enable.panel#desc'), 'type' => 'select', 'values' => array('0' => __('noa'), '1' => __('yesa')), 'value' => pluginGetVariable($plugin,'enable_panel')));
array_push($cfgX, array('name' => 'refresh', 'title' => __('jchat:refresh'), 'descr' => __('jchat:refresh#desc'), 'type' => 'input', 'value' => pluginGetVariable($plugin,'refresh')));
array_push($cfgX, array('name' => 'history', 'title' => __('jchat:history'), 'descr' => __('jchat:history#desc'), 'type' => 'input', 'value' => pluginGetVariable($plugin,'history')));
array_push($cfgX, array('name' => 'maxidle', 'title' => __('jchat:maxidle'), 'descr' => __('jchat:maxidle#desc'), 'type' => 'input', 'value' => pluginGetVariable($plugin,'maxidle')));
array_push($cfgX, array('name' => 'order', 'title' => __('jchat:order'), 'descr' => __('jchat:order#desc'), 'type' => 'select', 'values' => array('0' => __('jchat:order.asc'), '1' => __('jchat:order.desc')), 'value' => pluginGetVariable($plugin,'order')));
array_push($cfg, array('mode' => 'group', 'title' => '<b>'.__('jchat:conf.panel').'</b>', 'entries' => $cfgX));

$cfgX = array();
array_push($cfgX, array('name' => 'enable_win', 'title' => __('jchat:enable.win'), 'descr' => __('jchat:enable.win#desc'), 'type' => 'select', 'values' => array('0' => __('noa'), '1' => __('yesa')), 'value' => pluginGetVariable($plugin,'enable_win')));
array_push($cfgX, array('name' => 'win_mode', 'title' => __('jchat:win.mode'), 'descr' => __('jchat:win.mode#desc'), 'type' => 'select', 'values' => array('0' => __('jchat:win.mode.internal'), '1' => __('jchat:win.mode.external')), 'value' => pluginGetVariable($plugin,'win_mode')));
array_push($cfgX, array('name' => 'win_refresh', 'title' => __('jchat:refresh'), 'type' => 'input', 'value' => pluginGetVariable($plugin,'win_refresh')));
array_push($cfgX, array('name' => 'win_history', 'title' => __('jchat:history'), 'type' => 'input', 'value' => pluginGetVariable($plugin,'win_history')));
array_push($cfgX, array('name' => 'win_maxidle', 'title' => __('jchat:maxidle'), 'type' => 'input', 'value' => pluginGetVariable($plugin,'win_maxidle')));
array_push($cfgX, array('name' => 'win_order', 'title' => __('jchat:order'), 'type' => 'select', 'values' => array('0' => __('jchat:order.asc'), '1' => __('jchat:order.desc')), 'value' => pluginGetVariable($plugin,'win_order')));
array_push($cfg, array('mode' => 'group', 'title' => '<b>'.__('jchat:conf.window').'</b>', 'entries' => $cfgX));

// RUN
if ($_REQUEST['action'] == 'commit') {
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
	print_commit_complete($plugin, $cfg);
} else {
	generate_config_page($plugin, $cfg);
}