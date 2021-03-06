<?php
// Protect against hack attempts
if (!defined('BBCMS')) die ('HAL');
register_plugin_page('faq', '', 'plugin_faq');
register_plugin_page('faq', 'add', 'plugin_faq_add');

function plugin_faq_add() {
	global $mysql, $config;

	Lang::loadPlugin('faq', 'site', '', ':');
	$tpl_name = 'faq_add';

	if ($_SERVER['REQUEST_METHOD'] != "POST") {
		plugin_faq_addForm($tpl_name);
	} else {
		$question = isset($_REQUEST['question']) ? secure_html($_REQUEST['question']) : null;
		if (empty($question)) {
			$info = array('status' => 'error');
		} else {
			$mysql->query('INSERT INTO ' . prefix . '_faq (question, active) 
					VALUES 
					(
						' . db_squote($question) . ',
						0
					)
				');
			$info = array('status' => 'success');
			$body = str_replace(
				array(	'{question}',
						'{questionlink}',
						),
				array(	$question,
						admin_url . '?mod=extra-config&plugin=faq',
						),
				__('faq:email_body')
			);
			sendEmailMessage($config['admin_mail'], 'Новый вопрос', $body);
		}
		plugin_faq_addForm($tpl_name, $info);
	}
}

function plugin_faq_addForm($tpl_name, $info = array()) {

	global $twig, $template, $PHP_SELF;


	$tVars = array(
		'info' => $info,
	);

	$tpath = plugin_locateTemplates('faq', array($tpl_name));
	$template['vars']['mainblock'] .= $twig->render($tpath[$tpl_name] . $tpl_name . '.tpl', $tVars);
}


function plugin_faq() {

	global $catz, $twig, $catmap, $mysql, $config, $userROW, $tpl, $parse, $template, $lang, $PFILTERS, $SYSTEM_FLAGS, $CurrentHandler;
	$title_plg = 'Вопросы и ответы';
	$SYSTEM_FLAGS['info']['title']['group'] = isset($title_plg) ? $title_plg : $SYSTEM_FLAGS['info']['title']['group'];
	$tpath = plugin_locateTemplates('faq', array('faq_page'));

	foreach ($mysql->select("SELECT *
				FROM " . prefix . "_faq WHERE (active = 1)
				ORDER BY id ASC") as $row) {
		$tEntry[] = array(
			'id'       => $row['id'],
			'question' => $row['question'],
			'answer'   => $row['answer'],
		);
	}
	$tVars = array(
		'entries' => isset($tEntry) ? $tEntry : '',
		'home'    => home,
	);
	$template['vars']['mainblock'] .= $twig->render($tpath['faq_page'] . 'faq_page.tpl', $tVars);
}

function plug_faq($maxnum, $overrideTemplateName, $order, $cache_expire) {

	global $config, $mysql, $tpl, $template, $twig, $twigLoader, $langMonths, $lang;
	if (($maxnum < 1) or ($maxnum > 50)) $maxnum = 12;
	if ($overrideTemplateName) {
		$templateName = $overrideTemplateName;
	} else {
		$templateName = 'faq_block';
	}
	if ($order != 'ASC' and $order != 'DESC') {
		$order = 'DESC';
	}
	// Generate cache file name [ we should take into account SWITCHER plugin ]
	$cacheFileName = md5('faq' . $config['theme'] . $templateName . $config['default_lang']) . '.txt';
	if ($cache_expire > 0) {
		$cacheData = cacheRetrieveFile($cacheFileName, $cache_expire, 'faq');
		if ($cacheData != false) {
			// We got data from cache. Return it and stop
			return $cacheData;
		}
	}
	foreach ($mysql->select("SELECT * FROM " . prefix . "_faq WHERE active = '1' ORDER BY id " . $order . " limit $maxnum") as $row) {
		$tEntries [] = array(
			'id'       => $row['cnt'],
			'question' => $row['question'],
			'answer'   => $row['answer'],
		);
	}
	$tVars['entries'] = $tEntries;
	$tVars['tpl_url'] = tpl_url;
	// Determine paths for all template files
	$tpath = plugin_locateTemplates('faq', array($templateName));

	$output = $twig->render($tpath[$templateName] . $templateName . '.tpl', $tVars);
	if ($cache_expire > 0) {
		cacheStoreFile($cacheFileName, $output, 'archive');
	}

	return $output;
}

// Params:
// * maxnum		- Max num entries
// * template	- Personal template for plugin
// * cache_expire		- age of cache [in seconds]
function plugin_faq_showTwig($params) {

	global $CurrentHandler, $config;

	return plug_faq($params['maxnum'], isset($params['template']) ? $params['template'] : false, isset($params['order']) ? $params['order'] : 'DESC', isset($params['cache_expire']) ? $params['cache_expire'] : 0);
}

twigRegisterFunction('faq', 'show', 'plugin_faq_showTwig');