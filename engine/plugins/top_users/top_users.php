<?php

// Protect against hack attempts
if (!defined('BBCMS')) die ('HAL');

function plugin_top_users($number, $mode, $overrideTemplateName, $cache_expire) {
	global $config, $mysql, $template, $twig, $twigLoader, $TemplateCache;

	// Load lang files
	Lang::loadPlugin('top_users', 'site', '', ':');
	
	// Prepare keys for cacheing
	$cacheKeys = array();
	$cacheDisabled = false;

	if (($number < 1) or ($number > 100))
		$number = 5;

	switch ($mode) {
		case 'news':	$sql = 'SELECT id, name, com, news, avatar, mail, last, reg FROM '.uprefix.'_users ORDER BY news DESC'; break;
		case 'com':		$sql = 'SELECT id, name, com, news, avatar, mail, last, reg FROM '.uprefix.'_users ORDER BY com DESC'; break;
		case 'last':	$sql = 'SELECT id, name, com, news, avatar, mail, last, reg FROM '.uprefix.'_users ORDER BY reg DESC'; break;
		case 'rnd':		$sql = 'SELECT id, name, com, news, avatar, mail, last, reg FROM '.uprefix.'_users ORDER BY RAND() DESC'; $cacheDisabled = true; break;
		default:		$sql = 'SELECT id, name, com, news, avatar, mail, last, reg FROM '.uprefix.'_users ORDER BY news DESC'; $mode = 'news'; break;
	}
	$sql .= ' limit '.$number;

	if ($overrideTemplateName) {
		$templateName = $overrideTemplateName;
	} else {
		$templateName = 'top_users';
	}

	// Determine paths for all template files
	$tpath = plugin_locateTemplates('top_users', array($templateName));

	// Preload template configuration variables
	@templateLoadVariables();

	// Use default <noavatar> file
	// - Check if noavatar is defined on template level
	$tplVars = $TemplateCache['site']['#variables'];
	$noAvatarURL = ( isset($tplVars['configuration']) and is_array($tplVars['configuration']) and isset($tplVars['configuration']['noAvatarImage']) and $tplVars['configuration']['noAvatarImage'] ) ? (tpl_url.'/'.$tplVars['configuration']['noAvatarImage']) : (avatars_url.'/noavatar.png');

	$cacheKeys []= '|number='.$number;
	$cacheKeys []= '|mode='.$mode;
	$cacheKeys []= '|templateName='.$templateName;

	// Generate cache file name [ we should take into account SWITCHER plugin ]
	$cacheFileName = md5('top_users'.$config['theme'].$templateName.$config['default_lang'].join('', $cacheKeys)).'.txt';

	if (!$cacheDisabled and ($cache_expire > 0)) {
		$cacheData = cacheRetrieveFile($cacheFileName, $cache_expire, 'top_users');
		if ($cacheData != false) {
			// We got data from cache. Return it and stop
			return $cacheData;
		}
	}

	foreach ($mysql->select($sql) as $row) {
		$alink = checkLinkAvailable('uprofile', 'show')?
					generateLink('uprofile', 'show', array('name' => $row['name'], 'id' => $row['id'])):
					generateLink('core', 'plugin', array('plugin' => 'uprofile', 'handler' => 'show'), array('name' => $row['name'], 'id' => $row['id']));
		$ublog_link = generatePluginLink('ublog', null, array('uid' => $row['id'], 'uname' => $row['name']));

		$use_avatars = $config['use_avatars'];

		// Generate avatar link
		if ($config['use_avatars']) {
			if ($row['avatar']) {
				$avatars = avatars_url."/".$row['avatar'];
			} else {
				// If gravatar integration is active, show avatar from GRAVATAR.COM
				if ($config['avatars_gravatar']) {
					$avatars = 'http://www.gravatar.com/avatar/'.md5(strtolower($row['mail'])).'.jpg?s='.$config['avatar_wh'].'&d='.urlencode($noAvatarURL);
				} else {
					$avatars = $noAvatarURL;
				}
			}
		} else {
			$avatars = '';
		}

		$tVars['entries'] [] = array(
			'name' => $row['name'],
			'link' => $alink,
			'ulink' => $ublog_link,
			'avatar_url' => $avatars,
			'mail' => $row['mail'],
			'last' => $row['last'],
			'reg' => $row['reg'],
			'use_avatars' => $use_avatars,
			'news' => $row['news'],
			'com' => $row['com'],
			);
	}

	$tVars['tpl_url'] = tpl_url;

	$output = $twig->render($tpath[$templateName].$templateName.'.tpl', $tVars);

	if (!$cacheDisabled and ($cache_expire > 0)) {
		cacheStoreFile($cacheFileName, $output, 'top_users');
	}

	return $output;
}

//
// Show data block for xnews plugin
// Params:
// * number			- Max num entries for top_users
// * mode			- Mode for show
// * template		- Personal template for plugin
// * cache_expire	- age of cache [in seconds]
function plugin_top_users_showTwig($params) {
	global $CurrentHandler, $config;

	return plugin_top_users($params['number'], $params['mode'], $params['template'], isset($params['cache_expire'])?$params['cache_expire']:0);
}

twigRegisterFunction('top_users', 'show', 'plugin_top_users_showTwig');
