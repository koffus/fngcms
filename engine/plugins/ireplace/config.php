<?php

//
// Configuration file for plugin
//

// Protect against hack attempts
if (!defined('NGCMS')) die ('HAL');
Lang::loadPlugin($plugin, 'config', '', '', ':');

// Fill configuration parameters
$cfg = array('description' => __($plugin . ':description'));

array_push($cfg, array(
    'name' => 'area',
    'title' => __($plugin . ':area'),
    'descr' => __($plugin . ':area.descr'),
    'type' => 'select',
    'values' => array(
        '' => __($plugin . ':area.choose'),
        'news' => __($plugin . ':area.news'),
        'static' => __($plugin . ':area.static'),
        'comments' => __($plugin . ':area.comments'),
    ),
));
array_push($cfg, array(
    'name' => 'src',
    'title' => __($plugin . ':source'),
    'type' => 'input',
    'value' => '',
));
array_push($cfg, array(
    'name' => 'dest',
    'title' => __($plugin . ':destination'),
    'type' => 'input',
    'value' => '',
));

// RUN
if ($_REQUEST['action'] == 'commit') {

    // Perform a replace
    $query = '';

    do {

        // Check src/dest values
        $src = $_REQUEST['src'];
        $dest = $_REQUEST['dest'];
        $area = $_REQUEST['area'];

        if (empty($src) or empty($dest)) {
            // No src/dest text
            msg(array('type' => 'danger', 'message' => __($plugin . ':error.notext')));
            break;
        }

        if (empty($area)) {
            // No area selected
            msg(array('type' => 'danger', 'message' => __($plugin . ':error.noarea')));
            break;
        }

        // Check area
        switch ($area) {
            case 'news':
                $query = "update " . prefix . "_news set content = replace(content, " . db_squote($src) . ", " . db_squote($dest) . ")";
                break;
            case 'static':
                $query = "update " . prefix . "_static set content = replace(content, " . db_squote($src) . ", " . db_squote($dest) . ")";
                break;
            case 'comments':
                $query = "update " . prefix . "_comments set text = replace(text, " . db_squote($src) . ", " . db_squote($dest) . ")";
                break;
        }

    } while (0);

    // Check if we should make replacement
    if ($query) {
        $result = $mysql->query($query);
        if ($count = $mysql->num_rows($result)) {
            msg(array('type' => 'info', 'message' => str_replace('{count}', $count, __($plugin . ':info.done'))));
        } else {
            msg(array('type' => 'info', 'message' => __($plugin . ':info.nochange')));
        }
    }

}

// This plugin always generated config page
generate_config_page($plugin, $cfg);
