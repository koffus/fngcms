<?php

//
// Copyright (C) 2006-2017 BixBite CMS (http://bixbite.site/)
// Name: extras.inc.php
// Description: BixBite CMS extras managment functions
// Author: Vitaly Ponomarev
//

// Protect against hack attempts
if (!defined('BBCMS')) die ('HAL');

//
// Report if plugin is active // TWIG Enabled this
function pluginIsActive($pluginID)
{
    // Load list of active plugins
    $cPlugin = CPlugin::instance();
    $plugins = $cPlugin->getList();

    return isset($plugins['active'][$pluginID]);
}

//
// Add plugin's page
function register_plugin_page($pname, $mode, $func_name, $show_template = 1)
{
    global $PPAGES;

    if (!isset($PPAGES[$pname]) or !is_array($PPAGES[$pname])) {
        $PPAGES[$pname] = array();
    }
    $PPAGES[$pname][$mode] = array('func' => $func_name, 'mode' => $mode);
}

//
// Load plugins for specified action [ CAN BE USED FOR MANUAL PLUGIN PRELOAD ]
// * if $plugin name specified - manual PRELOAD mode is used:
// Each plugin can call loadActionHandlers(<action>, <plugin name>) for preloading
// plugin for action
function loadActionHandlers($action, $plugin = '')
{
    global $plugins;

    $timer = MicroTimer::instance();
    // Load list of active plugins
    $cPlugin = CPlugin::instance();
    $plugins = $cPlugin->getList();

    $loadedCount = 0;
    // Find extras for selected action
    if (isset($plugins['actions'][$action]) and is_array($plugins['actions'][$action])) {
        // There're some modules
        foreach ($plugins['actions'][$action] as $key => $value) {
            // Skip plugins in manual mode
            if ($plugin and ($key != $plugin))
                continue;

            // Do only if this file is was not loaded earlier
            if (!isset($plugins['loaded:files'][$value])) {
                // Try to load file. First check if it exists
                if (is_file(extras_dir . '/' . $value)) {
                    $tX = $timer->stop(4);
                    include_once extras_dir . '/' . $value;
                    $timer->registerEvent('loadActionHandlers(' . $action . '): preloaded file "' . $value . '" for ' . round($timer->stop(4) - $tX, 4) . " sec");
                    $plugins['loaded:files'][$value] = 1;
                    $loadedCount++;
                } else {
                    $timer->registerEvent('loadActionHandlers(' . $action . '): CAN\'t preload file that doesn\'t exists: "' . $value . '"');
                }
                $plugins['loaded'][$key] = 1;
            }
        }
    }
    // Return count of loaded plugins
    return $loadedCount;
}

function registerActionHandler($action, $function, $arguments = array(), $priority = 5)
{
    global $acts;

    // Check if function is already loaded for this action
    if (isset($acts['action']) and is_array($acts['action'])) {
        foreach ($acts['action'] as $k => $v) {
            if (is_array($v) and (array_search($function, $v) !== FALSE))
                return true;
        }
    }

    // Register new item
    if (is_array($function)) {
        $acts[$action][$priority][] = array($function, $arguments);
    } else {
        $acts[$action][$priority][] = $function;
    }

    return true;

}

function executeActionHandler($action)
{
    global $acts, $SYSTEM_FLAGS;

    $timer = MicroTimer::instance();

    // Do not run action if it's disabled
    if (!empty($SYSTEM_FLAGS['actions.disabled'][$action])) {
        $timer->registerEvent('disabled executeActionHandler (' . $action . ')');
        return;
    }

    $timer->registerEvent('executeActionHandler (' . $action . ')');

    // Preload plugins (if needed)
    loadActionHandlers($action);

    // Finish if there're no plugins for this action
    if (empty($acts[$action]) or !is_array($acts[$action])) {
        return true;
    }

    $output = '';

    foreach ($acts[$action] as $priority => $functions) {
        if (!is_array($functions))
            continue;

        foreach ($functions as $func) {
            $tX = $timer->stop(4);
            if (is_array($func)) {
                // func[0] = [$class, $method]; func[1] = [$args]
                $output .= call_user_func_array($func[0], $func[1]);
                $timer->registerEvent('executeActionHandler (' . $action . '): call function "' . get_class($func[0][0]) .'->' . $func[0][1] . '" for ' . round($timer->stop(4) - $tX, 4) . " sec");
            } else {
                $output .= call_user_func($func);
                $timer->registerEvent('executeActionHandler (' . $action . '): call function "' . $func . '" for ' . round($timer->stop(4) - $tX, 4) . " sec");
            }
        }
    }
    return $output;
}

// Disable desired action from plugin
function actionDisable($action)
{
    global $SYSTEM_FLAGS;
    $SYSTEM_FLAGS['actions.disabled'][$action] = 1;
    return;
}

// =========================================================
// plugins: parameters managment
// =========================================================

// Get plugin variable
function pluginGetVariable($pluginID, $var = null)
{
    // Load CORE Plugin
    $cPlugin = CPlugin::instance();
    return $cPlugin->getVar($pluginID, $var);
}

// Set variable
function pluginSetVariable($pluginID, $var, $value)
{
    // Load CORE Plugin
    $cPlugin = CPlugin::instance();
    $cPlugin->setVar($pluginID, $var, $value);
}

//
// Get configuration directory for plugin (and create it if needed)
function get_plugcfg_dir($plugin)
{

    $dir = confroot . 'extras';
    if ((!is_dir($dir)) and (!mkdir($dir))) {
        print "Can't create config directory for plugins. Please, check permissions for engine/conf/ dir<br/>\n";
        return '';
    }

    $dir .= '/' . $plugin;
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
function get_plugcache_dir($plugin)
{
    $dir = root . 'cache/';

    if ($plugin) {
        $dir .= $plugin . '/';
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
function cacheStoreFile($fname, $data, $plugin = '', $hugeMode = 0)
{

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

    $dest = pathinfo($dir . $fname)['dirname'];
    if(!file_exists($dest)) {
        mkdir($dest, 0777, true);
    }

    // Try to create file
    if (($fn = @fopen($dir . $fname, 'w')) == FALSE) {
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
function cacheRetrieveFile($fname, $expire, $plugin = '')
{

    // Try to get cache directory name. Return false if it's not possible
    if (!($dir = get_plugcache_dir($plugin))) {
        return false;
    }

    // Try to open file with data
    if (($fn = @fopen($dir . $fname, 'r')) == FALSE) {
        return false;
    }

    // Check if file is expired. Return if it's so.
    $stat = fstat($fn);
    if (!is_array($stat) or ($stat[9] + $expire < time())) {
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

function create_access_htaccess()
{

    $htaccess_array = array(
        array('dir' => site_root . 'logs', 'data' => "# Lock access\nOptions -Indexes\nOrder Deny,Allow\nDeny from all"),
        array('dir' => root . 'cache', 'data' => "# Lock access\nOrder Deny,Allow\nDeny from all"),
        array('dir' => root . 'backups', 'data' => "# Lock access\n\t<FilesMatch .*>\n\tDeny from all\n</FilesMatch>"),
        array('dir' => root . 'conf', 'data' => "<files *>\n\tOrder Deny,Allow\n\tDeny from All\n</files>"),
    );

    foreach ($htaccess_array as $result) {
        $htaccessFile = $result['dir'] . '/.htaccess';
        // Try to create file
        if (file_exists($htaccessFile)) {
            continue;
        }
        if (($fn = @fopen($htaccessFile, 'w')) == FALSE) {
            continue;
        }
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
// $plugin		- plugin name
// $tplNames	- template names (in string array or single name)
// $skin		- skin name in Theme site or all files are in own plugin dir
//function plugin_locateTemplates($tplNames, $plugin, $localSource = 0, $skin = '', $block = '')
function plugin_locateTemplates($plugin, $tplNames = null, $skin = null)
{

    // Check $plugin isset
    if (empty($plugin)) {
        return array();
    }

    // Check $tplNames isset
    if (empty($tplNames)) {
        $tplNames = array($plugin);
    }

    // Check $tplNames for correct
    if (!is_array($tplNames)) {
        $tplNames = array($tplNames);
    }

    // Check $skin isset
    if (!defined('ADMIN') and empty($skin)) {
        if (empty($skin = pluginGetVariable($plugin, 'skin'))) {
            $skin = 'basic';
        }
    }

    $tplPath = array();
    foreach ($tplNames as $tplName) {
        $fileName = (substr($tplName, 0, 1) == ':') ? substr($tplName, 1) : ($tplName . '.tpl');
        // Check site or admin templates
        if ($skin) {
            // site skin tempaltes
            if (is_readable(tpl_site . "plugins/$plugin/$skin/$fileName")) {
                // If isset skin in Theme templates
                $tplPath[$tplName] = tpl_site . "plugins/$plugin/$skin/";
                $tplPath['url:' . $tplName] = tpl_url . "plugins/$plugin/$skin/";
            } elseif (is_readable(extras_dir . "/$plugin/tpl/site/basic/$fileName")) {
                $tplPath[$tplName] = extras_dir . "/$plugin/tpl/site/basic/";
                $tplPath['url:' . $tplName] = admin_url . "/plugins/$plugin/tpl/site/basic/";
            }
        } else {
            // admin tempaltes
            if (is_readable(extras_dir . "/$plugin/tpl/admin/$fileName")) {
                $tplPath[$tplName] = extras_dir . "/$plugin/tpl/admin/";
                $tplPath['url:' . $tplName] = admin_url . "/plugins/$plugin/tpl/admin/";
            }
        }
    }

    return $tplPath;
}

// Register system filter
function pluginRegisterFilter($group, $name, $instance)
{
    global $PFILTERS;
    $PFILTERS[$group][$name] = $instance;
}

// Register admin filter
function register_admin_filter($group, $name, $instance)
{
    global $AFILTERS;
    $AFILTERS[$group][$name] = $instance;
}

// Register RPC function
function rpcRegisterFunction($name, $instance, $permanent = false)
{
    global $RPCFUNC;
    $RPCFUNC[$name] = $instance;
}

// Register TWIG function call
function twigRegisterFunction($pluginName, $funcName, $instance)
{
    global $TWIGFUNC;
    $TWIGFUNC[$pluginName . '.' . $funcName] = $instance;
}

//
// Check if we have handler for specified action
function checkLinkAvailable($pluginName, $handlerName)
{
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
function generateLink($pluginName, $handlerName, $params = array(), $xparams = array(), $intLink = false, $absoluteLink = false)
{
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
function generatePluginLink($pluginName, $handlerName, $params = array(), $xparams = array(), $intLink = false, $absoluteLink = false)
{

    return checkLinkAvailable($pluginName, $handlerName) ?
        generateLink($pluginName, $handlerName, $params, $xparams, $intLink, $absoluteLink) :
        generateLink('core', 'plugin', array('plugin' => $pluginName, 'handler' => $handlerName), array_merge($params, $xparams), $intLink, $absoluteLink);
}

// Generate link to page
function generatePageLink($paginationParams, $page, $intlink = false)
{
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
function _MASTER_defaultRUN($pluginName, $handlerName, $params, &$skip, $handlerParams)
{
    global $PPAGES, $SYSTEM_FLAGS, $CurrentHandler;

    // Load CORE Plugin
    $cPlugin = CPlugin::instance();
    // Preload requested plugin
    $cPlugin->loadPlugin($pluginName, 'ppages');

    // Make chain-load for all plugins, that want to activate during this plugin activation
    loadActionHandlers('action.ppages.' . $pluginName);
    loadActionHandlers('plugin.' . $pluginName);

    $pcall = $PPAGES[$pluginName][$handlerName];

    if (is_array($pcall) and ((is_array($pcall) and method_exists($pcall['func'][0], $pcall['func'][1])) or function_exists($pcall['func']))) {
        // Make page title
        $SYSTEM_FLAGS['info']['title']['group'] = __('loc_plugin');

        // Report current handler config
        $CurrentHandler = array(
            'pluginName' => $pluginName,
            'handlerName' => $handlerName,
            'params' => $params,
            'handlerParams' => $handlerParams,
        );
        if (is_array($pcall['func'])) {
            // func[0] = $class; func[1] = $method
            $req = call_user_func_array($pcall['func'], array($params));
        } else {
            $req = call_user_func($pcall['func'], $params);
        }
        if (!is_null($req) and $skip['FFC'] and !$req)
            $skip['fail'] = 1;
    } else {
        msg(array('type' => 'danger', 'message' => str_replace(array('{handler}', '{plugin}'), array(secure_html($handlerName), secure_html($pluginName)), __('plugins.nohadler'))));
        return false;
    }

    return true;
}

function _MASTER_URL_PROCESSOR($pluginName, $handlerName, $params, $skip, $handlerParams)
{
    global $PPAGES, $CurrentHandler;

    $timer = MicroTimer::instance();

    //print "## PLUGIN CALL: <b> (".$pluginName.", ".$handlerName.")</b><br/>\n";
    //print "<pre>".var_export($params, true)."</pre><br/>\n";
    $timer->registerEvent("URL Processor for [" . $pluginName . "][" . $handlerName . "]");

    // Check for predefined plugins call
    switch ($pluginName) {
        case 'news':
            $CurrentHandler = array('pluginName' => $pluginName, 'handlerName' => $handlerName, 'params' => $params, 'handlerParams' => $handlerParams);
            switch ($handlerName) {
                default:
                    include_once root . 'includes/news.php';
                    showNews($handlerName, $params);
            }
            break;

        case 'static':
            $CurrentHandler = array('pluginName' => $pluginName, 'handlerName' => $handlerName, 'params' => $params, 'FFC' => $skip['FFC'], 'handlerParams' => $handlerParams);
            switch ($handlerName) {
                default:
                    include_once root . 'includes/static.php';
                    $cResult = showStaticPage(array('id' => (isset($params['id']) ? intval($params['id']) : null), 'altname' => $params['altname'], 'FFC' => $skip['FFC'], 'print' => (($handlerName == 'print') ? true : false)));
                    if (!$cResult and $skip['FFC']) {
                        $skip['fail'] = 1;
                    }
            }
            break;

        case 'search':
            $CurrentHandler = array('pluginName' => $pluginName, 'handlerName' => $handlerName, 'params' => $params, 'handlerParams' => $handlerParams);
            switch ($handlerName) {
                default:
                    include_once root . 'includes/search.php';
                    search_news();
            }
            break;

        case 'core':
            $CurrentHandler = array('pluginName' => $pluginName, 'handlerName' => $handlerName, 'params' => $params, 'handlerParams' => $handlerParams);
            switch ($handlerName) {
                case 'plugin':
                    // Set our own params $pluginName and $handlerName and pass it to default handler
                    _MASTER_defaultRUN($params['plugin'], isset($params['handler']) ? $params['handler'] : null, $params, $skip, $handlerParams);
                    break;

                case 'registration':
                    include_once root . 'cmodules.php';
                    coreRegisterUser();
                    break;

                case 'activation':
                    include_once root . 'cmodules.php';
                    coreActivateUser();
                    break;

                case 'lostpassword':
                    include_once root . 'cmodules.php';
                    coreRestorePassword();
                    break;

                case 'login':
                    include_once root . 'cmodules.php';
                    coreLogin();
                    break;

                case 'logout':
                    include_once root . 'cmodules.php';
                    coreLogout();
                    break;

                default:
            }
            break;
        default:
            _MASTER_defaultRUN($pluginName, $handlerName, $params, $skip, $handlerParams);
    }

    // Return according to SKIP value
    if (!empty($skip['fail']))
        return array('fail' => $skip['fail']);

    return;
}
