<?php

/*
 * Configuration file for plugin
 */

// Protect against hack attempts
if (!defined('BBCMS')) die ('HAL');

// Load lang files
Lang::loadPlugin($plugin, 'admin', '', ':');

// Load CORE Plugin
$cPlugin = CPlugin::instance();

// Prepare configuration parameters
if (empty($skList = $cPlugin->getThemeSkin($plugin))) {
    msg(array( 'type' => 'danger', 'message' => __('msg.no_skin')));
}

// Fill configuration parameters
$cfg = array(
    'description' => __($plugin.':description'),
    'submit' => array(
        array('type' => 'default'),
    )
    );

/*$cfgX = array();
    array_push($cfgX, array(
        'type' => 'flat',
        'input' => '
        <div class="form-group">
            <div class="row">
                <div class="col-md-3"><label class="control-label"><input type="checkbox"> Вконтакте</label></div>
                <div class="col-md-3"><label class="control-label"><input type="checkbox"> Facebook</label></div>
                <div class="col-md-3"><label class="control-label"><input type="checkbox"> Одноклассники</label></div>
                <div class="col-md-3"><label class="control-label"><input type="checkbox"> Google+</label></div>
            </div>
        </div>',
        ));
array_push($cfg, array(
    'mode' => 'group',
    'title' => 'Список сервисов',
    'entries' => $cfgX,
    ));

$cfgX = array();
    array_push($cfgX, array(
        'name' => 'skin',
        'title' => __('skin'),
        'descr' => __('skin#desc'),
        'type' => 'input',
        'value' => pluginGetVariable($plugin, 'skin')
        ));
array_push($cfg, array(
    'mode' => 'group',
    'title' => 'Вконтакте',
    'entries' => $cfgX,
    'toggle' => true,
    'toggle.mode' => 'hide',
    ));*/

$cfgX = array();
    array_push($cfgX, array(
        'name' => 'skin',
        'title' => __('skin'),
        'descr' => __('skin#desc'),
        'type' => 'select',
        'values' => $skList,
        'value' => pluginGetVariable($plugin, 'skin'),
    ));
array_push($cfg, array(
    'mode' => 'group',
    'title' => __('group.source'),
    'entries' => $cfgX,
    ));

// RUN
if ('commit' == $action) {
    // If submit requested, do config save
    commit_plugin_config_changes($plugin, $cfg);
}

generate_config_page($plugin, $cfg);
