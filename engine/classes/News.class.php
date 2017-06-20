<?php

class News
{

    // Fill variables for news:
    // * $row		- SQL row
    // * $fullMode		- flag if desired mode is full
    // * $page		- page No to show in full mode
    // * $disablePagination	- flag if pagination should be disabled
    // * $regenShortNews	- array, describe what to do with `short news`
    //	mode:
    //		''	- no modifications
    //		'auto'	- generate short news from long news in case if short news is empty
    //		'force'	- generate short news from long news in any case
    //	len		- size in chars for part of long news to use
    //	finisher	- chars that will be added into the end to indicate that this is truncated line ( default = '...' )
    //function Prepare($row, $page) {
    static function fillVariables($row, $fullMode, $page = 0, $disablePagination = 0, $regenShortNews = array())
    {
        global $config, $parse, $catz, $catmap, $CurrentHandler, $currentCategory, $TemplateCache, $mysql, $PHP_SELF;

        $tvars = array(
            'vars' => array(
                'news' => array('id' => $row['id']),
                'pagination' => '',
            ),
            'flags' => array()
        );

        $alink = checkLinkAvailable('uprofile', 'show') ?
            generateLink('uprofile', 'show', array('name' => $row['author'], 'id' => $row['author_id'])) :
            generateLink('core', 'plugin', array('plugin' => 'uprofile', 'handler' => 'show'), array('name' => $row['author'], 'id' => $row['author_id']));

        // [TWIG] news.author.*
        $tvars['vars']['news']['author']['name'] = $row['author'];
        $tvars['vars']['news']['author']['id'] = $row['author_id'];
        $tvars['vars']['news']['author']['url'] = $alink;

        // [TWIG] number of comments
        if (getPluginStatusActive('comments'))
            $tvars['vars']['p']['comments']['count'] = $row['com'];

        $tvars['vars']['author'] = '<a href="' . $alink . '" target="_blank">' . $row['author'] . '</a>';
        $tvars['vars']['author_link'] = $alink;
        $tvars['vars']['author_name'] = $row['author'];

        // [TWIG] news.flags.fullMode: if we're in full mode
        $tvars['vars']['news']['flags']['isFullMode'] = $fullMode ? true : false;

        $nlink = self::generateLink($row);

        // Divide into short and full content
        if ($config['extended_more']) {
            if (preg_match('#^(.*?)\<\!--more(?:\="(.+?)"){0,1}--\>(.+)$#is', $row['content'], $pres)) {
                $short = $pres[1];
                $full = $pres[3];
                $more = $pres[2];
            } else {
                $short = $row['content'];
                $full = '';
                $more = '';
            }
        } else {
            list ($short, $full) = array_pad(explode('<!--more-->', $row['content']), 2, '');
            $more = '';
        }
        // Default page number
        $page = 1;

        // Check if long part is divided into several pages
        if ($full and (!$disablePagination) and (mb_strpos($full, '<!--nextpage-->', 0, 'UTF-8') !== false)) {
            $page = intval(isset($CurrentHandler['params']['page']) ? $CurrentHandler['params']['page'] : (intval($_REQUEST['page']) ? : 0));
            if ($page < 1) $page = 1;

            $pagination = '';
            $pages = explode('<!--nextpage-->', $full);
            $pcount = count($pages);

            // [TWIG] news.pageCount, pageNumber
            $tvars['vars']['news']['pageCount'] = count($pages);
            $tvars['vars']['news']['pageNumber'] = $page;

            $tvars['vars']['pageCount'] = count($pages);
            $tvars['vars']['page'] = $page;

            if ($pcount > 1) {
                // Prepare VARS for pagination
                $catid = intval(array_shift(explode(',', $row['catid'])));
                $cname = 'none';
                if ($catid and isset($catmap[$catid]))
                    $cname = $catmap[$catid];

                // Generate pagination within news
                $paginationParams = checkLinkAvailable('news', 'news') ?
                    array('pluginName' => 'news', 'pluginHandler' => 'news', 'params' => array('category' => $cname, 'catid' => $catid, 'altname' => $row['alt_name'], 'id' => $row['id']), 'xparams' => array(), 'paginator' => array('page', 0, false)) :
                    array('pluginName' => 'core', 'pluginHandler' => 'plugin', 'params' => array('plugin' => 'news', 'handler' => 'news'), 'xparams' => array('category' => $cname, 'catid' => $catid, 'altname' => $row['alt_name'], 'id' => $row['id']), 'paginator' => array('page', 1, false));

                templateLoadVariables(true);
                $navigations = $TemplateCache['site']['#variables']['navigation'];

                // Show pagination bar
                $tvars['vars']['pagination'] = generatePagination($page, 1, $pcount, 10, $paginationParams, $navigations);

                // [TWIG] news.pagination
                $tvars['vars']['news']['pagination'] = $tvars['vars']['pagination'];

                if ($page > 1) {
                    $tvars['vars']['short-story'] = '';
                }
                $full = $pages[$page - 1];
                $tvars['vars']['[pagination]'] = '';
                $tvars['vars']['[/pagination]'] = '';
                $tvars['vars']['news']['flags']['hasPagination'] = true;
            }
        } else {
            $tvars['regx']["'\[pagination\].*?\[/pagination\]'si"] = '';
            $tvars['vars']['news']['flags']['hasPagination'] = false;
        }

        // Conditional blocks for full-page
        if ($full) {
            $tvars['regx']['#\[page-first\](.*?)\[\/page-first\]#si'] = ($page < 2) ? '$1' : '';
            $tvars['regx']['#\[page-next\](.*?)\[\/page-next\]#si'] = ($page > 1) ? '$1' : '';
        }

        // Delete "<!--nextpage-->" if pagination is disabled
        if ($disablePagination)
            $full = str_replace('<!--nextpage-->', "\n", $full);

        // If HTML code is not permitted - LOCK it
        $title = $row['title'];

        if (!($row['flags'] & 2)) {
            $short = str_replace('<', '&lt;', $short);
            $full = str_replace('<', '&lt;', $full);
            $title = secure_html($title);
        }
        $tvars['vars']['title'] = $title;

        // [TWIG] news.title
        $tvars['vars']['news']['title'] = $row['title'];

        // Make conversion
        if ($config['blocks_for_reg']) {
            $short = $parse->userblocks($short);
            $full = $parse->userblocks($full);
        }
        if ($config['use_bbcodes']) {
            $short = $parse->bbcodes($short);
            $full = $parse->bbcodes($full);
        }
        if ($config['use_htmlformatter'] and (!($row['flags'] & 1))) {
            $short = $parse->htmlformatter($short);
            $full = $parse->htmlformatter($full);
        }
        if ($config['use_smilies']) {
            $short = $parse->smilies($short);
            $full = $parse->smilies($full);
        }
        if (1 and templateLoadVariables()) {
            $short = $parse->parseBBAttach($short, $mysql, $TemplateCache['site']['#variables']);
            $full = $parse->parseBBAttach($full, $mysql, $TemplateCache['site']['#variables']);
        }

        // Check if we need to regenerate short news
        if (isset($regenShortNews['mode']) and (trim($regenShortNews['mode']))) {
            if ((($regenShortNews['mode'] == 'force') or (trim($short) == '')) and (trim($full))) {
                // REGEN
                if (!isset($regenShortNews['len']) or (intval($regenShortNews['len']) < 0)) {
                    $regenShortNews['len'] = 50;
                }
                if (!isset($regenShortNews['finisher'])) {
                    $regenShortNews['finisher'] = '&nbsp;...';
                }
                $short = $parse->truncateHTML($full, $regenShortNews['len'], $regenShortNews['finisher']);
            }

        }

        $tvars['vars']['short-story'] = $short;
        $tvars['vars']['full-story'] = $full;

        // [TWIG] news.short, news.full
        $tvars['vars']['news']['short'] = $short;
        $tvars['vars']['news']['full'] = $full;

        // Activities for short mode
        if (!$fullMode) {
            // Make link for full news
            $tvars['vars']['[full-link]'] = '<a href="' . $nlink . '">';
            $tvars['vars']['[/full-link]'] = '</a>';

            $tvars['vars']['[link]'] = '<a href="' . $nlink . '">';
            $tvars['vars']['[/link]'] = '</a>';

            $tvars['vars']['full-link'] = $nlink;

            // Make blocks [fullnews] .. [/fullnews] and [nofullnews] .. [/nofullnews]
            $tvars['vars']['news']['flags']['hasFullNews'] = mb_strlen($full, 'UTF-8') ? true : false;
            if (mb_strlen($full, 'UTF-8')) {
                // we have full news
                $tvars['vars']['[fullnews]'] = '';
                $tvars['vars']['[/fullnews]'] = '';

                $tvars['regx']["'\[nofullnews\].*?\[/nofullnews\]'si"] = '';
            } else {
                // we have ONLY short news
                $tvars['vars']['[nofullnews]'] = '';
                $tvars['vars']['[/nofullnews]'] = '';

                $tvars['regx']["'\[fullnews\].*?\[/fullnews\]'si"] = '';
            }

        } else {
            $tvars['regx']["#\[full-link\].*?\[/full-link\]#si"] = '';
            $tvars['regx']["#\[link\](.*?)\[/link\]#si"] = '$1';
        }

        $tvars['vars']['pinned'] = $row['pinned'] ? 'news_pinned' : '';

        $tvars['vars']['category'] = @GetCategories($row['catid']);
        $tvars['vars']['masterCategory'] = @GetCategories($row['catid'], false, true);

        // [TWIG] news.categories.*
        $tCList = makeCategoryInfo($row['catid']);
        $tvars['vars']['news']['categories']['count'] = count($tCList);
        $tvars['vars']['news']['categories']['list'] = $tCList;
        $tvars['vars']['news']['categories']['masterText'] = count($tCList) > 0 ? $tCList[0]['text'] : '';

        $tCTextList = array();
        foreach ($tCList as $tV)
            $tCTextList [] = $tV['text'];

        $tvars['vars']['news']['categories']['text'] = join(", ", $tCTextList);

        $tvars['vars']['[print-link]'] = '<a href="' . self::generateLink($row, true, $page) . '">';
        $tvars['vars']['print-link'] = self::generateLink($row, true, $page);
        $tvars['vars']['print_link'] = self::generateLink($row, true, $page);
        $tvars['vars']['[/print-link]'] = '</a>';
        $tvars['vars']['news_link'] = $nlink;

        // [TWIG] news.url
        $tvars['vars']['news']['url'] = array(
            'full' => $nlink,
            'print' => self::generateLink($row, true, $page),
        );

        // [TWIG] news.flags.isPinned
        $tvars['vars']['news']['flags']['isPinned'] = ($row['pinned']) ? true : false;

        $tvars['vars']['news-id'] = $row['id'];
        $tvars['vars']['news_id'] = $row['id'];
        $tvars['vars']['php-self'] = $PHP_SELF;

        $tvars['vars']['date'] = Lang::retDate(timestamp, $row['postdate']);
        $tvars['vars']['views'] = $row['views'];

        // [TWIG] news.date, news.dateStamp, news.views
        $tvars['vars']['news']['date'] = Lang::retDate(timestamp, $row['postdate']);
        $tvars['vars']['news']['dateStamp'] = $row['postdate'];
        $tvars['vars']['news']['views'] = $row['views'];

        if ($row['editdate'] > $row['postdate']) {
            // [TWIG] news.flags.isUpdated, news.update, news.updateStamp
            $tvars['vars']['news']['flags']['isUpdated'] = true;
            $tvars['vars']['news']['update'] = Lang::retDate($config['timestamp_updated'], $row['editdate']);
            $tvars['vars']['news']['updateStamp'] = $row['editdate'];

            $tvars['regx']['[\[update\](.*)\[/update\]]'] = '$1';
            $tvars['vars']['update'] = Lang::retDate($config['timestamp_updated'], $row['editdate']);
            $tvars['vars']['updateStamp'] = $row['editdate'];
        } else {
            // [TWIG] news.flags.isUpdated, news.update, news.updateStamp
            $tvars['vars']['news']['flags']['isUpdated'] = false;

            $tvars['regx']['[\[update\](.*)\[/update\]]'] = '';
            $tvars['vars']['update'] = '';
        }

        if ($more == '') {
            // [TWIG] news.flags.hasPersonalMore
            $tvars['vars']['news']['flags']['hasPersonalMore'] = false;

            $tvars['vars']['[more]'] = '';
            $tvars['vars']['[/more]'] = '';
        } else {
            // [TWIG] news.flags.hasPersonalMore, news.personalMore
            $tvars['vars']['news']['flags']['hasPersonalMore'] = true;
            $tvars['vars']['news']['personalMore'] = $more;

            $tvars['vars']['personalMore'] = $more;
            $tvars['regx']['#\[more\](.*?)\[/more\]#is'] = $more;
        }

        return $tvars;
    }

    // Generate link to news
    static function generateLink($row, $flagPrint = false, $page = 0, $absoluteLink = false)
    {
        global $catmap, $config;

        // Prepare category listing
        $clist = 'none';
        $ilist = 0;
        if ($row['catid']) {
            $ccats = array();
            $icats = array();
            foreach (explode(',', $row['catid']) as $ccatid) {
                if (trim($catmap[$ccatid])) {
                    $ccats[] = $catmap[$ccatid];
                    $icats[] = $ccatid;
                }
                if ($config['news_multicat_url'])
                    break;
            }
            $clist = implode("-", $ccats);
            $ilist = implode("-", $icats);
        }

        // Get full news link
        $params = array('category' => $clist, 'catid' => $ilist, 'altname' => $row['alt_name'], 'id' => $row['id'], 'zid' => sprintf('%04u', $row['id']), 'year' => date('Y', $row['postdate']), 'month' => date('m', $row['postdate']), 'day' => date('d', $row['postdate']));
        if ($page)
            $params['page'] = $page;

        return generateLink('news', $flagPrint ? 'print' : 'news', $params, array(), false, $absoluteLink);
    }
}
