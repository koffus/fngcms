<?php

//
// Configuration file for plugin
//

// Protect against hack attempts
if (!defined('NGCMS')) die ('HAL');

// need for xfields sort & search
if ( xmode() and function_exists('xf_configLoad')) {
	include_once root.'plugins/xfields/xfields.php';
	$xf = xf_configLoad();
} else {
	msg(array('type' => 'danger', 'message' => 'XFields plugin is not loaded now!'));
}

function xmode() {
 // check if xfields plugin is active
 return (getPluginStatusActive('xfields')) ? true : false;
}

if (!is_array($xf))
	$xf = array();

//
// Управление необходимыми действиями
$sectionID = $_REQUEST['section'];
if (!in_array($sectionID, array('news', 'grp.news', 'users', 'grp.users', 'tdata')))
	$sectionID = 'news';

if (isset($_REQUEST['action'])) {
    switch ($_REQUEST['action']) {
        case 'add': showAddEditForm(); break;
        case 'doadd': doAddEdit(); break;
        case 'edit': showAddEditForm(); break;
        case 'doedit': doAddEdit(); break;
        case 'update': doUpdate(); showList(); break;
        default: showList();
    }
} else {
    showList();
}

//
// Показать список полей
function showList(){
	global $sectionID;

	if (in_array($sectionID, array('grp.news', 'grp.users'))) {
		showSectionList();
	} else {
		showFieldList();
	}
}

//
//
function showSectionList(){
	global $xf, $tpl, $twig, $sectionID;

	$tVars = array(
		'section_name' => __('xfields:section.'.$sectionID),
	);

	// Prepare data
	$grpNews = array();
	foreach ($xf['grp.news'] as $k => $v) {
		$grpNews[$k] = array(
			'title' => $v['title'],
			'entries' => $v['entries'],
			);
	}

	foreach (array('news', 'grp.news', 'users', 'grp.users', 'tdata') as $cID)
		$tVars['class'][$cID] = ($cID == $sectionID) ? 'active' : '';

	$tVars['json']['groups.config'] = json_encode($grpNews);
	$tVars['json']['fields.config'] = json_encode($xf['news']);

	$xt = $twig->loadTemplate('plugins/xfields/tpl/groups.tpl');
	echo $xt->render($tVars);

}

//
// Показать список доп. полей
function showFieldList(){
	global $xf, $twig, $sectionID;

	$xEntries = array();
    if(isset($xf[$sectionID]) and is_array($xf[$sectionID])) {
        foreach ($xf[$sectionID] as $id => $data) {
            $storage = '';
            if ($data['storage']) {
                $storage = '<br/><font color="red"><b>'.$data['db.type'].($data['db.len']?(' ('.$data['db.len'].')'):'').'</b> </font>';
            }

            $xEntry = array(
                'name' => $id,
                'title' => $data['title'],
                'type' => __('xfields:type_'.$data['type']).$storage,
                'default' => ( ($data['type']=="checkbox") ? ($data['default'] ? __('yesa') : __('noa')) : ($data['default']) ),
                'link' => '?mod=extra-config&plugin=xfields&action=edit&section='.$sectionID.'&field='.$id,
                'linkup' => '?mod=extra-config&plugin=xfields&action=update&subaction=up&section='.$sectionID.'&field='.$id,
                'linkdown' => '?mod=extra-config&plugin=xfields&action=update&subaction=down&section='.$sectionID.'&field='.$id,
                'linkdel' => '?mod=extra-config&plugin=xfields&action=update&subaction=del&section='.$sectionID.'&field='.$id,
                'extends' => __('extends_' . (!empty($data['extends']) ? $data['extends'] : 'additional')),
                'flags' => array(
                    'required' => !empty($data['required'])?true:false,
                    'default' => (!empty($data['default']) or ($data['type']=="checkbox"))?true:false,
                    'disabled' => !empty($data['disabled'])?true:false,
                    'regpage' => !empty($data['regpage'])?true:false,
                ),
            );

            $options = '';
            if (isset($data['options']) and is_array($data['options']) and count($data['options'])) {
                foreach ($data['options'] as $k => $v)
                    $options .= (($data['storekeys'])?('<b>'.$k.'</b>: '.$v):('<b>'.$v.'</b>'))."<br>\n";
            }
            $xEntry['options'] = $options;

            $xEntries []= $xEntry;
        }
    }
	$tVars = array(
		'entries' => $xEntries,
		'section_name' => __('xfields:section.'.$sectionID),
		'sectionID' => $sectionID,
	);

	foreach (array('news', 'grp.news', 'users', 'grp.users', 'tdata') as $cID)
		$tVars['class'][$cID] = ($cID == $sectionID)?'active':'';

	$xt = $twig->loadTemplate('plugins/xfields/tpl/config.tpl');
	echo $xt->render($tVars);
}

//
//
function showAddEditForm($xdata = '', $eMode = NULL, $efield = NULL){
	global $xf, $sectionID, $twig;

	$field = ($efield === NULL and isset($_REQUEST['field'])) ? $_REQUEST['field'] : $efield;

	if ($eMode == NULL) {
		$editMode = (isset($xf[$sectionID][$field]) and is_array($xf[$sectionID][$field]))?1:0;
	} else {
		$editMode = $eMode;
	}

	$tVars = array();
    $xsel = '';

	if ($editMode) {
		$data = is_array($xdata)?$xdata:$xf[$sectionID][$field];
        foreach (array('text', 'textarea', 'select', 'multiselect', 'checkbox', 'images') as $ts) {
			$tVars['defaults'][$ts] = ($data['type'] == $ts)?(($ts=="checkbox")?($data['default']?' checked="checked"':''):$data['default']):'';
			$xsel .= '<option value="'.$ts.'"'.(($data['type'] == $ts)?' selected':'').'>'.__('xfields:type_'.$ts).'</option>';
		}
		$tVars['flags']['editMode'] = 1;
		$tVars['flags']['disabled'] = !empty($data['disabled']) ? true : false;
		$tVars['flags']['regpage'] = !empty($data['regpage']) ? true : false;
		$tVars = $tVars + array(
			'id' => $field,
			'title' => $data['title'],
			'type' => $data['type'],
			'storage' => intval($data['storage']),
			'db_type' => $data['db.type'],
			'db_len' => (intval($data['db.len'])>0)?intval($data['db.len']):'',
			'main_selected' => ($data['extends'] == 'main') ? 'selected' : '',
			'additional_selected' => ($data['extends'] == 'additional') ? 'selected' : '',
			'bb_support' => !empty($data['bb_support'])?'checked="checked"':'',
			'html_support' => !empty($data['html_support'])?'checked="checked"':'',
			'noformat' => !empty($data['noformat'])?'checked="checked"':'',
		);

		$xsel = '';
		foreach (array('text', 'textarea', 'select', 'multiselect', 'checkbox', 'images') as $ts) {
			$tVars['defaults'][$ts] = ($data['type'] == $ts)?(($ts=="checkbox")?($data['default']?' checked="checked"':''):$data['default']):'';
			$xsel .= '<option value="'.$ts.'"'.(($data['type'] == $ts)?' selected':'').'>'.__('xfields:type_'.$ts).'</option>';
		}

		$sOpts = array();
		$fNum = 1;
		if ( $data['type'] == 'select' ) {
			if (is_array($data['options']))
				foreach ($data['options'] as $k => $v) {
					array_push($sOpts, '<tr>
								<td>
									<input type="text" size="12" name="so_data['.($fNum).'][0]" value="'.($data['storekeys']?htmlspecialchars($k, ENT_COMPAT | ENT_HTML401, 'UTF-8'):'').'" class="form-control" />
								</td>
								<td>
									<input type="text" size="55" name="so_data['.($fNum).'][1]" value="'.htmlspecialchars($v, ENT_COMPAT | ENT_HTML401, 'UTF-8').'" class="form-control" />
								</td>
								<td class="text-center">
									<a href="#" onclick="return false;" class="btn btn-danger"><i class="fa fa-trash"></i></a>
								</td>
							</tr>');
					$fNum++;
				}
		}
		if (!count($sOpts)) {
			array_push($sOpts, '<tr>
								<td>
									<input type="text" size="12" name="so_data[1][0]" value="" class="form-control" />
								</td>
								<td>
									<input type="text" size="55" name="so_data[1][1]" value="" class="form-control" />
								</td>
								<td class="text-center">
									<a href="#" onclick="return false;" class="btn btn-danger"><i class="fa fa-trash"></i></a>
								</td>
							</tr>');
		}

        $m_sOpts = array();
		$fNum = 1;
        if ( $data['type'] == 'multiselect' ) {
			if (is_array($data['options']))
				foreach ($data['options'] as $k => $v) {
					array_push($m_sOpts, '<tr>
								<td>
									<input size="12" name="mso_data['.($fNum).'][0]" type="text" value="'.($data['storekeys']?htmlspecialchars($k, ENT_COMPAT | ENT_HTML401, 'UTF-8'):'').'" class="form-control" />
								</td>
								<td>
									<input type="text" size="55" name="mso_data['.($fNum).'][1]" value="'.htmlspecialchars($v, ENT_COMPAT | ENT_HTML401, 'UTF-8').'" class="form-control" />
								</td>
								<td class="text-center">
									<a href="#" onclick="return false;" class="btn btn-danger"><i class="fa fa-trash"></i></a>
								</td>
							</tr>');
					$fNum++;
				}
		}
		if (!count($m_sOpts)) {
			array_push($m_sOpts, '<tr>
								<td>
									<input size="12" name="mso_data[1][0]" type="text" value="" class="form-control" />
								</td>
								<td>
									<input type="text" size="55" name="mso_data[1][1]" value="" class="form-control" />
								</td>
								<td class="text-center">
									<a href="#" onclick="return false;" class="btn btn-danger"><i class="fa fa-trash"></i></a>
								</td>
							</tr>');
		}

		$tVars = $tVars + array(
			'sOpts' => implode("\n", $sOpts),
            'm_sOpts' => implode("\n", $m_sOpts),
			'type_opts' => $xsel,
			'storekeys_opts' => '<option value="0">'.__('xfields:tselect_store_value').'</option><option value="1"'.(!empty($data['storekeys'])?' selected':'').'>'.__('xfields:tselect_store_key').'</option>',
			'required_opts' => '<option value="0">'.__('noa').'</option><option value="1"'.(($data['required'])?' selected':'').'>'.__('yesa').'</option>',
			'images' => array(
				'maxCount' => !empty($data['maxCount']) ? intval($data['maxCount']) : '0',
				'thumbWidth' => !empty($data['thumbWidth']) ? intval($data['thumbWidth']) : '0',
				'thumbHeight' => !empty($data['thumbHeight']) ? intval($data['thumbHeight']) : '0',
			),
		);

		foreach (array('imgStamp', 'imgShadow', 'imgThumb', 'thumbStamp', 'thumbShadow') as $k) {
			$tVars['images'][$k] = (!empty($data[$k]) and intval($data[$k])) ? 'checked="checked"':'';
		}
	//print "<pre>".var_export($tVars, true)."</pre>";
	} else {
		$sOpts = array();
		array_push($sOpts, '<tr>
								<td>
									<input type="text" size="12" name="so_data[1][0]" value="" class="form-control" />
								</td>
								<td>
									<input type="text" size="55" name="so_data[1][1]" value="" class="form-control" />
								</td>
								<td class="text-center">
									<a href="#" onclick="return false;" class="btn btn-danger"><i class="fa fa-trash"></i></a>
								</td>
							</tr>');
        $m_sOpts = array();
		array_push($m_sOpts, '<tr>
								<td>
									<input size="12" name="mso_data[1][0]" type="text" value="" class="form-control" />
								</td>
								<td>
									<input type="text" size="55" name="mso_data[1][1]" value="" class="form-control" />
								</td>
								<td class="text-center">
									<a href="#" onclick="return false;" class="btn btn-danger"><i class="fa fa-trash"></i></a>
								</td>
							</tr>');

		$tVars['flags']['editmode'] = 0;
		$tVars['flags']['disabled'] = false;
		$tVars = $tVars + array(
			'sOpts' => implode("\n", $sOpts),
            'm_sOpts' => implode("\n", $m_sOpts),
			'id' => '',
			'title' => '',
			'type' => 'text',
			'storage' => '0',
			'db_type' => '',
			'db_len' => '',
		);

		$xsel = '';
		foreach (array('text', 'textarea', 'select', 'multiselect', 'checkbox', 'images') as $ts) {
			$tVars['defaults'][$ts] = '';
			$xsel .= '<option value="'.$ts.'"'.((isset($data['type']) and ($data['type'] == 'text')) ? ' selected' : '').'>'.__('xfields:type_'.$ts).'</option>';
		}

		$tVars = $tVars + array(
			'type_opts' => $xsel,
			'storekeys_opts' => '<option value="0">'.__('xfields:tselect_store_value').'</option><option value="1">'.__('xfields:tselect_store_key').'</option>',
			'required_opts' => '<option value="0">'.__('noa').'</option><option value="1">'.__('yesa').'</option>',
			'select_options' => '',

			'images' => array(
				'maxCount' => '1',
				'thumbWidth' => '150',
				'thumbHeight' => '150',
			),
		);

		foreach (array('imgStamp', 'imgShadow', 'imgThumb', 'thumbStamp', 'thumbShadow') as $k) {
			$tVars['images'][$k] = '';
		}
	}
	$tVars['sectionID'] = $sectionID;
	$xt = $twig->loadTemplate('plugins/xfields/tpl/config_edit.tpl');
	echo $xt->render($tVars);
}

//
//
function doAddEdit() {
	global $xf, $XF, $tpl, $twig, $mysql, $sectionID;
	//print "<pre>".var_export($_POST, true)."</pre>";
	$error = 0;

	$field = $_REQUEST['id'];
	$editMode = $_REQUEST['edit']?1:0;

	// Check if field exists or not [depends on mode]
	if ($editMode and (!is_array($xf[$sectionID][$field]))) {
		msg(array('type' => 'danger', 'message' => __('xfields:msge_noexists')));
		$error = 1;
	} elseif (!$editMode and (is_array($xf[$sectionID][$field]))) {
		msg(array('type' => 'danger', 'message' => __('xfields:msge_exists')));
		$error = 1;
	}

	// Check if Field name fits our requirements
	if (!$editMode) {
		if (!preg_match('/^[a-z]{1}[a-z0-9]{2}[a-z0-9]*$/', $field)) {
			msg(array('type' => 'danger', 'message' => __('xfields:msge_format')));
			$error = 1;
		}
	}

	// Let's fill parameters
	$data['title'] = $_REQUEST['title'];
	$data['required'] = isset($_REQUEST['required']) ? intval($_REQUEST['required']) : 0;
	$data['disabled'] = isset($_REQUEST['disabled']) ? intval($_REQUEST['disabled']) : 0;
	$data['extends'] = isset($_REQUEST['extends']) ? $_REQUEST['extends'] : 'additional';
	$data['type'] = $_REQUEST['type'];
	$data['bb_support'] = isset($_REQUEST['bb_support']) ? intval($_REQUEST['bb_support']) : 0;
	$data['default'] = '';

	if (($sectionID == 'users') and ($data['type'] != 'images'))
		$data['regpage'] = intval($_REQUEST['regpage']);

	switch ($data['type']) {
		case 'checkbox':
				$data['default'] = isset($_REQUEST['checkbox_default']) ? intval($_REQUEST['checkbox_default']) : 0;
			break;
		case 'text':
			if ($_REQUEST['text_default'] != '')
				$data['default'] = $_REQUEST['text_default'];
			$data['bb_support'] = isset($_REQUEST['bb_support']) ? intval($_REQUEST['bb_support']) : 0;
			$data['html_support'] = isset($_REQUEST['html_support']) ? intval($_REQUEST['html_support']) : 0;
			break;
		case 'textarea':
			if ($_REQUEST['textarea_default'] != '')
				$data['default'] = $_REQUEST['textarea_default'];
			$data['bb_support'] = isset($_REQUEST['bb_support']) ? intval($_REQUEST['bb_support']) : 0;
			$data['html_support'] = isset($_REQUEST['html_support']) ? intval($_REQUEST['html_support']) : 0;
			$data['noformat'] = isset($_REQUEST['noformat']) ? intval($_REQUEST['noformat']) : 0;
			break;
		case 'select':

			// Check options
			$optlist = array();
			$optvals = array();

			if (isset($_REQUEST['so_data']) and is_array($_REQUEST['so_data'])) {
				foreach ($_REQUEST['so_data'] as $k => $v) {
					if (is_array($v) and isset($v[0]) and isset($v[1]) and (($v[0] != '') or ($v[1] != ''))) {
						if ($v[0] != '') {
							$optlist[$v[0]] = $v[1];
						} else {
							$optlist[] = $v[1];
						}
						//print "<pre>SO_LINE: ".$v[0].", ".$v[1]."</pre>";
					}
				}
			}

			$opt_vals = array_values($optlist);

			/*
			$opts = $_REQUEST['select_options'];
			$optlist = array();
			$optvals = array();
			foreach (explode("\n", $opts) as $line) {
				$line = trim($line);
				if (preg_match('/^(.+?) *\=\> *(.+?)$/', $line, $match)) {
					$optlist[$match[1]] = $match[2];
					$optvals[$match[2]] = 1;
				} elseif ($line != '') {
					$optlist[] = $line;
					$optvals[$line] = 1;
				}
			}
			*/

			$data['storekeys'] = intval($_REQUEST['select_storekeys'])?1:0;

			$data['options'] = $optlist;
			if (trim($_REQUEST['select_default'])) {
				$data['default'] = trim($_REQUEST['select_default']);
				if (
					(( $data['storekeys']) and (!array_key_exists($data['default'], $optlist))) ||
					((!$data['storekeys']) and (!in_array($data['default'], $optlist)))
				 ) {
					msg(array('type' => 'danger', 'message' => __('xfields:msge_errdefault')));
					$error = 1;
				}
			}

			break;
		case 'multiselect':

			// Check options
			$optlist = array();
			$optvals = array();

			if (isset($_REQUEST['mso_data']) and is_array($_REQUEST['mso_data'])) {
				foreach ($_REQUEST['mso_data'] as $k => $v) {
					if (is_array($v) and isset($v[0]) and isset($v[1]) and (($v[0] != '') or ($v[1] != ''))) {
						if ($v[0] != '') {
							$optlist[$v[0]] = $v[1];
						} else {
							$optlist[] = $v[1];
						}
						//print "<pre>SO_LINE: ".$v[0].", ".$v[1]."</pre>";
					}
				}
			}

			$opt_vals = array_values($optlist);
 
 

			/*
			$opts = $_REQUEST['select_options'];
			$optlist = array();
			$optvals = array();
			foreach (explode("\n", $opts) as $line) {
				$line = trim($line);
				if (preg_match('/^(.+?) *\=\> *(.+?)$/', $line, $match)) {
					$optlist[$match[1]] = $match[2];
					$optvals[$match[2]] = 1;
				} elseif ($line != '') {
					$optlist[] = $line;
					$optvals[$line] = 1;
				}
			}
			*/

			$data['storekeys'] = intval($_REQUEST['select_storekeys_multi'])?1:0;

			$data['options'] = $optlist;
			if (trim($_REQUEST['select_default_multi'])) {
				$data['default'] = trim($_REQUEST['select_default_multi']);
				if (
					(( $data['storekeys']) and (!array_key_exists($data['default'], $optlist))) ||
					((!$data['storekeys']) and (!in_array($data['default'], $optlist)))
				 ) {
					msg(array('type' => 'danger', 'message' => __('xfields:msge_errdefault')));
					$error = 1;
				}
			}

			break;
		case 'images':
			$data['maxCount'] = intval($_REQUEST['images_maxCount']);
			$data['imgShadow'] = intval($_REQUEST['images_imgShadow'])?1:0;
			$data['imgStamp'] = intval($_REQUEST['images_imgStamp'])?1:0;
			$data['imgThumb'] = intval($_REQUEST['images_imgThumb'])?1:0;
			$data['thumbWidth'] = intval($_REQUEST['images_thumbWidth']);
			$data['thumbHeight'] = intval($_REQUEST['images_thumbHeight']);
			$data['thumbStamp'] = intval($_REQUEST['images_thumbStamp'])?1:0;
			$data['thumbShadow'] = intval($_REQUEST['images_thumbShadow'])?1:0;
			break;
		default: $data['type'] = ''; break;
	}

	if (!$data['type']) {
			msg(array('type' => 'danger', 'message' => __('xfields:msge_errtype')));
			$error = 1;
	}

	if (!$data['title']) {
			msg(array('type' => 'danger', 'message' => __('xfields:msge_errtitle')));
			$error = 1;
	}

	// Check for storage params
	$data['storage'] = $_REQUEST['storage'];
	$data['db.type'] = $_REQUEST['db_type'];
	$data['db.len'] = intval($_REQUEST['db_len']);

	if ($data['storage']) {
		// Check for correct DB type
		if (!in_array($data['db.type'], array('int', 'decimal', 'char', 'datetime', 'text'))) {
			msg(array('type' => 'danger', 'message' => __('xfields:error.db.type')));
			$error = 1;
		}

		// Check for correct DB len (if applicable)
		if (($data['db.type'] == 'char') and ((intval($data['db.len'])<1)||(intval($data['db.len'])>255))) {
			msg(array('type' => 'danger', 'message' => __('xfields:error.db.len')));
			$error = 1;
		}
	}

	if ($error) {
		showAddEditForm($data, $editMode, $field, $sectionID);
		return;
	}

	$DB = array();
	$DB['new'] = array('storage' => $data['storage'], 'db.type' => $data['db.type'], 'db.len' => $data['db.len']);
	if ($editMode) {
		$DB['old'] = array('storage' => $XF[$sectionID][$field]['storage'], 'db.type' => $XF[$sectionID][$field]['db.type'], 'db.len' => $XF[$sectionID][$field]['db.len']);
	}

	$XF[$sectionID][$field] = $data;
	if (!xf_configSave()) {
		msg(array('type' => 'danger', 'message' => __('xfields:msge_errcsave')));
		showAddEditForm($data, $editMode, $field);
		return;
	}

	// Now we should update table `_news` structure and content
	if (!($tableName = xf_getTableBySectionID($sectionID))) {
		print 'Ошибка: неизвестная секция/блок ('.$sectionID.')';
		return;
	}

	$found = 0;
	foreach ($mysql->select("describe ".$tableName, 1) as $row) {
		if ($row['Field'] == 'xfields_'.$field) {
			$found = 1;
			break;
		}
	}

	$dbFlagChanged = 0;

	// 1. If we add XFIELD and field already exists in DB - drop it!
	// 2. If we don't want to store data in separate field - drop it!
	if ($found and (!$editMode or !$DB['new']['storage'])) {
		$mysql->query("alter table ".$tableName." drop column `xfields_".$field."`");
	}

	// If we need to have this field - let's make it. But only if smth was changed
	do {
		if (!$data['storage']) break;
		// Anything should be done only if field is changed
		if (($DB['old']['db.type'] == $DB['new']['db.type']) and ($DB['old']['db.len'] == $DB['new']['db.len'])) break;

		$ftype = '';
		switch ($DB['new']['db.type']) {
			case 'int': $ftype = 'int'; break;
			case 'decimal': $ftype = 'decimal (12,2)'; break;
			case 'datetime': $ftype = 'datetime'; break;
			case 'char': if (($DB['new']['db.len'] > 0) and ($DB['new']['db.len'] <= 255)) { $ftype = 'char('.intval($DB['new']['db.len']).')'; break; }
			case 'text': $ftype = 'text'; break;
		}

		if ($ftype) {
			$dbFlagChanged = 1;
			if ($found) {
				$mysql->query("alter table ".$tableName." change column `xfields_".$field.'` `xfields_'.$field.'` '.$ftype);
				$mysql->query("update ".$tableName." set `xfields_".$field."` = NULL");
			} else {
				$mysql->query("alter table ".$tableName." add column `xfields_".$field.'` '.$ftype);
			}
		}
	} while(0);


	// Second - fill field's content if required
	if ($DB['new']['storage'] and $dbFlagChanged) {
		// Make updates with chunks for 500 RECS
		$recCount = 0;
		$maxID = 0;
		do {
			$recCount = 0;
			foreach ($mysql->select("select id, xfields from ".$tableName." where (id > ".$maxID.") and (xfields is not NULL) and (xfields <> '') order by id limit 500") as $rec) {
				$recCount++;
				if ($rec['id'] > $maxID) $maxID = $rec['id'];
				$xlist = xf_decode($rec['xfields']);
				if (isset($xlist[$field]) and ($xlist[$field] != '')) {
					$mysql->query("update ".$tableName." set `xfields_".$field."` = ".db_squote($xlist[$field])." where id = ".db_squote($rec['id']));
				}
			}
		} while ($recCount);


	}

	$tVars = array (
		'id' => $field,
		'sectionID' => $sectionID,
		'flags' => array (
			'editMode' => $editMode?true:false,
		),
	);

	$tVars['sectionID']	= $sectionID;
	$xt = $twig->loadTemplate('plugins/xfields/tpl/config_done.tpl');
	echo $xt->render($tVars);

}

//
//
function doUpdate() {
	global $xf, $XF, $mysql, $sectionID;

	$notif = '';
	$error = 0;
	$field = $_REQUEST['field'];

	// Check if field exists or not [depends on mode]
	if (!is_array($xf[$sectionID][$field])) {
		msg(array('type' => 'danger', 'message' => sprintf(__('xfields:msge_noexists'), $sectionID, $field)));
		$error = 1;
	};

	switch ( $_REQUEST['subaction'] ) {
		// Delete field from SQL table if required
		case 'del':
			if ( ($XF[$sectionID][$field]['storage']) and ($tableName = xf_getTableBySectionID($sectionID)) ) {
				// Check if field really exist
				$found = 0;
				foreach ($mysql->select("describe ".$tableName, 1) as $row) {
					if ($row['Field'] == 'xfields_'.$field) {
						$found = 1;
						break;
					}
				}
				if ($found) {
					$mysql->query("alter table ".$tableName." drop column `xfields_".$field."`");
					$error = 0;
				}
			}
			unset($XF[$sectionID][$field]);
			$notif = __('xfields:done_del');
			break;
		case 'up':
			array_key_move($XF[$sectionID], $field, -1);
			$notif = __('xfields:done_up');
			break;
		case 'down':
			array_key_move($XF[$sectionID], $field, 1);
			$notif = __('xfields:done_down');
			break;
		default:
			$notif = __('xfields:updateunk');
	}

	if ( !xf_configSave() or $error ) {
		msg(array('type' => 'danger', 'message' => __('xfields:msge_errcsave')));
		return;
	} else {
		msg(array('message' => $notif));
	}

	$xf = $XF;

}

function array_key_move(&$arr, $key, $offset) {
	$keys = array_keys($arr);
	$index = -1;
	foreach ($keys as $k => $v) if ($v == $key) { $index = $k; break; }
	if ($index == -1) return 0;
	$index2 = $index + $offset;
	if ($index2 < 0) $index2 = 0;
	if ($index2 > (count($arr)-1)) $index2 = count($arr)-1;
	if ($index == $index2) return 1;

	$a = min($index, $index2);
	$b = max($index, $index2);

	$arr = array_slice($arr, 0, $a) +
	array_slice($arr, $b, 1) +
	array_slice($arr, $a+1, $b-$a) +
	array_slice($arr, $a, 1) +
	array_slice($arr, $b, count($arr) - $b);
}
