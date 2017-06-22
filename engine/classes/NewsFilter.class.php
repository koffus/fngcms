<?php

//
// Copyright (C) 2009-2012 Next Generation CMS (http://ngcms.ru/)
// Name: NewsFilter.class.php
// Description: Class template definition: news filter
// Author: Vitaly Ponomarev
//

class NewsFilter
{
    // ### Add news interceptor ###
    // Form generator
    public function addNewsForm(&$tvars)
    {
        return 1;
    }

    // Adding executor [done BEFORE actual add and CAN block adding ]
    public function addNews(&$tvars, &$SQL)
    {
        return 1;
    }

    // Adding notificator [ after successful adding ]
    public function addNewsNotify(&$tvars, $SQL, $newsid)
    {
        return 1;
    }

    // ### Edit news interceptor ###
    // Form generator
    public function editNewsForm($newsID, $SQLnews, &$tvars)
    {
        return 1;
    }

    // Edit executor [done BEFORE actual edit and CAN block editing ]
    public function editNews($newsID, $SQLnews, &$SQLnew, &$tvars)
    {
        return 1;
    }

    // Edit Notifier [ adfter successful editing ]
    public function editNewsNotify($newsID, $SQLnews, &$SQLnew, &$tvars)
    {
        return 1;
    }

    // List news form generator [ in admin panel ]
    public function listNewsForm($newsID, $SQLnews, &$tvars)
    {
        return 1;
    }

    // ### Delete news interceptor ###
    // Delete news call
    public function deleteNews($newsID, $SQLnews)
    {
        return 1;
    }

    // Delete news notifier [ after news is deleted ]
    function deleteNewsNotify($newsID, $SQLnews)
    {
        return 1;
    }

    // ### Mass modify news interceptor ###
    // Mass modify news call
    public function massModifyNews($idList, $setValue, $currentData)
    {
        return 1;
    }

    // Mass modify news call [ after news are modified ]
    public function massModifyNewsNotify($idList, $setValue, $currentData)
    {
        return 1;
    }

    // ### SHOW news interceptor ###
    // Show news call :: preprocessor (call directly after news fetch) for each record
    // Mode - news show mode [ array ]
    // 'style' =>
    // * short - short news show
    // * full - full news show
    // * export - export news ( print / some plugins and so on )
    // 'plugin' => if is called from plugin - ID of plugin
    // 'emulate' => flag if emulation mode is used [ for example, for preview ]
    // 'nCount' => news if order (1,2,...) for SHORT news
    public function showNewsPre($newsID, &$SQLnews, $mode = array())
    {
        return 1;
    }

    // Show news call :: processor (call after all processing is finished and before show) for each record
    public function showNews($newsID, $SQLnews, &$tvars, $mode = array())
    {
        return 1;
    }

    // Behaviour before/after starting showing any news template
    // $newsID	- ID of the news to show
    // $SQLnews	- SQL row of the news
    // $mode	- array with config params
    // style	- working mode ( 'short' / 'full' / 'export' )
    // num		- number in order (for short list)
    // limit	- show limit in order (for short list)
    public function onBeforeNewsShow($newsID, $SQLnews, $mode = array())
    {
        return 1;
    }

    public function onAfterNewsShow($newsID, $SQLnews, $mode = array())
    {
        return 1;
    }

    // Called BEFORE showing list of news, but after fetching SQL query
    // $mode - callingParams, interesing values:
    //		'style'	 => mode for which we're called
    //			* short		- short new display
    //			* full		- full news display
    //			* export	- export data [ for plugins or so on. No counters are updated ]
    //		'query' => results of SELECT news query
    //			* count		- number of fetched news
    //			* ids		- array with IDs of fetched news
    //			* results	- output from SELECT query
    public function onBeforeShowlist($mode)
    {
        return 1;
    }

    // Behaviour before/after showing news template
    // $mode	- calling mode (may be 'short' or 'full')
    public function onBeforeShow($mode)
    {
        return 1;
    }

    public function onAfterShow($mode)
    {
        return 1;
    }

}
