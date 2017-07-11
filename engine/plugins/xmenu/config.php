<?php

//
// Configuration file for plugin
//

// Preload header data
$header = file_get_contents(root.'plugins/xmenu/tpl/mhead.tpl');

// Make an activity array - to mark menu's that are activated
$activity = array();
for ($i = 1; $i <= 9; $i++) {
	if ((is_array($var=pluginGetVariable('xmenu','activate'))) and ($var[$i]))
		$activity[] = "#go_".$i." { color: red; }";
}

// Prepare configuration array
$cfg = array();
array_push($cfg, array('type' => 'flat', 'input' => str_replace('{activity}', join("\n", $activity), $header).'</table>'));

// FIRST: Category list with menu mapping
$cfgX = array();
array_push($cfg, array('type' => 'flat', 'input' => '<div id="menu_0" style="display: block;">'."\n".'<table class="content table table-condensed table-bordered">'."\n"));
$outline = array('<tr><td rowspan=2><b>Название категории</b></td><td colspan="9" class="text-center"><b>Меню, в которых данная категория отображается</b></td></tr><tr class="text-center"><td>Меню 1</td><td>Меню 2</td><td>Меню 3</td><td>Меню 4</td><td>Меню 5</td><td>Меню 6</td><td>Меню 7</td><td>Меню 8</td><td>Меню 9</td></tr>');
foreach ($catz as $name => $val) {
	$line = '<tr><td>'.str_repeat('&#8212; ', $val['poslevel']).$val['name'].'</td>';
	for ($i = 1; $i <= 9; $i++ ) {
		$line .= '<td class="text-center"><input name="cmenu['.$catz[$name]['id'].']['.$i.']" value="1" type="checkbox"'.(($catz[$name]['xmenu']{$i-1}=='#')?' checked ':'').'/></td>';
	}
	$line .='</tr>';
	$outline []= $line;
}

array_push($cfgX, array('type' => 'flat', 'input' => join("\n", $outline)));
array_push($cfg, array('mode' => 'group', 'title' => '<b>Распределение категорий</b>', 'entries' => $cfgX));
array_push($cfg, array('type' => 'flat', 'input' => "\n</table></div>\n\n"));

// SECOND: Populate config parameters for menus 1-9
for ($i = 1; $i <= 9; $i++) {
	$cfgX = array();
	array_push($cfg, array('type' => 'flat', 'input' => '<div id="menu_'.$i.'" style="display: '.(!$i?'block':'none').';">'."\n".'<table class="content" border="0" cellspacing="0" cellpadding="0" align="center">'."\n"));
	array_push($cfgX, array('type' => 'select','name' => 'activate['.$i.']', 'title' => 'Активация меню', 'descr' => '<b>Да</b> - данное меню активно<br/><b>Нет</b> - меню неактивно', 'values' => array('0' => 'Нет', '1' => 'Да'), value => (is_array($var=pluginGetVariable('xmenu','activate'))?$var[$i]:0)));
	array_push($cfgX, array('type' => 'select','name' => 'mode['.$i.']', 'title' => 'Режим отображения меню', 'descr' => '<b>Выбранные категории</b> - отображаются категории, по которым выставлен флаг для данного меню<br/><b>Текущие подкатегории</b> - подкатегории текущей категории. В случае главной страницы - все категории 1 уровня', 'values' => array('0' => 'Выбранные категории', '1' => 'Текущие подкатегории'), value => (is_array($var=pluginGetVariable('xmenu','mode'))?$var[$i]:0)));
	array_push($cfgX, array('type' => 'select','name' => 'news['.$i.']', 'title' => 'Добавить новость категории', 'descr' => '<b>Да</b> - для каждой категории будет выводиться последняя новость из этой категории<br/><b>Нет</b> - будет отображаться только сама категория', 'values' => array('0' => 'Нет', '1' => 'Да'), value => (is_array($var=pluginGetVariable('xmenu','news'))?$var[$i]:0)));
	array_push($cfgX, array('type' => 'select','name' => 'skin['.$i.']', 'title' => 'Шаблон отображения меню', 'descr' => 'Выберите шаблон, с помощью которого будет отображаться данное меню', 'values' => array('default' => 'default'), value => (is_array($var=pluginGetVariable('xmenu','skin'))?$var[$i]:0)));
	array_push($cfg, array('mode' => 'group', 'title' => '<b>Настройки меню # '.$i.'</b>', 'entries' => $cfgX));
	array_push($cfg, array('type' => 'flat', 'input' => "\n</table></div>\n\n"));
}

// Print footer
array_push($cfg, array('type' => 'flat', 'input' => '<table class="content">'));

// RUN
if (isset($_REQUEST['action']) and $_REQUEST['action'] == 'commit') {
	// 1. Menus
	pluginSetVariable('xmenu', 'activate', $_REQUEST['activate']);
	pluginSetVariable('xmenu', 'mode', $_REQUEST['mode']);
	pluginSetVariable('xmenu', 'news', $_REQUEST['news']);
	pluginSetVariable('xmenu', 'skin', $_REQUEST['skin']);
    // Load CORE Plugin
    $cPlugin = CPlugin::instance();
    // Save configuration parameters of plugins
    $cPlugin->saveConfig();

	// 2. Categories menu mapping
	if (!is_array($_REQUEST['cmenu'])) {
		// No menus activated
		$mysql->query("update ".prefix."_category set xmenu=''");
	} else {
		$cmenu = $_REQUEST['cmenu'];
		foreach ($catz as $catname => $catdata) {
			$xline = '';
			if (is_array($cmenu[$catdata['id']])) {
				for ($i = 1; $i <= 9; $i++)
					$xline .= ($cmenu[$catdata['id']][$i])?'#':'_';
			} else {
				$xline = str_repeat('_',9);
			}
			$mysql->query("update ".prefix."_category set xmenu=".db_squote($xline)." where id = ".db_squote($catdata['id']));
		}
	}

	// If submit requested, do config save
	commit_plugin_config_changes($plugin, $cfg);
}

generate_config_page($plugin, $cfg);
