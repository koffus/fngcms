<?php

//
// Copyright (C) 2009-2012 BixBite CMS (http://bixbite.site/)
// Name: PermissionRuleManager.class.php
// Description: PermissionRuleManager
// Author: Vitaly Ponomarev
//

class PermissionRuleManager
{
    private $isLoaded = false;
    private $rules = array();

    function load()
    {
        if (is_file(confroot . 'perm.rules.php')) {
            // Try to load it
            include confroot . 'perm.rules.php';

            // Update GLOBAL variable $PERM
            if (isset($permRules)) {
                $this->rules = $permRules;
                $this->isLoaded = true;
                return true;
            }
        }
        return false;
    }

    function save()
    {
        if (!$this->isLoaded)
            return false;

        $prData = "<?php\n" . '$permRules = ' . var_export($this->$rules, true) . "\n;?>";

        // Try to save config
        $fcHandler = @fopen(confroot . 'perm.rules.php', 'w');
        if ($fcHandler) {
            fwrite($fcHandler, $prData);
            fclose($fcHandler);
            return true;
        }
        return false;
    }

    function getPluginTree($plugin)
    {
        if (!$this->isLoaded) {
            return false;
        }
        return $this->rules[$plugin];
    }

    function setPluginTree($plugin, $tree)
    {
        if (!$this->isLoaded) {
            return false;
        }
        $this->rules[$plugin] = $tree;
        return true;
    }

    function removePlugin($plugin)
    {
        if (!$this->isLoaded or ($plugin == '#admin')) {
            return false;
        }
        if (isset($this->rules[$plugin]))
            unset($this->rules[$plugin]);

        return true;
    }

    function listPlugins()
    {
        if (!$this->isLoaded) {
            return false;
        }

        $x = array();
        foreach ($this->rules as $k => $v)
            if ($k != '#admin')
                $x [] = $k;

        return $x;
    }

    function getList()
    {
        if (!$this->isLoaded)
            return false;
        return $this->rules;
    }
}
