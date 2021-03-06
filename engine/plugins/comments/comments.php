<?php

// Protect against hack attempts
if (!defined('BBCMS')) die ('HAL');

Lang::loadPlugin('comments', 'site', '', ':');

class CommentsNewsFilter extends NewsFilter
{

    protected $countDeleted = 0;

    function addNewsForm(&$tvars)
    {

        Lang::loadPlugin('comments', 'admin', '', ':');

        for ($ix = 0; $ix <= 2; $ix++) {
            $tvars['plugin']['comments']['acom:' . $ix] = (pluginGetVariable('comments', 'default_news') == $ix) ? 'selected="selected"' : '';
        }
    }

    function addNews(&$tvars, &$SQL)
    {
        $SQL['allow_com'] = intval($_REQUEST['allow_com']);
        return 1;
    }

    function editNewsForm($newsID, $SQLnews, &$tvars)
    {
        global $mysql, $config, $parse, $tpl, $PHP_SELF;

        Lang::loadPlugin('comments', 'admin', '', ':');

        // List comments
        $comments = '';
        $tpl->template('comments', tpl_actions . 'news');

        $crows = $mysql->select("SELECT * FROM " . prefix . "_comments WHERE post=" . db_squote($newsID) . " AND module='news' ORDER BY id");
        foreach ($crows as $crow) {
            $text = $crow['text'];

            if ($config['blocks_for_reg']) {
                $text = $parse->userblocks($text);
            }
            if ($config['use_bbcodes']) {
                $text = $parse->bbcodes($text);
            }
            if ($config['use_htmlformatter']) {
                $text = $parse->htmlformatter($text);
            }
            if ($config['use_smilies']) {
                $text = $parse->smilies($text);
            }

            $txvars['vars'] = array(
                'php_self' => $PHP_SELF,
                'com_author' => $crow['author'],
                'com_post' => $crow['post'],
                'com_url' => (!empty($crow['url'])) ? $crow['url'] : $PHP_SELF . '?mod=users&action=edituser&id=' . $crow['author_id'],
                'com_id' => $crow['id'],
                'com_ip' => $crow['ip'],
                'com_time' => Lang::retDate(pluginGetVariable('comments', 'timestamp'), $crow['postdate']),
                'com_part' => $text
            );

            if (!is_null($crow['author_id'])) {
                $txvars['vars']['[userlink]'] = '';
                $txvars['vars']['[/userlink]'] = '';
            } else {
                $txvars['regx']["'\\[userlink\\].*?\\[/userlink\\]'si"] = $crow['author'];
            }

            $tpl->vars('comments', $txvars);
            $comments .= $tpl->show('comments');
        }
        $tvars['plugin']['comments']['list'] = $comments;
        $tvars['plugin']['comments']['count'] = $SQLnews['com'] ? $SQLnews['com'] : __('noa');

        for ($ix = 0; $ix <= 2; $ix++) {
            $tvars['plugin']['comments']['acom:' . $ix] = ($SQLnews['allow_com'] == $ix) ? 'selected="selected"' : '';
        }
    }

    function editNews($newsID, $SQLold, &$SQLnew, &$tvars)
    {
        $SQLnew['allow_com'] = intval($_REQUEST['allow_com']);
        return 1;
    }

    function showNews($newsID, $SQLnews, &$tvars, $callingParams = array())
    {
        global $catmap, $catz, $config, $userROW, $template, $tpl;

        // Determine if comments are allowed in this specific news
        $allowCom = $SQLnews['allow_com'];
        if ($allowCom == 2) {
            // `Use default` - check master category
            $catid = explode(',', $SQLnews['catid']);
            $masterCat = intval(array_shift($catid));
            if ($masterCat and isset($catmap[$masterCat])) {
                $allowCom = intval($catz[$catmap[$masterCat]]['allow_com']);
            }

            // If we still have 2 (no master category or master category also have 'default' - fetch plugin's config
            if ($allowCom == 2) {
                $allowCom = pluginGetVariable('comments', 'global_default');
            }
        }

        // Fill variables within news template
        $tvars['vars']['comments-num'] = $SQLnews['com'];
        $tvars['vars']['comnum'] = $SQLnews['com'];
        $tvars['regx']['[\[comheader\](.*)\[/comheader\]]'] = ($SQLnews['com']) ? '$1' : '';

        // Blocks [comments] .. [/comments] and [nocomments] .. [/nocomments]
        $tvars['regx']['[\[comments\](.*)\[/comments\]]'] = ($SQLnews['com']) ? '$1' : '';
        $tvars['regx']['[\[nocomments\](.*)\[/nocomments\]]'] = ($SQLnews['com']) ? '' : '$1';

        // Check if we need to add comments block:
        //	* style == full
        // * emulate == false
        // * plugin == not set
        if (!(($callingParams['style'] == 'full') and (!isset($callingParams['emulate'])) and (!isset($callingParams['plugin'])))) {
            // No, we don't need to show comments
            $tvars['vars']['plugin_comments'] = '';
            return 1;
        }

        // Set we need to override news template
        $callingCommentsParams = array('outprint' => true, 'total' => $SQLnews['com']);

        // desired template
        $templateName = 'comments.internal';

        // Check if isset custom template for category in news
        if(tpl_site != ($tPath = getCatTemplate($SQLnews['catid'], $templateName))) {
            $callingCommentsParams['overrideTemplatePath'] = $tPath;
        } else {
            $tPath = plugin_locateTemplates('comments', $templateName);
            $tPath = $tPath[$templateName];
        }

        include_once(root . "/plugins/comments/inc/comments.show.php");

        // Check if we need pagination
        $flagMoreComments = false;
        $skipCommShow = false;

        if (pluginGetVariable('comments', 'multipage')) {
            $multi_mcount = intval(pluginGetVariable('comments', 'multi_mcount'));
            // If we have comments more than for one page - activate pagination
            if (($multi_mcount >= 0) and ($SQLnews['com'] > $multi_mcount)) {
                $callingCommentsParams['limitCount'] = $multi_mcount;
                $flagMoreComments = true;
                if (!$multi_mcount)
                    $skipCommShow = true;
            }
        }

        $tcvars = array();
        // Show comments [ if not skipped ]
        $tcvars['vars']['entries'] = $skipCommShow ? '' : comments_show($newsID, 0, 0, $callingCommentsParams);

        // If multipage is used and we have more comments - show
        if ($flagMoreComments) {
            $link = checkLinkAvailable('comments', 'show') ?
                generateLink('comments', 'show', array('post_id' => $newsID)) :
                generateLink('core', 'plugin', array('plugin' => 'comments', 'handler' => 'show'), array('post_id' => $newsID));

            $tcvars['vars']['more_comments'] = str_replace(array('{link}', '{count}'), array($link, $SQLnews['com']), __('comments:link.more'));
            $tcvars['regx']['#\[more_comments\](.*?)\[\/more_comments\]#is'] = '$1';
        } else {
            $tcvars['vars']['more_comments'] = '';
            $tcvars['regx']['#\[more_comments\](.*?)\[\/more_comments\]#is'] = '';
        }

        // Show form for adding comments
        if ($allowCom and (!pluginGetVariable('comments', 'regonly') or is_array($userROW))) {
            $tcvars['vars']['form'] = comments_showform($newsID, $callingCommentsParams);
            $tcvars['regx']['#\[regonly\](.*?)\[\/regonly\]#is'] = '';
            $tcvars['regx']['#\[commforbidden\](.*?)\[\/commforbidden\]#is'] = '';
        } else {
            $tcvars['vars']['form'] = '';
            $tcvars['regx']['#\[regonly\](.*?)\[\/regonly\]#is'] = $allowCom ? '$1' : '';
            $tcvars['regx']['#\[commforbidden\](.*?)\[\/commforbidden\]#is'] = $allowCom ? '' : '$1';
        }
        $tcvars['regx']['#\[comheader\](.*)\[/comheader\]#is'] = ($SQLnews['com']) ? '$1' : '';

        $tpl->template($templateName, $tPath);
        $tpl->vars($templateName, $tcvars);
        $tvars['vars']['plugin_comments'] = $tpl->show($templateName);

        return 1;
    }

    // Delete news call
    public function deleteNews($newsID, $SQLnews)
    {
        global $mysql;

        // Delete comments (with updating user's comment counter)
        if ($SQLnews['author_id']) {
            foreach ($mysql->select("SELECT * FROM ".prefix."_comments WHERE post=" . db_squote(intval($newsID)) . " AND author_id=".db_squote(intval($SQLnews['author_id'])) . " AND module='news'") as $crow) {
                if ($crow['author_id']) {
                    $mysql->query("UPDATE ".uprefix."_users set com=com-1 WHERE id=" . db_squote(intval($crow['author_id'])));
                }
            }
        }

        $result = $mysql->query("DELETE FROM ".prefix."_comments WHERE post=".db_squote($newsID) . " AND module='news'");
        $this->countDeleted = $mysql->num_rows($result);
        return 1;
    }

    // Delete news notifier [ after news is deleted ]
    function deleteNewsNotify($newsID, $SQLnews)
    {
        if ($this->countDeleted) {
            Lang::loadPlugin('comments', 'admin', '', ':');
            msg(array('type' => 'info', 'message' => sprintf(__('comments:msg.countDeleted'), $this->countDeleted)));
        }
        return 1;
    }
}

class CommentsFilterAdminCategories extends FilterAdminCategories
{
    function addCategory(&$tvars, &$SQL)
    {
        $SQL['allow_com'] = intval($_POST['allow_com']);
        return 1;
    }

    function addCategoryForm(&$tvars)
    {

        Lang::loadPlugin('comments', 'admin', '', ':');

        $ac = MakeDropDown(array('0' => 'запретить', '1' => 'разрешить', '2' => 'по умолчанию'), 'allow_com', pluginGetVariable('comments', 'default_categories'));

        $tvars['extend'] = isset($tvars['extend']) ? $tvars['extend'] :'';
        $tvars['extend'] .= '<div class="form-group"><div class="col-sm-5">' . __('comments:categories.comments') . '<span class="help-block">' . __('comments:categories.comments#desc') . '</span></div><div class="col-sm-7">' . $ac . '</div></div>';
        return 1;
    }

    function editCategoryForm($categoryID, $SQL, &$tvars)
    {

        Lang::loadPlugin('comments', 'admin', '', ':');

        if (!isset($SQL['allow_com'])) {
            $SQL['allow_com'] = pluginGetVariable('comments', 'default_categories');
        }

        $ac = MakeDropDown(array('0' => 'запретить', '1' => 'разрешить', '2' => 'по умолчанию'), 'allow_com', $SQL['allow_com']);

        $tvars['extend'] = isset($tvars['extend']) ? $tvars['extend'] :'';
        $tvars['extend'] .= '<div class="form-group"><div class="col-sm-5">' . __('comments:categories.comments') . '<span class="help-block">' . __('comments:categories.comments#desc') . '</span></div><div class="col-sm-7">' . $ac . '</div></div>';
        return 1;
    }

    function editCategory($categoryID, $SQL, &$SQLnew, &$tvars)
    {
        $SQLnew['allow_com'] = intval($_POST['allow_com']);
        return 1;
    }


}

// Show dedicated page for comments
function plugin_comments_show()
{
    global $config, $catz, $mysql, $catmap, $tpl, $template, $SUPRESS_TEMPLATE_SHOW, $userROW, $TemplateCache, $SYSTEM_FLAGS;

    // Load lang file, that is required for [hide]..[/hide] block
    Lang::load('news', 'site');

    $SYSTEM_FLAGS['info']['title']['group'] = __('comments:header.title');

    include_once(root . '/plugins/comments/inc/comments.show.php');

    // Try to fetch news
    $postID = intval($_REQUEST['post_id']);

    // Check module table
    if (!empty($_REQUEST['module']) and in_array($_REQUEST['module'], ['news','images']) ) {
        $table = secure_html($_REQUEST['module']);
    } else {
        $table = 'news';
    }

    if (!$postID or !is_array($newsRow = $mysql->record("select * from " . prefix . "_" . $table . " where id = " . db_squote($postID)))) {
        error404();
        return;
    }
    $SYSTEM_FLAGS['info']['title']['item'] = $newsRow['title'];

    // Prepare params for call
    // AJAX is turned off by default
    $callingCommentsParams = array('noajax' => 1, 'outprint' => true);

    // desired template
    $templateName = 'comments.external';

    // Check if isset custom template for category in news
    if(tpl_site != ($tPath = getCatTemplate($newsRow['catid'], $templateName))) {
        $callingCommentsParams['overrideTemplatePath'] = $tPath;
        if (file_exists($tPath . DS . 'main.tpl')) {
            $SYSTEM_FLAGS['template.main.path'] = $tPath;
        }
    } else {
        $tPath = plugin_locateTemplates('comments', $templateName);
        $tPath = $tPath[$templateName];
    }

    // Check if we need pagination
    $page = 0;
    $pageCount = 0;

    // If we have comments more than for one page - activate pagination
    $multi_scount = intval(pluginGetVariable('comments', 'multi_scount'));
    if (($multi_scount > 0) and ($newsRow['com'] > $multi_scount)) {

        // Page count
        $pageCount = ceil($newsRow['com'] / $multi_scount);

        // Check if user wants to access not first page
        $page = isset($_REQUEST['page']) ? intval($_REQUEST['page']) : '1';
        if ($page < 1) $page = 1;

        $callingCommentsParams['limitCount'] = intval(pluginGetVariable('comments', 'multi_scount'));
        $callingCommentsParams['limitStart'] = ($page - 1) * intval(pluginGetVariable('comments', 'multi_scount'));
    }

    // Pass total number of comments
    $callingCommentsParams['total'] = $newsRow['com'];

    // Show comments
    $tcvars = array();
    $tcvars['vars']['entries'] = comments_show($postID, 0, 0, $callingCommentsParams);

    if ($pageCount > 1) {
        $paginationParams = checkLinkAvailable('comments', 'show') ?
            array('pluginName' => 'comments', 'pluginHandler' => 'show', 'params' => array('post_id' => $postID), 'xparams' => array(), 'paginator' => array('page', 0, false)) :
            array('pluginName' => 'core', 'pluginHandler' => 'plugin', 'params' => array('plugin' => 'comments', 'handler' => 'show'), 'xparams' => array('post_id' => $postID), 'paginator' => array('page', 1, false));

        templateLoadVariables(true);
        $navigations = $TemplateCache['site']['#variables']['navigation'];
        $tcvars['vars']['more_comments'] = generatePagination($page, 1, $pageCount, 10, $paginationParams, $navigations, true);
        $tcvars['regx']['#\[more_comments\](.*?)\[\/more_comments\]#is'] = '$1';
    } else {
        $tcvars['vars']['more_comments'] = '';
        $tcvars['regx']['#\[more_comments\](.*?)\[\/more_comments\]#is'] = '';
    }

    // Enable AJAX in case if we are on last page
    if ($page == $pageCount)
        $callingCommentsParams['noajax'] = 0;

    $allowCom = $newsRow['allow_com'];

    // Show form for adding comments
    if ($newsRow['allow_com'] and (!pluginGetVariable('comments', 'regonly') or is_array($userROW))) {
        $tcvars['vars']['form'] = comments_showform($postID, $callingCommentsParams);
        $tcvars['regx']['#\[regonly\](.*?)\[\/regonly\]#is'] = '';
        $tcvars['regx']['#\[commforbidden\](.*?)\[\/commforbidden\]#is'] = '';
    } else {
        $tcvars['vars']['form'] = '';
        $tcvars['regx']['#\[regonly\](.*?)\[\/regonly\]#is'] = $allowCom ? '$1' : '';
        $tcvars['regx']['#\[commforbidden\](.*?)\[\/commforbidden\]#is'] = $allowCom ? '' : '$1';
    }

    // Show header file
    $tcvars['vars']['link'] = News::generateLink($newsRow);
    $tcvars['vars']['title'] = secure_html($newsRow['title']);
    $tcvars['regx']['[\[comheader\](.*)\[/comheader\]]'] = ($newsRow['com']) ? '$1' : '';

    $tpl->template($templateName, $tPath);
    $tpl->vars($templateName, $tcvars);
    $template['vars']['mainblock'] .= $tpl->show($templateName);
}

// Delete comment
function plugin_comments_delete()
{
    global $mysql, $config, $userROW, $tpl, $template, $SUPRESS_MAINBLOCK_SHOW, $SUPRESS_TEMPLATE_SHOW, $HTTP_REFERER;

    $output = array();
    $params = array();

    // First: check if user have enough permissions
    if (!is_array($userROW) or ($userROW['status'] > 2) or ($_GET['uT'] != genUToken(intval($_REQUEST['id'])))) {
        // Not allowed
        $output['status'] = 0;
        $output['data'] = __('perm.denied');
    } else {
        // Second: check if this comment exists
        $comid = intval($_REQUEST['id']);

        // Check module table
        if (!empty($_REQUEST['module']) and in_array($_REQUEST['module'], ['news','images']) ) {
            $table = secure_html($_REQUEST['module']);
        } else {
            $table = 'news';
        }

        if (($comid) and ($row = $mysql->record("select * from " . prefix . "_comments where id=" . db_squote($comid)))) {
            $mysql->query("delete from " . prefix . "_comments where id=" . db_squote($comid));
            $mysql->query("update " . uprefix . "_users set com=com-1 where id=" . db_squote($row['author_id']));
            $mysql->query("update " . prefix . "_" . $table . " set com=com-1 where id=" . db_squote($row['post']));

            $output['status'] = 1;
            $output['data'] = __('comments:deleted.text');
            $params['postID'] = $row['post'];
        } else {
            $output['status'] = 0;
            $output['data'] = __('comments:err.nocomment');
        }
    }

    $SUPRESS_TEMPLATE_SHOW = 1;

    // Check if we run AJAX request
    if (isset($_REQUEST['ajax'])) {
        $template['vars']['mainblock'] .= json_encode($output);
    } else {
        // NON-AJAX mode

        // If ok - redirect to news
        if ($output['status']) {
            msg(['type' => 'info', 'title' => __('comments:deleted.title'), 'message' => $output['data'], 'referer' => $HTTP_REFERER], 1, 3);
        } else {
            // Print error messag
            msg(['type' => 'danger', 'title' => __('comments:err.redir.title'), 'message' => $output['data'], 'referer' => $HTTP_REFERER], 1, 3);
        }
    }
}

Lang::loadPlugin('comments', 'site', '', ':');
pluginRegisterFilter('news', 'comments', new CommentsNewsFilter);
register_admin_filter('categories', 'comments', new CommentsFilterAdminCategories);

register_plugin_page('comments', 'show', 'plugin_comments_show', 0);
register_plugin_page('comments', 'delete', 'plugin_comments_delete', 0);
