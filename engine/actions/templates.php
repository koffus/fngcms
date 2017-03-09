<?php

//
// Copyright (C) 2006-2012 Next Generation CMS (http://ngcms.ru/)
// Name: templates.php
// Description: Manage/Edit templates
// Author: Vitaly Ponomarev, Alexey Zinchenko
//

// Protect against hack attempts
if (!defined('NGCMS')) die ('HAL');

Lang::load('templates', 'admin', 'templates');

//
// Preload templates version files
function loadTemplateVersions() {
	
	$tDir = root . '../templates';
	$tlist = array();
	if ( $dRec = opendir($tDir) ) {
		
		while (($dName = readdir($dRec)) !== false) {
			if ( ($dName == '.') or ($dName == '..') )
				continue;
			
			if ( is_dir($tDir.'/'.$dName) and file_exists($vfn = $tDir.'/'.$dName.'/version') and (filesize($vfn)) and ($vf = @fopen($vfn, 'r')) ) {
				$tRec = array('name' => $dName);
				while (!feof($vf)) {
					$line = fgets($vf);
					if ( preg_match("/^(.+?) *\: *(.+?) *$/i", trim($line), $m) ) {
						if (in_array(strtolower($m[1]), array('id', 'title', 'author', 'version', 'reldate', 'plugins', 'image', 'imagepreview')))
							$tRec[strtolower($m[1])] = $m[2];
					}
				}
				fclose($vf);
				if ( isset($tRec['id']) and isset($tRec['title']) )
					array_push($tlist, $tRec);
			}
		}
		
		closedir($dRec);
	}
	
	return $tlist;
	
}

//
// ================================================================== //
// CODE //
// ================================================================== //
//

$tVars = array(
	'home_url'	=> home,
	'token'		=> genUToken('admin.templates'),
);

$tVars['siteTemplates'] = array();

$tlist = loadTemplateVersions();
foreach ($tlist as $tver) {
	$tVars['siteTemplates'][]= $tver;
}

$xt = $twig->loadTemplate('skins/default/tpl/templates.tpl');
print $xt->render($tVars);
