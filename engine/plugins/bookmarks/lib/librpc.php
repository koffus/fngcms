<?php

function bookmarks_rpc_manage($params)
{
    global $mysql, $config, $userROW, $bookmarksList, $bookmarksLoaded, $twig;

    Lang::loadPlugin('bookmarks', 'site', '', ':');
    $newsID = intval($params['news']);

    // Check for permissions
    if (!is_array($userROW)) {
        return array('status' => 0, 'errorCode' => 3, 'errorText' => 'Access denied');
    }

    if(!$bookmarksLoaded) {
        $count_list = $mysql->result('SELECT COUNT(*) FROM '.prefix.'_bookmarks WHERE user_id = '.db_squote($userROW['id']));
    } else {
        $count_list = count($bookmarksList);
    }

    // for return reverse action 
    $action = '';
    if ($params['action'] == 'add') {
        // check limits
        if(intval(pluginGetVariable('bookmarks', 'bookmarks_limit')) < $count_list + 1){
            return array( 'status' => 0, 'errorCode' => 999, 'errorText' => __('bookmarks:err_add_limit'));
        }
        // check that this news exists & we didn't bookmarked this news earlier
        if (count($mysql->select("SELECT id FROM ".prefix."_news WHERE id = ".$newsID)) &&
            (!count($mysql->select("SELECT * FROM ".prefix.'_bookmarks WHERE user_id = '.db_squote($userROW['id'])." AND news_id=".$newsID)))) {
                // ok, bookmark it
                $mysql->query("INSERT INTO `".prefix."_bookmarks` (`user_id`,`news_id`) VALUES (".db_squote($userROW['id']).",".db_squote($newsID).")");
                $action = 'delete';
            } else {
                return array( 'status' => 0, 'errorCode' => 999, 'errorText' => __('bookmarks:err_add'));
            }
    } elseif ($params['action'] == 'delete') {
        $mysql->query("DELETE FROM `".prefix."_bookmarks` WHERE `user_id`=".db_squote($userROW['id'])." AND `news_id`=".db_squote($newsID));
        $action = 'add';
    }

    // if cache is activated - truncate cache file [ to clear cache ]
    if (pluginGetVariable('bookmarks','cache')) {
        $cacheFileName = md5('bookmarks'.$config['theme'].$config['default_lang']).$userROW['id'].'.txt';
        cacheStoreFile($cacheFileName, '', 'bookmarks');
    }

    // generate link
    $link = generatePluginLink('bookmarks', 'update', array(), array('news' => $newsID, 'action' => $action));
    $url = generatePluginLink('bookmarks', 'update');
    $tVars = array(
        'news' => $newsID,
        'action' => $action,
        'link' => $link,
        'text' => __('bookmarks:act_'.$action),
        'url' => $url,
        'link_title' => __('bookmarks:title_'.$action),
        );

    // generate counter [if requested]
    if(pluginGetVariable('bookmarks', 'counter')){
        $tVars['counter'] = $mysql->result('SELECT COUNT(*) FROM '.prefix.'_bookmarks WHERE news_id='.$newsID);
        $tVars['counter'] = $tVars['counter'] ? $tVars['counter'] : '';
    } else {
        $tVars['counter'] = '';
    }

    $tpath = plugin_locateTemplates('bookmarks', array('ajax.add.remove.links.style'));
    return [
        'status' => 1,
        'errorCode' => 0,
        'content' => $twig->render($tpath['ajax.add.remove.links.style'].'ajax.add.remove.links.style.tpl', $tVars),
        'action' => $action
        ];
}

rpcRegisterFunction('plugin.bookmarks.update', 'bookmarks_rpc_manage');
