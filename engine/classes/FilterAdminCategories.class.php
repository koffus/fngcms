<?php

//
// Copyright (C) 2009-2012 BixBite CMS (http://bixbite.site/)
// Name: FilterAdminCategories.class.php
// Description: Categories edit interceptors
// Author: Vitaly Ponomarev
//

class FilterAdminCategories
{

    // ### Add category interceptor ###
    // Form generator
    function addCategoryForm(&$tvars)
    {
        return 1;
    }

    // Adding executor [done BEFORE actual add and CAN block adding ]
    function addCategory(&$tvars, &$SQL)
    {
        return 1;
    }

    // Adding notificator [ after successful adding ]
    function addCategoryNotify(&$tvars, $SQL, $newsid)
    {
        return 1;
    }

    // ### Edit category interceptor ###
    // Form generator
    function editCategoryForm($categoryID, $SQL, &$tvars)
    {
        return 1;
    }

    // Edit executor [done BEFORE actual edit and CAN block editing ]
    function editCategory($categoryID, $SQL, &$SQLnew, &$tvars)
    {
        return 1;
    }

    // Edit Notifier [ adfter successful editing ]
    function editCategoryNotify($categoryID, $SQL, &$SQLnew, &$tvars)
    {
        return 1;
    }

}
