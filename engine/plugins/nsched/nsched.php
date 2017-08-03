<?php

// #====================================================================================#
// # Наименование плагина: nsched [ News SCHEDuller ] #
// # Разрешено к использованию с: Next Generation CMS #
// # Автор: Vitaly A Ponomarev, vp7@mail.ru #
// #====================================================================================#

// #====================================================================================#
// # Ядро плагина #
// #====================================================================================#
// Protect against hack attempts
if (!defined('NGCMS')) die ('HAL');

class NSchedNewsFilter extends NewsFilter {

	function __construct() {
		// не загружать здесь языки !!! При просмотре новости не нужны языки настроек плагина из админ.панели !!!
	}

	function addNewsForm(&$tvars) {
		global $twig;

		Lang::loadPlugin('nsched', 'config', '', ':');

		$perm = checkPermission(array('plugin' => '#admin', 'item' => 'news'), null, array('personal.publish', 'personal.unpublish', 'other.publish', 'other.unpublish'));

		$ttvars = array(
			'nactivate' => '',
			'ndeactivate' => '',
			'flags' => array(
				'permPublish' => $perm['personal.publish'] ? true : false,
				'permUnPublish' => $perm['personal.unpublish'] ? true : false,
				),
			);

		$extends = pluginGetVariable('nsched','extends') ? pluginGetVariable('nsched','extends') : 'owner';
		$tpath = locatePluginTemplates(array('news'), 'nsched', 1, 0, 'admin');
		$xt = $twig->loadTemplate($tpath['news'] . 'news.tpl');
		$tvars['extends'][$extends][] = array(
			'header_title' => __('nsched:header_title'),
			'body' => ($perm['personal.publish'] or $perm['personal.unpublish']) ? $xt->render($ttvars) : '',
			);

		return 1;
	}

	function addNews(&$tvars, &$SQL) {

		$perm = checkPermission(array('plugin' => '#admin', 'item' => 'news'), null, array('personal.publish', 'personal.unpublish', 'other.publish', 'other.unpublish'));
		if ($perm['personal.publish'])
			$SQL['nsched_activate'] = $_REQUEST['nsched_activate'];

		if ($perm['personal.unpublish'])
			$SQL['nsched_deactivate'] = $_REQUEST['nsched_deactivate'];

		return 1;
	}

	function editNewsForm($newsID, $SQLold, &$tvars) {
		global $userROW, $twig;

		Lang::loadPlugin('nsched', 'config', '', ':');

		$perm = checkPermission(array('plugin' => '#admin', 'item' => 'news'), null, array('personal.publish', 'personal.unpublish', 'other.publish', 'other.unpublish'));
		$isOwn = ($SQLold['author_id'] == $userROW['id']) ? 1 : 0;
		$permGroupMode = $isOwn ? 'personal' : 'other';
		$ndeactivate = $SQLold['nsched_deactivate'];
		$nactivate = $SQLold['nsched_activate'];
		if ( !intval($nactivate) ) { $nactivate = ''; }
		if ( !intval($ndeactivate) ) { $ndeactivate = ''; }

		$ttvars = array(
			'nactivate' => $nactivate,
			'ndeactivate' => $ndeactivate,
			'flags' => array(
				'permPublish' => $perm[$permGroupMode.'.publish'] ? true : false,
				'permUnPublish' => $perm[$permGroupMode.'.unpublish'] ? true : false,
				),
			);

		$extends = pluginGetVariable('nsched','extends') ? pluginGetVariable('nsched','extends') : 'owner';
		$tpath = locatePluginTemplates(array('news'), 'nsched', 1, 0, 'admin');
		$xt = $twig->loadTemplate($tpath['news'] . 'news.tpl');
		$tvars['extends'][$extends][] = array(
			'header_title' => __('nsched:header_title'),
			'body' => ($perm[$permGroupMode.'.publish'] or $perm[$permGroupMode.'.unpublish']) ? $xt->render($ttvars) : '',
			);

		return 1;
	}

	function editNews($newsID, $SQLold, &$SQLnew, &$tvars) {
		global $userROW;

		$perm = checkPermission(array('plugin' => '#admin', 'item' => 'news'), null, array('personal.publish', 'personal.unpublish', 'other.publish', 'other.unpublish'));
		$isOwn = ($SQLold['author_id'] == $userROW['id'])?1:0;
		$permGroupMode = $isOwn?'personal':'other';

		if ($perm[$permGroupMode.'.publish'])
			$SQLnew['nsched_activate'] = $_REQUEST['nsched_activate'];

		if ($perm[$permGroupMode.'.unpublish'])
			$SQLnew['nsched_deactivate'] = $_REQUEST['nsched_deactivate'];

		return 1;
	}

}

pluginRegisterFilter('news','nsched', new NSchedNewsFilter);

//registerActionHandler('cron_nsched', 'plugin_nsched');

//
// Функция вызываемая по крону
function plugin_nsched_cron() {
	global $mysql, $catz, $catmap;

	// Список новостей для (де)активации
	$listActivate = array();
	$dataActivate = array();

	$listDeactivate = array();
	$dataDeactivate = array();

	// Выбираем новости для которых сработал флаг "опубликовать по дате"
	foreach ($mysql->select("select * from ".prefix."_news where (nsched_activate>0) and (nsched_activate <= now())") as $row) {
		$listActivate[] = $row['id'];
		$dataActivate[$row['id']] = $row;
		//$mysql->query("update ".prefix."_news set approve=1, nsched_activate=0 where id = ".$row['id']);
	}
	// Выбираем новости для которых сработал флаг "снять публикацию по дате"
	foreach ($mysql->select("select * from ".prefix."_news where (nsched_deactivate>0) and (nsched_deactivate <= now())") as $row) {
		$listDeactivate[] = $row['id'];
		$dataDeactivate[$row['id']] = $row;
		//$mysql->query("update ".prefix."_news set approve=0, nsched_deactivate=0 where id = ".$row['id']);
	}

	// Проверяем, есть ли новости для (де)активации
	if (count($listActivate) or count($listDeactivate)) {
		// Загружаем необходимые плагины
		loadActionHandlers('admin');
		loadActionHandlers('admin:mod:editnews');

		// Загружаем системную библиотеку
		require_once(root.'includes/inc/lib_admin.php');

		// Запускаем модификацию новостей
		if (count($listActivate)) {
			massModifyNews(array('data' => $dataActivate), array('approve' => 1, 'nsched_activate' => ''), false);
		}
		if (count($listDeactivate)) {
			massModifyNews(array('data' => $dataDeactivate), array('approve' => 0, 'nsched_deactivate' => ''), false);
		}
	}
}
