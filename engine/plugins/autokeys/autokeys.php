<?php

// Protect against hack attempts
if (!defined('BBCMS')) die ('HAL');

// Load library
@include_once(root."/plugins/autokeys/lib/Autokeys.class.php");

// News filtering class
class autoKeysNewsFilter extends NewsFilter {

	function __construct() {
		Lang::loadPlugin('autokeys', 'admin', '', ':');
	}

	function addNews(&$tvars, &$SQL) {
		if (isset($_POST['autokeys_generate']) and $_POST['autokeys_generate'] == 1) {
			$SQL['keywords'] = akeysGetKeys(array('content' => $SQL['content'], 'title' => $SQL['title']));
		}
		return 1;
	}

	function editNews($newsID, $SQLold, &$SQLnew, &$tvars) {
		if (isset($_POST['autokeys_generate']) and $_POST['autokeys_generate'] == 1) {
			$SQLnew['keywords'] = akeysGetKeys(array('content' => $SQLnew['content'], 'title' => $SQLnew['title']));
		}
		return 1;
	}

	function editNewsForm($newsID, $SQLold, &$tvars) {
		global $twig;

		$extends = 'js'; //$extends = pluginGetVariable($plugin,'extends') ? pluginGetVariable($plugin,'extends') : 'js';

		$tpath = plugin_locateTemplates('autokeys', array('news'));
        $tvars['extends'][$extends][] = array(
			'header_title' => __('autokeys:header_title'),
			'body' => $twig->render($tpath['news'].'/news.tpl', array('flags' => array('checked' => pluginGetVariable('autokeys', 'activate_edit')))),
			);

		return 1;
	}

	function addNewsForm(&$tvars) {
		global $twig;

		$extends = 'js'; //$extends = pluginGetVariable($plugin,'extends') ? pluginGetVariable($plugin,'extends') : 'js';

		$tpath = plugin_locateTemplates('autokeys', array('news'));
		$tvars['extends'][$extends][] = array(
			'header_title' => __('autokeys:header_title'),
			'body' => $twig->render($tpath['news'].'/news.tpl', array('flags' => array('checked' => pluginGetVariable('autokeys', 'activate_add')))),
			);

		return 1;
	}
}

pluginRegisterFilter('news','autokeys', new autoKeysNewsFilter);