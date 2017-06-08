<?php

// Protect against hack attempts
if (!defined('NGCMS')) die ('HAL');

function messaging($subject, $content) {
	global $mysql;

	Lang::loadPlugin('messaging', 'messaging', '', 'mes');

	if (!$subject or trim($subject) == "") {
		msg(array('type' => 'danger', 'message' => __('mes_msge_subject')));
	} elseif (!$content or trim($content) == "") {
		msg(array('type' => 'danger', 'message' => __('mes_msge_content')));
	} else {
		$mailBody = nl2br($content);
		$mailSubject = $subject;

		foreach ($mysql->select("SELECT mail FROM `".uprefix."_users`") as $row) {
			$mailTo = $row['mail'];
			sendEmailMessage($mailTo, $mailSubject, $mailBody, $filename = false, $mail_from = false, $ctype = 'text/html');
		}

		msg(array('message' => __('mes_msgo_sent')));
	}
}
