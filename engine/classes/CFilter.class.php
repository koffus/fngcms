<?php

//
// Copyright (C) 2009-2012 Next Generation CMS (http://ngcms.ru/)
// Name: CFilter.class.php
// Description: Class template definition: core actions filter
// Author: Vitaly Ponomarev
//

class CFilter
{
    // Register new user: FORM handler
    function registerUserForm(&$tvars)
    {
        return 1;
    }

    // Register new user: BEFORE actual registration
    function registerUser($params, &$msg)
    {
        return 1;
    }

    // Register new user: Notifier [ after successful adding ]
    function registerUserNotify($userID, $userRec)
    {
        return 1;
    }

    // Show usermenu
    function showUserMenu(&$tVars)
    {
        return 1;
    }
}
