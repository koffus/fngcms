<?php

class Lang
{

    protected static $data;

    public static $weekdays;
    public static $short_weekdays;
    public static $months;
    public static $short_months;

    final function __construct()
    {
        global $config, $twig;

        // Загружаем общий язык сайта
        $this::load('common');

        // Загружаем язык темы
        // как сказано в core
        if (file_exists($dir_lang = tpl_dir . $config['theme'] . '/lang/' . $config['default_lang'] . '.ini'))
            self::$data['theme'] = parse_ini_file($dir_lang, true);

        self::$weekdays = explode(',', __('weekdays'));
        self::$short_weekdays = explode(',', __('short_weekdays'));
        self::$months = explode(',', __('months'));
        self::$short_months = explode(',', __('short_months'));

        // - Global variables [by REFERENCE]
        $twig->addGlobalRef('lang', self::$data);
    }

    public static function load($what, $where = '', $area = '')
    {
        global $config;

        $where = $where ? '/' . $where : '';

        if (empty ($config['default_lang']))
            $config['default_lang'] = isset($_REQUEST['language']) ? $_REQUEST['language'] : 'english';

        if (!file_exists($toinc = root . 'lang/' . $config['default_lang'] . $where . '/' . $what . '.ini')) {
            $toinc = root . 'lang/english' . $where . '/' . $what . '.ini';
        }

        if (!file_exists($toinc)) {
            $toinc = root . 'lang/russian' . $where . '/' . $what . '.ini';
        }

        if (file_exists($toinc)) {
            $content = parse_ini_file($toinc, true);

            self::set($content, $area);

            //return;

        } else {
            // only the administrator can see alerts
            global $userROW;
            if ($userROW['status'] == 1)
                msg(array('type' => 'danger', 'message' => 'Unable to load file <code>' . $toinc . '</code>'));
        }

    }

    // Load LANG file for plugin
    public static function loadPlugin($plugin, $file, $group = '', $prefix = '', $delimiter = '_')
    {
        global $config;

        if (!$prefix) {
            $prefix = $plugin;
        }
        // Load list of active plugins
        $cPlugin = CPlugin::instance();
        $active = $cPlugin->getListActive();

        if (!isset($active['active'][$plugin])) {
            // No, plugin is not active. Let's load plugin list
            // Load plugin list  
            $extras = $cPlugin->getList();

            // Exit if no data about this plugin is found
            if (!$extras[$plugin]) {
                return 0;
            }
            $lang_dir = extras_dir . '/' . $extras[$plugin]['dir'] . '/lang';
        } else {
            $lang_dir = extras_dir . '/' . $active['active'][$plugin] . '/lang';
        }

        // Exit if no lang dir
        if (!is_dir($lang_dir)) {
            return 0;
        }

        // find if we have 'lang' dir in plugin directory
        // Try to load langs in order: default / english / russian
        $lfn = ($group ? $group . '/' : '') . $file . '.ini';

        // Default language
        if (is_dir($lang_dir . '/' . $config['default_lang']) and is_file($lang_dir . '/' . $config['default_lang'] . '/' . $lfn)) {
            $lang_dir = $lang_dir . '/' . $config['default_lang'];
        } else if (is_dir($lang_dir . '/english') and is_file($lang_dir . '/english/' . $lfn)) {
            $lang_dir = $lang_dir . '/english';
        } else if (is_dir($lang_dir . '/russian') and is_file($lang_dir . '/russian/' . $lfn)) {
            $lang_dir = $lang_dir . '/russian';
        } else {
            return 0;
        }

        $plugin_lang = parse_ini_file($lang_dir . '/' . $lfn);

        // merge values
        if (is_array($plugin_lang)) {
            // Delimiter = '#' - special delimiter, make a separate array
            if ($delimiter == '#') {
                self::set($plugin_lang, $prefix);
            } else if (($delimiter == '') and ($prefix == '')) {
                self::set($plugin_lang);
            } else {
                foreach ($plugin_lang as $p => $v) {
                    self::set(array($prefix . $delimiter . $p => $v));
                }
            }
        }
        return 1;

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

        if (isset(self::$data[$key]) and !empty(self::$data[$key]))
            return self::$data[$key];

        // this need to global, admin.panel
        if (empty($key))
            return self::$data;

        if ($default_value)
            return $default_value;

        return '<code class="alert-danger">[LANG_LOST: ' . $key . ']</code>';

    }

    public static function retDate($format, $timestamp)
    {

        $weekdays = self::$weekdays;
        $short_weekdays = self::$short_weekdays;
        $months = self::$months;
        $short_months = self::$short_months;

        foreach ($weekdays as $name => $value)
            $weekdays[$name] = preg_replace("/./", "\\\\\\0", $value);

        foreach ($short_weekdays as $name => $value)
            $short_weekdays[$name] = preg_replace("/./", "\\\\\\0", $value);

        foreach ($months as $name => $value)
            $months[$name] = preg_replace("/./", "\\\\\\0", $value);

        foreach ($short_months as $name => $value)
            $short_months[$name] = preg_replace("/./", "\\\\\\0", $value);

        $format = @preg_replace("/(?<!\\\\)D/", $short_weekdays[date("w", $timestamp)], $format);
        $format = @preg_replace("/(?<!\\\\)F/", $months[date("n", $timestamp) - 1], $format);
        $format = @preg_replace("/(?<!\\\\)l/", $weekdays[date("w", $timestamp)], $format);
        $format = @preg_replace("/(?<!\\\\)M/", $short_months[date("n", $timestamp) - 1], $format);

        return @date($format, $timestamp);
    }
}
