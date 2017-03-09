<?php

// Protect against hack attempts
if (!defined('NGCMS')) die ('HAL');

// Load LIBRARY
loadPluginLibrary('comments', 'lib');

// Filter class
class clFilterComments extends FilterComments {
	function addComments($userRec, $newsRec, &$tvars, &$SQL) {
		

		// Manage filtering
		$flagUpdated = false;
		$c = $SQL['text'];

		foreach (explode("\n",pluginGetVariable('filter','replace')) as $line) {
			list($rsrc, $rdest) = explode('|',$line);
			if ($rsrc && $rdest) {
				$c = str_replace($rsrc, $rdest, $c);
				$flagUpdated = true;
			}
		}

		// Manage blocking
		foreach (explode("\n",pluginGetVariable('filter','block')) as $line) {
			if ($line && stripos(' '.$c,trim($line))) {
				Lang::loadPlugin('filter', 'filter', '', '', ':');
				msg(array('type' => 'danger', 'message' => str_replace('%lock%', trim($line), __('filter:block'))));
				return 0;
			}
		}

		if ($flagUpdated)
			$SQL['text'] = $c;

		return 1;
	}
}

// Activate interceptor
pluginRegisterFilter('comments','filter', new clFilterComments);

