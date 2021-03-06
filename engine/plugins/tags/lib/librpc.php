<?php

//
// Suggest helper for Add/Edit news
function tagsSuggest($params){
	global $userROW, $DSlist, $mysql, $twig;

	// Only registered users can use suggest
	if (!is_array($userROW))
		return array('status' => 0, 'errorCode' => 1, 'errorText' => 'Permission denied');

	// Check if suggest module is enabled
	if (pluginGetVariable('tags', 'suggestHelper')) {
		return array('status' => 0, 'errorCode' => 2, 'errorText' => 'Suggest helper is not enabled');
	}

	// Check if tag is specified
	if (empty($params))
		return array('status' => 1, 'errorCode' => 0, 'data' => array($params, array()));

	$searchTag = $params;
	$output = array();

	foreach ($mysql->select("select * from ".prefix."_tags where tag like ".db_squote($searchTag.'%')." order by posts desc limit 10") as $row) {
		$output[] = array($row['tag'], $row['posts'] . ' ' . Padeg($row['posts'], __('news.counter_case')));
	}

	return array('status' => 1, 'errorCode' => 0, 'data' => array($params, $output));
}

rpcRegisterFunction('plugin.tags.suggest', 'tagsSuggest');
