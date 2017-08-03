<?php

/*
 * Class CPlugin
 * Description: CORE Plugin - manager configuration and functions
*/

class CPlugin
{
    use Singleton;
    
    // List of active plugins,
    // their configs and hooks system (acts, action)
    protected $plugins;

    // All information about plugins
    // from the "version" files
    protected $info;
    
    /**
     * CPlugin constructor.
     */
    protected function __construct()
    {
        $this->plugins = array(
            'active' => array(),
            'active:loaded' => 0,
            'loaded' => array(),
            'loaded:files' => array(),
            'config' => array(),
            'config:loaded' => 0,
        );

        $this->info = array();

        $this->loadList();
        $this->loadConfig();
        
        //dd($this->plugins);
    }

    // Load list of active plugins and required files
    // Protected reloading
    protected function loadList()
    {
        if ($this->plugins['active:loaded']) {
            return $this->plugins;
        }
        if (is_file(conf_pactive)) {
            include conf_pactive;
            if (is_array($array)) {
                $this->plugins = $array;
                $this->plugins['active:loaded'] = 1;

                return $this->plugins;
            } else {
                // Тут ошибку надо выдать
            }
        }
    }

    // getPluginsActiveList
    // Get list of active plugins and required files
    public function getList()
    {
        return $this->plugins;
    }

    // Set list of active plugins and required files
    // !!!!!!!!!!!! defined ADMIN
    public function setList($plugins)
    {
        if (is_array($plugins)) {
            $this->plugins = $plugins;
            return true;
        }

        return false;
    }

    // Save list of active plugins and required files
    // !!!!!!!!!!!! defined ADMIN
    public function saveListActive()
    {
        if(function_exists('opcache_reset')){
            opcache_reset(); 
        }
        if (!is_file(conf_pactive)) {
            return false;
        }
        if (!($file = fopen(conf_pactive, "w"))) {
            return false;
        }
        $listActive = [
            'active' => $this->plugins['active'],
            'actions' => $this->plugins['actions'],
            'installed' => $this->plugins['installed'],
            'libs' => $this->plugins['libs'],
            ];
        $content = '<?php $array = '.var_export($listActive, true).'; ?>';
        fwrite($file, $content);
        fclose($file);

        return true;
    }

    // Load configuration variables for all plugins
    // Protected reloading
    protected function loadConfig()
    {
        if (!empty($this->plugins['config:loaded'])) {
            return true;
        }
        if ($fconfig = @fopen(conf_pconfig, 'r')) {
            if (filesize(conf_pconfig)) {
                $content = fread($fconfig, filesize(conf_pconfig));
            } else {
                $content = serialize(array());
            }
            $this->plugins['config'] = unserialize($content);
            $this->plugins['config:loaded'] = 1;
            fclose($fconfig);

            return true;
        } else {
            // File doesn't exists, but Mark as `loaded`
            $this->plugins['config'] = array();
            $this->plugins['config:loaded'] = 1;
        }
        return false;
    }

    // $PLUGINS['config']
    // Get configuration variables
    // for all plugins, or one plugin, or one var
    public function getConfig($plugin = false, $var = false)
    {
        if (empty($this->plugins['config:loaded'])) {
            return null;
        }

        $pConfig = $this->plugins['config'];

        if($var and $plugin and isset($pConfig[$plugin]) and isset($pConfig[$plugin][$var])) {
            return $pConfig[$plugin][$var];
        } else if ($plugin and isset($pConfig[$plugin])) {
            return $pConfig[$plugin];
        } else {
            return $pConfig;
        }

        return null;
    }

    // Set configuration variables
    // for all plugins
    // !!!!!!!!!!!! defined ADMIN
    public function setConfig($pConfig)
    {
        if (empty($this->plugins['config:loaded'])) {
            return false;
        }
        if (is_array($pConfig)) {
            $this->plugins['config'] = $pConfig;
            return true;
        }
        return false;
    }

    // Save configuration parameters of plugins (should be called after pluginSetVariable)
    // !!!!!!!!!!!! defined ADMIN
    public function saveConfig($suppressNotify = false)
    {
        if (empty($this->plugins['config:loaded'])) {
            if (!$suppressNotify) {
                msg(array('type' => 'danger', 'title' => str_replace('{name}', conf_pconfig, __('error.config.read')), 'message' => __('error.config.read#desc')));
            }
            return false;
        }

        if (!($fconfig = fopen(conf_pconfig, 'w'))) {
            if (!$suppressNotify) {
                msg(array('type' => 'danger', 'title' => str_replace('{name}', conf_pconfig, __('error.config.write')), 'message' => __('error.config.write#desc')));
            }
            return false;
        }

        fwrite($fconfig, serialize($this->getConfig()));
        fclose($fconfig);

        return true;
    }

    // Load plugin [ Same behaviour as for loadActionHandlers ]
    public function loadPlugin($plugin, $actionList = '*')
    {
        $timer = MicroTimer::instance();
        $loadCount = 0;

        // Don't load if plugin is not activated
        if (!$this->plugins['active'][$plugin])
            return false;

        // Scan all available actions and preload plugin's file if needed
        foreach ($this->plugins['actions'] as $aName => $pList) {
            if (isset($pList[$plugin]) and
                ((is_array($actionList) and in_array($aName, $actionList)) or
                    (!is_array($actionList) and (($actionList == '*') or ($actionList == $aName))))
            ) {
                // Yes, we should load this file. If it's not loaded earlier
                $pluginFileName = $pList[$plugin];

                if (!isset($this->plugins['loaded:files'][$pluginFileName])) {
                    // Try to load file. First check if it exists
                    if (is_file(extras_dir . '/' . $pluginFileName)) {
                        $tX = $timer->stop(4);
                        include_once extras_dir . '/' . $pluginFileName;
                        $timer->registerEvent('func loadPlugin (' . $plugin . '): preloaded file "' . $pluginFileName . '" for ' . ($timer->stop(4) - $tX) . " sec");
                        $this->plugins['loaded:files'][$pluginFileName] = 1;
                        $loadCount++;
                    } else {
                        $timer->registerEvent('func loadPlugin (' . $plugin . '): CAN\'t preload file that doesn\'t exists: "' . $pluginFileName . '"');
                    }
                }
            }
        }

        $this->plugins['loaded'][$plugin] = 1;
        return $loadCount;
    }

    // Load plugin's library
    public function loadLibrary($plugin, $libname = '')
    {
        $timer = MicroTimer::instance();

        // Check if we know about this plugin
        if (!isset($this->plugins['active'][$plugin])) return false;

        // Check if we need to load all libs
        if (!$libname) {
            foreach ($this->plugins['libs'][$plugin] as $id => $file) {
                $tX = $timer->stop(4);
                include_once extras_dir . '/' . $this->plugins['active'][$plugin] . '/' . $file;
                $timer->registerEvent('loadLibrary: ' . $plugin . '.' . $id . ' [' . $file . '] for ' . round($timer->stop(4) - $tX, 4) . " sec");
            }
            return true;
        } else {
            if (isset($this->plugins['libs'][$plugin][$libname])) {
                $tX = $timer->stop(4);
                include_once extras_dir . '/' . $this->plugins['active'][$plugin] . '/' . $this->plugins['libs'][$plugin][$libname];
                $timer->registerEvent('loadLibrary: ' . $plugin . ' [' . $libname . '] for ' . round($timer->stop(4) - $tX, 4) . " sec");
                return true;
            }
            return false;
        }
    }

    // Load info about plugin from 'version' file
    private function loadInfo($filename)
    {

        // config variables & function init
        $ver = array();
        $config_params = array('id', 'name', 'version', 'acts', 'file', 'config', 'install', 'deinstall', 'management', 'type', 'description', 'author', 'author_uri', 'permanent', 'library', 'actions');
        $required_params = array('id', 'name', 'version', 'type');
        $list_params = array('library', 'actions');

        foreach ($list_params as $id) {
            $ver[$id] = array();
        }

        // open file
        if (!($file = @fopen($filename, 'r'))) {
            return false;
        }

        // read file
        while (!feof($file)) {
            $line = fgets($file);
            if (preg_match("/^(.+?) *\: *(.+?) *$/i", $line, $r) == 1) {
                $key = rtrim(strtolower($r[1]));
                $value = rtrim($r[2]);
                if (in_array($key, $config_params)) {
                    if (in_array($key, $list_params)) {
                        $ver[$key][] = $value;
                    } else {
                        $ver[$key] = $value;
                    }
                }
            }
        }

        // Make some cleanup
        $ver['acts'] = isset($ver['acts']) ? str_replace(' ', '', $ver['acts']) : '';
        if (isset($ver['permanent']) and ($ver['permanent'] == 'yes')) {
            $ver['permanent'] = 1;
        } else {
            $ver['permanent'] = 0;
        }

        // check for filling required params
        foreach ($required_params as $v) {
            if (!$ver[$v]) {
                return false;
            }
        }

        // check for library/actions filling
        foreach (array('library', 'actions') as $key) {
            $list = $ver[$key];
            $ver[$key] = array();
            foreach ($list as $rec) {
                if (!$rec) {
                    continue;
                }
                list ($ids, $fname) = explode(';', $rec);
                $ids = trim($ids);
                $fname = trim($fname);
                if (!$ids or !$fname) {
                    return false;
                }
                $idlist = explode(',', $ids);
                foreach ($idlist as $entry) {
                    if (trim($entry)) {
                        $ver[$key][trim($entry)] = $fname;
                    }
                }
            }
        }
        return $ver;
    }

    // pluginsGetList
    // Get info of all plugins
    public function getInfo()
    {
        //debug_print_backtrace();

        if (empty($this->info)) {
            $timer = MicroTimer::instance();
            $timer->registerEvent('@ Plugin->getInfo() called');
            // open directory
            $handle = @opendir(extras_dir);
            $info = array();
            // load info of extras
            while (false != ($dir = @readdir($handle))) {
                $edir = extras_dir . '/' . $dir;
                // Skip special dirs ',' and '..'
                if (($dir == '.') or ($dir == '..') or (!is_dir($edir))) {
                    continue;
                }
                // Check 'version' file
                if (!is_file($edir . '/version')) {
                    continue;
                }
                // Load version file
                $ver = $this->loadInfo($edir . '/version');
                if (!is_array($ver)) {
                    continue;
                }
                // fill fully file path (within 'plugins' directory)
                $ver['dir'] = $dir;
                // Good, version file is successfully loaded, add data into array
                $info[$ver['id']] = $ver;
            }
            ksort($info);
            $this->info = $info;
        }

        return $this->info;
    }

    // getPluginStatusInstalled
    // Report if plugin is installed
    public function isInstalled($pluginID)
    {
        return isset($this->plugins['installed'][$pluginID]);
    }

    // Return plugin folder
    public function getFolder($plugin)
    {
        global $userROW;

        if (isset($this->plugins['active'][$plugin])) {
            $dir = extras_dir . '/' . $this->plugins['active'][$plugin];
        } else {
            if (empty($_REQUEST['stype']) and '1' == $userROW['status'])
                msg(array('type' => 'danger', 'title' => 'For admin', 'message' => 'Requested folder for not activated plugin <code>' . $plugin . '</code>'));
            $extras = $this->getInfo();
            if ($extras[$plugin]) {
                $dir = extras_dir . '/' . $extras[$plugin]['dir'];
            } else {
                return false;
            }
        }
        if (is_dir($dir)) {
            return $dir;
        }
        return false;
    }

    // Return plugin lang folder
    public function getFolderLang($plugin)
    {
        global $config;

        if ($langDir = $this->getFolder($plugin)) {
            $langDir .= '/lang/' . $config['default_lang'];
        }

        if (is_dir($langDir)) {
            return $langDir;
        }
        // Exit if no lang dir
        return false;
    }

    // Add item into list of additional HTML meta tags
    public function regHtmlVar($type, $data)
    {
        global $EXTRA_HTML_VARS;

        $EXTRA_HTML_VARS[] = array('type' => $type, 'data' => $data);
    }

    // Get plugin variable
    function getVar($pluginID, $var)
    {
        if (!$this->plugins['config:loaded'])
            return false;

        if (!isset($this->plugins['config'][$pluginID])) {
            return null;
        }
        if (!isset($this->plugins['config'][$pluginID][$var])) {
            return null;
        }
        return $this->plugins['config'][$pluginID][$var];
    }

    // Set variable
    function setVar($pluginID, $var, $value)
    {
        if (!$this->plugins['config:loaded'])
            return false;

        $this->plugins['config'][$pluginID][$var] = $value;
        return true;
    }
    
    /**
     * @return array
     */
    public function getPlugins()
    {
        return $this->plugins;
    }

    /**
     * @param array $plugins
     */
    public function setPlugins($plugins)
    {
        $this->plugins = $plugins;
    }
}
