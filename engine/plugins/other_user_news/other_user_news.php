<?php

// Protect against hack attempts
if (!defined('BBCMS')) die ('HAL');

function plugin_other_user_news($number, $mode, $overrideTemplateName, $cache_expire) {
	global $config, $mysql, $tpl, $template, $twig, $twigLoader, $TemplateCache, $CurrentHandler, $SYSTEM_FLAGS, $parse;

	// Prepare keys for cacheing
	$cacheKeys = array();
	$cacheDisabled = false;
	
	if (($number < 1) or ($number > 100))
		$number = 5;

 $current_news_id = $SYSTEM_FLAGS['news']['db.record']['id'];
 $current_author = $SYSTEM_FLAGS['news']['db.record']['author'];
 $current_author_id = $SYSTEM_FLAGS['news']['db.record']['author_id'];
 
 $sql = "SELECT * FROM ".prefix."_news WHERE id != ".$current_news_id." AND author_id = ".$current_author_id." AND approve = 1 ";
		
	switch ($mode) {
 case 'view': $sql .= "ORDER BY views DESC";
						break;
		case 'com': 	$sql .= "ORDER BY com DESC";
						break;
 case 'dt':	$sql .= "ORDER BY postdate DESC";
						break;
		case 'rnd': $cacheDisabled = true;
						$sql .= "ORDER BY RAND() DESC";
						break;
		default: $mode = 'dt';
						$sql .= "ORDER BY postdate DESC";
						break;
	}
	$sql .= " LIMIT ".$number;
 
 //var_dump($sql);

	if ($overrideTemplateName) {
 $templateName = $overrideTemplateName;
 } else {
 $templateName = 'other_user_news';
 }
	
	// Determine paths for all template files
	$tpath = plugin_locateTemplates('other_user_news', array($templateName));

	$cacheKeys []= '|current_news_id='.$current_news_id;
 $cacheKeys []= '|current_author_id='.$current_author_id;
 $cacheKeys []= '|number='.$number;
	$cacheKeys []= '|mode='.$mode;
	$cacheKeys []= '|templateName='.$templateName;

	// Generate cache file name [ we should take into account SWITCHER plugin ]
	$cacheFileName = md5('other_user_news'.$config['theme'].$templateName.$config['default_lang'].join('', $cacheKeys)).'.txt';

	if (!$cacheDisabled and ($cache_expire > 0)) {
		$cacheData = cacheRetrieveFile($cacheFileName, $cache_expire, 'other_user_news');
		if ($cacheData != false) {
			// We got data from cache. Return it and stop
			return $cacheData;
		}
	}

 $author_link = checkLinkAvailable('uprofile', 'show')?
 generateLink('uprofile', 'show', array('name' => $current_author, 'id' => $current_author_id)):
 generateLink('core', 'plugin', array('plugin' => 'uprofile', 'handler' => 'show'), array('name' => $current_author, 'id' => $current_author_id));
 $ublog_link = generatePluginLink('ublog', null, array('uid' => $current_author_id, 'uname' => $current_author));

 foreach ($mysql->select($sql) as $row) {
 
 $news_link = News::generateLink($row);
 $categories = GetCategories($row['catid']);
 
 $short_news = '';
 list ($short_news, $full_news) = explode('<!--more-->', $row['content'], 2);
 if ($config['blocks_for_reg']) $short_news = $parse->userblocks($short_news);
 if ($config['use_htmlformatter']) $short_news = $parse->htmlformatter($short_news);
 if ($config['use_bbcodes']) $short_news = $parse->bbcodes($short_news);
 if ($config['use_smilies']) $short_news = $parse->smilies($short_news);
 //if (strlen($short_news) > $newslength) $short_news = $parse->truncateHTML($short_news, $newslength);
 
 $row['news_link'] = $news_link;
 $row['categories'] = $categories;
 $row['short_news'] = $short_news;

 $tEntries [] = $row;
 }

 $tVars['entries'] = $tEntries;
 $tVars['author'] = $current_author;
 $tVars['author_id'] = $current_author_id;
 $tVars['author_link'] = $author_link;
 $tVars['ublog_link'] = $ublog_link;

	$output = $twig->render($tpath[$templateName].$templateName.'.tpl', $tVars);
	
	if (!$cacheDisabled and ($cache_expire > 0)) {
		cacheStoreFile($cacheFileName, $output, 'other_user_news');
	}
	
	return $output;
}

//
// Show data block for plugin
// Params:
// * number			- Max num entries for top_active_users
// * mode			- Mode for show
// * template		- Personal template for plugin
// * cache_expire	- age of cache [in seconds]
function plugin_other_user_news_showTwig($params) {
	global $CurrentHandler, $config;

	return plugin_other_user_news($params['number'], $params['mode'], $params['template'], isset($params['cache_expire'])?$params['cache_expire']:0);
}

twigRegisterFunction('other_user_news', 'show', plugin_other_user_news_showTwig);
