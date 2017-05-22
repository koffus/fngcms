<?php

//
// Copyright (C) 2006-2012 Next Generation CMS (http://ngcms.ru/)
// Name: extras.inc.php
// Description: NGCMS extras managment functions
// Author: Vitaly Ponomarev
//

// Protect against hack attempts
if (!defined('NGCMS')) die ('HAL');

// Register admin filter
function register_admin_filter($group, $name, $instance) {
 global $AFILTERS;
 $AFILTERS[$group][$name] = $instance;
}

//
// Get/Load list of active plugins & required files
function getPluginsActiveList() {
	global $PLUGINS;

	if ($PLUGINS['active:loaded']) { return $PLUGINS['active']; }
	if (is_file(conf_pactive)) {
		@include conf_pactive;
		if (is_array($array)) { $PLUGINS['active'] = $array; }
	}
	$PLUGINS['active:loaded'] = 1;
	return $PLUGINS['active'];
}

//
// Report if plugin is active
function getPluginStatusActive($pluginID) {
	$array = getPluginsActiveList();
	if (isset($array['active'][$pluginID]) and $array['active'][$pluginID]) { return true; }
	return false;
}

//
// Report if plugin is installed
function getPluginStatusInstalled($pluginID) {
	$array = getPluginsActiveList();
	if (isset($array['installed'][$pluginID]) and $array['installed'][$pluginID]) { return true; }
	return false;
}

//
// Load plugins for specified action [ CAN BE USED FOR MANUAL PLUGIN PRELOAD ]
// * if $plugin name specified - manual PRELOAD mode is used:
// Each plugin can call loadActionHandlers(<action>, <plugin name>) for preloading
// plugin for action
function loadActionHandlers($action, $plugin = '') {
	global $PLUGINS, $timer;

	$loadedCount = 0;
	$array = getPluginsActiveList();
	// Find extras for selected action
	if (isset($array['actions'][$action]) and is_array($array['actions'][$action])) {
		// There're some modules
		foreach ($array['actions'][$action] as $key => $value) {
			// Skip plugins in manual mode
			if ($plugin and ($key != $plugin))
				continue;

			// Do only if this file is was not loaded earlier
			if (!isset($PLUGINS['loaded:files'][$value])) {
				// Try to load file. First check if it exists
				if (is_file(extras_dir.'/'.$value)) {
				 $tX = $timer->stop(4);
					include_once extras_dir.'/'.$value;
					$timer->registerEvent('loadActionHandlers('.$action.'): preloaded file "'.$value.'" for '.round($timer->stop(4) - $tX, 4)." sec");
					$PLUGINS['loaded:files'][$value] = 1;
					$loadedCount ++;
				} else {
					$timer->registerEvent('loadActionHandlers('.$action.'): CAN\'t preload file that doesn\'t exists: "'.$value.'"');
				}
				$PLUGINS['loaded'][$key] = 1;
			}
		}
	}
	// Return count of loaded plugins
	return $loadedCount;
}

//
// Load plugin [ Same behaviour as for loadActionHandlers ]
function loadPlugin($pluginName, $actionList = '*') {
	global $PLUGINS, $timer;

	$plugList = getPluginsActiveList();
	$loadCount = 0;

	// Don't load if plugin is not activated
	if (!$plugList['active'][$pluginName])
		return false;

	// Scan all available actions and preload plugin's file if needed
	foreach ($plugList['actions'] as $aName => $pList) {
		if (isset($pList[$pluginName]) &&
			((is_array($actionList) and in_array($aName, $actionList)) ||
			(!is_array($actionList) and (($actionList == '*')||($actionList == $aName))))
		 ) {
			// Yes, we should load this file. If it's not loaded earlier
			$pluginFileName = $pList[$pluginName];

			if (!isset($PLUGINS['loaded:files'][$pluginFileName])) {
				// Try to load file. First check if it exists
				if (is_file(extras_dir.'/'.$pluginFileName)) {
				 $tX = $timer->stop(4);
					include_once extras_dir.'/'.$pluginFileName;
					$timer->registerEvent('func loadPlugin ('.$pluginName.'): preloaded file "'.$pluginFileName.'" for '.($timer->stop(4) - $tX)." sec");
					$PLUGINS['loaded:files'][$pluginFileName] = 1;
					$loadCount++;
				} else {
					$timer->registerEvent('func loadPlugin ('.$pluginName.'): CAN\'t preload file that doesn\'t exists: "'.$pluginFileName.'"');
				}
			}
		}
	}

	$PLUGINS['loaded'][$pluginName] = 1;
	return $loadCount;
}

//
// Load plugin's library
function loadPluginLibrary($plugin, $libname = '') {
	global $timer;

	$list = getPluginsActiveList();

	// Check if we know about this plugin
	if (!isset($list['active'][$plugin])) return false;

	// Check if we need to load all libs
	if (!$libname) {
		foreach ($list['libs'][$plugin] as $id => $file) {
			$tX = $timer->stop(4);
			include_once extras_dir.'/'.$list['active'][$plugin].'/'.$file;
			$timer->registerEvent('loadPluginLibrary: '.$plugin.'.'.$id.' ['.$file.'] for '.round($timer->stop(4) - $tX,4)." sec");
		}
		return true;
	} else {
		if (isset($list['libs'][$plugin][$libname])) {
			$tX = $timer->stop(4);
			include_once extras_dir.'/'.$list['active'][$plugin].'/'.$list['libs'][$plugin][$libname];
			$timer->registerEvent('loadPluginLibrary: '.$plugin.' ['.$libname.'] for '.round($timer->stop(4) - $tX, 4)." sec");
			return true;
		}
		return false;
	}
}

function registerActionHandler($action, $function, $arguments = 0, $priority = 5) {
	global $acts;

	// Check if function is already loaded for this action
	if (isset($acts['action']) and is_array($acts['action'])) {
		foreach ($acts['action'] as $k => $v) {
			if (is_array($v) and (array_search($function, $v) !== FALSE))
				return true;
		}
	}

	// Register new item
	$acts[$action][$priority][] = $function;

	return true;

}

function executeActionHandler($action) {
	global $acts, $timer, $SYSTEM_FLAGS;

	$output = '';

	// Do not run action if it's disabled
	if (isset($SYSTEM_FLAGS['actions.disabled'][$action]) and $SYSTEM_FLAGS['actions.disabled'][$action]) {
		$timer->registerEvent('disabled executeActionHandler ('.$action.')');
		return;
	}

	$timer->registerEvent('executeActionHandler ('.$action.')');

	// Preload plugins (if needed)
	loadActionHandlers($action);

	// Finish if there're no plugins for this action
	if (!isset($acts[$action]) or !$acts[$action] or !is_array($acts[$action])) {
		return true;
	}
	foreach ($acts[$action] as $priority => $functions) {
		if (!is_array($functions))
			continue;

		foreach ($functions as $func) {
			$tX = $timer->stop(4);
			$output.=call_user_func($func);
			$timer->registerEvent('executeActionHandler ('.$action.'): call function "'.$func.'" for '.round($timer->stop(4) - $tX, 4)." sec");
		}
	}
	return $output;
}

// Disable desired action from plugin
function actionDisable($action) {
	global $SYSTEM_FLAGS;
	$SYSTEM_FLAGS['actions.disabled'][$action] = 1;
	return;
}

// =========================================================
// PLUGINS: parameters managment
// =========================================================

//
// Get variable
function pluginGetVariable($pluginID, $var) {
	global $PLUGINS;

	if (!$PLUGINS['config:loaded'] and !pluginsLoadConfig())
		return false;

	if (!isset($PLUGINS['config'][$pluginID])) { return null; }
	if (!isset($PLUGINS['config'][$pluginID][$var])) { return null; }
	return $PLUGINS['config'][$pluginID][$var];
}

//
// Set variable
function pluginSetVariable($pluginID, $var, $value) {
	global $PLUGINS;

	if (!$PLUGINS['config:loaded'] and !pluginsLoadConfig())
		return false;

	$PLUGINS['config'][$pluginID][$var] = $value;
	return true;
}

//
// Save configuration parameters of plugins (should be called after pluginSetVariable)
function pluginsSaveConfig($suppressNotify = false) {
	global $PLUGINS;

	if (!$PLUGINS['config:loaded'] and !pluginsLoadConfig()) {
		if (!$suppressNotify) {
			msg(array('type' => 'danger', 'title' => str_replace('{name}', conf_pconfig, __('error.config.read')), 'message' => __('error.config.read#desc')));
		}
		return false;
	}

	//
	if (!($fconfig = @fopen(conf_pconfig, 'w'))) {
		if (!$suppressNotify) {
			msg(array('type' => 'danger', 'title' => str_replace('{name}', conf_pconfig, __('error.config.write')), 'message' => __('error.config.write#desc')));
		}
		return false;
	}

	fwrite($fconfig, serialize($PLUGINS['config']));
	fclose($fconfig);
	return true;
}

//
// Load configuration variables for plugins
function pluginsLoadConfig() {
 global $PLUGINS;

	if ($PLUGINS['config:loaded']) { return 1; }
	$fconfig = @fopen(conf_pconfig,'r');
	if ($fconfig) {
		if (filesize(conf_pconfig)) {
			$content = fread($fconfig, filesize(conf_pconfig));
		} else {
			$content = serialize(array());
		}
 $PLUGINS['config'] = unserialize($content);
 	$PLUGINS['config:loaded'] = 1;
 fclose($fconfig);
 return true;
	} else {
		// File doesn't exists. Mark as `loaded`
		$PLUGINS['config'] = array();
		$PLUGINS['config:loaded'] = 1;
	}
	return false;
}

//
// Load 'version' file from plugin directory
function plugins_load_version_file($filename) {

	// config variables & function init
	$config_params = array ( 'id', 'name', 'version', 'acts', 'file', 'config', 'install', 'deinstall', 'management', 'type', 'description', 'author', 'author_uri', 'permanent', 'library', 'actions' );
	$required_params = array ('id', 'name', 'version', 'type');
	$list_params = array( 'library', 'actions' );
	$ver = array();

	foreach ($list_params as $id)
		$ver[$id] = array();

	// open file
	if (!($file = @fopen($filename,'r'))) { return false; }

	// read file
	while (!feof($file)) {
		$line = fgets($file);
		if (preg_match("/^(.+?) *\: *(.+?) *$/i",$line, $r) == 1) {
				$key = rtrim(strtolower($r[1]));
				$value = rtrim($r[2]);
			if (in_array($key, $config_params)) {
				if (in_array($key, $list_params)) {
					$ver[$key][] = $value;
				} else {
					$ver[$key] = $value;
				}
			}
		}
	}

	// Make some cleanup
	$ver['acts'] = isset($ver['acts'])?str_replace(' ','',$ver['acts']):'';
	if (isset($ver['permanent']) and ($ver['permanent'] == 'yes')) { $ver['permanent'] = 1; } else { $ver['permanent'] = 0; }

	// check for filling required params
	foreach ($required_params as $v) { if (!$ver[$v]) { return false; } }

	// check for library/actions filling
	foreach (array('library', 'actions') as $key) {
		$list = $ver[$key];
		$ver[$key] = array();

		foreach ($list as $rec) {
			if (!$rec) continue;
			list ($ids, $fname) = explode(';', $rec);

			$ids = trim($ids);
			$fname = trim($fname);

			if (!$ids or !$fname) return false;
			$idlist = explode(',', $ids);
			foreach ($idlist as $entry)
				if (trim($entry))
					$ver[$key][trim($entry)] = $fname;
		}
	}
	return $ver;
}

//
// Get a list of installed plugins
function pluginsGetList() {
	global $timer;

	$timer->registerEvent('@ pluginsGetList() called');
	// open directory
	$handle = @opendir(extras_dir);
	$extras = array();
	// load list of extras
	while (false != ($dir = @readdir($handle))) {
		$edir = extras_dir.'/'.$dir;
		// Skip special dirs ',' and '..'
		if (($dir == '.')||($dir == '..')||(!is_dir($edir))) { continue; }

		// Check 'version' file
		if (!is_file($edir.'/version')) { continue; }

		// Load version file
		$ver = plugins_load_version_file($edir.'/version');
		if (!is_array($ver)) { continue; }

		// fill fully file path (within 'plugins' directory)
		$ver['dir'] = $dir;

		// Good, version file is successfully loaded, add data into array
		$extras[$ver['id']] = $ver;
		//array_push($extras, $ver);
	}
	return $extras;
}

//
// Add plugin's page
function register_plugin_page($pname, $mode, $func_name, $show_template = 1) {
	global $PPAGES;

	if (!isset($PPAGES[$pname]) or !is_array($PPAGES[$pname])) {
		$PPAGES[$pname] = array();
	}
	$PPAGES[$pname][$mode] = array('func' => $func_name, 'mode' => $mode);
}

//
// Add item into list of additional HTML meta tags
function register_htmlvar($type, $data) {
	global $EXTRA_HTML_VARS;

	// Check for duplicate

	$EXTRA_HTML_VARS[] = array('type' => $type, 'data' => $data);
}

//
// Add new stylesheet into template
function register_stylesheet($url) {
	global $EXTRA_CSS;
	register_htmlvar('css', $url);
}

//
// Get configuration directory for plugin (and create it if needed)
function get_plugcfg_dir($plugin) {

 $dir = confroot.'extras';
 if ((!is_dir($dir)) and (!mkdir($dir))) {
	print "Can't create config directory for plugins. Please, check permissions for engine/conf/ dir<br/>\n";
	return '';
 }

 $dir .= '/'.$plugin;
 if (!is_dir($dir)) {
 if (!mkdir($dir)) {
 	print "Can't create config directory for plugin '$plugin'. Please, check permissions for engine/conf/plugins/ dir<br>\n";
 	return '';
 }
 }
 return $dir;
}

//
// Get plugin cache dir
function get_plugcache_dir($plugin) {
 global $multiDomainName, $multimaster;

 $dir = root.'cache/';
 if ($multiDomainName and $multimaster and ($multiDomainName != $multimaster)) {
 	$dir .= 'multi/';
 	if ((!is_dir($dir)) and (!mkdir($dir))) {
 		print "Can't create multi cache dir!<br>\n";
 		return '';
 	}
 }
 if ($plugin) {
	 $dir .= $plugin.'/';
	 if ((!is_dir($dir)) and (!mkdir($dir))) {
		print "Can't create cache for plugin '$plugin'<br>\n";
		return '';
	 }
 }
 return $dir;
}

//
// Save file into cache
// Params:
// $fname - file name to store
// $data - what should be written into cache
// $plugin - [optional] plugin name that stores data
// $hugeMode - flag if plugin wants to store huge data
// [ data will be stored in binary tree 32 x 32 = 1024 different dirs ]
function cacheStoreFile($fname, $data, $plugin = '', $hugeMode = 0) {

	// Try to get cache directory name. Return false if it's not possible
	if (!($dir = get_plugcache_dir($plugin))) {
		return false;
	}

	// In case of huge mode - try to access the tree
	//if ($hugeMode) {
	//	$fhash = md5($fname);
	//	//$dp1 =
 //	//
 //	//if (!
	//}

	// Try to create file
	if (($fn = @fopen($dir.$fname, 'w')) == FALSE) {
		return false;
	}

	// Try to make exclusive file lock. Return if failed
	if (@flock($fn, LOCK_EX) == FALSE) {
		fclose($fn);
		return false;
	}

	// Write into file
	if (@fwrite($fn, $data) == -1) {
		// Failed.
		flock($fn, LOCK_UN);
		fclose($fn);
		return false;
	}

	flock($fn, LOCK_UN);
	fclose($fn);
	return true;
}

// Load file from cache
// Params:
// $fname - file name to store
// $expire - expiration period for data. Nothing will be returned if data expired
// $plugin - [optional] plugin name that stores data
function cacheRetrieveFile($fname, $expire, $plugin = '') {

	// Try to get cache directory name. Return false if it's not possible
	if (!($dir = get_plugcache_dir($plugin))) {
		return false;
	}

	// Try to open file with data
	if (($fn = @fopen($dir.$fname, 'r')) == FALSE) {
		return false;
	}

	// Check if file is expired. Return if it's so.
	$stat = fstat($fn);
	if (!is_array($stat) or ($stat[9]+$expire<time())) {
		return false;
	}

	// Try to make shared file lock. Return if failed
	if (@flock($fn, LOCK_SH) == FALSE) {
		fclose($fn);
		return false;
	}

	// Return if file is empty
	if ($stat[7] < 1) {
		fclose($fn);
		return false;
	}

	// Read data from file
	$data = fread($fn, $stat[7]);

	// Unlock and close file
	flock($fn, LOCK_UN);
	fclose($fn);

	// Return data
	return $data;
}

function create_access_htaccess() {
	
	$htaccess_array = array(
		array('dir' => 'cache', 'data' => "# Lock access\nOrder Deny,Allow\nDeny from all"),
		array('dir' => 'backups', 'data' => "# Lock access\n\t<FilesMatch .*>\n\tDeny from all\n</FilesMatch>"),
		array('dir' => 'conf', 'data' => "<files *>\n\tOrder Deny,Allow\n\tDeny from All\n</files>"),
	);
	
	//print '<pre>'.var_export($htaccess_array, true).'</pre>';
	
	if(is_array($htaccess_array))
		foreach ($htaccess_array as $result) {
			$htaccessFile = root.$result['dir'].'/.htaccess';
			
			// Try to create file
			if(file_exists($htaccessFile))
				continue;

			if (($fn = @fopen($htaccessFile, 'w')) == FALSE)
				continue;
				
			// Try to make exclusive file lock. Return if failed
			if (@flock($fn, LOCK_EX) == FALSE) {
				fclose($fn);
				continue;
				
			}
			// Write into file
			if (@fwrite($fn, $result['data']) == -1) {
				// Failed.
				flock($fn, LOCK_UN);
				fclose($fn);
				continue;
			}
			flock($fn, LOCK_UN);
			fclose($fn);
		}
}

//
// Routine that helps plugin to locate template files. It checks if required file
// exists in "global template" dir
//
// $tname		- template names (in string array or single name)
// $plugin		- plugin name
// $localSource		- flag if function should work in "local only" mode, i.e.
//				 that all files are in own plugin dir
// $skin		- skin name in plugin dir ( plugins/PLUGIN/tpl/skin/ )
// $block		- name of subdir within current template/block
function locatePluginTemplates($tname, $plugin, $localsource = 0, $skin = '', $block = '') {
	global $config;

	// Check if $tname is correct
	if (!is_array($tname)) {
		if ($tname == '') {
			return array();
		}
		$tname = array($tname);
	}

	// Text SKIN+BLOCK
	$tsb = ((($skin != '')||($block != ''))?'/':'').
			($skin?'skins/'.$skin:'').
			((($skin != '') and ($block != ''))?'/':'').
			($block?$block:'');

	$tpath = array();
	foreach ($tname as $fn) {
		$fnc = (substr($fn, 0, 1) == ':')?substr($fn,1):($fn.'.tpl');
		if (!$localsource and is_readable(tpl_site.'plugins/'.$plugin.$tsb.'/'.$fnc)) {
			$tpath[$fn] = tpl_site.'plugins/'.$plugin.$tsb.'/';
			$tpath['url:'.$fn] = tpl_url.'/plugins/'.$plugin.$tsb;
		} else	if (!$localsource and is_readable(tpl_site.'plugins/'.$plugin.($block?('/'.$block):'').'/'.$fnc)) {
			$tpath[$fn] = tpl_site.'plugins/'.$plugin.($block?('/'.$block):'').'/';
			$tpath['url:'.$fn] = tpl_url.'/plugins/'.$plugin.($block?('/'.$block):'');
		} else if (is_readable(extras_dir.'/'.$plugin.'/tpl'.$tsb.'/'.$fnc)) {
			$tpath[$fn] = extras_dir.'/'.$plugin.'/tpl'.$tsb.'/';
			$tpath['url:'.$fn] = admin_url.'/plugins/'.$plugin.'/tpl'.$tsb;
		}
	}
	return $tpath;
}

// Register system filter
function pluginRegisterFilter($group, $name, $instance) {
 global $PFILTERS;
 $PFILTERS[$group][$name] = $instance;
}

// Register RPC function
function rpcRegisterFunction($name, $instance, $permanent = false) {
 global $RPCFUNC;
 $RPCFUNC[$name] = $instance;
}

// Register TWIG function call
function twigRegisterFunction($pluginName, $funcName, $instance) {
	global $TWIGFUNC;
	$TWIGFUNC[$pluginName.'.'.$funcName] = $instance;
}

//
// Check if we have handler for specified action
function checkLinkAvailable($pluginName, $handlerName) {
	global $UHANDLER;

	return isset($UHANDLER->hPrimary[$pluginName][$handlerName]);
}

//
// Generate link
// Params:
// $pluginName	- ID of plugin
// $handlerName	- Handler name
// $params	- Params to pass to processor
// $xparams	- External params to pass as "?param1=value1&...&paramX=valueX"
// $intLink	- Flag if links should be treated as `internal` (i.e. all '&' should be displayed as '&amp;'
// $absoluteLink - Flag if absolute link (including http:// ... ) should be generated
function generateLink($pluginName, $handlerName, $params = array(), $xparams = array(), $intLink = false, $absoluteLink = false) {
	global $UHANDLER;
 	return $UHANDLER->generateLink($pluginName, $handlerName, $params, $xparams, $intLink, $absoluteLink);
}

//
// Generate plugin link [ generate personal link if available. if not - generate common link ]
// Params:
// $pluginName	- ID of plugin
// $handlerName	- Handler name
// $params	- Params to pass to processor
// $xparams	- External params to pass as "?param1=value1&...&paramX=valueX"
// $intLink	- Flag if links should be treated as `internal` (i.e. all '&' should be displayed as '&amp;'
// $absoluteLink - Flag if absolute link (including http:// ... ) should be generated
function generatePluginLink($pluginName, $handlerName, $params = array(), $xparams = array(), $intLink = false, $absoluteLink = false) {

	return checkLinkAvailable($pluginName, $handlerName)?
		generateLink($pluginName, $handlerName, $params, $xparams, $intLink, $absoluteLink):
		generateLink('core', 'plugin', array('plugin' => $pluginName, 'handler' => $handlerName), array_merge($params, $xparams), $intLink, $absoluteLink);
}

//
// Generate link to page
function generatePageLink($paginationParams, $page, $intlink = false) {
	//print "generatePageLink(".var_export($paginationParams, true).", ".$intlink.";".$page.")<br/>\n";
	// Generate link
	$lparams = $paginationParams['params'];
	$lxparams = $paginationParams['xparams'];

	if ($paginationParams['paginator'][2] or ($page > 1)) {
		if ($paginationParams['paginator'][1]) {
			$lxparams[$paginationParams['paginator'][0]] = $page;
		} else {
			$lparams[$paginationParams['paginator'][0]] = $page;
		}
	}
	//return generateLink($paginationParams['pluginName'], $paginationParams['pluginHandler'], $lparams, $lxparams);
	return generatePluginLink($paginationParams['pluginName'], $paginationParams['pluginHandler'], $lparams, $lxparams, $intlink);
}

//
//
function _MASTER_defaultRUN($pluginName, $handlerName, $params, &$skip, $handlerParams) {
	global $PPAGES, $SYSTEM_FLAGS, $CurrentHandler;
	// Preload requested plugin
	loadPlugin($pluginName, 'ppages');

	// Make chain-load for all plugins, that want to activate during this plugin activation
 loadActionHandlers('action.ppages.'.$pluginName);
	loadActionHandlers('plugin.'.$pluginName);

	$pcall = $PPAGES[$pluginName][$handlerName];

	if (is_array($pcall) and function_exists($pcall['func'])) {
		// Make page title
		$SYSTEM_FLAGS['info']['title']['group']	= __('loc_plugin');

		// Report current handler config
		$CurrentHandler = array(
			'pluginName' => $pluginName,
			'handlerName' => $handlerName,
			'params' => $params,
			'handlerParams' => $handlerParams,
		);
		$req = call_user_func($pcall['func'], $params);
		if (!is_null($req) and $skip['FFC'] and !$req)
			$skip['fail'] = 1;
	} else {
		msg(array('type' => 'danger', 'message' => str_replace(array('{handler}', '{plugin}'), array(secure_html($handlerName), secure_html($pluginName)), __('plugins.nohadler'))));
		return false;
	}
	return true;
}

function _MASTER_URL_PROCESSOR($pluginName, $handlerName, $params, $skip, $handlerParams) {
	global $PPAGES, $CurrentHandler, $timer;

	//print "## PLUGIN CALL: <b> (".$pluginName.", ".$handlerName.")</b><br/>\n";
	//print "<pre>".var_export($params, true)."</pre><br/>\n";
	$timer->registerEvent("URL Processor for [".$pluginName."][".$handlerName."]");

	// Check for predefined plugins call
	switch ($pluginName) {
		case 'news':
			$CurrentHandler = array('pluginName' => $pluginName, 'handlerName' => $handlerName, 'params' => $params, 'handlerParams' => $handlerParams);
			switch ($handlerName) {
				default:
					include_once root.'includes/news.php';
					showNews($handlerName, $params);
			}
			break;

		case 'static':
			$CurrentHandler = array('pluginName' => $pluginName, 'handlerName' => $handlerName, 'params' => $params, 'FFC' => $SKIP['FFC'], 'handlerParams' => $handlerParams);
			switch ($handlerName) {
				default:
					include_once root.'includes/static.php';
					$cResult = showStaticPage(array('id' => intval($params['id']), 'altname' => $params['altname'], 'FFC' => $skip['FFC'], 'print' => (($handlerName == 'print')?true:false)));
					if (!$cResult and $skip['FFC']) {
						$skip['fail'] = 1;
					}
			}
			break;

		case 'search':
			$CurrentHandler = array('pluginName' => $pluginName, 'handlerName' => $handlerName, 'params' => $params, 'handlerParams' => $handlerParams);
			switch ($handlerName) {
				default:
					include_once root.'includes/search.php';
					search_news();
			}
			break;

		case 'core':
			$CurrentHandler = array('pluginName' => $pluginName, 'handlerName' => $handlerName, 'params' => $params, 'handlerParams' => $handlerParams);
			switch ($handlerName) {
				case 'plugin':
					// Set our own params $pluginName and $handlerName and pass it to default handler
					_MASTER_defaultRUN($params['plugin'], isset($params['handler'])?$params['handler']:null, $params, $skip, $handlerParams);
					break;

				case 'registration':
					include_once root.'cmodules.php';
					coreRegisterUser();
					break;

				case 'activation':
					include_once root.'cmodules.php';
					coreActivateUser();
					break;

				case 'lostpassword':
					include_once root.'cmodules.php';
					coreRestorePassword();
					break;

				case 'login':
					include_once root.'cmodules.php';
					coreLogin();
					break;

				case 'logout':
					include_once root.'cmodules.php';
					coreLogout();
					break;

				default:
			}
			break;
		default:
			_MASTER_defaultRUN($pluginName, $handlerName, $params, $skip, $handlerParams);
	}

	// Return according to SKIP value
	if (isset($skip['fail']) and $skip['fail'])
		return array('fail' => $skip['fail']);

	return;
}
