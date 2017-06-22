<?php

//
// Copyright (C) 2006-2013 Next Generation CMS (http://ngcms.ru/)
// Name: CacheClassFile.class.php
// Description: Cache manager
// Author: Vitaly Ponomarev
//

class CacheClassFile extends CacheClassAbstract
{
    function get($plugin, $key, $expire = -1)
    {
        // Default expiration time = 120 sec
        if ($expire < 0)
            $expire = 120;

        // Try to get cache directory name. Return false if it's not possible
        if (!($dir = get_plugcache_dir($plugin))) {
            return false;
        }

        // Try to open file with data
        if (($fn = @fopen($dir . $fname, 'r')) == FALSE) {
            return false;
        }

        // Check if file is expired. Return if it's so.
        $stat = fstat($fn);
        if (!is_array($stat) or ($stat[9] + $expire < time())) {
            return false;
        }

        // Try to make shared file lock. Return if failed
        if (@flock($fn, LOCK_SH) == FALSE) {
            fclose($fn);
            return false;
        }

        // Return if file is empty
        if ($stat[7] < 1) {
            fclose($fn);
            return false;
        }

        // Read data from file
        $data = fread($fn, $stat[7]);

        // Unlock and close file
        flock($fn, LOCK_UN);
        fclose($fn);

        // Return data
        return $data;
    }

    function getMulti($plugin, $keyList, $expire = -1)
    {
        $res = array();
        foreach ($keyList as $key) {
            $res[$key] = $this->get($plugin, $key, $expire);
        }
        return $res;
    }

    function set($plugin, $key, $value, $expire = -1)
    {
        // Default expiration time = 120 sec
        if ($expire < 0)
            $expire = 120;

        // Try to get cache directory name. Return false if it's not possible
        if (!($dir = get_plugcache_dir($plugin))) {
            return false;
        }

        // Try to create file
        if (($fn = @fopen($dir . $fname, 'w')) == FALSE) {
            return false;
        }

        // Try to make exclusive file lock. Return if failed
        if (@flock($fn, LOCK_EX) == FALSE) {
            fclose($fn);
            return false;
        }

        // Write into file
        if (@fwrite($fn, $data) == -1) {
            // Failed.
            flock($fn, LOCK_UN);
            fclose($fn);
            return false;
        }

        flock($fn, LOCK_UN);
        fclose($fn);
        return true;
    }

    function setMulti($plugin, $keyList, $expire = -1)
    {
        $res = array();
        foreach ($keyList as $key) {
            $res[$key] = $this->set($plugin, $key, $expire);
        }
        return $res;
    }

}
