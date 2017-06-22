<?php

// Protect against hack attempts
if (!defined('NGCMS')) die ('HAL');

function plugin_rating_update(){
	global $mysql, $twig, $userROW;

	Lang::loadPlugin('rating', 'site');

	// Security protection - limit rating values between 1..5
	$rating = intval($_REQUEST['rating']);
	$post_id = intval($_REQUEST['post_id']);
	if ( ($rating <1) or ($rating >5) ) {
		return msg(array('type' => 'danger', 'message' => __('rating_incorrect')), 0 , 2);
	}

	// Check if referred news exists
	if ( !is_array($row = $mysql->record("select * from ".prefix."_news where id = ".db_squote($post_id))) ) {
		return msg(array('type' => 'danger', 'message' => __('rating_not_found')), 0 , 2);
	}

	// Check if we try to make a duplicated rate
	if ( $_COOKIE['rating'.$row['id']] ) {
		return msg(array('type' => 'danger', 'message' => __('rating_already')), 0 , 2);
	}

	// Check if we feet "register only" limitation
	if (pluginGetVariable('rating','regonly') and !is_array($userROW)) {
		return msg(array('type' => 'danger', 'message' => __('rating_only_reg')), 0 , 2);
	}

	// Ok, everything is fine. Let's update rating.

	@setcookie('rating'.$post_id, 'voted', (time() + 31526000), '/');
	$mysql->query("update ".prefix."_news set rating=rating+".$rating.", votes=votes+1 where id = ".db_squote($post_id));
	$data = $mysql->record("select rating, votes from ".prefix."_news where id = ".db_squote($post_id));

	$localSkin = pluginGetVariable('rating', 'localSkin');
	if (!$localSkin) $localSkin='basic';
	$tpath = locatePluginTemplates(array('rating', ':rating.css'), 'rating', pluginGetVariable('rating', 'localSource'), $localSkin);
	register_stylesheet($tpath['url::rating.css'].'/rating.css'); 

	$tVars = array(
		'tpl_url' => $tpath['url::rating.css'],
		'home' => home,
		'rating' => ($data['rating'] == 0) ? 0 : round(($data['rating'] / $data['votes']), 0),
		'votes' => $data['votes'],
		);
	
	$templateName = 'rating';
	$xt = $twig->loadTemplate($tpath[$templateName] . $templateName . '.tpl');
	return $xt->render($tVars);
}

function rating_show($newsID, $rating, $votes){
	global $twig, $userROW;

	Lang::loadPlugin('rating', 'site');
	$localSkin = pluginGetVariable('rating', 'localSkin');
	if (!$localSkin) $localSkin='basic';

	$tpath = locatePluginTemplates(array('rating', 'rating.form', ':rating.css'), 'rating', pluginGetVariable('rating', 'localSource'), $localSkin);
	register_stylesheet($tpath['url::rating.css'].'/rating.css'); 
	
	$tVars = array(
		'tpl_url' => $tpath['url::rating.css'],
		'home' => home,
		'ajax_url' => generateLink('core', 'plugin', array('plugin' => 'rating'), array()),
		'post_id' => $newsID,
		'rating' => (!$rating or !$votes) ? 0 : round(($rating / $votes), 0),
		'votes' => $votes,
		);

	if ((isset($_COOKIE['rating'.$newsID]) and $_COOKIE['rating'.$newsID]) or (pluginGetVariable('rating','regonly') and !is_array($userROW))) {
		// Show
		$templateName = 'rating';
		$xt = $twig->loadTemplate($tpath[$templateName] . $templateName . '.tpl');
		return $xt->render($tVars);
	} else {
		// Edit
		$templateName = 'rating.form';
		$xt = $twig->loadTemplate($tpath[$templateName] . $templateName . '.tpl');
		return $xt->render($tVars);
	}
	return;
}

function plugin_rating_screen(){
	global $SUPRESS_TEMPLATE_SHOW, $template;

	@header('Content-type: text/html; charset="UTF-8"');
	if ($_REQUEST['post_id']) {
		$template['vars']['mainblock'] = plugin_rating_update();
		$SUPRESS_TEMPLATE_SHOW = 1;
	} else {
		$template['vars']['mainblock'] =  msg(array('type' => 'danger', 'message' => 'unsupported action'), 0 , 2);
	}
}

//
// ”Ё«мва ­®ў®бвҐ© (¤«п Ї®Є §  аҐ©вЁ­Ј )
//
class RatingNewsFilter extends NewsFilter {
	function showNews($newsID, $SQLnews, &$tvars, $mode = array()) {
		global $mysql, $userROW;

		$tvars['vars']['rating'] = rating_show($SQLnews['id'], $SQLnews['rating'], $SQLnews['votes']);
	}
}

pluginRegisterFilter('news','rating', new RatingNewsFilter);
register_plugin_page('rating','','plugin_rating_screen',0);
