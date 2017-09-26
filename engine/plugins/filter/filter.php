<?php

// Protect against hack attempts
if (!defined('BBCMS')) die ('HAL');

// Load CORE Plugin
$cPlugin = CPlugin::instance();
// preload required libraries
$cPlugin->loadLibrary('comments', 'lib');

// Filter class
class clFilterComments extends FilterComments {

	function addComments($userRec, $newsRec, &$tvars, &$SQL) {

		// Manage filtering
		$flagUpdated = false;
		$c = $SQL['text'];

		foreach (explode("\n",pluginGetVariable('filter','replace')) as $line) {
			list($rsrc, $rdest) = explode('|',$line);
			if ($rsrc and $rdest) {
				$c = str_replace($rsrc, $rdest, $c);
				$flagUpdated = true;
			}
		}

		// Manage blocking
		foreach (explode("\n",pluginGetVariable('filter','block')) as $line) {
			if ($line and stripos(' '.$c,trim($line))) {
				Lang::loadPlugin('filter', 'site', '', ':');
				return array('result' => 0, 'errorText' => str_replace('%lock%', trim($line), __('filter:block')));
			}
		}

		if ($flagUpdated)
			$SQL['text'] = $c;

		return 1;
	}
}

// Activate interceptor
pluginRegisterFilter('comments','filter', new clFilterComments);
