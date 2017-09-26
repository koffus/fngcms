<?php

//
// Copyright (C) 2006-2017 BixBite CMS (http://bixbite.site/)
// Name: CacheClassAbstract.class.php
// Description: Cache manager
// Author: Vitaly Ponomarev
//

class CacheClassAbstract
{
    function get($plugin, $key, $expire = -1)
    {
        return false;
    }

    function set($plugin, $key, $value, $expire = -1)
    {
        return false;
    }

    function del($plugin, $key)
    {
        return false;
    }

    function getMulti($plugin, $keyList, $expire = -1)
    {
        return false;
    }

    function setMulti($plugin, $dataList, $expire = -1)
    {
        return false;
    }

    function increment($plugin, $key, $offset = 1)
    {
        return false;
    }

    function decrement($plugin, $key, $offset = 1)
    {
        return false;
    }
}
