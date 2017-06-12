<?php

//
// Configuration file for plugin
//

// Protect against hack attempts
if (!defined('NGCMS')) die ('HAL');

// Preload config file
pluginsLoadConfig();

// Load lang files
Lang::loadPlugin($plugin, 'config', '', '', ':');

// Prepare configuration parameters
$skList = array();
if ($skDir = opendir(extras_dir.'/'.$plugin.'/tpl/skins')) {
	while ($skFile = readdir($skDir)) {
		if (!preg_match('/^\./', $skFile)) {
			$skList[$skFile] = $skFile;
		}
	}
	closedir($skDir);
}

// Fill configuration parameters
$cfg = array('description' => __($plugin.':description'));

$cfgX = array();
	array_push($cfgX, array(
		'name' => 'rotate',
		'title' => __($plugin.':rotate'),
		'descr' => __($plugin.':rotate#desc'),
		'type' => 'select',
		'values' => array('1' => __('yesa'), '0' => __('noa')),
		'value' => pluginGetVariable('voting','rotate'),
		));
	array_push($cfgX, array(
		'name' => 'active',
		'title' => __($plugin.':active'),
		'descr' => __($plugin.':active#desc'),
		'type' => 'select',
		'values' => (array('0' => ' -- ') + mkVoteList()),
		'value' => pluginGetVariable('voting','active'),
		));
	array_push($cfgX, array(
		'name' => 'secure',
		'title' => __($plugin.':secure'),
		'descr' => __($plugin.':secure#desc'),
		'type' => 'select',
		'values' => array('1' => 'БД', '0' => 'Cookie'),
		'value' => pluginGetVariable('voting','secure'),
		));
	array_push($cfgX, array(
		'name' => 'vpp',
		'title' => __($plugin.':vpp'),
		'descr' => __($plugin.':vpp#desc'),
		'type' => 'input',
		'value' => intval(pluginGetVariable('voting','vpp')),
		));
array_push($cfg, array(
	'mode' => 'group',
	'title' => __('group.config'),
	'entries' => $cfgX,
	));

$cfgX = array();
	array_push($cfgX, array(
		'name' => 'localSource',
		'title' => __('localSource'),
		'descr' => __('localSource#desc'),
		'type' => 'select',
		'values' => array('0' => __('localSource_0'), '1' => __('localSource_1'),),
		'value' => intval(pluginGetVariable($plugin, 'localSource')) ? intval(pluginGetVariable($plugin, 'localSource')) : '0',
		));
	array_push($cfgX, array(
		'name' => 'localSkin',
		'title' => __('localSkin'),
		'descr' => __('localSkin#desc'),
		'type' => 'select',
		'values' => $skList,
		'value' => pluginGetVariable($plugin,'localSkin') ? pluginGetVariable($plugin,'localSkin') : 'basic',
		));
array_push($cfg, array(
	'mode' => 'group',
	'title' => __('group.source'),
	'entries' => $cfgX,
	));

function mkVoteList() {
	global $mysql;
	$res = array();
	foreach ($mysql->select("select * from ".prefix."_vote where active = 1") as $row) {
		$res[$row['id']] = $row['name'];
	}
	return $res;
}

function mkVoteSkinList() {
	$dir = opendir();
}

if ($_REQUEST['action'] == 'newvote') {
	$mysql->query("insert into ".prefix."_vote (name) values (".db_squote('** новое голосование **').")");
	print "Новый опрос создан. <a href='$PHP_SELF?mod=extra-config&plugin=voting'>переход к редактированию</a>";

} else if ($_REQUEST['action'] == 'delvote') {
	$voteid = intval($_REQUEST['id']);

	if ($row = $mysql->record("select * from ".prefix."_vote where id = $voteid")) {
		$mysql->query("delete from ".prefix."_voteline where voteid = $voteid");
		$mysql->query("delete from ".prefix."_vote where id = $voteid");
		print "Опрос удалён. ";

	} else {
		print "Такого опроса не существует. ";
	}
	print "<a href='$PHP_SELF?mod=extra-config&plugin=voting'>переход к редактированию</a>";
} else if ($_REQUEST['action'] == 'commit') {
	// Let's look what do we need to do.

	// First - process voteline updates/deletes
	foreach ($_REQUEST as $rq => $rv) {
		if (preg_match('/^vename_(\d+)$/',$rq, $match)) {
			$lid = $match[1];
			$vecnt = (strlen($_REQUEST['vecount_'.$lid])) ? ", cnt = ".intval($_REQUEST['vecount_'.$lid]) : '';
			$vename = $_REQUEST['vename_'.$lid];
			$veactive = intval($_REQUEST['veactive_'.$lid]);
			$vedel = $_REQUEST['vedel_'.$lid];

			if ($vedel) {
				$mysql->query("delete from ".prefix."_voteline where id = $lid");
			} else {
				$mysql->query("update ".prefix."_voteline set name = ".db_squote($vename)." $vecnt, active = $veactive where id = $lid");
			}
		}
	}

	// Next, process voteline inserts
	foreach ($_REQUEST as $rq => $rv) {
		if (preg_match('/^viname_(\d+)_(\d+)$/',$rq, $match)) {
			$vid = $match[1];
			$lid = $vid.'_'.$match[2];

			$vecnt = intval($_REQUEST['vicount_'.$lid]);
			$vename = $_REQUEST['viname_'.$lid];
			$veactive = intval($_REQUEST['viactive_'.$lid]);
			$vedel = $_REQUEST['videl_'.$lid];

			if (!$vedel) {
				$mysql->query("insert into ".prefix."_voteline(voteid, name, cnt, active) values($vid,".db_squote($vename).",$vecnt, $veactive)");
			}
		}
	}

	// Next, process vote updates
	foreach ($_REQUEST as $rq => $rv) {
		if (preg_match('/^vname_(\d+)$/',$rq, $match)) {
			$lid = $match[1];
			$vname = $_REQUEST['vname_'.$lid];
			$vdescr = $_REQUEST['vdescr_'.$lid];
			$vactive = intval($_REQUEST['vactive_'.$lid]);
			$vclosed = intval($_REQUEST['vclosed_'.$lid]);
			$vregonly = intval($_REQUEST['vregonly_'.$lid]);

			$mysql->query("update ".prefix."_vote set name=".db_squote($vname).", descr=".db_squote($vdescr).", active=$vactive, closed=$vclosed, regonly=$vregonly where id = $lid");
		}
	}

	// Next, process inserts

	//var_dump($_REQUEST);
	commit_plugin_config_changes($plugin, $cfg);
	print_commit_complete($plugin, $cfg);
} else {
	array_push($cfg, array('type' => 'flat', 'input' => '<tr><td colspan=2 style="background-color: #FFFF00;font : normal 14px verdana, sans-serif; padding: 4px;">'.__($plugin.':hdr.votelist').'</td></tr>'));
	array_push($cfg, array('type' => 'flat', 'input' => '<tr><td align=left style="padding-left: 14px;"><input type=button value="'.__($plugin.':button.create').'" style="width:343px;" onclick="document.location='."'".$PHP_SELF."?mod=extra-config&plugin=voting&action=newvote'".';"/></td><td align=right style="padding-top: 8px; padding-bottom: 8px;"> <input type=button value="'.__($plugin.':button.show_all').'" style="width:170px;" onclick="showHide(1);"/> <input type=button value="'.__($plugin.':button.hide_all').'" style="width:170px;" onclick="showHide(0);"/>'));
	$tpl->template('sheader',extras_dir.'/voting/tpl');
	$tpl->vars('sheader', array());
	array_push($cfg, array('type' => 'flat', 'input' => $tpl->show('sheader')));

	$tpl->template('vote',extras_dir.'/voting/tpl');
	$tpl->template('ventry',extras_dir.'/voting/tpl');

	$flag_nonactive = 0;
	$flag_active = 0;
	$flag_closed = 0;

	foreach ($mysql->select("select * from ".prefix."_vote order by active,closed") as $vrow) {
 	$cfgX = array();

	 if (!$vrow['active'] && !$vrow['closed'] && !$flag_nonactive) {
	 	$flag_nonactive = 1;
			array_push($cfg, array('type' => 'flat', 'input' => '<tr><td colspan=2 style="background-color: #AAAAAA;font : normal 10px verdana, sans-serif; padding: 4px;"><b>'.__($plugin.':hdr.inactive').'</b></td></tr>'));
	 }
	 if ($vrow['active'] && !$vrow['closed'] && !$flag_active) {
	 	$flag_active = 1;
			array_push($cfg, array('type' => 'flat', 'input' => '<tr><td colspan=2 style="background-color: #99BB88;font : normal 10px verdana, sans-serif; padding: 4px;"><b>'.__($plugin.':hdr.active').'</b></td></tr>'));
		}

	 if ($vrow['active'] && $vrow['closed'] && !$flag_closed) {
	 	$flag_closed = 1;
			array_push($cfg, array('type' => 'flat', 'input' => '<tr><td colspan=2 style="background-color: #77BB44;font : normal 10px verdana, sans-serif; padding: 4px;"><b>'.__($plugin.':hdr.closed').'</b></td></tr>'));
		}

		$ll = '';
		$allcnt = 0;
		foreach ($mysql->select("select * from ".prefix."_voteline where voteid=".$vrow['id']." order by id") as $row) {
			$tvars['vars'] = array(
					'name' => secure_html($row['name']),
					'count' => $row['cnt'],
					'id' => $row['id'],
					'veactive' => $row['active']?'checked':'');
			$allcnt += $row['cnt'];
			$tpl->vars('ventry', $tvars);
			$ll .= $tpl->show('ventry');
		}

		$tvars['vars'] = array(
			'entries' => $ll,
			'name' => secure_html($vrow['name']),
			'allcnt' => $allcnt,
			'descr' => secure_html($vrow['descr']),
			'voteid' => $vrow['id'],
			'vactive' => $vrow['active']?'checked':'',
			'vclosed' => $vrow['closed']?'checked':'',
			'vregonly' => $vrow['regonly']?'checked':'',
			'fregonly' => $vrow['regonly']?'[<b>'.__($plugin.':hdr.regflag').'</b>]':'',
			'php_self' => $PHP_SELF);
		$tpl->vars('vote', $tvars);
		array_push($cfgX, array('type' => 'flat', 'input' => $tpl->show('vote')));
		array_push($cfg, array('mode' => 'group',
		'title' => '[ '.__($plugin.':hdr.voting').': <b><font color=blue>'.$vrow['name'].'</font></b> ]', 'entries' => $cfgX));
	}
	generate_config_page($plugin, $cfg);
}
