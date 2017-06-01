<?php

# protect against hack attempts
if (!defined('NGCMS')) die ('HAL');

# preload config file
pluginsLoadConfig();

Lang::loadPlugin('xfilter', 'config', '', 'xfl', ':');

# fill configuration parameters
$cfg = array();
array_push($cfg, array('descr' => __('xfl:description')));

$cfgX = array();
array_push($cfgX, array(
				'name' => "{$currentVar}_skipcat", 
				'title' => __('xfl:skipcat'), 
				'type' => 'input',
				'value' => pluginGetVariable($plugin, "{$currentVar}_skipcat"))
);

array_push($cfgX, array(
				'name' => "{$currentVar}_showAllCat",
				'type' => 'select',
				'title' => __('xfl:showAllCat'),
				'values' => array(1 => __('yesa'), 0 => __('noa')), 
				'value' => pluginGetVariable($plugin, "{$currentVar}_showAllCat"))
);

$orderby = array(
			'id_desc' => __('xfl:orderby_iddesc'), 
			'id_asc' => __('xfl:orderby_idasc'), 
			'postdate_desc' => __('xfl:orderby_postdatedesc'), 
			'postdate_asc' => __('xfl:orderby_postdateasc'), 
			'title_desc' => __('xfl:orderby_titledesc'), 
			'title_asc' => __('xfl:orderby_titleasc')
);

array_push($cfgX, array(
				'name' => "{$currentVar}_order",
				'type' => 'select',
				'title' => __('xfl:orderby_title'),
				'values' => $orderby,
				'value' => pluginGetVariable($plugin, "{$currentVar}_order"))
);

array_push($cfgX, array(
				'name' => "{$currentVar}_showNumber",
				'title' => __('xfl:number_title'),
				'type' => 'input',
				'value' => intval(pluginGetVariable($plugin, "{$currentVar}_showNumber")) ? pluginGetVariable($plugin, "{$currentVar}_showNumber") : '10')
);

array_push($cfg, array(
					'mode' => 'group', 
					'title' => __('xfl:group'), 
					'entries' => $cfgX)
);

$cfgX = array();
array_push($cfgX, array(
					'name' => 'localsource', 
					'title' => __('xfl:localsource'), 
					'type' => 'select', 
					'values' => array ( '0' => __('xfl:localsource_0'), '1' => __('xfl:localsource_1')), 
					'value' => intval(pluginGetVariable($plugin, 'localsource')))
);
array_push($cfg, array(
					'mode' => 'group', 
					'title' => __('xfl:group_2'), 
					'entries' => $cfgX)
);

# RUN 
if ($_REQUEST['action'] == 'commit') {
	# if submit requested, do config save
	commit_plugin_config_changes($plugin, $cfg);
	print_commit_complete($plugin, $cfg);
} else {
	generate_config_page($plugin, $cfg);
}
