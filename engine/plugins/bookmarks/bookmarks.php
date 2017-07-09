<?php

/*
 * bookmarks for NextGeneration CMS (http://ngcms.ru/)
 */
 
// protect against hack attempts
if (!defined('NGCMS')) die ('HAL');

// Load CORE Plugin
$cPlugin = CPlugin::instance();
//$cPlugin->regHtmlVar('js', admin_url.'/plugins/bookmarks/js/bookmarks.js');
//$cPlugin->regHtmlVar('plain', $bookmarks_script);
//$tpath = locatePluginTemplates(array(':bookmarks.css'), 'bookmarks', intval(pluginGetVariable('bookmarks', 'localSource')));
//$cPlugin->regHtmlVar('css', $tpath['url::bookmarks.css'].'/bookmarks.css'); 

Lang::loadPlugin('bookmarks', 'main', '', '', ':');

registerActionHandler('index', 'bookmarks_view');
register_plugin_page('bookmarks', 'update' , 'update', 0);
register_plugin_page('bookmarks', '' , 'bookmarksPage', 0);

/* declare variables to be global
 * bookmarksLoaded - flag is bookmarks already loaded
 * bookmarksList - result of $mysql -> select
 */
global $bookmarksLoaded, $bookmarksList;

$bookmarksLoaded = 0;
$bookmarksList = array();

// generate links for add/remove bookmark 
class BookmarksNewsFilter extends NewsFilter {

    function showNews($newsID, $SQLnews, &$tvars, $mode = array()) {
        global $bookmarksLoaded, $bookmarksList, $userROW, $tpl, $mysql, $twig;

        // determine paths for template files
        $tpath = locatePluginTemplates(array('add.remove.links.style', 'not.logged.links'), 'bookmarks', pluginGetVariable('bookmarks', 'localSource'));
        
        // exit if user is not logged in
        if (!is_array($userROW)) {
            // generate counter [if requested]
            if(pluginGetVariable('bookmarks', 'counter')){
                $tVars['counter'] = $mysql->result('SELECT COUNT(*) FROM '.prefix.'_bookmarks WHERE news_id='.$newsID);
                $tVars['counter'] = $tVars['counter'] ? $tVars['counter'] : '';
                //$tVars['text'] = __('bookmarks:act_delete');
                $xg = $twig->loadTemplate($tpath['not.logged.links'].'not.logged.links.tpl');
                $tvars['vars']['plugin_bookmarks_news'] = $xg->render($tVars);
            }
            else $tvars['vars']['plugin_bookmarks_news'] = '';
            return;
        }	

        // preload user's bookmarks
        if (!$bookmarksLoaded)
            bookmarks_sql();
            
        // check if this news is already in bookmark
        $found = 0;
        foreach ($bookmarksList as $brow) {
            if ($brow['id'] == $newsID) {
                $found = 1;
                break;
            }
        }
        
        // generate link
        $link = generatePluginLink('bookmarks', 'update', array(), array('news' => $newsID, 'action' => ($found ? 'delete' : 'add')));
        $url = generatePluginLink('bookmarks', 'update');

        $tVars = array('news' => $newsID, 'action' => ($found ? 'delete' : 'add'), 'link' => $link, 'found' => $found, 'url' => $url, 'link_title' => ($found ? __('bookmarks:title_delete') : __('bookmarks:title_add')));

        // generate counter [if requested]
        if(pluginGetVariable('bookmarks', 'counter')){
            $tVars['counter'] = $mysql->result('SELECT COUNT(*) FROM '.prefix.'_bookmarks WHERE news_id='.$newsID);
            $tVars['counter'] = $tVars['counter'] ? $tVars['counter'] : '';
        } else {
            $tVars['counter'] = '';
        }
        
        $xg = $twig->loadTemplate($tpath['add.remove.links.style'].'add.remove.links.style.tpl');
        $tvars['vars']['plugin_bookmarks_news'] = $xg->render($tVars);

    }
}

// function for fetching SQL bookmarks data
function bookmarks_sql(){
    global $mysql, $config, $userROW, $bookmarksLoaded, $bookmarksList;

    $bookmarksLoaded = 1;
    if ($userROW['id']) {
        $bookmarksList = $mysql->select("SELECT n.id, n.title, n.alt_name, n.catid, n.postdate FROM ".prefix."_bookmarks AS b LEFT JOIN ".prefix."_news n ON n.id = b.news_id WHERE b.user_id = ".db_squote($userROW['id']));
    }
}

// view bookmarks on sidebar
function bookmarks_view(){
    global $template, $tpl, $mysql, $config, $parse, $userROW, $bookmarksLoaded, $bookmarksList, $twig;

    // view on sidebar?
    if(!pluginGetVariable('bookmarks', 'sidebar')){
        $template['vars']['plugin_bookmarks'] = '';
        return;
    }
    
    // generate cache file name
    $cacheFileName = md5('bookmarks'.$config['theme'].$config['default_lang']).$userROW['id'].'.txt';

    if (pluginGetVariable('bookmarks','cache')) {
        $cacheData = cacheRetrieveFile($cacheFileName, pluginGetVariable('bookmarks','cacheExpire'), 'bookmarks');
        if ($cacheData != false) {
            // we got data from cache. Return it and stop
            $template['vars']['plugin_bookmarks'] = $cacheData;
            return;
        }
    }

    // determine paths for all template files
    $tpath = locatePluginTemplates(array('entries', 'bookmarks'), 'bookmarks', pluginGetVariable('bookmarks', 'localSource'));

    $maxlength = intval(pluginGetVariable('bookmarks','maxlength'));
    if (!$maxlength)	{ $maxlength = 100; }

    // preload user's bookmarks
    if (!$bookmarksLoaded and pluginGetVariable('bookmarks', 'sidebar'))
        bookmarks_sql();

    $output = '';
    $count = 0;
    
    foreach ($bookmarksList as $row){
        $count++;
        if($count > intval(pluginGetVariable('bookmarks', 'max_sidebar'))) break; 

        if (mb_strlen($row['title'], 'UTF-8') > $maxlength) {
        
            $title = mb_substr(secure_html($row['title']), 0, $maxlength, 'UTF-8') . "...";
        } else {
            $title = secure_html($row['title']);
        }
        
        $result[] = array('link' => News::generateLink($row), 'title' => $title);
    }

    // action on "hide empty"
    if ((!$count) and pluginGetVariable('bookmarks','hide_empty')) {
        if (pluginGetVariable('bookmarks','cache')) {
            cacheStoreFile($cacheFileName, ' ', 'bookmarks');
        }
        $template['vars']['plugin_bookmarks'] = '';
        return;
    }

    $tVars = array (
        'tpl_url' => tpl_url,
        'entries' => ($count ? $result : __('bookmarks:noentries')),
        'bookmarks_page' => generatePluginLink('bookmarks', null),
        'count' => $count,
        );

    $xt = $twig->loadTemplate($tpath['bookmarks'].'bookmarks.tpl');
    $output = $xt->render($tVars);
    $template['vars']['plugin_bookmarks'] = $output;

    // create cache file
    if (pluginGetVariable('bookmarks','cache')) {
        cacheStoreFile($cacheFileName, $output, 'bookmarks');
    }
}

// personal plugin pages for display all user's bookmarks
function bookmarksPage() {
    global $SYSTEM_FLAGS, $userROW, $bookmarksLoaded, $bookmarksList, $template, $config, $template, $tpl, $twig;

    // process bookmarks only for logged in users
    if (!is_array($userROW)) {
        // Redirect UNREG users far away :)
        coreRedirectAndTerminate($config['home_url'].'');
        return;
    }

    // preload user's bookmarks
    if (!$bookmarksLoaded) {
        bookmarks_sql();
    }

    // determine paths for template files
    $tpath = locatePluginTemplates(array('bookmarks.page', 'news.short'), 'bookmarks', pluginGetVariable('bookmarks', 'localSource'));

    $SYSTEM_FLAGS['info']['title']['group'] = __('bookmarks:pp_title');

    if(!count($bookmarksList)) {
        $output_data = __('bookmarks:nobookmarks');
    } else {

        include_once root.'includes/news.php';

        loadActionHandlers('news');

        // get id's news
        $ids = [];
        foreach ($bookmarksList as $brow) {
                $ids[]=$brow['id'];
        }

        // set news filter
        $filter = array('DATA', 'ID', 'IN', $ids);
        
        $callingParams = array('style' => 'short', 'plugin' => 'bookmarks', 'overrideTemplatePath' => (pluginGetVariable('bookmarks', 'news_short') ? $tpath['news.short'] : null));
        
        if (isset($_GET['page']) and (intval($_GET['page']) > 0)) {
            $callingParams['page'] = intval($_GET['page']);
        }
        else $callingParams['page'] = 1;
        
        $paginationParams = array('pluginName' => 'bookmarks', 'xparams' => array(), 'params' => array(), 'paginator' => array('page', 1, false));
        
        $newslist = news_showlist($filter, $paginationParams, $callingParams);

    }

    $tVars =array(
        'all_bookmarks' => $newslist,
        'count' => count($bookmarksList),
    );

    $xt = $twig->loadTemplate($tpath['bookmarks.page'].'bookmarks.page.tpl');
    $template['vars']['mainblock'] = $xt->render($tVars);
}

pluginRegisterFilter('news', 'bookmarks', new BookmarksNewsFilter);
