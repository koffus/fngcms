<?php

//
// Copyright (C) 2006-2011 Next Generation CMS (http://ngcms.ru/)
// Name: extrainst.inc.php
// Description: Functions required for plugin managment scripts
// Author: Vitaly Ponomarev
//

// Protect against hack attempts
if (!defined('NGCMS')) die ('HAL');

// automatic config screen generator

/*
params:
 array of arrays with variables:
	name = parameter name
	title = parameter title (showed in html)
	descr = description (small symbols show)
	type = input / select / text
	value = default filled value
	values = array of possible values (for select)
	html_flags = additional html flags for parameter
	validate = array with validation parameters, several lines may be applied
		: type = int
			: min, max = define minimum and maximum values
		: type = regex
			: match = define regex that shoud be matched

		: type = integer
		:

*/

function generate_config_page($module, $params, $values = array()) {
	global $tpl, $twig;

	function mkParamLine($param) {

		if ($param['type'] == 'flat') {
			return $param['input'];
		}

		$tvars = array(
			'name' => $param['name'],
			'title' => $param['title'],
			'descr' => $param['descr'],
			'error' => str_replace('%error%', $param['error'], __('param_error')),
			'input' => '',
			'flags' => array(
					'descr' => $param['descr'] ? true : false,
					'error' => $param['error'] ? true : false
				)
			);
		
		if ( $values[$param['name']] ) {
			$param['value'] = $values[$param['name']];
		}

		if ( $param['type'] == 'text' ) {
			$tvars['input'] = '<textarea name="'.$param['name'].'" '.$param['html_flags'].' class="form-control">'.secure_html($param['value']).'</textarea>';
		} elseif ( $param['type'] == 'input' ) {
			$tvars['input'] = '<input type="text" name="'.$param['name'].'" '.$param['html_flags'].' value="'.secure_html($param['value']).'" class="form-control" />';
		} elseif ( $param['type'] == 'checkbox' ) {
			$tvars['input'] = '<input type="checkbox" name="'.$param['name'].'" '.$param['html_flags'].' value="1"'.($param['value']?' checked':'').' />';
		} elseif ( $param['type'] == 'hidden' ) {
			$tvars['input'] = '<input type="hidden" name="'.$param['name'].'" value="'.secure_html($param['value']).'" class="form-control" />';
		} elseif ($param['type'] == 'select') {
			$tvars['input'] = '<select name="'.$param['name'].'" '.$param['html_flags'].' class="form-control">';
			foreach ($param['values'] as $oid => $oval) {
				$tvars['input'] .= '<option value="'.secure_html($param['value']).'"'.($param['value']==$oid?' selected':'').'>'.$oval.'</option>';
			}
			$tvars['input'] .='</select>';
		} else if ($param['type'] == 'manual') {
			$tvars['input'] = $param['input'];
		}

		return $tvars;
	}

	$entries = array();
	$description = $params['description'];
	unset($params['description']);

	// For each param do
	foreach($params as $param) {
		if ( $param['mode'] == 'group' ) {
			// Lets' group parameters into one block
			if ( $param['title'] ) {
				$entries[] = array(
					'groupTitle' => $param['title'],
					'flags' => array(
						'group' => isset($param['title']) ? true : false,
					));
				unset( $param['title'] );
			}
			foreach ( $param['entries'] as $entr ) {
				$entries[] = mkParamLine($entr);
			}
		} else {
			$entries[] = mkParamLine($param);
		}
	}

	$tVars = array(
		'description' => $description,
		'entries' => $entries,
		'plugin' => $module,
		'php_self' => $PHP_SELF,
		'token' => genUToken('admin.extra-config'),
		);
	$xt = $twig->loadTemplate(tpl_actions.'extra-config/table.tpl');
	echo $xt->render($tVars);
}

// Automatic save values into module parameters DB
function commit_plugin_config_changes($module, $params) {

	// Load cofig
	pluginsLoadConfig();

	$cfgUpdate = array();

	// For each param do save data
	foreach($params as $param) {
		// Validate parameter if needed
		if ($param['mode'] == 'group') {
	 	if (is_array($param['entries'])) {
	 		foreach ($param['entries'] as $gparam) {
					if ($gparam['name'] && (!$gparam['nosave'])) {
						pluginSetVariable($module, $gparam['name'], $_POST[$gparam['name']].'');
						$cfgUpdate[$gparam['name']] = $_POST[$gparam['name']].'';
					}
	 		}
	 	}
		} else if ($param['name'] && (!$param['nosave'])) {
			pluginSetVariable($module, $param['name'], $_POST[$param['name']].'');
		}
	}

	// Save config
	pluginsSaveConfig();

	// Generate log
	ngSYSLOG(array('plugin' => '#admin', 'item' => 'config#'.$module), array('action' => 'update', 'list' => $cfgUpdate), null, array(1));
}

// Load params sent by POST request in plugin configuration
function load_commit_params($cfg, $outparams) {

	foreach ($cfg as $param) {
		if ($param['name']) {
			$outparams[$param['name']] = $_POST[$param['name']];
		}
	}
	return $outparams;
}

// Priint page with config change complition notification
function print_commit_complete($plugin, $cfg) {
	generate_config_page($plugin, $cfg);
	msg(array('message' => __('commited')));
}

// check if table exists
function mysql_table_exists($table) {
	global $config, $mysql;

	if (is_array($mysql->record("show tables like ".db_squote($table)))) {
		return 1;
	}
	return 0;
}

// check field params
function get_mysql_field_type($table, $field) {
	global $mysql;

	$result = $mysql->query("SELECT * FROM $table limit 0");
	$fields = $mysql->num_fields($result);
	for ($i=0; $i < $fields; $i++) {
	 if ($mysql->field_name($result, $i) == $field) {
	 	$ft = $mysql->field_type($result, $i);
	 	$fl = $mysql->field_len($result, $i);
	 	if ($ft == 'string') { $ft = 'char'; }
	 	if ($ft == 'blob') { $ft = 'text'; $fl = ''; }
	 	$res = $ft.($fl?' ('.$fl.')':'');
	 	return $res;
		}
	}
	return '';
}

// Database update during install
function fixdb_plugin_install($module, $params, $mode='install', $silent = false) {
	global $tpl, $mysql;

	// Load config
	pluginsLoadConfig();

	$publish = array();
	if ($mode == 'install') {
		array_push($publish, array('title' => '<b>'.__('idbc_process').'</b>', 'descr' => '', 'result' => ''));
	} else {
		array_push($publish, array('title' => '<b>'.__('ddbc_process').'</b>', 'descr' => '', 'result' => ''));
	}
	// For each params do update DB
	foreach($params as $table) {
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

		$chgTableName = (($table['table'] == 'users')?uprefix:prefix)."_".$table['table'];

		if (($table['action'] != 'create')&&
		 ($table['action'] != 'cmodify')&&
		 ($table['action'] != 'modify')&&
		 ($table['action'] != 'drop')) {
		 $publish_title = 'Table operations';
			$publish_result = 'Unknown action type specified ['.$table['action'].']';
			$publish_error = 1;
		 	break;
		}

	if ($table['action'] == 'drop') {
			$publish_title = __('idbc_tdrop');
			$publish_title = str_replace('%table%', $table['table'], $publish_title);

			if (!mysql_table_exists($chgTableName)) {
		 $publish_result = __('idbc_tnoexists');
				$publish_error = 1;
				break;
			}

 	$query = "drop table ".$chgTableName;
 	$mysql->query($query);

			array_push($publish, array('title' => $publish_title, 'descr' => "SQL: [$query]", 'result' => ($publish_result?$publish_result:($error?__('idbc_fail'):__('idbc_ok')))));
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

			if (!mysql_table_exists($chgTableName)) {
		 $publish_result = __('idbc_tnoexists');
				$publish_error = 1;
				break;
			}
		}

		if ($table['action'] == 'create') {
			$publish_title = __('idbc_tcreate');
			$publish_title = str_replace('%table%', $table['table'], $publish_title);

			if (mysql_table_exists($chgTableName)) {
		 $publish_result = __('idbc_t_alreadyexists');
				$publish_error = 1;
				break;
			}
			$create_mode = 1;
		}

		if ($table['action'] == 'cmodify') {
			$publish_title = __('idbc_tcmodify');
			$publish_title = str_replace('%table%', $table['table'], $publish_title);
			if (!mysql_table_exists($chgTableName)) {
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
				if (($field['action'] == 'create')||($field['action'] == 'cmodify')||($field['action'] == 'cleave')) {
					if (!$field['type']) {
						$publish_result = 'Field type should be specified';
						$publish_error = 1;
						break;
					}
					array_push($fieldlist, $field['name']." ".$field['type']." ".$field['params']);
				} else if ($field['action'] != 'drop') {
					$publish_result = 'Unknown action';
					$publish_error = 1;
					break;
				}
			}

			// Check if different character set are supported [ version >= 4.1.1 ]
			$charset = is_array($mysql->record("show variables like 'character_set_client'"))?(' DEFAULT CHARSET='.($table['charset']?$table['charset']:'utf8')):'';

			$query = "create table ".$chgTableName." (".implode(', ',$fieldlist).($table['key']?', '.$table['key']:'').")".$charset.($table['engine']?' engine='.$table['engine']:'');
			$mysql->query($query);
			array_push($publish, array('title' => $publish_title, 'descr' => "SQL: [$query]", 'result' => ($publish_result?$publish_result:($error?__('idbc_fail'):__('idbc_ok')))));
		} else {
			foreach ($table['fields'] as $field) {
				if (!$field['name']) {
					$publish_result = 'Field name should be specified';
					$publish_error = 1;
					break;
				}
				if (($field['action'] == 'create')||($field['action'] == 'cmodify')||($field['action'] == 'cleave')) {
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
					$query = "alter table ".$chgTableName." drop column `".$field['name']."`";
					$mysql->query($query);
					array_push($publish, array('title' => $publish_title, 'descr' => "SQL: [$query]", 'result' => ($publish_result?$publish_result:($error?__('idbc_fail'):__('idbc_ok')))));
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
					$query = "alter table ".$chgTableName." add column `".$field['name']."` ".$field['type']." ".$field['params'];
					$mysql->query($query);
					array_push($publish, array('title' => $publish_title, 'descr' => "SQL: [$query]", 'result' => ($publish_result?$publish_result:($error?__('idbc_fail'):__('idbc_ok')))));
					continue;
				}
				if ($field['action'] == 'cmodify') {
					if (!$ft) {
						$query = "alter table ".$chgTableName." add column `".$field['name']."` ".$field['type']." ".$field['params'];
					} else {
						$query = "alter table ".$chgTableName." change column `".$field['name']."` `".$field['name']."` ".$field['type']." ".$field['params'];
					}
					$mysql->query($query);
					array_push($publish, array('title' => $publish_title, 'descr' => "SQL: [$query]", 'result' => ($publish_result?$publish_result:($error?__('idbc_fail'):__('idbc_ok')))));
					continue;

				}

			}
			if ($publish_error) { break; }
			$publish_title = '';

		}

	}

	// Scan for messages
	if ($publish_title && $publish_error) {
		array_push($publish, array('title' => $publish_title, 'descr' => $publish_descr, 'error' => $publish_error, 'result' => ($publish_result?$publish_result:($publish_error?__('idbc_fail'):__('idbc_ok')))));
	}

	$tpl -> template('install-entries', tpl_actions.'extra-config');

	// Write an info
	foreach ($publish as $v) {
		$tvars['vars'] = $v;
		if ($tvars['vars']['error']) { $tvars['vars']['result'] = '<font color="red">'.$tvars['vars']['result'].'</font>'; }
		$tpl -> vars('install-entries', $tvars);
		$entries .= $tpl -> show('install-entries');
	}

	$tpl -> template('install-process', tpl_actions.'extra-config');
	$tvars['vars'] = array(
		'entries' => $entries,
		'plugin' => $module,
		'php_self' => $PHP_SELF,
		'mode_text' => ($mode=='install')?__('install_text'):__('deinstall_text'),
		'msg' => ($mode=='install'?($publish_error?__('ibdc_ifail'):__('idbc_iok')):($publish_error?__('dbdc_ifail'):__('ddbc_iok')))
	);
	$tpl -> vars('install-process', $tvars);
	if (!$silent) {
		print $tpl -> show('install-process');
	}

	if ($publish_error) { return 0; }
	return 1;
}

// Create install page
function generate_install_page($plugin, $text, $stype = 'install') {
	global $tpl;

	$tpl -> template('install', tpl_actions.'extra-config');
	$tvars['vars'] = array(
		'plugin' => $plugin,
		'stype' => $stype,
		'install_text' => $text,
		'mode_text' => ($stype == 'install')?__('install_text'):__('deinstall_text'),
		'mode_commit' => ($stype == 'install')?__('commit_install'):__('commit_deinstall'),
		'php_self' => $PHP_SELF
	);

	$tpl -> vars('install', $tvars);
	echo $tpl -> show('install');

}
