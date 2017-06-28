<?php

/**
 * Class CPlugin
 * Description: CORE Plugin - manager configuration and functions
 */
class CPlugin
{
    use Singleton;
    
    protected $plugins;
    
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
        
        $this->loadList();
        $this->loadConfig();
    }

    // Load list of active plugins and required files
    protected function loadList()
    {
        global $PLUGINS;
        
        if ($PLUGINS['active:loaded']) {
            return $PLUGINS['active'];
        }
        if (is_file(conf_pactive)) {
            include conf_pactive;
            if (is_array($array)) {
                $PLUGINS['active'] = $array;
            }
        }
        $PLUGINS['active:loaded'] = 1;
    }

    // Load configuration variables for plugins
    public function loadConfig()
    {
        global $PLUGINS;

        if ($PLUGINS['config:loaded']) {
            return 1;
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

    // Load plugin [ Same behaviour as for loadActionHandlers ]
    public function load($pluginName, $actionList = '*')
    {
        global $PLUGINS;

        $timer = MicroTimer::instance();
        // Load list of active plugins
        $active = $this->getListActive();
        
        $loadCount = 0;

        // Don't load if plugin is not activated
        if (!$active['active'][$pluginName])
            return false;

        // Scan all available actions and preload plugin's file if needed
        foreach ($active['actions'] as $aName => $pList) {
            if (isset($pList[$pluginName]) and
                ((is_array($actionList) and in_array($aName, $actionList)) or
                    (!is_array($actionList) and (($actionList == '*') or ($actionList == $aName))))
            ) {
                // Yes, we should load this file. If it's not loaded earlier
                $pluginFileName = $pList[$pluginName];

                if (!isset($PLUGINS['loaded:files'][$pluginFileName])) {
                    // Try to load file. First check if it exists
                    if (is_file(extras_dir . '/' . $pluginFileName)) {
                        $tX = $timer->stop(4);
                        include_once extras_dir . '/' . $pluginFileName;
                        $timer->registerEvent('func loadPlugin (' . $pluginName . '): preloaded file "' . $pluginFileName . '" for ' . ($timer->stop(4) - $tX) . " sec");
                        $PLUGINS['loaded:files'][$pluginFileName] = 1;
                        $loadCount++;
                    } else {
                        $timer->registerEvent('func loadPlugin (' . $pluginName . '): CAN\'t preload file that doesn\'t exists: "' . $pluginFileName . '"');
                    }
                }
            }
        }

        $PLUGINS['loaded'][$pluginName] = 1;
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

    // pluginsGetList
    // Get list of installed plugins
    // Сделать загрузку один раз, передавать в массив $extras[$ver['id']], как и в $PLUGINS
    public function getList()
    {
        $timer = MicroTimer::instance();

        $timer->registerEvent('@ Plugin->getList() called');
        // open directory
        $handle = @opendir(extras_dir);
        $extras = array();
        // load list of extras
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
            $ver = $this->loadVersionFile($edir . '/version');
            if (!is_array($ver)) {
                continue;
            }

            // fill fully file path (within 'plugins' directory)
            $ver['dir'] = $dir;

            // Good, version file is successfully loaded, add data into array
            $extras[$ver['id']] = $ver;
            //array_push($extras, $ver);
        }
        ksort($extras);

        return $extras;
    }

    // Load 'version' file from plugin directory
    private function loadVersionFile($filename)
    {

        // config variables & function init
        $config_params = array('id', 'name', 'version', 'acts', 'file', 'config', 'install', 'deinstall', 'management', 'type', 'description', 'author', 'author_uri', 'permanent', 'library', 'actions');
        $required_params = array('id', 'name', 'version', 'type');
        $list_params = array('library', 'actions');
        $ver = array();

        foreach ($list_params as $id)
            $ver[$id] = array();

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
                if (!$rec) continue;
                list ($ids, $fname) = explode(';', $rec);

                $ids = trim($ids);
                $fname = trim($fname);

                if (!$ids or !$fname) return false;
                $idlist = explode(',', $ids);
                foreach ($idlist as $entry)
                    if (trim($entry))
                        $ver[$key][trim($entry)] = $fname;
            }
        }
        return $ver;
    }

    // getPluginsActiveList
    // Get list of active plugins and required files
    public function getListActive()
    {
        global $PLUGINS;

        return $PLUGINS['active'];
    }
    
    // getPluginStatusInstalled
    // Report if plugin is installed
    public function getStatusInstalled($pluginID)
    {
        global $PLUGINS;
        
        $active = $PLUGINS['active'];

        if (isset($active['installed'][$pluginID]) and $active['installed'][$pluginID]) {
            return true;
        }
        return false;
    }

    //
    // Add item into list of additional HTML meta tags
    public function regHtmlVar($type, $data)
    {
        global $EXTRA_HTML_VARS;

        $EXTRA_HTML_VARS[] = array('type' => $type, 'data' => $data);
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
