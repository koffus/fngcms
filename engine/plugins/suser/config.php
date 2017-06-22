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
switch($_REQUEST['action']) {
	case 'url': url(); break;
	default: main();
}

function url() {
	global $tpl;

	$tpath = locatePluginTemplates(array('config/main', 'config/url'), 'suser', 1);

	if (isset($_REQUEST['submit'])) {
		if(isset($_REQUEST['url']) and !empty($_REQUEST['url'])) {
			$ULIB = new urlLibrary();
			$ULIB->loadConfig(); 
			$ULIB->registerCommand('suser', '', array ('vars' => array(), 'descr' => array ('russian' => 'Список пользователей')));
			$ULIB->registerCommand('suser', 'search', array ('vars' => array(), 'descr' => array ('russian' => 'Поиск пользователей')));
			$ULIB->saveConfig();
		} else {
			$ULIB = new urlLibrary();
			$ULIB->loadConfig();
			$ULIB->removeCommand('suser', '');
			$ULIB->removeCommand('suser', 'search');
			$ULIB->saveConfig();
		}
		pluginSetVariable('suser', 'url', intval($_REQUEST['url']));
		pluginsSaveConfig();
		coreRedirectAndTerminate('admin.php?mod=extra-config&plugin=suser&action=url');
	}

	$url = pluginGetVariable('suser', 'url');
	$url = '<option value="0" '.(empty($url)?'selected':'').'>Нет</option><option value="1" '.(!empty($url)?'selected':'').'>Да</option>';
	$pvars['vars']['info'] = $url;

	$tpl->template('url', $tpath['config/url'].'config');
	$tpl->vars('url', $pvars);
	$tvars['vars']= array (
		'entries' => $tpl->show('url'),
		'global' => 'Настройка ЧПУ'
		);

	$tpl->template('main', $tpath['config/main'].'config');
	$tpl->vars('main', $tvars);

	print $tpl->show('main');
}

function main() {
	global $tpl;

	$tpath = locatePluginTemplates(array('config/main', 'config/general.from'), 'suser', 1);

	if (isset($_REQUEST['submit'])) {
		pluginSetVariable('suser', 'user_per_page', intval($_REQUEST['user_per_page']));
		pluginSetVariable('suser', 'title_plg', trim($_REQUEST['title_plg']));
		pluginSetVariable('suser', 'description', secure_html($_REQUEST['description']));
		pluginSetVariable('suser', 'keywords', secure_html($_REQUEST['keywords']));
		pluginSetVariable('suser', 'localSource', intval($_REQUEST['localSource']));
		pluginsSaveConfig();

		coreRedirectAndTerminate('admin.php?mod=extra-config&plugin=suser');
	}

	$user_per_page = pluginGetVariable('suser', 'user_per_page');
	$title_plg = pluginGetVariable('suser', 'title_plg');
	$description = pluginGetVariable('suser', 'description');
	$keywords = pluginGetVariable('suser', 'keywords');
	$localSource = pluginGetVariable('suser', 'localSource');
	$localSource = '<option value="0" '.(empty($localSource)?'selected':'').'>Шаблон сайта</option><option value="1" '.(!empty($localSource)?'selected':'').'>Плагин</option>';

	if(empty($user_per_page))
		msg(array('type' => 'danger', 'message' => "Критическая ошибка. <br /> Не задано количество пользователей на странице"), 1);

	$pvars['vars'] = array (
		'user_per_page' => $user_per_page,
		'title_plg' => $title_plg,
		'description' => $description,
		'keywords' => $keywords,
		'localSource' => $localSource,
		);

	$tpl->template('general.from', $tpath['config/general.from'].'config');
	$tpl->vars('general.from', $pvars);
	$tvars['vars']= array (
		'entries' => $tpl->show('general.from'),
		'global' => 'Общие'
		);

	$tpl->template('main', $tpath['config/main'].'config');
	$tpl->vars('main', $tvars);

	print $tpl->show('main');
}
