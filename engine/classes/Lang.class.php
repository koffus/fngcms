<?php

class Lang
{

    protected static $data;
    protected static $language;

    public static $weekdays;
    public static $short_weekdays;
    public static $months;
    public static $short_months;

    final function __construct()
    {
        global $config, $twig;
        
        // Загружаем общий язык сайта
        $this::load('common');

        // Загружаем язык темы как сказано в core
        if (file_exists($dir_lang = tpl_dir . $config['theme'] . '/lang/' . self::$language . '.ini')) {
            self::$data['theme'] = parse_ini_file($dir_lang, true);
        }

        self::$weekdays = explode(',', self::$data['weekdays']);
        self::$short_weekdays = explode(',', self::$data['short_weekdays']);
        self::$months = explode(',', self::$data['months']);
        self::$short_months = explode(',', self::$data['short_months']);
        
        // - Global variables [by REFERENCE]
        $twig->addGlobalRef('lang', self::$data);
    }

    public static function load($what, $where = '', $area = '')
    {
        global $config;

        if (empty($config['default_lang'])) {
            self::$language = isset($_REQUEST['language']) ? $_REQUEST['language'] : 'english';
        } else {
            self::$language = $config['default_lang'];
        }

        $where = $where ? '/' . $where : '';

        if (!file_exists($toinc = root . 'lang/' . self::$language . $where . '/' . $what . '.ini')) {
            $toinc = root . 'lang/english' . $where . '/' . $what . '.ini';
        }
        if (!file_exists($toinc)) {
            $toinc = root . 'lang/russian' . $where . '/' . $what . '.ini';
        }
        if (file_exists($toinc)) {
            $content = parse_ini_file($toinc, true);
            self::set($content, $area);
        } else {
            // only the administrator can see alerts
            global $userROW;
            if ($userROW['status'] == 1)
                msg(array('type' => 'danger', 'message' => 'Unable to load file <code>' . $toinc . '</code>'));
        }
    }

    // Load LANG file for plugin
    public static function loadPlugin($plugin, $file, $prefix = '', $delimiter = '_')
    {
        // Get folder language of plugin
        $cPlugin = CPlugin::instance();
        if ($langDir = $cPlugin->getFolderLang($plugin)) {
            $langFile = $langDir . '/' . $file . '.ini';
        } else {
            return false;
        }
        if (is_file($langFile)) {
            $plugin_lang = parse_ini_file($langFile);
        } else {
            return false;
        }
        // merge values
        if (is_array($plugin_lang)) {
            if (empty($prefix)) {
                $prefix = $plugin;
            }
            // Delimiter = '#' - special delimiter, make a separate array
            if ($delimiter == '#') {
                self::set($plugin_lang, $prefix);
            } else if (empty($delimiter) and empty($prefix)) {
                self::set($plugin_lang);
            } else {
                foreach ($plugin_lang as $p => $v) {
                    self::set(array($prefix . $delimiter . $p => $v));
                }
            }
            return true;
        }
        return false;
    }

    public static function set($content, $area = false)
    {
        if (!is_array(self::$data))
            self::$data = array();
        if ($area) {
            self::$data[$area] = $content;
        } else {
            self::$data = array_merge(self::$data, $content);
        }
    }

    public static function get($key, $default_value = false)
    {
        if (!empty(self::$data[$key]))
            return self::$data[$key];
        // this need to global, admin.panel
        if (empty($key))
            return self::$data;
        if ($default_value)
            return $default_value;

        return '<code class="alert-danger">[LANG_LOST: ' . $key . ']</code>';
    }

    public static function retDate($format, $date)
    {
        foreach (self::$weekdays as $name => $value)
            $weekdays[$name] = preg_replace("/./", "\\\\\\0", $value);

        foreach (self::$short_weekdays as $name => $value)
            $short_weekdays[$name] = preg_replace("/./", "\\\\\\0", $value);

        foreach (self::$months as $name => $value)
            $months[$name] = preg_replace("/./", "\\\\\\0", $value);

        foreach (self::$short_months as $name => $value)
            $short_months[$name] = preg_replace("/./", "\\\\\\0", $value);

        $format = @preg_replace("/(?<!\\\\)l/", $weekdays[date("w", $date)], $format);
        $format = @preg_replace("/(?<!\\\\)D/", $short_weekdays[date("w", $date)], $format);
        $format = @preg_replace("/(?<!\\\\)F/", $months[date("n", $date) - 1], $format);
        $format = @preg_replace("/(?<!\\\\)M/", $short_months[date("n", $date) - 1], $format);

        return @date($format, $date);
    }
}
