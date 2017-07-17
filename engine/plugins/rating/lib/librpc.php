<?php

/*
 *
 * Always return 'msg' and 'content' !!!
 * Then user not clicked many times
 *
*/

function rating_rpc_manage($params) {
    global $mysql, $twig, $userROW;

    // Load CORE Plugin
    $cPlugin = CPlugin::instance();
    Lang::loadPlugin('rating', 'site');

    if (empty($params['rating']) or empty($params['post_id'])) {
        return array('status' => 0, 'errorCode' => 999, 'msg' => 'unsupported action', 'content' => '');
    }

    $rating = intval($params['rating']);
    $post_id = intval($params['post_id']);

    // Limit protection - rating values between 1..5
    if (($rating <1) or ($rating >5)) {
        return array('status' => 0, 'errorCode' => 999, 'msg' => __('rating_incorrect'), 'content' => '');
    }

    // Check if we feet "register only" limitation
    if (pluginGetVariable('rating','regonly') and !is_array($userROW)) {
        return array('status' => 0, 'errorCode' => 999, 'msg' => __('rating_only_reg'), 'content' => '');
    }

    // Check if referred news exists
    if (!is_array($row = $mysql->record("select * from ".prefix."_news where id = ".db_squote($post_id)))) {
        return array('status' => 0, 'errorCode' => 999, 'msg' => __('rating_not_found'), 'content' => '');
    }

    // Check if we try to make a duplicated rate
    if (isset($_COOKIE['rating'.$row['id']])) {
        return array('status' => 0, 'errorCode' => 999, 'msg' => __('rating_already'), 'content' => '');
    }

    @setcookie('rating'.$post_id, 'voted', (time() + 31526000), '/');
    $mysql->query("update ".prefix."_news set rating=rating+".$rating.", votes=votes+1 where id = ".db_squote($post_id));
    $data = $mysql->record("select rating, votes from ".prefix."_news where id = ".db_squote($post_id));

    $templateName = 'rating';
    $localSkin = pluginGetVariable('rating', 'localSkin');
    if (!$localSkin)
        $localSkin='basic';
    $tpath = locatePluginTemplates(array('rating', ':rating.css'), 'rating', pluginGetVariable('rating', 'localSource'), $localSkin);
    $cPlugin->regHtmlVar('css', $tpath['url::rating.css'].'/rating.css'); 

    $tVars = array(
        'tpl_url' => $tpath['url::rating.css'],
        'home' => home,
        'rating' => ($data['rating'] == 0) ? 0 : round(($data['rating'] / $data['votes']), 0),
        'votes' => $data['votes'],
        );

    $xt = $twig->loadTemplate($tpath[$templateName] . $templateName . '.tpl');
    return array('status' => 1, 'errorCode' => 0, 'content' => $xt->render($tVars), 'msg' => __('rating_thanks'));
}

rpcRegisterFunction('plugin.rating.update', 'rating_rpc_manage');
