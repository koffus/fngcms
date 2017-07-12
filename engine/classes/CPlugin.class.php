<?php

/**
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

        $this->loadListActive();
        $this->loadConfig();
    }

    // Load list of active plugins and required files
    // Protected reloading
    protected function loadListActive()
    {
        global $PLUGINS;
        
        if ($PLUGINS['active:loaded']) {
            return $PLUGINS['active'];
        }
        if (is_file(conf_pactive)) {
            include conf_pactive;
            if (is_array($array)) {
                $PLUGINS['active'] = $array;
                $PLUGINS['active:loaded'] = 1;
            } else {
                // Тут ошибку надо выдать
            }
        }
    }

    // getPluginsActiveList
    // Get list of active plugins and required files
    public function getListActive()
    {
        global $PLUGINS;

        return $PLUGINS['active'];
    }

    // Save list of active plugins and required files
    public function saveListActive()
    {
        global $PLUGINS;

        if (!is_file(conf_pactive)) {
            return false;
        }
        if (!($file = fopen(conf_pactive, "w"))) {
            return false;
        }
        $content = '<?php $array = '.var_export($PLUGINS['active'], true).'; ?>';
        fwrite($file, $content);
        fclose($file);
        return true;
    }

    // Load configuration variables for all plugins
    // Protected reloading
    protected function loadConfig()
    {
        global $PLUGINS;

        if ($PLUGINS['config:loaded']) {
            return true;
        }
        $fconfig = @fopen(conf_pconfig, 'r');
        if ($fconfig) {
            if (filesize(conf_pconfig)) {
                $content = fread($fconfig, filesize(conf_pconfig));
            } else {
                $content = serialize(array());
            }
            $PLUGINS['config'] = unserialize($content);
            $PLUGINS['config:loaded'] = 1;
            fclose($fconfig);
            return true;
        } else {
            // File doesn't exists, but Mark as `loaded`
            $PLUGINS['config'] = array();
            $PLUGINS['config:loaded'] = 1;
        }
        return false;
    }

    // $PLUGINS['config']
    // Get configuration variables
    // for all plugins, or one plugin, or one var
    public function getConfig($plugin = false, $var = false)
    {
        global $PLUGINS;

        if(!$this->loadConfig()) {
            return null;
        }

        $pConfig = $PLUGINS['config'];

        if($var and $plugin and isset($pConfig[$plugin]) and isset($pConfig[$plugin][$var])) {
            return $pConfig[$plugin][$var];
        } else if ($plugin and isset($pConfig[$plugin])) {
            return $pConfig[$plugin];
        } else {
            return $pConfig;
        }

        return null;
    }
    
    // Save configuration parameters of plugins (should be called after pluginSetVariable)
    public function saveConfig($suppressNotify = false)
    {
        global $PLUGINS;

        if (!$PLUGINS['config:loaded']) {
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
        global $PLUGINS;

        $timer = MicroTimer::instance();
        // Load list of active plugins
        $active = $this->getListActive();
        
        $loadCount = 0;

        // Don't load if plugin is not activated
        if (!$active['active'][$plugin])
            return false;

        // Scan all available actions and preload plugin's file if needed
        foreach ($active['actions'] as $aName => $pList) {
            if (isset($pList[$plugin]) and
                ((is_array($actionList) and in_array($aName, $actionList)) or
                    (!is_array($actionList) and (($actionList == '*') or ($actionList == $aName))))
            ) {
                // Yes, we should load this file. If it's not loaded earlier
                $pluginFileName = $pList[$plugin];

                if (!isset($PLUGINS['loaded:files'][$pluginFileName])) {
                    // Try to load file. First check if it exists
                    if (is_file(extras_dir . '/' . $pluginFileName)) {
                        $tX = $timer->stop(4);
                        include_once extras_dir . '/' . $pluginFileName;
                        $timer->registerEvent('func loadPlugin (' . $plugin . '): preloaded file "' . $pluginFileName . '" for ' . ($timer->stop(4) - $tX) . " sec");
                        $PLUGINS['loaded:files'][$pluginFileName] = 1;
                        $loadCount++;
                    } else {
                        $timer->registerEvent('func loadPlugin (' . $plugin . '): CAN\'t preload file that doesn\'t exists: "' . $pluginFileName . '"');
                    }
                }
            }
        }

        $PLUGINS['loaded'][$plugin] = 1;
        return $loadCount;
    }

    // Load plugin's library
    public function loadLibrary($plugin, $libname = '')
    {
        $timer = MicroTimer::instance();
        // Load list of active plugins
        $active = $this->getListActive();

        // Check if we know about this plugin
        if (!isset($active['active'][$plugin])) return false;

        // Check if we need to load all libs
        if (!$libname) {
            foreach ($active['libs'][$plugin] as $id => $file) {
                $tX = $timer->stop(4);
                include_once extras_dir . '/' . $active['active'][$plugin] . '/' . $file;
                $timer->registerEvent('loadLibrary: ' . $plugin . '.' . $id . ' [' . $file . '] for ' . round($timer->stop(4) - $tX, 4) . " sec");
            }
            return true;
        } else {
            if (isset($active['libs'][$plugin][$libname])) {
                $tX = $timer->stop(4);
                include_once extras_dir . '/' . $active['active'][$plugin] . '/' . $active['libs'][$plugin][$libname];
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
        global $PLUGINS;
        
        $active = $PLUGINS['active'];

        if (isset($active['installed'][$pluginID]) and $active['installed'][$pluginID]) {
            return true;
        }
        return false;
    }

    // Return plugin folder
    public function getFolder($plugin)
    {
        global $EXTRA_CONFIG, $userROW;

        $listActive = $this->getListActive();

        if (isset($listActive['active'][$plugin])) {
            $dir = extras_dir . '/' . $listActive['active'][$plugin];
        } else {
            if ($userROW['status'] == 1 and empty($_REQUEST['stype']))
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
        global $PLUGINS;

        if (!$PLUGINS['config:loaded'])
            return false;

        if (!isset($PLUGINS['config'][$pluginID])) {
            return null;
        }
        if (!isset($PLUGINS['config'][$pluginID][$var])) {
            return null;
        }
        return $PLUGINS['config'][$pluginID][$var];
    }

    // Set variable
    function setVar($pluginID, $var, $value)
    {
        global $PLUGINS;

        if (!$PLUGINS['config:loaded'])
            return false;

        $PLUGINS['config'][$pluginID][$var] = $value;
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
