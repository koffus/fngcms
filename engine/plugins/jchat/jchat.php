<?php

// Protect against hack attempts
if (!defined('BBCMS')) die ('HAL');

// Index screen for side panel
function plugin_jchat_index()
{
    global $template, $tpl, $SUPRESS_TEMPLATE_SHOW, $userROW, $CurrentHandler;

    // Load CORE Plugin
    $cPlugin = CPlugin::instance();
    Lang::loadPlugin('jchat', 'site', '', ':');

    // We shouldn't show side jchat panel if user currently visited separate jchat window
    if (isset($CurrentHandler['pluginName']) and ('jchat' == $CurrentHandler['pluginName'])) {
        $template['vars']['plugin_jchat'] = '';
        return;
    }

    // Check permissions [ guests do not see chat ]
    if (!pluginGetVariable('jchat', 'access') and !is_array($userROW)) {
        $template['vars']['plugin_jchat'] = '';
        return;
    }

    // Determine paths for all template files
    $tpath = plugin_locateTemplates('jchat', array(':jchat.css', 'jchat'));
    $cPlugin->regHtmlVar('css', $tpath['url::jchat.css'].'/jchat.css');

    $tvars = array();
    $start = isset($_REQUEST['start'])?intval($_REQUEST['start']):0;
    $tvars['vars']['data'] = json_encode(jchat_show(0,0));

    $history = intval(pluginGetVariable('jchat', 'history'));
    if (($history < 1) or ($history > 500)) $history = 30;

    $refresh = intval(pluginGetVariable('jchat', 'refresh'));
    if (($refresh < 5) or ($refresh > 1800)) $refresh = 120;

    $rate_limit = intval(pluginGetVariable('jchat', 'rate_limit'));
    if (($rate_limit < 5) or ($rate_limit > 60)) $rate_limit = 10;

    $maxlen = intval(pluginGetVariable('jchat', 'maxlen'));
    if (($maxlen < 1) or ($maxlen > 5000)) $maxlen = 500;

    $tvars['vars']['history'] = $history;
    $tvars['vars']['rate_limit'] = $rate_limit;
    $tvars['vars']['refresh'] = $refresh;

    $tvars['vars']['maxlen'] = $maxlen;
    $tvars['vars']['msgOrder'] = intval(pluginGetVariable('jchat', 'order'));

    $tvars['vars']['link_add'] = generateLink('core', 'plugin', array('plugin' => 'jchat', 'handler' => 'add'), array());
    $tvars['vars']['link_del'] = generateLink('core', 'plugin', array('plugin' => 'jchat', 'handler' => 'del'), array());
    $tvars['vars']['link_show'] = generateLink('core', 'plugin', array('plugin' => 'jchat', 'handler' => 'show'), array());
    $tvars['regx']['#\[is\.admin\](.*?)\[\/is\.admin\]#is'] = (is_array($userROW) and ($userROW['status'] == 1))?'$1':'';
    $tvars['regx']['#\[not-logged\](.*?)\[\/not-logged\]#is'] = is_array($userROW)?'':'$1';
    $tvars['regx']['#\[post-enabled\](.*?)\[\/post-enabled\]#is'] = (!is_array($userROW) and (pluginGetVariable('jchat', 'access') < 2))?'':'$1';

    $tvars['regx']['#\[selfwin\](.*?)\[\/selfwin\]#is'] = pluginGetVariable('jchat', 'enable_win') ? '$1' : '';
    $tvars['vars']['link_selfwin'] = generatePluginLink('jchat', null);

    $tpl->template('jchat', $tpath['jchat'], '', array('includeAllowed' => true));
    $tpl->vars('jchat', $tvars);
    //print $tpl->show('jchat');
    $template['vars']['plugin_jchat'] = $tpl->show('jchat');
}

// Index screen for self window
function plugin_jchat_win()
{
    global $template, $tpl, $SUPRESS_TEMPLATE_SHOW, $userROW;

    // Load CORE Plugin
    $cPlugin = CPlugin::instance();

    Lang::loadPlugin('jchat', 'site', '', ':');

    if (pluginGetVariable('jchat', 'win_mode'))
        $SUPRESS_TEMPLATE_SHOW = 1;

    // Check permissions [ guests receive an error ]
    if (!pluginGetVariable('jchat', 'access') and !is_array($userROW)) {
        if (pluginGetVariable('jchat', 'win_mode')) {
            $template['vars']['mainblock'] .= __('jchat:win.regonly');
        } else {
            msg(array('type' => 'danger', 'message' => __('jchat:regonly')));
        }
        return;
    }

    $tvars = array();

    // Determine paths for all template files
    $tpath = plugin_locateTemplates('jchat', array(':jchat.css', 'jchat.main', 'jchat.self'));
    $cPlugin->regHtmlVar('css', $tpath['url::jchat.css'].'/jchat.css');

    if ( intval(pluginGetVariable('jchat', 'win_mode')) ) {
        $tvars['vars']['home'] = home;
        $tvars['vars']['jchat.self.css'] = $tpath['url::jchat.css'].'/jchat.self.css';
    }

    $tvars['vars']['data'] = json_encode(jchat_show(0,0, array(array('setWinMode', 1))));

    $history = intval(pluginGetVariable('jchat', 'win_history'));
    if (($history < 1) or ($history > 500)) $history = 30;

    $refresh = intval(pluginGetVariable('jchat', 'win_refresh'));
    if (($refresh < 5) or ($refresh > 1800)) $refresh = 120;

    $rate_limit = intval(pluginGetVariable('jchat', 'rate_limit'));
    if (($rate_limit < 5) or ($rate_limit > 60)) $rate_limit = 10;

    $maxlen = intval(pluginGetVariable('jchat', 'maxlen'));
    if (($maxlen < 1) or ($maxlen > 5000)) $maxlen = 500;

    $tvars['vars']['history'] = $history;
    $tvars['vars']['refresh'] = $refresh;
    $tvars['vars']['rate_limit'] = $rate_limit;

    $tvars['vars']['maxlen'] = $maxlen;
    $tvars['vars']['msgOrder'] = intval(pluginGetVariable('jchat', 'win_order'));

    $tvars['vars']['link_add'] = generateLink('core', 'plugin', array('plugin' => 'jchat', 'handler' => 'add'), array());
    $tvars['vars']['link_show'] = generateLink('core', 'plugin', array('plugin' => 'jchat', 'handler' => 'show'), array());
    $tvars['vars']['link_del'] = generateLink('core', 'plugin', array('plugin' => 'jchat', 'handler' => 'del'), array());
    $tvars['regx']['#\[is\.admin\](.*?)\[\/is\.admin\]#is'] = (is_array($userROW) and ($userROW['status'] == 1))?'$1':'';
    $tvars['regx']['#\[not-logged\](.*?)\[\/not-logged\]#is'] = is_array($userROW)?'':'$1';
    $tvars['regx']['#\[post-enabled\](.*?)\[\/post-enabled\]#is'] = (!is_array($userROW) and (pluginGetVariable('jchat', 'access') < 2))?'':'$1';

    $templateName = intval(pluginGetVariable('jchat', 'win_mode'))?'jchat.self':'jchat.main';

    $tpl->template($templateName, $tpath[$templateName], '', array('includeAllowed' => true));
    $tpl->vars($templateName, $tvars);
    $template['vars']['mainblock'] .= $tpl->show($templateName);
}

// Show current chat state
function jchat_show($lastEventID, $maxLoadedID, $commands = array())
{
    global $userROW, $mysql, $tpl;

    // Check permissions [ guests do not see chat ]
    if (!pluginGetVariable('jchat', 'access') and !is_array($userROW))
        return false;

    // Format of TIME/DATE generation
    $format_time = pluginGetVariable('jchat', 'format_time');
    $format_date = pluginGetVariable('jchat', 'format_date');
    if (!$format_time) { $format_time = "%H:%M"; }
    if (!$format_date) { $format_date = "%d.%m.%Y %H:%M"; }

    // Get limit
    $limit = intval(pluginGetVariable('jchat', 'history'));
    if (($limit < 1) or ($limit > 500))
        $limit = 30;

    $result = '';
    $maxID = 0;
    $data = array();

    // Prepare data bundle
    $bundle = array(array(), array());

    // Check if chat work in WINDOW mode
    $winMode = intval(isset($_REQUEST['win'])?$_REQUEST['win']:0);

    $conf_maxidle = intval(pluginGetVariable('jchat', ($winMode?'win.':'').'maxidle'));
    if (isset($_REQUEST['idle']) and ($conf_maxidle > 0) and (intval($_REQUEST['idle']) > $conf_maxidle)) {
        $bundle[0] []= array('stop');
    }

    // Check if we have new events
    $newEvents = $mysql->record("select max(id) as id, max(type) as type from ".prefix."_jchat_events where id > ".intval($lastEventID));

    // Check if we have to update lastEventID
    if ($newEvents['id'] > $lastEventID) {
        $bundle[0] []= array('setLastEvent', intval($newEvents['id']));
    } else {
        // No new events
        return $bundle;
    }

    // Possible actions
    // * 3 = RELOAD [ only if $lastEventID is set ]
    if (($newEvents['type'] == 3) and ($lastEventID > 0)) {
        $bundle[0] []= array('reload');
        return $bundle;
    }

    // * 2 = There are deleted messages, return the whole list [ only if $lastEventID is set ]
    if (($newEvents['type'] == 2) and ($lastEventID > 0)) {
        $bundle[0] []= array('clear');
        $query = "select id, postdate, author, author_id, text from ".prefix."_jchat order by id desc limit ".$limit;
    }

    // * 1 = There are new messages [ or no $lastEventID is set ]
    if (($newEvents['type'] == 1) or ($lastEventID < 1)) {
        $query = "select id, postdate, author, author_id, text from ".prefix."_jchat where id >".intval($maxLoadedID)." order by id desc limit ".$limit;
    }

    // * NO NEW EVENTS - do not fetch data
    if (intval($newEvents['type']) < 1) {
        return $bundle;
    }
    
    foreach (array_reverse($mysql->select($query, 1)) as $row) {
        $maxID = max($maxID, $row['id']);
        $row['text'] = preg_replace('#^\@(.+?)\:#','<i>$1</i>:',$row['text']);
        $row['time'] = strftime($format_time, $row['postdate']);
        $row['datetime'] = strftime($format_date, $row['postdate']);
        $row['cdate'] = cDate($row['postdate']);
        if (pluginIsActive('uprofile')) {
            $row['profile_link'] = generatePluginLink('uprofile', 'show', array('name' => $row['author'], 'id' => $row['author_id']));
        }

        // Make some conversions to INT type
        $row['id'] = intval($row['id']);

        $data []= $row;
    }

    // Attach messages to bundle
    $bundle[1] = $data;

    // 1. Check if we need to reconfigure refresh rate
    $conf_refresh = intval(pluginGetVariable('jchat', 'refresh'));
    if (($conf_refresh < 5) or ($conf_refresh > 1800))
        $conf_refresh = 120;

    //if (isset($_REQUEST['timer']) and ($conf_refresh >= 5) and (intval($_REQUEST['timer']) != $conf_refresh))
    //	$bundle[0] []= array('settimer', $conf_refresh);

    // Add extra commands (if passed)
    if (is_array($commands) and count($commands)) {
        foreach ($commands as $cmd) {
            $bundle[0] []= $cmd;
        }
    }

    return $bundle;
}

function jchat_rpc_add($params)
{
    global $userROW, $mysql, $ip;

    $SQL = [];
    if (is_array($userROW)) {
        $SQL['author'] = $userROW['name'];
        $SQL['author_id'] = $userROW['id'];
    } else {
        if (empty($params['name'])) {
            return array('status' => 0, 'errorCode' => 999, 'errorText' => 'No name specified');
            coreNormalTerminate(2);
            exit;
        }
        $SQL['author'] = secure_html(mb_substr(trim($params['name']),0,30, 'UTF-8'));
        $SQL['author_id'] = 0;
    }
    if (!trim($params['text'])) {
            return array('status' => 0, 'errorCode' => 999, 'errorText' => 'No text specified');
            coreNormalTerminate(2);
            exit;
    }
    // If we're guest - check if we can make posts
    if (!is_array($userROW) and (pluginGetVariable('jchat', 'access') < 2)) {
            return array('status' => 0, 'errorCode' => 999, 'errorText' => 'Guests are not allowed to post');
            coreNormalTerminate(2);
            exit;
    }
    // Check for rate limit
    $rate_limit = intval(pluginGetVariable('jchat', 'rate_limit'));
    if ($rate_limit < 0)
        $rate_limit = 10;
    if (is_array($mysql->record("select id from ".prefix."_jchat where (ip = ".db_squote($ip).") and (postdate + ".$rate_limit.') > '.time()))) {
        return array('status' => 0, 'errorCode' => 999, 'errorText' => 'Rate limit. Only 1 message per '.$rate_limit.' sec is allowed');
        coreNormalTerminate(2);
        exit;
    }
    $maxlen = intval(pluginGetVariable('jchat', 'maxlen'));
    if (($maxlen < 1) or ($maxlen > 5000)) $maxlen = 500;
    $maxwlen = intval(pluginGetVariable('jchat', 'maxwlen'));
    if (($maxwlen < 1) or ($maxlen > 5000)) $maxwlen = 500;
    // Load text & strip it to maxlen
    $postText = mb_substr(secure_html($params['text']), 0, $maxlen, 'UTF-8');
    $ptb = array();
    foreach (preg_split('#(\s|^)(http\:\/\/[A-Za-z\-\.0-9]+\/\S*)(\s|$)#', $postText, -1, PREG_SPLIT_DELIM_CAPTURE) as $cx) {
        if (preg_match('#http\:\/\/[A-Za-z\-\.0-9]+\/\S*#', $cx, $m)) {
            // LINK
            $cx = '<a href="'.htmlspecialchars($cx, ENT_COMPAT | ENT_HTML401, 'UTF-8').'">'.((strlen($cx)>$maxwlen)?(mb_substr($cx, 0, $maxwlen-2, 'UTF-8').'..'):$cx).'</a>';
        } else {
            $cx = preg_replace('/(\S{'.$maxwlen.'})(?!\s)/', '$1 ', $cx);
        }
        $ptb[] = $cx;
    }
    $SQL['text'] = join('', $ptb);
    $SQL['chatid'] = 1;
    $SQL['ip'] = $ip;
    $SQL['postdate'] = time();
    // Create sql
    $vnames = array();
    $vparams = array();
    foreach ($SQL as $k => $v) {
        $vnames[] = $k;
        $vparams[] = db_squote($v);
    }
    // Add new message to chat
    $mysql->query("insert into ".prefix."_jchat (".implode(",",$vnames).") values (".implode(",",$vparams).")");
    // Update LastEventNotification
    $mysql->query("insert into ".prefix."_jchat_events (chatid, postdate, type) values (".$SQL['chatid'].", ".db_squote($SQL['postdate']).", 1)");
    $lid = $mysql->result("select LAST_INSERT_ID()");
    $mysql->query("delete from ".prefix."_jchat_events where type=1 and id <> ".db_squote($lid));
    // Return
    return array('status' => 1, 'errorCode' => 999, 'bundle' => jchat_show(intval($params['lastEvent']), intval($params['start'])));
    coreNormalTerminate(2);
    exit;
}

function jchat_rpc_del($params)
{
    global $userROW, $mysql, $ip;

    // Only ADMINS can delete items from chat
    if (!is_array($userROW) or ($userROW['status'] > 1)) {
        return array('status' => 0, 'errorCode' => 999, 'errorText' => 'Permission denied');
        coreNormalTerminate(2);
        exit;
    }

    // Try to load chat message
    $id = intval($params['id']);

    if (!($crow = $mysql->record("select * from ".prefix."_jchat where id = ".db_squote($id)))) {
        return array('status' => 0, 'errorCode' => 999, 'errorText' => 'Item not found (ID: '.$id.')');
        coreNormalTerminate(2);
        exit;
    }

    // Delete item
    $mysql->query("delete from ".prefix."_jchat where id = ".$id);
    // Update LastEventNotification
    $mysql->query("insert into ".prefix."_jchat_events (chatid, postdate, type) values (1, unix_timestamp(now()), 2)");
    $lid = $mysql->result("select LAST_INSERT_ID()");
    $mysql->query("delete from ".prefix."_jchat_events where type=2 and id <> ".db_squote($lid));

    // Return updated list of items from chat
    return array('status' => 1, 'errorCode' => 999, 'bundle' => jchat_show(intval($params['lastEvent']), intval($params['start'])));
    coreNormalTerminate(2);
    exit;
}

function jchat_rpc_show($params)
{
    return array('status' => 1, 'errorCode' => 999, 'bundle' => jchat_show(intval($params['lastEvent']), intval($params['start']), $params));
    coreNormalTerminate(2);
    exit;
}

// Register handler if self window is enabled
if (pluginGetVariable('jchat', 'enable_win'))
    register_plugin_page('jchat', '', 'plugin_jchat_win', 0);

// Register main page processor if panel windows is enabled
if (pluginGetVariable('jchat', 'enable_panel')) {
    registerActionHandler('index', 'plugin_jchat_index');
} else {
    global $template;
    $template['vars']['plugin_jchat'] = '';
}

rpcRegisterFunction('plugin.jchat.add', 'jchat_rpc_add');
rpcRegisterFunction('plugin.jchat.delete', 'jchat_rpc_del');
rpcRegisterFunction('plugin.jchat.show', 'jchat_rpc_show');
