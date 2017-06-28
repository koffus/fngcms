<?php

//
// Copyright (C) 2006-2012 Next Generation CMS (http://ngcms.ru/)
// Name: extras.inc.php
// Description: NGCMS extras managment functions
// Author: Vitaly Ponomarev
//

// Protect against hack attempts
if (!defined('NGCMS')) die ('HAL');

//
// Report if plugin is active // TWIG Enabled this
function getPluginStatusActive($pluginID)
{
    // Load list of active plugins
    $cPlugin = CPlugin::instance();
    $active = $cPlugin->getListActive();
    
    if (isset($active['active'][$pluginID])) {
        return true;
    }
    return false;
}

//
// Load plugins for specified action [ CAN BE USED FOR MANUAL PLUGIN PRELOAD ]
// * if $plugin name specified - manual PRELOAD mode is used:
// Each plugin can call loadActionHandlers(<action>, <plugin name>) for preloading
// plugin for action
function loadActionHandlers($action, $plugin = '')
{
    global $PLUGINS;

    $timer = MicroTimer::instance();
    // Load list of active plugins
    $cPlugin = CPlugin::instance();
    $active = $cPlugin->getListActive();

    $loadedCount = 0;
    // Find extras for selected action
    if (isset($active['actions'][$action]) and is_array($active['actions'][$action])) {
        // There're some modules
        foreach ($active['actions'][$action] as $key => $value) {
            // Skip plugins in manual mode
            if ($plugin and ($key != $plugin))
                continue;

            // Do only if this file is was not loaded earlier
            if (!isset($PLUGINS['loaded:files'][$value])) {
                // Try to load file. First check if it exists
                if (is_file(extras_dir . '/' . $value)) {
                    $tX = $timer->stop(4);
                    include_once extras_dir . '/' . $value;
                    $timer->registerEvent('loadActionHandlers(' . $action . '): preloaded file "' . $value . '" for ' . round($timer->stop(4) - $tX, 4) . " sec");
                    $PLUGINS['loaded:files'][$value] = 1;
                    $loadedCount++;
                } else {
                    $timer->registerEvent('loadActionHandlers(' . $action . '): CAN\'t preload file that doesn\'t exists: "' . $value . '"');
                }
                $PLUGINS['loaded'][$key] = 1;
            }
        }
    }
    // Return count of loaded plugins
    return $loadedCount;
}


function registerActionHandler($action, $function, $arguments = 0, $priority = 5)
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
    $acts[$action][$priority][] = $function;

    return true;

}

function executeActionHandler($action)
{
    global $acts, $SYSTEM_FLAGS;

    $timer = MicroTimer::instance();

    $output = '';

    // Do not run action if it's disabled
    if (isset($SYSTEM_FLAGS['actions.disabled'][$action]) and $SYSTEM_FLAGS['actions.disabled'][$action]) {
        $timer->registerEvent('disabled executeActionHandler (' . $action . ')');
        return;
    }

    $timer->registerEvent('executeActionHandler (' . $action . ')');

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
            $output .= call_user_func($func);
            $timer->registerEvent('executeActionHandler (' . $action . '): call function "' . $func . '" for ' . round($timer->stop(4) - $tX, 4) . " sec");
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
// PLUGINS: parameters managment
// =========================================================

//

    // Get plugin variable
    function pluginGetVariable($pluginID, $var)
    {
        global $PLUGINS;

        if (!$PLUGINS['config:loaded'])
            return false;

        if (!isset($PLUGINS['config'][$pluginID])) {
            return null;
        }
        if (!isset($PLUGINS['config'][$pluginID][$var])) {
            return null;
        }
        return $PLUGINS['config'][$pluginID][$var];
    }
//
// Set variable
function pluginSetVariable($pluginID, $var, $value)
{
    global $PLUGINS;

    if (!$PLUGINS['config:loaded'])
        return false;

    $PLUGINS['config'][$pluginID][$var] = $value;
    return true;
}

//
// Save configuration parameters of plugins (should be called after pluginSetVariable)
function pluginsSaveConfig($suppressNotify = false)
{
    global $PLUGINS;

    if (!$PLUGINS['config:loaded']) {
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
    global $multiDomainName, $multimaster;

    $dir = root . 'cache/';
    if ($multiDomainName and $multimaster and ($multiDomainName != $multimaster)) {
        $dir .= 'multi/';
        if ((!is_dir($dir)) and (!mkdir($dir))) {
            print "Can't create multi cache dir!<br>\n";
            return '';
        }
    }
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
        array('dir' => 'cache', 'data' => "# Lock access\nOrder Deny,Allow\nDeny from all"),
        array('dir' => 'backups', 'data' => "# Lock access\n\t<FilesMatch .*>\n\tDeny from all\n</FilesMatch>"),
        array('dir' => 'conf', 'data' => "<files *>\n\tOrder Deny,Allow\n\tDeny from All\n</files>"),
    );

    //print '<pre>'.var_export($htaccess_array, true).'</pre>';

    if (is_array($htaccess_array))
        foreach ($htaccess_array as $result) {
            $htaccessFile = root . $result['dir'] . '/.htaccess';

            // Try to create file
            if (file_exists($htaccessFile))
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
function locatePluginTemplates($tname, $plugin, $localSource = 0, $skin = '', $block = '')
{
    global $config;

    // Check if $tname is correct
    if (!is_array($tname)) {
        if ($tname == '') {
            return array();
        }
        $tname = array($tname);
    }

    // Text SKIN+BLOCK
    $tsb = (((trim($skin)) or (trim($block))) ? '/' : '') .
        ($skin ? 'skins/' . $skin : '') .
        (((trim($skin)) and (trim($block))) ? '/' : '') .
        ($block ? $block : '');

    $tpath = array();
    foreach ($tname as $fn) {
        $fnc = (substr($fn, 0, 1) == ':') ? substr($fn, 1) : ($fn . '.tpl');
        if (!$localSource and is_readable(tpl_site . 'plugins/' . $plugin . $tsb . '/' . $fnc)) {
            $tpath[$fn] = tpl_site . 'plugins/' . $plugin . $tsb . '/';
            $tpath['url:' . $fn] = tpl_url . '/plugins/' . $plugin . $tsb;
        } else if (!$localSource and is_readable(tpl_site . 'plugins/' . $plugin . ($block ? ('/' . $block) : '') . '/' . $fnc)) {
            $tpath[$fn] = tpl_site . 'plugins/' . $plugin . ($block ? ('/' . $block) : '') . '/';
            $tpath['url:' . $fn] = tpl_url . '/plugins/' . $plugin . ($block ? ('/' . $block) : '');
        } else if (is_readable(extras_dir . '/' . $plugin . '/tpl' . $tsb . '/' . $fnc)) {
            $tpath[$fn] = extras_dir . '/' . $plugin . '/tpl' . $tsb . '/';
            $tpath['url:' . $fn] = admin_url . '/plugins/' . $plugin . '/tpl' . $tsb;
        }
    }
    return $tpath;
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
    $cPlugin->load($pluginName, 'ppages');

    // Make chain-load for all plugins, that want to activate during this plugin activation
    loadActionHandlers('action.ppages.' . $pluginName);
    loadActionHandlers('plugin.' . $pluginName);

    $pcall = $PPAGES[$pluginName][$handlerName];

    if (is_array($pcall) and function_exists($pcall['func'])) {
        // Make page title
        $SYSTEM_FLAGS['info']['title']['group'] = __('loc_plugin');

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
            $CurrentHandler = array('pluginName' => $pluginName, 'handlerName' => $handlerName, 'params' => $params, 'FFC' => $SKIP['FFC'], 'handlerParams' => $handlerParams);
            switch ($handlerName) {
                default:
                    include_once root . 'includes/static.php';
                    $cResult = showStaticPage(array('id' => intval($params['id']), 'altname' => $params['altname'], 'FFC' => $skip['FFC'], 'print' => (($handlerName == 'print') ? true : false)));
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
    if (isset($skip['fail']) and $skip['fail'])
        return array('fail' => $skip['fail']);

    return;
}
