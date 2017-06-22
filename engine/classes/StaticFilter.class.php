<?php

//
// Copyright (C) 2009-2012 Next Generation CMS (http://ngcms.ru/)
// Name: StaticFilter.class.php
// Description: Class template definition: static
// Author: Vitaly Ponomarev
//

class StaticFilter
{
    // ### Add static interceptor ###
    // Form generator
    function addStaticForm(&$tvars)
    {
        return 1;
    }

    // Adding executor
    function addStatic(&$tvars, &$SQL)
    {
        return 1;
    }

    // ### Edit static interceptor ###
    // Form generator
    function editStaticForm($staticID, $SQLnews, &$tvars)
    {
        return 1;
    }

    // Edit executor
    function editStatic($staticID, $SQLstatic, &$SQLnew, &$tvars)
    {
        return 1;
    }

    // ### Delete static page interceptor ###
    // Delete static call
    function deleteStatic($staticID, $SQLstatic)
    {
        return 1;
    }

    // ### SHOW static interceptor ###
    // Show static call :: preprocessor (call directly after news fetch)
    // Mode - news show mode [ array ]
    // 'plugin' => if is called from plugin - ID of plugin
    // 'emulateMode' => flag if emulation mode is used [ for example, for preview ]
    function showStaticPre($staticID, &$SQLstatic, $mode)
    {
        return 1;
    }

    // Show static call :: processor (call after all processing is finished and before show)
    function showStatic($staticID, $SQLstatic, &$tvars, $mode)
    {
        return 1;
    }
}
