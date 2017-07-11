<?php

//
// Configuration file for plugin
//

// Protect against hack attempts
if (!defined('NGCMS')) die ('HAL');

// Load lang files
Lang::loadPlugin($plugin, 'config', '', ':');

// Prepare configuration parameters
switch ($_REQUEST['action']) {
	case 'list_faq': show_faq(); break;
	case 'add_faq': show_add_faq(); break;
	case 'edit_faq': show_edit_faq(); break;
	case 'modify': modify(); show_faq(); break;
	default: show_faq();
}

function show_add_faq() {
	global $mysql, $twig;

	$tpath = locatePluginTemplates(array('main', 'add_faq'), 'faq', 1);
	if (isset($_REQUEST['submit'])) {
		$question = $_REQUEST['question'];
		$answer = $_REQUEST['answer'];
		$active = 1;
		if (empty($question) or empty($answer)) {
			$error_text[] = 'Вы заполнили не все обязательные поля';
		}
		if (empty($error_text)) {
			$mysql->query('INSERT INTO ' . prefix . '_faq (question, answer, active) 
					VALUES 
					(
						' . db_squote($question) . ',
						' . db_squote($answer) . ',
						' . db_squote($active) . '
					)
				');
			coreRedirectAndTerminate('admin.php?mod=extra-config&plugin=faq&action=list_faq');
		}
	}
	if (!empty($error_text)) {
		foreach ($error_text as $error) {
			$error_input .= msg(array("type" => "danger", 'message' => $error), 0, 2);
		}
	} else {
		$error_input = '';
	}
	$xt = $twig->loadTemplate($tpath['add_faq'] . 'add_faq.tpl');
	$tVars = array(
		'skins_url' => skins_url,
		'home'      => home,
		'tpl_home'  => admin_url,
		'question'  => $question,
		'answer'    => $answer,
		'active'    => $active,
		'error'     => $error_input,
	);
	$xg = $twig->loadTemplate($tpath['main'] . 'main.tpl');
	$tVars = array(
		'entries' => $xt->render($tVars),
		'action'     => 'Добавить вопрос',
		'class'     => array(
			'add_faq' => 'active',
			),
	);
	print $xg->render($tVars);
}

function show_edit_faq() {
	global $mysql, $twig;

	$tpath = locatePluginTemplates(array('main', 'edit_faq'), 'faq', 1);
	$id = intval($_REQUEST['id']);
	if (!empty($id)) {
		$row = $mysql->record('SELECT * FROM ' . prefix . '_faq WHERE id = ' . db_squote($id) . ' LIMIT 1');
		if (isset($_REQUEST['submit'])) {
			$question = $_REQUEST['question'];
			$answer = $_REQUEST['answer'];
			if (empty($question) or empty($answer)) {
				$error_text[] = 'Вы заполнили не все обязательные поля';
			}
			if (empty($error_text)) {
				$mysql->query('UPDATE ' . prefix . '_faq SET 
						question = ' . db_squote($question) . ',
						answer = ' . db_squote($answer) . '
						WHERE id = ' . intval($id) . ' ');
				//var_dump($_REQUEST);
				coreRedirectAndTerminate('admin.php?mod=extra-config&plugin=faq&action=list_faq');
			}
		}
		if (!empty($error_text)) {
			foreach ($error_text as $error) {
				$error_input .= msg(array('type' => 'danger', 'message' => $error), 0, 2);
			}
		} else {
			$error_input = '';
		}
		$xt = $twig->loadTemplate($tpath['edit_faq'] . 'edit_faq.tpl');
		$tVars = array(
			'skins_url' => skins_url,
			'home'      => home,
			'tpl_home'  => admin_url,
			'question'  => $question,
			'answer'    => $answer,
			'active'    => $active,
			'error'     => $error_input,
		);
	} else {
		msg(array('type' => 'danger', 'message' => "Не найден id"));
	}
	$tVars = array(
		'skins_url' => skins_url,
		'home'      => home,
		'tpl_home'  => admin_url,
		'question'  => $row['question'],
		'answer'    => $row['answer'],
		'active'    => $row['active'],
		'error'     => $error_input,
	);
	$xg = $twig->loadTemplate($tpath['main'] . 'main.tpl');
	$tVars = array(
		'entries' => $xt->render($tVars),
		'action'     => 'Редактировать вопрос',
		'class'     => array(
			'edit_faq' => 'active',
			),
	);
	print $xg->render($tVars);
}

function modify() {
	global $mysql;

	$selected_faq = $_REQUEST['selected_faq'];
	$subaction = $_REQUEST['subaction'];
	if (empty($selected_faq)) {
		return msg(array('type' => 'danger', 'message' => "Ошибка, вы не выбрали записи"));
	}
	switch ($subaction) {
		case 'mass_approve'      :
			$active = 'active = 1';
			break;
		case 'mass_forbidden'    :
			$active = 'active = 0';
			break;
		case 'mass_delete'       :
			$del = true;
			break;
	}
	foreach ($selected_faq as $id) {
		if (isset($active)) {
			$mysql->query('update ' . prefix . '_faq
					set ' . $active . '
					WHERE id = ' . db_squote($id) . '
					');
			$result = 'Записи Активированы/Деактивированы';
		}
		if (isset($del)) {
			$mysql->query('delete from ' . prefix . '_faq where id = ' . db_squote($id));
			$result = 'Записи удалены';
		}
	}
	msg(array('type' => 'info', 'message' => $result));
}

function show_faq() {
	global $mysql, $twig;

	$tpath = locatePluginTemplates(array('main', 'list_faq'), 'faq', 1);
	$tVars = array();
	// Records Per Page
	// - Load
	$news_per_page = 10;
	// - Set default value for `Records Per Page` parameter
	if (($news_per_page < 2) or ($news_per_page > 2000))
		$news_per_page = 10;
	$fSort = " ORDER BY id ASC";
	$sqlQPart = "from " . prefix . "_faq" . $fSort;
	$sqlQCount = "select count(id) " . $sqlQPart;
	$sqlQ = "select * " . $sqlQPart;
	$pageNo = intval($_REQUEST['page']) ? $_REQUEST['page'] : 0;
	if ($pageNo < 1) $pageNo = 1;
	if (!$start_from) $start_from = ($pageNo - 1) * $news_per_page;
	$count = $mysql->result($sqlQCount);
	$countPages = ceil($count / $news_per_page);
	foreach ($mysql->select($sqlQ . ' LIMIT ' . $start_from . ', ' . $news_per_page) as $row) {
		$tEntry[] = array(
			'id'       => $row['id'],
			'question' => $row['question'],
			'answer'   => $row['answer'],
			'active'   => $row['active'],
			'rat_p'   => $row['rat_p'],
			'rat_m'   => $row['rat_m'],
		);
	}

	$tVars = array(
		'entries'   => isset($tEntry) ? $tEntry : '',
		'php_self'  => $PHP_SELF,
		'skins_url' => skins_url,
		'home'      => home,
		'rpp'       => $news_per_page,
	);

	$maxNavigations = !(empty($config['newsNavigationsAdminCount']) or $config['newsNavigationsAdminCount'] < 1)?$config['newsNavigationsAdminCount']:8;

	if ( count($tEntry) ) {
		$pagesss = new Paginator;
		$tVars['pagesss'] = $pagesss->get(
			array(
				'maxNavigations' => $maxNavigations,
				'current' => $pageNo,
				'count' => $countPages,
				'url' => admin_url.
					'/admin.php?mod=extra-config&plugin=faq'.
					($news_per_page ? '&rpp=' . $news_per_page : '').
					'&page=%page%'
			));
	}

	$xt = $twig->loadTemplate($tpath['list_faq'] . 'list_faq.tpl');
	$xg = $twig->loadTemplate($tpath['main'] . 'main.tpl');
	$tVars = array(
		'entries' => $xt->render($tVars),
		'action'     => 'Список вопросов',
		'class'     => array(
			'list_faq' => 'active',
			),
	);
	print $xg->render($tVars);
}
