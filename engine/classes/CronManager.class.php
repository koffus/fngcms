<?php

//
// Copyright (C) 2009-2012 Next Generation CMS (http://ngcms.ru/)
// Name: CronManager.class.php
// Description: CRON scheduler management
// Author: Vitaly Ponomarev
//

class CronManager
{
    // Configuration parameters
    public $config;

    // Constructor
    function __construct()
    {
        // Try to load configuration
        $configFileName = root . 'conf/cron.php';

        // Check if config file exists
        if (!is_file($configFileName)) {
            $this->config = array();
            return;
        }

        // Load config file
        $this->config = @include root . 'conf/cron.php';
        if (!is_array($this->config)) {
            $this->config = array();
        }
    }

    // Save updated config
    function saveConfig()
    {
        $configFileName = root . 'conf/cron.php';

        // Prepare resulting config content
        $fcData = "<?php\n" . 'return ' . var_export($this->config, true) . ";";

        // Try to save config
        $fcHandler = @fopen($configFileName, 'w');
        if ($fcHandler) {
            fwrite($fcHandler, $fcData);
            fclose($fcHandler);
            return true;
        }

        return false;
    }

    function checkList($value, $min, $max)
    {
        if ($value == '*')
            return true;

        foreach (explode(',', $value) as $v) {
            if (!preg_match("#^(\d+)$#", $v, $m))
                return false;

            if (($m[1] < $min) or ($m[1] > $max))
                return false;
        }
        return true;
    }

    function getConfig()
    {
        return $this->config;
    }

    function setConfig($config)
    {
        $this->config = $config;
        return $this->saveConfig();
    }

    // Register new CRON task
    function registerTask($plugin, $handler, $min = '*', $hour = '*', $day = '*', $month = '*', $dow = '*')
    {
        // Проверяем параметры
        if ((!$this->checkList($min, 0, 59)) or (!$this->checkList($hour, 0, 23)) ||
            (!$this->checkList($day, 0, 31)) or (!$this->checkList($month, 0, 12)) ||
            (!$this->checkList($dow, 0, 6)) or (!$plugin)
        ) {
            // Неверные значения параметров
            return 0;
        }

        $this->config [] = array(
            'min' => $min,
            'hour' => $hour,
            'day' => $day,
            'month' => $month,
            'dow' => $dow,
            'plugin' => $plugin,
            'handler' => $handler,
        );

        return $this->saveConfig();
    }

    // Deregister CRON task(s)
    function unregisterTask($plugin, $handler = '', $min = '', $hour = '', $day = '', $month = '', $DOW = '')
    {

        $ok = 0;
        foreach ($this->config as $k => $v) {
            if (((!$min) and ($v['plugin'] == $plugin) and ((!$handler) or ($v['handler'] == $handler))) or
                (($v['min'] == $min) and ($v['hour'] == $hour) and ($v['day'] == $day) and ($v['month'] == $month) and
                    ($v['dow'] == $DOW) and ($v['plugin'] == $plugin) and ($v['handler'] == $handler))
            ) {
                array_splice($this->config, $k, 1);
                $ok = 1;
            }
        }
        if ($ok) {
            return $this->saveConfig();
        }
        return 0;
    }

    // Cron core - run tasks
    function run($isSysCron = false)
    {

        $timer = MicroTimer::instance();
        // Load CORE Plugin
        $cPlugin = CPlugin::instance();

        // Check if there're any CRON tasks, return if no tasks
        if (sizeof($this->config) == 0) {
            return 0;
        }

        // Prepare for creating internal execution flag
        $cacheDir = get_plugcache_dir('core');

        $timeout = 120; // 120 секунд (2 минуты) на попытку
        $period = 300; // 5 минут между запусками

        //$timeout = 5;
        //$period = 10;

        if (!is_dir($cacheDir) and !mkdir($cacheDir)) {
            print "Can't create temp directory for plugin 'core'<br />\n";
            return;
        }

        // Determine time of last successfull run
        $lastRunTime = 0;
        $nowTime = time();
        $fn_progress = 0;

        if (!($dir = opendir($cacheDir))) {
            return -1;
        }
        while (false !== ($file = readdir($dir))) {
            if ((false !== ($fsize = filesize($cacheDir . '/' . $file))) and (preg_match('#^cron_(\d+)$#', $file, $m))) {
                if ($fsize and (intval($m[1]) > $lastRunTime)) {
                    $lastRunTime = intval($m[1]);
                } else if (intval($m[1]) > $fn_progress) {
                    $fn_progress = intval($m[1]);
                }
            }
        }
        closedir($dir);

        // Stop if there're still running processes or $period is not finished yet
        if (!(($lastRunTime + $period < $nowTime) and ($fn_progress + $timeout < $nowTime))) {
            return 0;
        }

        // Create temporally file for flag
        if (false === ($temp = tempnam($cacheDir, 'tmp_cron_'))) {
            // Не смогли создать файл (???)
            return -1;
        }

        // Create flag
        $myFlagTime = $nowTime;
        $myFlagFile = 'cron_' . $myFlagTime;

        // Try to rename
        if (!rename($temp, $cacheDir . '/' . $myFlagFile)) {
            // Unsuccessfull, delete temp file, terminate (someone was before us)
            unlink($temp);
            return 0;
        }

        // Check if someone else already created his flag
        $fn_max = 0;
        if (!($dir = opendir($cacheDir))) {
            return -1;
        }
        while (false !== ($file = readdir($dir))) {
            if (preg_match("#^cron_(\d+)$#", $file, $m) and intval($m[1]) > $fn_max) {
                $fn_max = $m[1];
            }
        }
        closedir($dir);

        if ($fn_max > $myFlagFile) {
            // Someone was faster, terminate
            unlink($cacheDir . '/' . $myFlagFile);
            return 0;
        }

        //===========================================================================================
        // Fine, we created our own flag! Now we can run jobs, but within $timeout period!
        //===========================================================================================

        // Prepare a list of tasks that we should run after previous call
        $runList = array();

        //print ">> Last run: ".date("Y-m-d H:i:s", $lastRunTime)." ($lastRunTime)<br />\n";
        //print ">> Now: ".date("Y-m-d H:i:s", $nowTime)." ($nowTime)<br />\n";
        foreach ($this->config as $cronLine) {
            // Expand lines that uses lists
            $execList = array();
            foreach (explode(',', $cronLine['month']) as $xm)
                foreach (explode(',', $cronLine['day']) as $xd)
                    foreach (explode(',', $cronLine['hour']) as $xh)
                        foreach (explode(',', $cronLine['min']) as $xmin)
                            $execList [] = array('month' => $xm, 'day' => $xd, 'hour' => $xh, 'min' => $xmin);

            // Scan expanded lines and check if we should run plugin now
            //print "Exec lines: <pre>".var_export($execList, true)."</pre>";
            $flagRun = false;
            foreach ($execList as $rec) {
                // Use 'last execution time' as basis
                $at = localtime($lastRunTime, 1);

                // Zero seconds
                $at['tm_sec'] = 0;

                // Process line
                if ($rec['min'] !== '*') {
                    if ($rec['min'] < $at['tm_min']) {
                        $at['tm_hour']++;
                    }
                    $at['tm_min'] = $rec['min'];
                }
                if ($rec['hour'] !== '*') {
                    if ($rec['hour'] < $at['tm_hour']) {
                        $at['tm_mday']++;
                    }
                    $at['tm_hour'] = $rec['hour'];
                }
                if ($rec['day'] !== '*') {
                    if ($rec['day'] < $at['tm_mday']) {
                        $at['tm_mon']++;
                    }
                    $at['tm_mday'] = $rec['day'];
                }
                if ($rec['month'] !== '*') {
                    if ($rec['month'] < ($at['tm_mon'] + 1)) {
                        $at['tm_year']++;
                    }
                    $at['tm_mon'] = $rec['month'] - 1;
                }

                $newtime = mktime($at['tm_hour'], $at['tm_min'], 0, $at['tm_mon'] + 1, $at['tm_mday'], $at['tm_year'] + 1900);

                if ($newtime <= $nowTime) {
                    $flagRun = true;
                    break;
                }
            }

            if ($flagRun) {
                // Mark plugin as 'need to run'
                $runList[$cronLine['plugin'] . '_' . $cronLine['handler']] = array($cronLine['plugin'], $cronLine['handler']);
            }
        }

        $trace = '';
        // Check if now we have anything to run
        if (sizeof($runList)) {

            // Call handlers
            foreach ($runList as $num => $run) {
                $funcName = '';
                // Preload plugin and get function name
                if ($run[0] == 'core') {
                    // SYSTEM plugins
                    $funcName = 'core_cron';
                } else {
                    // COMMON plugins - load plugin for handler "CRON"
                    $cPlugin->load($run[0], 'cron');
                    $funcName = 'plugin_' . $run[0] . '_cron';
                }
                // Try to call function and to pass parameter (handler)
                if (function_exists($funcName)) {
                    $t0 = $timer->stop(4);
                    call_user_func($funcName, $isSysCron, $run[1]);
                    $t1 = $timer->stop(4);

                    ngSYSLOG(array('plugin' => 'core', 'item' => 'cronExecute'), array('action' => $run[0], 'list' => $run), null, array(1, 'Execute cron job for ' . sprintf("%7.4f", $t1 - $t0) . ' sec'));
                } else {
                    ngSYSLOG(array('plugin' => 'core', 'item' => 'cronExecute'), array('action' => $run[0], 'list' => $run), null, array(0, 'Function does not exists!'));
                }
                $trace .= 'Execute cron job [' . $run[0] . '] action [' . $run[1] . "]\n";
            }
        }

        // ====================================
        // All tasks are finished
        // ====================================

        // Mark flag as 'complete'
        if (false !== ($f = fopen($cacheDir . '/' . $myFlagFile, 'w'))) {
            fwrite($f, $trace);
            fwrite($f, 'OK');
            fclose($f);
        } else {
            return -1;
        }

        // ====================================
        // CleanUP old flags
        // ====================================

        if (!($dir = opendir($cacheDir))) {
            return -1;
        }
        while (false !== ($file = readdir($dir))) {
            if (preg_match("#^cron_(\d+)$#", $file, $m) and (intval($m[1]) < $myFlagTime)) {
                unlink($cacheDir . '/' . $file);
            }
        }
        closedir($dir);

        // !! DONE !! //
        return 1;
    }
}
