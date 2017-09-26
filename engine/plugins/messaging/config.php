<?php

/*
 * Configuration file for plugin
 */

// Protect against hack attempts
if (!defined('BBCMS')) die ('HAL');

// Load lang files
Lang::loadPlugin($plugin, 'admin', '', ':');

// Fill configuration parameters
$cfg = array(
    'description' => __($plugin.':description'),
    'submit' => array(
        array('type' => 'default'),
    )
    );

array_push($cfg, array(
    'name' => 'subject',
    'title' => __($plugin.':subject'),
    'type' => 'input',
    'value' => '',
    ));
array_push($cfg, array(
    'name' => 'content',
    'title' => __($plugin.':content'),
    'type' => 'text',
    'html_flags' => 'rows="10" name="content" id="content"',
    'value' => '',
    ));

// RUN
if ('commit' == $action) {
    // If submit requested, do

    $subject = $_REQUEST['subject'];
    $content = $_REQUEST['content'];
    if (!$subject or trim($subject) == "") {
        msg(array('type' => 'danger', 'message' => __($plugin.':msge_subject')));
    } elseif (!$content or trim($content) == "") {
        msg(array('type' => 'danger', 'message' => __($plugin.':msge_content')));
    } else {
        global $mysql;

        foreach ($mysql->select("SELECT mail FROM `".uprefix."_users`") as $row) {
            sendEmailMessage($row['mail'], $subject, nl2br($content), $filename = false, $mail_from = false, $ctype = 'text/html');
        }

        msg(array('message' => __($plugin.':msgo_sent')));
    }
}

// This plugin always generated config page
generate_config_page($plugin, $cfg);
