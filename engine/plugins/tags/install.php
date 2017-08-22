<?php

// Protect against hack attempts
if (!defined('NGCMS')) die ('HAL');

//
// Configuration file for plugin
//

//
// Install script for plugin.
// $action: possible action modes
// 	confirm		- screen for installation confirmation
//	apply		- apply installation, with handy confirmation
//	autoapply - apply installation in automatic mode [INSTALL script]
//
function plugin_tags_install($action) {
    
    global $mysql, $config;

    if ($action != 'autoapply') {
        Lang::loadPlugin('tags', 'config', '', ':');
    }

    // Fill DB_UPDATE configuration scheme
    $db_update = array(
        array(
            'table' => 'news',
            'action' => 'modify',
            'fields' => array(
                array('action' => 'cmodify', 'name' => 'tags', 'type' => 'varchar(255)', 'params' => ''),
            )
        ),
        array(
            'table' => 'tags',
            'action' => 'cmodify',
            'key' => 'primary key(`id`), unique key `tag` (`tag`)',
            'fields' => array(
                array('action' => 'cmodify', 'name' => 'id', 'type' => 'int', 'params' => 'not null auto_increment'),
                array('action' => 'cmodify', 'name' => 'tag', 'type' => 'varchar(60)', 'params' => ''),
                array('action' => 'cmodify', 'name' => 'posts', 'type' => 'int(11)', 'params' => 'default 1'),
                array('action' => 'cmodify', 'name' => 'views', 'type' => 'int(11)', 'params' => 'default 0'),
            )
        ),
        array(
            'table' => 'tags_index',
            'action' => 'cmodify',
            'key' => 'primary key(`id`), key `tagID` (`tagID`), key `newsID` (`newsID`) ',
            'fields' => array(
                array('action' => 'cmodify', 'name' => 'id', 'type' => 'int', 'params' => 'not null auto_increment'),
                array('action' => 'cmodify', 'name' => 'newsID', 'type' => 'int'),
                array('action' => 'cmodify', 'name' => 'tagID', 'type' => 'varchar(60)', 'params' => ''),
            )
        ),
    );

    $ULIB = new UrlLibrary();
    $ULIB->loadConfig();
    $ULIB->registerCommand('tags', '',
        array ('vars' => array(
                '' => array(
                    'matchRegex' => '.+?', 
                    'descr' => array(
                        $config['default_lang'] => __('tags:ULIB_main')
                    )
                ),
                'page' => array(
                    'matchRegex' => '\d{1,4}', 
                    'descr' => array(
                        $config['default_lang'] => __('tags:ULIB_page')
                    )
                ),
            ),
            'descr' => array ($config['default_lang'] => __('tags:ULIB_main_d')),
        )
    );
    $ULIB->registerCommand('tags', 'tag',
        array ('vars' => array(
                'tag' => array(
                    'matchRegex' => '.+?', 
                    'descr' => array(
                        $config['default_lang'] => __('tags:ULIB_tag_name')
                        )
                    ),
                'page' => array(
                    'matchRegex' => '\d{1,4}', 
                    'descr' => array(
                        $config['default_lang'] => __('tags:ULIB_page')
                    )
                ),
            ),
            'descr' => array ($config['default_lang'] => __('tags:ULIB_tag_d')),
        )
    );


    $UHANDLER = new UrlHandler();
    $UHANDLER->loadConfig();
    $UHANDLER->registerHandler(0,
        array (
    'pluginName' => 'tags',
    'handlerName' => 'tag',
    'flagPrimary' => true,
    'flagFailContinue' => false,
    'flagDisabled' => false,
    'rstyle' => 
    array (
      'rcmd' => '/plugin/tags/{tag}[/{page}]/',
      'regex' => '#^/plugin/tags/(.+?)(?:/(\\d{1,4})){0,1}/$#',
      'regexMap' => 
      array (
        1 => 'tag',
        2 => 'page',
      ),
      'reqCheck' => 
      array (
      ),
      'setVars' => 
      array (
      ),
      'genrMAP' => 
      array (
        0 => 
        array (
          0 => 0,
          1 => '/plugin/tags/',
          2 => 0,
        ),
        1 => 
        array (
          0 => 1,
          1 => 'tag',
          2 => 0,
        ),
        2 => 
        array (
          0 => 0,
          1 => '/',
          2 => 1,
        ),
        3 => 
        array (
          0 => 1,
          1 => 'page',
          2 => 1,
        ),
        4 => 
        array (
          0 => 0,
          1 => '/',
          2 => 0,
        ),
      ),
    ),
  )
    );

    $UHANDLER->registerHandler(0,
        array (
    'pluginName' => 'tags',
    'handlerName' => '',
    'flagPrimary' => true,
    'flagFailContinue' => false,
    'flagDisabled' => false,
    'rstyle' => 
    array (
      'rcmd' => '/plugin/tags[/{page}]/',
      'regex' => '#^/plugin/tags(?:/(\\d{1,4})){0,1}/$#',
      'regexMap' => 
      array (
        1 => 'page',
      ),
      'reqCheck' => 
      array (
      ),
      'setVars' => 
      array (
      ),
      'genrMAP' => 
      array (
        0 => 
        array (
          0 => 0,
          1 => '/plugin/tags',
          2 => 0,
        ),
        1 => 
        array (
          0 => 0,
          1 => '/',
          2 => 1,
        ),
        2 => 
        array (
          0 => 1,
          1 => 'page',
          2 => 1,
        ),
        3 => 
        array (
          0 => 0,
          1 => '/',
          2 => 0,
        ),
      ),
    ),
  )
    );

    // Apply requested action
    switch ($action) {
        case 'confirm':
            generate_install_page('tags', __('tags:desc_install'));
            break;
        case 'autoapply':
        case 'apply':
            if (fixdb_plugin_install('tags', $db_update, 'install', ($action=='autoapply')?true:false)) {

                // Now we need to set some default params
                $params = array(
                    'limit' => 20,
                    'orderby' => 4,
                    'ppage_limit' => 0,
                    'ppage_orderby' => 1,
                    'localSource' => 0,
                    'cache' => 1,
                    'cacheExpire' => 120,
                );

                foreach ($params as $k => $v) {
                    pluginSetVariable('tags', $k, $v);
                }
                // Load CORE Plugin
                $cPlugin = CPlugin::instance();
                // Save configuration parameters of plugins
                $cPlugin->saveConfig();
                $ULIB->saveConfig();
                $UHANDLER->saveConfig();

                plugin_mark_installed('tags');
            } else {
                return false;
            }
            break;
    }
    return true;
}

