<?php

//
// Copyright (C) 2006-2014 Next Generation CMS (http://ngcms.ru/)
// Name: extras.php
// Description: List plugins
// Author: Vitaly Ponomarev
//

// Protect against hack attempts
if (!defined('NGCMS')) die ('HAL');

// ==============================================================
// Module functions
// ==============================================================
@include_once root . 'includes/inc/extraconf.inc.php';
@include_once root . 'includes/inc/httpget.inc.php';

if ('clearCacheFiles' == $action) {
    clearCacheFiles();
}

// ==========================================================
// Functions
// ==========================================================

//
// Generate list of plugins
function admGeneratePluginList()
{
    global $twig, $repoPluginInfo, $PHP_SELF;

    // Load CORE Plugin
    $cPlugin = CPlugin::instance();
    $extras = $cPlugin->getInfo();

    $pCount = array(0 => 0, 1 => 0, 2 => 0, 3 => 0);

    $tEntries = array();
    foreach ($extras as $id => $extra) {
        if (!isset($extra['author_uri'])) {
            $extra['author_uri'] = '';
        }
        if (!isset($extra['author'])) {
            $extra['author'] = 'Unknown';
        }

        $tEntry = array(
            'version' => $extra['version'],
            'description' => isset($extra['description']) ? $extra['description'] : '',
            'author_url' => ($extra['author_uri']) ? ('<a href="' . ((strpos($extra['author_uri'], '@') !== FALSE) ? 'mailto:' : '') . $extra['author_uri'] . '">' . $extra['author'] . "</a>") : $extra['author'],
            'author' => $extra['author'],
            'id' => $extra['id'],
            'style' => pluginIsActive($id) ? 'pluginEntryActive' : 'pluginEntryInactive',
            'readme' => (file_exists(extras_dir . '/' . $id . '/readme') and filesize(extras_dir . '/' . $id . '/readme')) ? ('<a href="' . admin_url . '/includes/showinfo.php?mode=plugin&amp;item=readme&amp;plugin=' . $id . '" target="_blank" title="' . __('entry.readme') . '"><img src="' . skins_url . '/images/readme.png" width=16 height=16/></a>') : '',
            'history' => (file_exists(extras_dir . '/' . $id . '/history') and filesize(extras_dir . '/' . $id . '/history')) ? ('<a href="' . admin_url . '/includes/showinfo.php?mode=plugin&amp;item=history&amp;plugin=' . $id . '" target="_blank" title="' . __('entry.history') . '"><img src="' . skins_url . '/images/history.png" width=16 height=16/></a>') : ''
        );

        if (isset($repoPluginInfo[$extra['id']]) and ($repoPluginInfo[$extra['id']][1] > $extra['version'])) {
            $tEntry['new'] = '<a href="http://ngcms.ru/sync/plugins.php?action=jump&amp;id=' . $extra['id'] . '.html" title="' . $repoPluginInfo[$extra['id']][1] . '"target="_blank"><sup class="label label-success">NEW</sup></a>';
        } else {
            $tEntry['new'] = '';
        }

        $tEntry['type'] = in_array($extra['type'], array('plugin', 'module', 'filter', 'auth', 'widget', 'maintanance')) ? __($extra['type']) : 'Undefined';

        //
        // Check for permanent modules
        //
        if (($extra['permanent']) and (!pluginIsActive($id))) {
            // turn on
            if (pluginSwitch($id, 'on')) {
                msg(array('message' => sprintf(__('msgo_is_on'), $extra['name'])));
            } else {
                // generate error message
                msg(array('message' => 'ERROR: ' . sprintf(__('msgo_is_on'), $extra['name'])));
            }
        }

        $needinstall = false;
        $tEntry['install'] = '';
        if ($cPlugin->isInstalled($extra['id'])) {
            if (isset($extra['deinstall']) and $extra['deinstall'] and is_file(extras_dir . '/' . $extra['dir'] . '/' . $extra['deinstall'])) {
                $tEntry['install'] = '<a href="' . $PHP_SELF . '?mod=extra-config&amp;plugin=' . $extra['id'] . '&amp;stype=deinstall">' . __('deinstall') . '</a>';
            }
        } else {
            if (isset($extra['install']) and $extra['install'] and is_file(extras_dir . '/' . $extra['dir'] . '/' . $extra['install'])) {
                $tEntry['install'] = '<a href="' . $PHP_SELF . '?mod=extra-config&amp;plugin=' . $extra['id'] . '&amp;stype=install">' . __('install') . '</a>';
                $needinstall = true;
            }
        }

        $tEntry['url'] = (isset($extra['config']) and $extra['config'] and (!$needinstall) and is_file(extras_dir . '/' . $extra['dir'] . '/' . $extra['config'])) ? '<a href="' . $PHP_SELF . '?mod=extra-config&amp;plugin=' . $extra['id'] . '">' . $extra['name'] . '</a>' : $extra['name'];
        $tEntry['link'] = (pluginIsActive($id) ? '<a href="' . $PHP_SELF . '?mod=extras&amp;token=' . genUToken('admin.extras') . '&amp;disable=' . $id . '">' . __('switch_off') . '</a>' : '<a href="' . $PHP_SELF . '?mod=extras&amp;token=' . genUToken('admin.extras') . '&amp;enable=' . $id . '">' . __('switch_on') . '</a>');

        if ($needinstall) {
            $tEntry['link'] = '';
            $tEntry['style'] = 'pluginEntryUninstalled';
            $pCount[3]++;
        } else {
            $pCount[1 + (!pluginIsActive($id))]++;
        }
        $pCount[0]++;

        $tEntries [] = $tEntry;
    }

    $tVars = array(
        'entries' => $tEntries,
        'token' => genUToken('admin.extras'),
        'cntAll' => $pCount[0],
        'cntActive' => $pCount[1],
        'cntInactive' => $pCount[2],
        'cntUninstalled' => $pCount[3]
    );
    $xt = $twig->loadTemplate(tpl_actions . 'extras/table.tpl');
    echo $xt->render($tVars);
}

function repoSync()
{
    /*global $extras, $config;
    if (($vms = cacheRetrieveFile('plugversion.dat', 86400)) === false) {
        // Prepare request to repository
        $paramList = array('_ver=' . urlencode(engineVersion), 'UUID=' . $config['UUID']);
        foreach ($extras as $id => $extra)
            $paramList [] = urlencode($extra['id']) . "=" . urlencode($extra['version']);

        $req = new http_get();
        $vms = $req->get('http://ngcms.ru/components/update/?action=info&' . join('&', $paramList), 3, 1);

        // Save into cache
        cacheStoreFile('plugversion.dat', $vms);
    }
    $rps = unserialize($vms);
    return is_array($rps) ? $rps : array();*/
    return array();
}

// ==============================================================
// Main module code
// ==============================================================

// Load CORE Plugin
$cPlugin = CPlugin::instance();
// Load plugin list  
$extras = $cPlugin->getInfo();

Lang::load('extras', 'admin');

// ==============================================================
// Load a list of updated plugins from central repository
// ==============================================================
$repoPluginInfo = repoSync();

// ==============================================================
// Process enable request
// ==============================================================
$enable = isset($_REQUEST['enable']) ? $_REQUEST['enable'] : '';
$disable = isset($_REQUEST['disable']) ? $_REQUEST['disable'] : '';
$manage = (isset($_REQUEST['manageConfig']) and '1' === $_REQUEST['manageConfig']) ? true : false;

$id = (isset($_REQUEST['id'])) ? intval($_REQUEST['id']) : 0;

// Check for security token
if ('commit' == $action and ($enable or $disable or $manage)) {
    if (empty($_REQUEST['token']) or ($_REQUEST['token'] != genUToken('admin.extras'))) {
        msg(array('type' => 'danger', 'title' => __('error.security.token'), 'message' => __('error.security.token#desc')));
        ngSYSLOG(array('plugin' => '#admin', 'item' => 'extras', 'ds_id' => $id), array('action' => 'modify'), null, array(0, 'SECURITY.TOKEN'));
        exit;
    }
}

if ($manage) {

    if ('commit' == $action) {
        print "TRY COMMIT";
    }

    // Load of plugins configurations
    $pConfig = $cPlugin->getConfig();
    
    $confLine = json_encode($pConfig);
    $confLine = jsonFormatter($confLine);

    $tVars = array(
        'config' => $confLine,
        'token' => genUToken('admin.extras'),
    );
    $xt = $twig->loadTemplate('skins/default/tpl/extras/manage_config.tpl');
    echo $xt->render($tVars);

    //exit;
}

if ($enable) {
    if (pluginSwitch($enable, 'on')) {
        ngSYSLOG(array('plugin' => '#admin', 'item' => 'extras'), array('action' => 'switch_on', 'list' => array('plugin' => $enable)), null, array(1, ''));
        msg(array('message' => sprintf(__('msgo_is_on'), 'admin.php?mod=extra-config&plugin=' . $extras[$enable]['id'], $extras[$enable]['name'])));
    } else {
        // generate error message
        ngSYSLOG(array('plugin' => '#admin', 'item' => 'extras'), array('action' => 'switch_on', 'list' => array('plugin' => $enable)), null, array(0, 'ERROR: ' . $enable));
        msg(array('type' => 'danger', 'message' => 'ERROR: ' . sprintf(__('msgo_is_on'), $extras[$id]['name'])));
    }
}

if ($disable) {
    if ($extras[$disable]['permanent']) {
        ngSYSLOG(array('plugin' => '#admin', 'item' => 'extras'), array('action' => 'switch_off', 'list' => array('plugin' => $disable)), null, array(0, 'ERROR: PLUGIN is permanent ' . $disable));
        msg(array('type' => 'danger', 'title' => __('permanent.lock'), 'message' => str_replace('{name}', $disable, __('permanent.lock#desc'))));
    } else {
        if (pluginSwitch($disable, 'off')) {
            ngSYSLOG(array('plugin' => '#admin', 'item' => 'extras'), array('action' => 'switch_off', 'list' => array('plugin' => $disable)), null, array(1, ''));
            msg(array('message' => sprintf(__('msgo_is_off'), 'admin.php?mod=extra-config&plugin=' . $extras[$disable]['id'], $extras[$disable]['name'])));
        } else {
            // generate error message
            ngSYSLOG(array('plugin' => '#admin', 'item' => 'extras'), array('action' => 'switch_on', 'list' => array('plugin' => $disable)), null, array(0, 'ERROR: ' . $disable));
            msg(array('type' => 'danger', 'message' => 'ERROR: ' . sprintf(__('msgo_is_off'), $extras[$enable]['name'])));
        }
    }
}

if(empty($_REQUEST['manageConfig']))
    admGeneratePluginList();