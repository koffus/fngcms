<?php

//
// Copyright (C) 2006-2017 Next Generation CMS (http://ngcms.ru/)
// Name: extraconf.inc.php
// Description: Plugin configuration manager AND Functions required for plugin managment scripts
// Author: Vitaly Ponomarev
//

// Protect against hack attempts
if (!defined('NGCMS')) die ('HAL');

//
// Switch plugin ON/OFF
function pluginSwitch($pluginID, $mode = 'on')
{

    // Load list of active plugins
    $cPlugin = CPlugin::instance();
    $plugins = $cPlugin->getList();

    // Decide what to do
    switch ($mode) {
        // TURN _ON_
        case 'on':
            // Load plugin list
            $extras = $cPlugin->getInfo();
            if (!is_array($extras)) {
                return false;
            }
            if (!$extras[$pluginID]) {
                return false;
            }

            // Mark module as active
            $plugins['active'][$pluginID] = $extras[$pluginID]['dir'];

            // Mark module to be activated in all listed actions
            if (isset($extras[$pluginID]['acts']) and isset($extras[$pluginID]['file'])) {
                foreach (explode(',', $extras[$pluginID]['acts']) as $act) {
                    $plugins['actions'][$act][$pluginID] = $extras[$pluginID]['dir'] . '/' . $extras[$pluginID]['file'];
                }
            }

            foreach ($extras[$pluginID]['actions'] as $act => $file) {
                $plugins['actions'][$act][$pluginID] = $extras[$pluginID]['dir'] . '/' . $file;
            }

            if (count($extras[$pluginID]['library']))
                $plugins['libs'][$pluginID] = $extras[$pluginID]['library'];

            // update active extra list in memory: SET and SAVEACTIVE
            return ($cPlugin->setList($plugins) and $cPlugin->saveListActive());

        // TURN _OFF_
        case 'off':
            unset($plugins['active'][$pluginID]);
            unset($plugins['libs'][$pluginID]);

            foreach ($plugins['actions'] as $key => $value) {
                if (isset($plugins['actions'][$key][$pluginID])) {
                    unset($plugins['actions'][$key][$pluginID]);
                }
            }
            
            if (isset($plugins['config'][$pluginID])) {
                unset($plugins['config'][$pluginID]);
                // Save configuration parameters of plugins
                $cPlugin->setConfig($plugins['config']);
            }

            // update active extra list in memory: SET and SAVEACTIVE and SAVECONFIG
            return ($cPlugin->setList($plugins) and $cPlugin->saveListActive() and $cPlugin->saveConfig());
    }
    return false;
}

//
// Mark plugin as installed
function plugin_mark_installed($plugin)
{
    // Load list of active plugins
    $cPlugin = CPlugin::instance();
    $plugins = $cPlugin->getList();

    // return if already installed
    if (isset($plugins['installed'][$plugin])) {
        return 1;
    }

    $plugins['installed'][$plugin] = 1;
    // update active extra list in memory: SET and SAVEACTIVE
    return ($cPlugin->setList($plugins) and $cPlugin->saveListActive());
}

//
// Mark plugin as deinstalled
function plugin_mark_deinstalled($plugin)
{

    // Load list of active plugins
    $cPlugin = CPlugin::instance();
    $plugins = $cPlugin->getList();

    // return if already installed
    if (!$plugins['installed'][$plugin]) {
        return 1;
    }

    unset($plugins['installed'][$plugin]);
    unset($plugins['active'][$plugin]);
    foreach ($plugins['actions'] as $k => $v) {
        unset($plugins['actions'][$k][$plugin]);
    }

    // update active extra list in memory: SET and SAVEACTIVE
    return ($cPlugin->setList($plugins) and $cPlugin->saveListActive());
}

/**
 *
automatic config screen generator

params:
 array of arrays with variables:
    name = parameter name
    title = parameter title (showed in html)
    descr = description (small symbols show)
    type = input / select / text
    value = default filled value
    values = array of possible values (for select)
    html_flags = additional html flags for parameter (id, data-, etc ...)
    validate = array with validation parameters, several lines may be applied
        : type = int
            : min, max = define minimum and maximum values
        : type = regex
            : match = define regex that shoud be matched

        : type = integer
        :

*/

function generate_config_page($module, $params)
{
    global $twig, $PHP_SELF;

    function mkParamLine($param)
    {

        if ('flat' == $param['type']) {
            $tvars['type'] = 'flat';
            $tvars['input'] = $param['input'];

            return $tvars;
        }

        $tvars = array(
            'name' => isset($param['name']) ? $param['name'] : '',
            'title' => isset($param['title']) ? $param['title'] : '',
            'descr' => isset($param['descr']) ? $param['descr'] : '',
            'error' => isset($param['error']) ? (str_replace('%error%', $param['error'], __('param_error'))) : '',
            'input' => '',
            'flags' => array(
                'descr' => isset($param['descr']) ? true : false,
                'error' => isset($param['error']) ? true : false
            )
        );

        if (!empty($_POST[$param['name']])) {
            $param['value'] = $_POST[$param['name']];
        }

        $html_flags = isset($param['html_flags'] ) ? $param['html_flags'] : '';

        if ('input' == $param['type']) {
            $tvars['input'] = '<input type="text" name="' . $param['name'] . '" ' . $html_flags . ' value="' . secure_html($param['value']) . '" class="form-control" />';
            $tvars['type'] = 'input';
        } elseif ('button' == $param['type']) {
            $tvars['input'] = '<input type="button" name="' . $param['name'] . '" ' . $html_flags . ' value="' . secure_html($param['value']) . '" class="btn btn-default" />';
            $tvars['type'] = 'button';
        } elseif ('checkbox' == $param['type']) {
            $tvars['input'] = '<input type="checkbox" name="' . $param['name'] . '" ' . $html_flags . ' value="1"' . ($param['value'] ? ' checked' : '') . ' />';
            $tvars['type'] = 'checkbox';
        } elseif ('hidden' == $param['type']) {
            $tvars['input'] = '<input type="hidden" name="' . $param['name'] . '" value="' . secure_html($param['value']) . '" class="form-control" />';
            $tvars['type'] = 'hidden';
        } elseif ('text' == $param['type']) {
            $tvars['input'] = '<textarea name="' . $param['name'] . '" ' . $html_flags . ' class="form-control">' . secure_html($param['value']) . '</textarea>';
            $tvars['type'] = 'text';
        } elseif ('select' == $param['type']) {
            $tvars['input'] = MakeDropDown($param['values'], $param['name'], $param['value']);
            $tvars['type'] = 'select';
        } elseif ('manual' == $param['type']) {
            $tvars['input'] = $param['input'];
            $tvars['type'] = 'manual';
        }

        return $tvars;
    }

    // Make action (POST form), description, dependence, navigation panel and submit footer to page plugin config
    $settings = ['action', 'description', 'dependence', 'navigation', 'submit'];
    foreach ($settings as $set) {
        $$set = isset($params[$set]) ? $params[$set] : false;
        unset($params[$set]);
    }

    $entries = array();
    // For each param do
    foreach ($params as $param) {
        if (isset($param['mode']) and 'group' == $param['mode']) {
            // Lets' group parameters into one block
            $subentries = array();
            foreach ($param['entries'] as $entr) {
                $subentries[] = mkParamLine($entr);
            }
            $entries[] = array(
                'groupTitle' => $param['title'],
                'subentries' => $subentries,
                'flags' => array(
                    'group' => isset($param['title']) ? true : false,
                    'toggle' => (isset($param['toggle']) and ($param['toggle'] == 'hide')) ? true : false,
                ));
        } else {
            $entries[] = mkParamLine($param);
        }
    }

    $tVars = array(
        'plugin' => $module,
        'description' => $description,
        'action' => $action,
        'dependence' => $dependence,
        'navigation' => $navigation,
        'entries' => $entries,
        'submit' => $submit,
        'php_self' => $PHP_SELF,
        'token' => genUToken('admin.extra-config'),
    );
    $xt = $twig->loadTemplate(tpl_actions . 'extra-config/table.tpl');
    echo $xt->render($tVars);
}

// Automatic save values into module parameters DB
function commit_plugin_config_changes($module, $params)
{
    // Load CORE Plugin
    $cPlugin = CPlugin::instance();

    $cfgUpdate = array();

    // For each param do save data
    foreach ($params as $param) {
        // Validate parameter if needed
        if (isset($param['mode']) and 'group' == $param['mode']) {
            if (is_array($param['entries'])) {
                foreach ($param['entries'] as $gparam) {
                    if (isset($gparam['name']) and isset($_POST[$gparam['name']]) and empty($gparam['nosave'])) {
                        pluginSetVariable($module, $gparam['name'], $_POST[$gparam['name']]);
                        $cfgUpdate[$gparam['name']] = $_POST[$gparam['name']];
                    }
                }
            }
        } elseif (isset($param['name']) and empty($param['nosave'])) {
            pluginSetVariable($module, $param['name'], $_POST[$param['name']]);
            $cfgUpdate[$param['name']] = $_POST[$param['name']];
        }
    }

    // Save configuration parameters of plugins
    if($cPlugin->saveConfig()) {
        msg(array('message' => __('commited')));
    } else {
        msg(array('type' => 'danger', 'message' => __('commited_fail')));
    }

    // Generate log
    ngSYSLOG(array('plugin' => '#admin', 'item' => 'config#' . $module), array('action' => 'update', 'list' => $cfgUpdate), null, array(1));
}

// Load params sent by POST request in plugin configuration
function load_commit_params($cfg, $outparams)
{

    foreach ($cfg as $param) {
        if ($param['name']) {
            $outparams[$param['name']] = $_POST[$param['name']];
        }
    }
    return $outparams;
}

// Database update during install
function fixdb_plugin_install($module, $params, $mode = 'install', $silent = false)
{
    global $twig, $mysql, $PHP_SELF, $config;

    $publish = array();
    if ($mode == 'install') {
        array_push($publish, array('title' => '<b>' . __('idbc_process') . '</b>', 'descr' => '', 'result' => ''));
    } else {
        array_push($publish, array('title' => '<b>' . __('ddbc_process') . '</b>', 'descr' => '', 'result' => ''));
    }

    // For each params do update DB
    foreach ($params as $table) {
        $error = 0;
        $publish_title = '';
        $publish_descr = '';
        $publish_result = '';
        $publish_error = 0;

        $create_mode = 0;

        if (!$table['table']) {
            $publish_result = 'No table name specified';
            $publish_error = 1;
            break;
        }

        $chgTableName = (($table['table'] == 'users') ? uprefix : prefix) . "_" . $table['table'];

        if (($table['action'] != 'create') &&
            ($table['action'] != 'cmodify') &&
            ($table['action'] != 'modify') &&
            ($table['action'] != 'drop')
        ) {
            $publish_title = 'Table operations';
            $publish_result = 'Unknown action type specified [' . $table['action'] . ']';
            $publish_error = 1;
            break;
        }

        if ($table['action'] == 'drop') {
            $publish_title = __('idbc_tdrop');
            $publish_title = str_replace('%table%', $table['table'], $publish_title);

            if (!$mysql->table_exists($chgTableName)) {
                $publish_result = __('idbc_tnoexists');
                $publish_error = 1;
                break;
            }

            $query = "drop table " . $chgTableName;
            $mysql->query($query);

            array_push($publish, array('title' => $publish_title, 'descr' => "SQL: [$query]", 'result' => ($publish_result ? $publish_result : ($error ? __('idbc_fail') : __('idbc_ok')))));
            continue;
        }

        if (!is_array($table['fields'])) {
            $publish_result = 'Field list should be specified';
            $publish_error = 1;
            break;
        }

        if ($table['action'] == 'modify') {
            $publish_title = __('idbc_tmodify');
            $publish_title = str_replace('%table%', $table['table'], $publish_title);

            if (!$mysql->table_exists($chgTableName)) {
                $publish_result = __('idbc_tnoexists');
                $publish_error = 1;
                break;
            }
        }

        if ($table['action'] == 'create') {
            $publish_title = __('idbc_tcreate');
            $publish_title = str_replace('%table%', $table['table'], $publish_title);

            if ($mysql->table_exists($chgTableName)) {
                $publish_result = __('idbc_t_alreadyexists');
                $publish_error = 1;
                break;
            }
            $create_mode = 1;
        }

        if ($table['action'] == 'cmodify') {
            $publish_title = __('idbc_tcmodify');
            $publish_title = str_replace('%table%', $table['table'], $publish_title);
            if (!$mysql->table_exists($chgTableName)) {
                $create_mode = 1;
            }
        }

        // Now we can perform field creation
        if ($create_mode) {
            $fieldlist = array();
            foreach ($table['fields'] as $field) {
                if (!$field['name']) {
                    $publish_result = 'Field name should be specified';
                    $publish_error = 1;
                    break;
                }
                if (($field['action'] == 'create') or ($field['action'] == 'cmodify') or ($field['action'] == 'cleave')) {
                    if (!$field['type']) {
                        $publish_result = 'Field type should be specified';
                        $publish_error = 1;
                        break;
                    }
                    array_push($fieldlist, $field['name'] . " " . $field['type'] . (!empty($field['params']) ? ' ' . $field['params'] : ''));
                } else if ($field['action'] != 'drop') {
                    $publish_result = 'Unknown action';
                    $publish_error = 1;
                    break;
                }
            }

            // Check if different character set are supported [ version >= 4.1.1 ]
            $charset = is_array($mysql->record("show variables like 'character_set_client'")) ? (' DEFAULT CHARSET=' . (isset($table['charset']) ? $table['charset'] : 'utf8')) : '';

            $query = "create table " . $chgTableName . " (" . implode(', ', $fieldlist) . ($table['key'] ? ', ' . $table['key'] : '') . ")" . $charset . (isset($table['engine']) ? ' ENGINE=' . $table['engine'] : (isset($config['dbengine']) ? ' ENGINE=' . $config['dbengine'] : ' ENGINE=MyISAM'));
            $mysql->query($query);
            array_push($publish, array('title' => $publish_title, 'descr' => "SQL: [$query]", 'result' => ($publish_result ? $publish_result : ($error ? __('idbc_fail') : __('idbc_ok')))));
        } else {
            foreach ($table['fields'] as $field) {
                if (!$field['name']) {
                    $publish_result = 'Field name should be specified';
                    $publish_error = 1;
                    break;
                }
                if (($field['action'] == 'create') or ($field['action'] == 'cmodify') or ($field['action'] == 'cleave')) {
                    if (!$field['type']) {
                        $publish_result = 'Field type should be specified';
                        $publish_error = 1;
                        break;
                    }
                } else if ($field['action'] != 'drop') {
                    $publish_result = 'Unknown action';
                    $publish_error = 1;
                    break;
                }

                $ft = get_mysql_field_type($chgTableName, $field['name']);

                if ($field['action'] == 'drop') {
                    $publish_title = __('idbc_drfield');
                    $publish_title = str_replace('%field%', $field['name'], $publish_title);
                    $publish_title = str_replace('%table%', $table['table'], $publish_title);
                    if (!$ft) {
                        $publish_result = __('idbc_fnoexists');
                        $publish_error = 1;
                        break;
                    }
                    $query = "alter table " . $chgTableName . " drop column `" . $field['name'] . "`";
                    $mysql->query($query);
                    array_push($publish, array('title' => $publish_title, 'descr' => "SQL: [$query]", 'result' => ($publish_result ? $publish_result : ($error ? __('idbc_fail') : __('idbc_ok')))));
                }
                if ($field['action'] == 'create') {
                    $publish_title = __('idbc_amfield');
                    $publish_title = str_replace('%field%', $field['name'], $publish_title);
                    $publish_title = str_replace('%type%', $field['type'], $publish_title);
                    $publish_title = str_replace('%table%', $table['table'], $publish_title);
                    if ($ft) {
                        $publish_result = __('idbc_f_alreadyexists');
                        $publish_error = 1;
                        break;
                    }
                    $query = "alter table " . $chgTableName . " add column `" . $field['name'] . "` " . $field['type'] . " " . $field['params'];
                    $mysql->query($query);
                    array_push($publish, array('title' => $publish_title, 'descr' => "SQL: [$query]", 'result' => ($publish_result ? $publish_result : ($error ? __('idbc_fail') : __('idbc_ok')))));
                    continue;
                }
                if ($field['action'] == 'cmodify') {
                    if (!$ft) {
                        $query = "alter table " . $chgTableName . " add column `" . $field['name'] . "` " . $field['type'] . (!empty($field['params']) ? ' ' . $field['params'] : '');
                    } else {
                        $query = "alter table " . $chgTableName . " change column `" . $field['name'] . "` `" . $field['name'] . "` " . $field['type'] . (!empty($field['params']) ? ' ' . $field['params'] : '');
                    }
                    $mysql->query($query);
                    array_push($publish, array('title' => $publish_title, 'descr' => "SQL: [$query]", 'result' => ($publish_result ? $publish_result : ($error ? __('idbc_fail') : __('idbc_ok')))));
                    continue;

                }

            }
            if ($publish_error) {
                break;
            }
            $publish_title = '';

        }

    }

    // Scan for messages
    if ($publish_title and $publish_error) {
        array_push($publish, array(
            'title' => $publish_title,
            'descr' => $publish_descr,
            'error' => $publish_error,
            'result' => ($publish_result ? $publish_result : ($publish_error ? __('idbc_fail') : __('idbc_ok'))),
        ));
    }

    // Write an info
    foreach ($publish as $v) {
        $entry = $v;
        if (isset($entry['error'])) {
            $entry['result'] = '<font color="red">' . $entry['result'] . '</font>';
        }
        $entries[] = $entry;
    }

    $tVars = array(
        'entries' => $entries,
        'plugin' => $module,
        'php_self' => $PHP_SELF,
        'token' => genUToken('admin.extras'),
        'mode_text' => ($mode == 'install') ? __('install_text') : __('deinstall_text'),
        'msg' => ($mode == 'install' ? ($publish_error ? __('ibdc_ifail') : __('idbc_iok')) : ($publish_error ? __('dbdc_ifail') : __('ddbc_iok'))),
        'flags' => array(
            'enable' => ($publish_error) ? false : ($mode == 'install' ? true : false),
        ),
    );

    $xt = $twig->loadTemplate(tpl_actions . 'extra-config/install-process.tpl');

    if (!$silent) {
        print $xt->render($tVars);
    }

    if ($publish_error) {
        return 0;
    }
    return 1;
}

// Create install page
function generate_install_page($plugin, $text, $stype = 'install')
{
    global $twig, $PHP_SELF;

    $tVars = array(
        'plugin' => $plugin,
        'stype' => $stype,
        'install_text' => $text,
        'mode_text' => ($stype == 'install') ? __('install_text') : __('deinstall_text'),
        'mode_commit' => ($stype == 'install') ? __('commit_install') : __('commit_deinstall'),
        'php_self' => $PHP_SELF,
    );

    $xt = $twig->loadTemplate(tpl_actions . 'extra-config/install.tpl');
    echo $xt->render($tVars);

}

// check field params to install plugins
function get_mysql_field_type($table, $field)
{
    global $mysql;

    $result = $mysql->query("SELECT * FROM $table limit 0");
    $fields = $mysql->num_fields($result);
    for ($i = 0; $i < $fields; $i++) {
        if ($mysql->field_name($result, $i) == $field) {
            $ft = $mysql->field_type($result, $i);
            $fl = $mysql->field_len($result, $i);
            if ($ft == 'string') {
                $ft = 'char';
            }
            if ($ft == 'blob') {
                $ft = 'text';
                $fl = '';
            }
            $res = $ft . ($fl ? ' (' . $fl . ')' : '');
            return $res;
        }
    }
    return '';
}

// clear Cache Files
function clearCacheFiles($plugin = false)
{
    $error = false;
    $listSkip = '';

    if ($plugin) {
        $cacheDir = get_plugcache_dir($plugin);
    } else {
        $cacheDir = root . 'cache/';
    }

    $dirIterator = new RecursiveDirectoryIterator($cacheDir, RecursiveDirectoryIterator::SKIP_DOTS);
    $iterator = new RecursiveIteratorIterator($dirIterator, RecursiveIteratorIterator::LEAVES_ONLY);

    foreach ($iterator as $object) {
        if ($object->isFile() or $object->isDir()) {
            if (!@unlink($object->getPathname())) {
                $listSkip .= '<br>' . $object->getBasename();
                $error = true;
            }
        }
    }
    if ($error) {
        msg(array('message' => __('msg.cashe_not_clear') . $listSkip, 'type' => 'warning'));
    } else {
        if ($plugin) {
            msg(array('message' => __('msg.cashe_clear_plugin')));
        } else {
            msg(array('message' => __('msg.cashe_clear')));
        }
    }
    
    // Clear cache OPCache
    if(function_exists('opcache_get_status')) {
        opcache_reset();
    }

    // Create a protective .htaccess
    create_access_htaccess();
}
