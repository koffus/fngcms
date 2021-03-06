<?php

// #==========================================================#
// # Plugin name: xfields [ Additional fields managment ] #
// # Author: Vitaly A Ponomarev, vp7@mail.ru #
// # Allowed to use only with: BixBite CMS #
// #==========================================================#

// Protect against hack attempts
if (!defined('BBCMS')) die ('HAL');

// Load CORE Plugin
$cPlugin = CPlugin::instance();
// preload required libraries
$cPlugin->loadLibrary('xfields', 'common');

// Load lang files
Lang::loadPlugin('xfields', 'admin', '', ':');

//
// XFields: Add/Modify attached files
function xf_modifyAttachedImages($dsID, $newsID, $xf, $attachList)
{
    global $mysql, $config, $DSlist;

    // Init file/image processing libraries
    $fmanager = new FileManagment();
    $imanager = new ImageManagment();

    // Select xf group name
    $xfGroupName = '';
    foreach (array('news', 'users') as $k) {
        if ($DSlist[$k] == $dsID) {
            $xfGroupName = $k;
            break;
        }
    }

    if (!$xfGroupName) {
        return false;
    }

    // Scan if user want to change description
    foreach ($attachList as $iRec) {
        //print "[A:".$iRec['id']."]";
        if (isset($_REQUEST['xfields_'.$iRec['pidentity'].'_dscr']) and is_array($_REQUEST['xfields_'.$iRec['pidentity'].'_dscr']) and isset($_REQUEST['xfields_'.$iRec['pidentity'].'_dscr'][$iRec['id']])) {
            // We have this field in EDIT mode
            if (empty($iRec['decsription']) or ($_REQUEST['xfields_'.$iRec['pidentity'].'_dscr'][$iRec['id']] != $iRec['decsription'])) {
                $mysql->query("update ".prefix."_images set description = ".db_squote($_REQUEST['xfields_'.$iRec['pidentity'].'_dscr'][$iRec['id']])." where id = ".intval($iRec['id']));
            }
        }
        
        
    }

    $xdata = array();
    foreach ($xf[$xfGroupName] as $id => $data) {
        // Attached images are processed in special way
        if ($data['type'] == 'images') {
            // Check if we should delete some images
            if (isset($_POST['xfields_'.$id.'_del']) and is_array($_POST['xfields_'.$id.'_del'])) {
                foreach ($_POST['xfields_'.$id.'_del'] as $key => $value) {
                    // Allow to delete only images, that are attached to current news
                    if ($value) {
                        $xf = false;
                        foreach ($attachList as $irow) {
                            if ($irow['id'] == $key) {
                                $xf = true; break;
                            }
                        }
                        if (!$xf)
                            continue;

                        //print "NEED TO DEL [$key]<br/>\n";
                        $fmanager->file_delete(array('type' => 'image', 'id' => $key));
                    }
                }
            }

            // Check for new attached files
            if (isset($_FILES['xfields_'.$id]) and isset($_FILES['xfields_'.$id]['name']) and is_array($_FILES['xfields_'.$id]['name'])) {
                foreach ($_FILES['xfields_'.$id]['name'] as $iId => $iName) {
                    if ($_FILES['xfields_'.$id]['error'][$iId] > 0) {
                        //print $iId." >>ERROR: ".$_FILES['xfields_'.$id]['error'][$iId]."<br/>\n";
                        continue;
                    }
                    if ($_FILES['xfields_'.$id]['size'][$iId] == 0) {
                        //print $iId." >>EMPTY IMAGE<br/>\n";
                        continue;
                    }

                    // Check if we try to overcome limits
                    $currCount = $mysql->record("select count(*) as cnt from ".prefix."_images where (linked_ds = ".intval($dsID).") and (linked_id = ".intval($newsID).") and (plugin = 'xfields') and (pidentity=".db_squote($id).")");
                    if ($currCount['cnt'] >= $data['maxCount'])
                        continue;

                    // Upload file
                    $up = $fmanager->file_upload(
                        array(
                            'dsn' => true,
                            'linked_ds' => $dsID,
                            'linked_id' => $newsID,
                            'type' => 'image',
                            'http_var' => 'xfields_'.$id,
                            'http_varnum' => $iId,
                            'plugin' => 'xfields',
                            'pidentity' => $id,
                            'description' => (isset($_REQUEST['xfields_'.$id.'_adscr']) and is_array($_REQUEST['xfields_'.$id.'_adscr']) and isset($_REQUEST['xfields_'.$id.'_adscr'][$iId]))?($_REQUEST['xfields_'.$id.'_adscr'][$iId]):'',
                        )
                    );

                    // Process upload error
                    if (!is_array($up)) {
                        continue;
                    }

                    //print "<pre>CREATED: ".var_export($up, true)."</pre>";
                    // Check if we need to create preview
                    $mkThumb = $data['imgThumb'];
                    $mkStamp = $data['imgStamp'];
                    $mkShadow = $data['imgShadow'];

                    $stampFileName = '';
                    if (file_exists(root.'trash/'.$config['wm_image'].'.gif')) {
                        $stampFileName = root.'trash/'.$config['wm_image'].'.gif';
                    } else if (file_exists(root.'trash/'.$config['wm_image'])) {
                        $stampFileName = root.'trash/'.$config['wm_image'];
                    }

                    if ($mkThumb) {
                        // Calculate sizes
                        $tsx = $data['thumbWidth'];
                        $tsy = $data['thumbHeight'];

                        if ($tsx < 10) {	$tsx = 150;		}
                        if ($tsy < 10) {	$tsy = 150;		}

                        $thumb = $imanager->create_thumb($config['attach_dir'].$up[2], $up[1], $tsx,$tsy, $config['thumb_quality'], array());
                        //print "<pre>THUMB: ".var_export($thumb, true)."</pre>";
                        if ($thumb) {
                            //print "THUMB_OK<br/>";
                            // If we created thumb - check if we need to transform it
                            $stampThumb = ($data['thumbStamp'] and ($stampFileName != ''))?1:0;
                            $shadowThumb = $data['thumbShadow'];
                            if ($shadowThumb or $stampThumb) {
                                $stamp = $imanager->image_transform(
                                    array(
                                        'image' => $config['attach_dir'].$up[2].'/thumb/'.$up[1],
                                        'stamp' => $stampThumb,
                                        'stamp_transparency' => $config['wm_image_transition'],
                                        'stamp_noerror' => true,
                                        'shadow' => $shadowThumb,
                                        'stampfile' => $stampFileName
                                    )
                                );
                                //print "THUMB [STAMP/SHADOW = (".$stamp.")]<br/>";
                            }
                        }
                    }

                    if ($mkStamp or $mkShadow) {
                        $stamp = $imanager->image_transform(
                        array(
                            'image' => $config['attach_dir'].$up[2].'/'.$up[1],
                            'stamp' => $mkStamp,
                            'stamp_transparency' => $config['wm_image_transition'],
                            'stamp_noerror' => true,
                            'shadow' => $mkShadow,
                            'stampfile' => $stampFileName
                        )
                        );
                        //print "IMG [STAMP/SHADOW = (".var_export($stamp, true).")]<br/>";

                    }

                    // Now write info about image into DB
                    if (is_array($sz = $imanager->get_size($config['attach_dir'].$up[2].'/'.$up[1]))) {
                        $fmanager->get_limits($type);

                        // Gather filesize for thumbinals
                        $thumb_size_x = 0;
                        $thumb_size_y = 0;
                        if (is_array($thumb) and is_readable($config['attach_dir'].$up[2].'/thumb/'.$up[1]) and is_array($szt = $imanager->get_size($config['attach_dir'].$up[2].'/thumb/'.$up[1]))) {
                            $thumb_size_x = $szt[1];
                            $thumb_size_y = $szt[2];
                        }
                        $mysql->query("update ".prefix."_".$fmanager->tname." set width=".db_squote($sz[1]).", height=".db_squote($sz[2]).", preview=".db_squote(is_array($thumb)?1:0).", p_width=".db_squote($thumb_size_x).", p_height=".db_squote($thumb_size_y).", stamp=".db_squote(is_array($stamp)?1:0)." where id = ".db_squote($up[0]));
                    }

                }
            }
        }
    }
}

// Perform replacements while showing news
class XFieldsNewsFilter extends NewsFilter
{

    function __construct()
    {
        Lang::loadPlugin('xfields', 'admin', '', ':');
    }

    function addNewsForm(&$tvars)
    {
        global $catz, $mysql, $config, $twig, $twigLoader;

        // Load config
        $xf = xf_configLoad();
        if (!is_array($xf))
            return false;

        $xfEntries = array();
        $txVars = array();

        if (is_array($xf['news'])) {
            foreach ($xf['news'] as $id => $data) {
                if ($data['disabled'])
                    continue;

                $xfEntry = array(
                    'title' => $data['title'],
                    'id' => $id,
                    'value' => '',
                    'secure_value' => '',
                    'data' => $data,
                    'required' => __('xfields:fld_'.($data['required']?'required':'optional')),
                    'flags' => array(
                        'required' => $data['required']?true:false,
                    ),
                );

                switch ($data['type']) {
                    case 'checkbox':
                        $val = '<input type="checkbox" id="form_xfields_'.$id.'" name="xfields['.$id.']" title="'.$data['title'].'" value="1" '.($data['default']?'checked="checked"':'').'"/>';
                        $xfEntry['input'] = $val;
                        break;
                    case 'text':
                        $val = '<input type="text" name="xfields['.$id.']" id="form_xfields_'.$id.'" title="'.$data['title'].'" value="'.secure_html($data['default']).'" class="form-control" />';
                        $xfEntry['input'] = $val;
                        break;
                    case 'select':
                        $val = '<select name="xfields['.$id.']" id="form_xfields_'.$id.'" class="form-control">';
                        if (!$data['required']) $val .= '<option value=""></option>';
                        if (is_array($data['options']))
                            foreach ($data['options'] as $k => $v)
                                $val .= '<option value="'.secure_html(($data['storekeys'])?$k:$v).'"'.((($data['storekeys'] and $data['default'] == $k)||(!$data['storekeys'] and $data['default'] == $v))?' selected':'').'>'.$v.'</option>';
                        $val .= '</select>';
                        $xfEntry['input'] = $val;
                        break;
                    case 'multiselect':
                        $val = '<select name="xfields['.$id.'][]" id="form_xfields_'.$id.'" multiple="multiple" class="form-control">';
                        if (!$data['required']) $val .= '<option value=""></option>';
                        if (is_array($data['options']))
                            foreach ($data['options'] as $k => $v)
                                $val .= '<option value="'.secure_html(($data['storekeys'])?$k:$v).'"'.((($data['storekeys'] and $data['default'] == $k)||(!$data['storekeys'] and $data['default'] == $v))?' selected':'').'>'.$v.'</option>';
                        $val .= '</select>';
                        $xfEntry['input'] = $val;
                        break;
                    case 'textarea':
                        $val = '<textarea rows="5" name="xfields['.$id.']" id="form_xfields_'.$id.'" class="form-control">'.$data['default'].'</textarea>';
                        $xfEntry['input'] = $val;
                        break;
                    case 'images':
                        $iCount = 0;
                        $input = '';
                        $tVars = array( 'images' => array());
                        // Show entries for allowed number of attaches
                        for ($i = $iCount+1; $i <= intval($data['maxCount']); $i++) {
                            $tImage = array(
                                'number' => $i,
                                'id' => $id,
                                'flags' => array(
                                    'exist' => false,
                                ),
                            );
                            $tVars['images'][] = $tImage;
                        }

                        // Make template
                        $val = $twig->render('plugins/xfields/tpl/ed_entry.image.tpl', $tVars);
                        $xfEntry['input'] = $val;
                        break;
                    default: continue;
                }
                $xfEntries[$data['extends']][] = $xfEntry;
            }
        }
        $xfCategories = array();
        foreach ($catz as $cId => $cData) {
            if(isset($cData['xf_group']))
                $xfCategories[$cData['id']] = $cData['xf_group'];
        }
        // Prepare table data [if needed]
        $flagTData = false;
        if (isset($xf['tdata']) and is_array($xf['tdata'])) {
            // Data are not provisioned
            $tlist = array();
            // Prepare config
            $tclist = array();
            $thlist = array();
            foreach ($xf['tdata'] as $fId => $fData) {
                if ($fData['disabled'])
                    continue;
                $flagTData = true;
                $tclist[$fId] = array(
                    'title' => $fData['title'],
                    'required' => $fData['required'],
                    'type' => $fData['type'],
                    'default' => $fData['default'],
                );
                $thlist[] = array(
                    'id' => $fId,
                    'title' => $fData['title'],
                );
                if ($fData['type'] == 'select') {
                    $tclist[$fId]['storekeys'] = $fData['storekeys'];
                    $tclist[$fId]['options'] = $fData['options'];
                }
            }
            // Prepare personal [group] variables
            $txVars = array(
                'xtableConf' => json_encode($tclist),
                'xtableVal' => json_encode(isset($_POST['xftable'])?$_POST['xftable']:$tlist),
                'xtableHdr' => !empty($thlist) ? $thlist : null,
                'xtablecnt' => count(!empty($thlist) ? $thlist : 0),
                'flags' => array(
                    'tdata' => $flagTData,
                ),
            );
            if($flagTData) {
                $extends = 'owner';
                $tvars['extends'][$extends][] = array(
                    'table' => true,
                    'header_title' => __('xfields:tdata_title'),
                    'body' => $twig->render('plugins/xfields/tpl/news.tdata.tpl', $txVars),
                    );
            }
        } else {
            $txVars = array(
                'xtableConf' => json_encode(array()),
                'xtableVal' => json_encode(array()),
                );
        }
        // Prepare personal [group] variables
        $txVars['xfGC'] = json_encode(!empty($xf['grp.news']) ? $xf['grp.news'] : array());
        $txVars['xfCat'] = json_encode($xfCategories);
        $txVars['xfList'] = json_encode(array_keys($xf['news']));
        foreach ($xfEntries as $k => $v) {
            $txVars['entries'] = $v;
            $txVars['entryCount'] = count($v);
            $txVars['extends'] = $k;
            // Render block
            $tvars['extends'][$k][] = array(
                'header_title' => __('xfields:header_title'),
                'body' => $twig->render('plugins/xfields/tpl/news.edit.tpl', $txVars),
                );
        }
        unset($txVars['entries']);
        unset($txVars['extends']);
        // Render general part [with JavaScript]
        $tvars['extends']['js'][] = array(
                'body' => $twig->render('plugins/xfields/tpl/news.general.tpl', $txVars),
                );

        return 1;
    }

    function addNews(&$tvars, &$SQL)
    {
        global $twig, $twigLoader;

        // Load config
        $xf = xf_configLoad();
        if (!is_array($xf))
            return 1;

        $rcall = $_REQUEST['xfields'];
        if (!is_array($rcall)) $rcall = array();

        $xdata = array();
        foreach ($xf['news'] as $id => $data) {
            if ($data['disabled'])
                continue;

            if ($data['type'] == 'images') { continue; }
            // Fill xfields. Check that all required fields are filled
            if ($rcall[$id] != '') {
                $xdata[$id] = $rcall[$id];
            } else if ($data['required']) {
                msg(array('type' => 'danger', 'message' => str_replace('{field}', $id, __('xfields:msge_emptyrequired'))));
                return 0;
            }
            // Check if we should save data into separate SQL field
            if ($data['storage'] and ($rcall[$id] != ''))
                $SQL['xfields_'.$id] = $rcall[$id];
        }
        
        //var_dump($xdata);

     $SQL['xfields'] = xf_encode($xdata);
        return 1;
    }

    function addNewsNotify(&$tvars, $SQL, $newsID)
    {
        global $mysql;

        // Load config
        $xf = xf_configLoad();
        if (!is_array($xf))
            return 1;

        xf_modifyAttachedImages(1, $newsID, $xf, array());

        // Scan fields and check if we have attached images for fields with type 'images'
        $haveImages = false;
        foreach ($xf['news'] as $fid => $fval) {
            if ($fval['type'] == 'images') {
                $haveImages = true;
                break;
            }
        }

        if ($haveImages) {
            // Get real ID's of attached images and print here
            $idlist = array();

            foreach ($mysql->select("select id, plugin, pidentity from ".prefix."_images where (linked_ds = 1) and (linked_id = ".db_squote($newsID).")") as $irec) {
                if ($irec['plugin'] == 'xfields') {
                    $idlist[$irec['pidentity']] []= $irec['id'];
                }
            }

            // Decode xfields
            $xdata = xf_decode($SQL['xfields']);
            //print "<pre>IDLIST: ".var_export($idlist, tru)."</pre>";
            // Scan for fields that should be configured to have attached images
            foreach ($xf['news'] as $fid => $fval) {
                if (($fval['type'] == 'images')&&(isset($idlist[$fid]))) {
                    $xdata[$fid] = join(",", $idlist[$fid]);
                }
            }
            $mysql->query("update ".prefix."_news set xfields = ".db_squote(xf_encode($xdata))." where id = ".db_squote($newsID));
        }

        // Prepare table data [if needed]
        if (isset($xf['tdata']) and is_array($xf['tdata']) and isset($_POST['xftable']) and is_array($xft = json_decode($_POST['xftable'], true))) {
            //print "<pre>[".(is_array($xft)?'ARR':'NOARR')."]INCOMING ARRAY: ".var_export($xft, true)."</pre>";
            $recList = array();
            $queryList = array();
            // SCAN records
            foreach ($xft as $k => $v) {
                if (is_array($v) and isset($v['#id'])) {
                    $editMode = 0;

                    $tRec = array('xfields' => array());
                    foreach ($xf['tdata'] as $fId => $fData) {
                        if ($fData['storage']) {
                            $tRec['xfields_'.$fId] = db_squote($v[$fId]);
                        }
                        $tRec['xfields'][$fId] = $v[$fId];
                    }

                    $tRec['xfields'] = db_squote(serialize($tRec['xfields']));

                    // Now update record info
                    $query = "insert into ".prefix."_xfields (".join(", ", array_keys($tRec)).", linked_ds, linked_id) values (".join(", ", array_values($tRec)).", 1, ".(intval($newsID)).")";
                    //print "SQL: $query <br/>\n";
                    $queryList []= $query;
                    //$mysql->query($query);

                    //print "GOT LINE:<pre>".var_export($tRec, true)."</pre>";
                }
            }

            // Execute queries
            foreach ($queryList as $query) {
                $mysql->query($query);
            }
        }

        return 1;
    }

    function editNewsForm($newsID, $SQLold, &$tvars)
    {
        global $catz, $mysql, $config, $twig, $twigLoader;

        // Load config
        $xf = xf_configLoad();
        if (!is_array($xf))
            return false;

        // Fetch xfields data
        $xdata = xf_decode($SQLold['xfields']);
        if (!is_array($xdata))
            return false;

        $xfEntries = array();
        $txVars = array();

        if (is_array($xf['news'])) {
            foreach ($xf['news'] as $id => $data) {
                if ($data['disabled'])
                    continue;

                $xfEntry = array(
                    'title' => $data['title'],
                    'id' => $id,
                    'required' => __('xfields:fld_'.($data['required']?'required':'optional')),
                    'flags' => array(
                        'required' => $data['required']?true:false,
                    ),
                );
                switch ($data['type']) {
                    case 'checkbox':
                        $val = '<input type="checkbox" id="form_xfields_'.$id.'" name="xfields['.$id.']" title="'.$data['title'].'" value="1" '.($xdata[$id]?'checked="checked"':'').'"/>';
                        $xfEntry['input'] = $val;
                        break;
                    case 'text' :
                        $val = '<input type="text" name="xfields['.$id.']" id="form_xfields_'.$id.'" title="'.$data['title'].'" value="'.(!empty($xdata[$id]) ? secure_html($xdata[$id]) : '').'" class="form-control" />';
                        $xfEntry['input'] = $val;
                        break;
                    case 'select':
                        $val = '<select name="xfields['.$id.']" id="form_xfields_'.$id.'" class="form-control">';
                        if (!$data['required']) $val .= '<option value="">&nbsp;</option>';
                        if (is_array($data['options']))
                            foreach ($data['options'] as $k => $v) {
                                $val .= '<option value="'.secure_html(($data['storekeys'])?$k:$v).'"'.((($data['storekeys'] and ($xdata[$id] == $k))||(!$data['storekeys'] and ($xdata[$id] == $v)))?' selected':'').'>'.$v.'</option>';
                            }
                        $val .= '</select>';
                        $xfEntry['input'] = $val;
                        break;
                    case 'multiselect':
                        $val = '<select name="xfields['.$id.'][]" id="form_xfields_'.$id.'" multiple="multiple" class="form-control">';
                        if (!$data['required']) $val .= '<option value=""></option>';
                        if (is_array($data['options']))
                            foreach ($data['options'] as $k => $v) {
                                var_dump();
                                $val .= '<option value="'.secure_html(($data['storekeys'])?$k:$v).'"'.((($data['storekeys'] and (in_array($k, $xdata[$id])))||(!$data['storekeys'] and (in_array($v, $xdata[$id]))))?' selected':'').'>'.$v.'</option>';
                            }
                        $val .= '</select>';
                        $xfEntry['input'] = $val;
                        break;
                    case 'textarea':
                        $val = '<textarea rows="5" name="xfields['.$id.']" id="form_xfields_'.$id.'" class="form-control">'.$xdata[$id].'</textarea>';
                        $xfEntry['input'] = $val;
                        break;
                    case 'images':
                        $iCount = 0;
                        $input = '';
                        $tVars = array( 'images' => array());
                        // First - show already attached images
                        if (is_array($SQLold['#images'])) {
                            foreach ($SQLold['#images'] as $irow) {
                                // Skip images, that are not related to current field
                                if (($irow['plugin'] != 'xfields') or ($irow['pidentity'] != $id)) continue;

                                // Show attached image
                                $iCount++;

                                $tImage = array(
                                    'number' => $iCount,
                                    'id' => $id,
                                    'preview' => array(
                                        'width' => $irow['p_width'],
                                        'height' => $irow['p_height'],
                                        'url' 		=> $config['attach_url'].'/'.$irow['folder'].'/thumb/'.$irow['name'],
                                    ),
                                    'image' => array(
                                        'id' => $irow['id'],
                                        'number' => $iCount,
                                        'url' => $config['attach_url'].'/'.$irow['folder'].'/'.$irow['name'],
                                        'width' => $irow['width'],
                                        'height' => $irow['height'],
                                    ),
                                    'flags' => array(
                                        'preview' => $irow['preview']?true:false,
                                        'exist' => true,
                                    ),
                                    'description' => secure_html($irow['description']),
                                );
                                $tVars['images'][] = $tImage;
                            }
                        }

                        // Second - show entries for allowed number of attaches
                        for ($i = $iCount+1; $i <= intval($data['maxCount']); $i++) {
                            $tImage = array(
                                'number' => $i,
                                'id' => $id,
                                'flags' => array(
                                    'exist' => false,
                                ),
                            );
                            $tVars['images'][] = $tImage;
                        }

                        // Make template
                        $val = $twig->render('plugins/xfields/tpl/ed_entry.image.tpl', $tVars);
                        $xfEntry['input'] = $val;
                        break;
                    default: continue;
                }
                $xfEntries[$data['extends']][] = $xfEntry;
            }
        }
        $xfCategories = array();
        foreach ($catz as $cId => $cData) {
            if(isset($cData['xf_group']))
                $xfCategories[$cData['id']] = $cData['xf_group'];
        }

        // Prepare table data [if needed]
        $flagTData = false;
        if (isset($xf['tdata']) and is_array($xf['tdata']) and count($xf['tdata'])) {
            // Load table data for specific news
            $tlist = array();
            foreach ($mysql->select("select * from ".prefix."_xfields where (linked_ds = 1) and (linked_id = ".db_squote($newsID).")") as $trow) {
                $ts = unserialize($trow['xfields']);
                $tEntry = array('#id' => $trow['id']);
                // Scan every field for value
                foreach ($xf['tdata'] as $fId => $fData) {
                    $fValue = '';
                    if (is_array($ts) and isset($ts[$fId])) {
                        $fValue = $ts[$fId];
                    } elseif (isset($trow['xfields_'.$fId])) {
                        $fValue = $trow['xfields_'.$fId];
                    }
                    $tEntry[$fId] = $fValue;
                }
                $tlist []= $tEntry;
            }
            // Prepare config
            $tclist = array();
            $thlist = array();
            foreach ($xf['tdata'] as $fId => $fData) {
                if ($fData['disabled'])
                    continue;
                $flagTData = true;
                $tclist[$fId] = array(
                    'title' => $fData['title'],
                    'required' => $fData['required'],
                    'type' => $fData['type'],
                    'default' => $fData['default'],
                );
                $thlist[] = array(
                    'id' => $fId,
                    'title' => $fData['title'],
                );
                if ($fData['type'] == 'select') {
                    $tclist[$fId]['storekeys'] = $fData['storekeys'];
                    $tclist[$fId]['options'] = $fData['options'];
                }
            }
            // Prepare personal [group] variables
            $txVars = array(
                'xtableConf' => json_encode(!empty($tclist) ? $tclist : array()),
                'xtableVal' => json_encode(!empty($tlist) ? $tlist : array()),
                'xtableHdr' => !empty($thlist) ? $thlist : null,
                'xtablecnt' => count(!empty($thlist) ? $thlist : 0),
                'flags' => array(
                    'tdata' => $flagTData,
                ),
            );
            if($flagTData) {
                $extends = 'owner';
                $tvars['extends'][$extends][] = array(
                    'table' => true,
                    'header_title' => __('xfields:tdata_title'),
                    'body' => $twig->render('plugins/xfields/tpl/news.tdata.tpl', $txVars),
                    );
            }
        } else {
            $txVars = array(
                'xtableConf' => json_encode(array()),
                'xtableVal' => json_encode(array()),
                );
        }
        // Prepare personal [group] variables
        $txVars['xfGC'] = json_encode(!empty($xf['grp.news']) ? $xf['grp.news'] : array());
        $txVars['xfCat'] = json_encode($xfCategories);
        $txVars['xfList'] = json_encode(array_keys($xf['news']));
        foreach ($xfEntries as $k => $v) {
            $txVars['entries'] = $v;
            $txVars['entryCount'] = count($v);
            $txVars['extends'] = $k;
            // Render block
            $tvars['extends'][$k][] = array(
                'header_title' => __('xfields:header_title'),
                'body' => $twig->render('plugins/xfields/tpl/news.edit.tpl', $txVars),
                );
        }
        unset($txVars['entries']);
        unset($txVars['extends']);
        // Render general part [with JavaScript]
        $tvars['extends']['js'][] = array(
                'body' => $twig->render('plugins/xfields/tpl/news.general.tpl', $txVars),
                );

        return 1;
    }

    function editNews($newsID, $SQLold, &$SQLnew, &$tvars)
    {
        global $config, $mysql;

        //	print "<pre>POST VARS: ".var_export($_POST, true)."</pre>";

        // Load config
        $xf = xf_configLoad();
        if (!is_array($xf))
            return 1;

        $rcall = (!empty($_POST['xfields']) and is_array($_POST['xfields'])) ? $_POST['xfields']: array();

        // Decode previusly stored data
        $oldFields = xf_decode($SQLold['xfields']);

        // Manage attached images
        xf_modifyAttachedImages(1, $newsID, $xf, $SQLold['#images']);

        $xdata = array();

        // Scan fields and check if we have attached images for fields with type 'images'
        $haveImages = false;
        foreach ($xf['news'] as $fid => $fval) {
            if ($fval['type'] == 'images') {
                $haveImages = true;
                break;
            }
        }

        if ($haveImages) {
            // Get real ID's of attached images and print here
            $idlist = array();
            foreach ($mysql->select("select id, plugin, pidentity from ".prefix."_images where (linked_ds = 1) and (linked_id = ".db_squote($newsID).")") as $irec) {
                if ($irec['plugin'] == 'xfields') {
                    $idlist[$irec['pidentity']] []= $irec['id'];
                }
            }

            // Scan for fields that should be configured to have attached images
            foreach ($xf['news'] as $fid => $fval) {
                if (($fval['type'] == 'images') && (is_array($idlist[$fid]))) {
                    $xdata[$fid] = join(",", $idlist[$fid]);
                }
            }
        }

        foreach ($xf['news'] as $id => $data) {
            // Attached images are processed in special way
            if ($data['type'] == 'images') {
                continue;
            }

            // Skip disabled fields
            if ($data['disabled']) {
                $xdata[$id] = $oldFields[$id];
                continue;
            }

            if (isset($rcall[$id]) and $rcall[$id] != '') {
                $xdata[$id] = $rcall[$id];
            } elseif ($data['required']) {
                msg(array('type' => 'danger', 'message' => str_replace('{field}', $id, __('xfields:msge_emptyrequired'))));
                return 0;
            }
            // Check if we should save data into separate SQL field
            if (1 == $data['storage']) {
                $SQLnew['xfields_'.$id] = $rcall[$id];
            }
        }

        // Prepare table data [if needed]
        $haveTable = false;

        if (isset($xf['tdata']) and is_array($xf['tdata']) and isset($_POST['xftable']) and is_array($xft = json_decode($_POST['xftable'], true))) {
            //print "<pre>[".(is_array($xft)?'ARR':'NOARR')."]INCOMING ARRAY: ".var_export($xft, true)."</pre>";
            $recList = array();
            $queryList = array();
            // SCAN records
            foreach ($xft as $k => $v) {
                if (is_array($v) and isset($v['#id'])) {
                    $editMode = 0;
                    $tOldRec = array();
                    $tOldRecX = array();
                    if (intval($v['#id'])) {
                        $recList []= intval($v['#id']);
                        $editMode = 1;
                        $tOldRec = $mysql->record("select * from ".prefix."_xfields where (id = ".intval($v['#id']).") and (linked_ds = 1) and (linked_id = ".intval($newsID).")");
                        $tOldRecX = unserialize($tOldRec['xfields']);
                    }

                    $tRec = array('xfields' => array());
                    foreach ($xf['tdata'] as $fId => $fData) {
                        // Manage disabled fields
                        if ($fData['disabled']) {
                            $tRec['xfields'][$fId] = $tOldRecX[$fId];
                            continue;
                        }

                        if ($fData['storage']) {
                            $tRec['xfields_'.$fId] = db_squote($v[$fId]);
                        }
                        $tRec['xfields'][$fId]= $v[$fId];
                    }

                    $tRec['xfields'] = db_squote(serialize($tRec['xfields']));

                    // Now update record info
                    $haveTable = true;
                    if ($editMode) {
                        $vt = array();
                        foreach ($tRec as $kx => $vx) { $vt []= $kx." = ".$vx;	}

                        $query = "update ".prefix."_xfields set ".join(", ", $vt)." where (id = ".intval($v['#id']).") and (linked_ds = 1) and (linked_id = ".intval($newsID).")";
                        //print "SQL: $query <br/>\n";
                        $queryList []= $query;
                        //$mysql->query($query);
                    } else {

                        $query = "insert into ".prefix."_xfields (".join(", ", array_keys($tRec)).", linked_ds, linked_id) values (".join(", ", array_values($tRec)).", 1, ".(intval($newsID)).")";
                        //print "SQL: $query <br/>\n";
                        $queryList []= $query;
                        //$mysql->query($query);
                    }

                    //print "GOT LINE:<pre>".var_export($tRec, true)."</pre>";
                }
            }
            // Now delete old lines
            if (count($recList)) {
                $query = "delete from ".prefix."_xfields where (linked_ds = 1) and (linked_id = ".intval($newsID).") and id not in (".join(", ", $recList).")";
            } else {
                $query = "delete from ".prefix."_xfields where (linked_ds = 1) and (linked_id = ".intval($newsID).")";
            }
            $mysql->query($query);

            // Execute queries
            foreach ($queryList as $query) {
                $mysql->query($query);
            }

        }
        // Save info about table data
        if ($haveTable)
            $xdata['#table'] = 1;

        $SQLnew['xfields'] = xf_encode($xdata);
        return 1;
    }

    // Delete news notifier [ after news is deleted ]
    function deleteNewsNotify($newsID, $SQLnews)
    {
        global $mysql;

        $query = "delete from ".prefix."_xfields where (linked_ds = 1) and (linked_id = ".intval($newsID).")";
        $mysql->query($query);

        return 1;
    }

    // Called before showing list of news
    function onBeforeShowlist($callingParams)
    {
        if (isset($linkedFiles['data']) and is_array($linkedFiles['data'])) {
            // Check for news that have attached TABLE data and load this table into memory
            // ...
        }
    }

    // Show news call :: processor (call after all processing is finished and before show)
    function showNews($newsID, $SQLnews, &$tvars, $mode = array())
    {
        global $mysql, $config, $twigLoader, $twig, $PFILTERS, $parse;

        // Try to load config. Stop processing if config was not loaded
        if (($xf = xf_configLoad()) === false) return;

        $fields = xf_decode($SQLnews['xfields']);
        $content = $SQLnews['content'];

        // Check if we have at least one `image` field and load TWIG template if any
        if (isset($xf['news']) and is_array($xf['news']))
            foreach ($xf['news'] as $k => $v) {
                if ($v['type'] == 'images') {

                    // Yes, we have it!
                    $conversionParams = array();
                    $imagesTemplateFileName = 'plugins/xfields/tpl/news.show.images.tpl';
                    if (isset($conversionConfig))
                        $twigLoader->setConversion($imagesTemplateFileName, $conversionConfig);
                    $xtImages = $twig->loadTemplate($imagesTemplateFileName);
                    break;
                }
            }

        // Show extra fields if we have it
        if (isset($xf['news']) and is_array($xf['news']))
            foreach ($xf['news'] as $k => $v) {
                $kp = preg_quote($k, "#");
                $xfk = isset($fields[$k])?$fields[$k]:'';

                // TWIG stype data fill
                $tvars['vars']['p']['xfields'][$k]['type'] = $v['type'];
                $tvars['vars']['p']['xfields'][$k]['title'] = secure_html($v['title']);

                // Our behaviour depends on field type
                if ($v['type'] == 'images') {
                    // Check if there're attached images
                    $imglist = array();

                    if ($xfk and count($ilist = explode(',', $xfk))) {
                        // Check if we have already preloaded (by engine) images
                        $ilk = array();
                        foreach ($ilist as $irec) {
                            if (isset($mode['linkedImages']['data'][$irec])) {
                                $imglist []= $mode['linkedImages']['data'][$irec];
                            } else {
                                $ilk []= $irec;
                            }
                        }

                        // Check if we have some not loaded news
                        if (count($ilk) and count($timglist = $mysql->select("select * from ".prefix."_images where id in (".$xfk.")"))) {
                            $imglist = array_merge($imglist, $timglist);
                            unset($timglist);
                        }

                    }

//					if ($xfk and count($ilist = explode(',', $xfk)) and count($imglist = $mysql->select("select * from ".prefix."_images where id in (".$xfk.")"))) {
                    if (count($imglist)) {
                    // Yes, show field block
                        $tvars['regx']["#\[xfield_".$kp."\](.*?)\[/xfield_".$kp."\]#is"] = '$1';
                        $tvars['regx']["#\[nxfield_".$kp."\](.*?)\[/nxfield_".$kp."\]#is"] = '';

                        // Scan for images and prepare data for template show
                        $tiVars = array(
                            'fieldName' => $k,
                            'fieldTitle' => secure_html($v['title']),
                            'fieldType' => $v['type'],
                            'entriesCount' => count($imglist),
                            'entries' => array(),
                            'execStyle' => $mode['style'],
                            'execPlugin' => isset($mode['plugin']) ? $mode['plugin'] : null,
                        );
                        foreach ($imglist as $imgInfo) {
                            $tiEntry = array(
                                'url' => ($imgInfo['storage']?$config['attach_url']:$config['images_url']).'/'.$imgInfo['folder'].'/'.$imgInfo['name'],
                                'width' => $imgInfo['width'],
                                'height' => $imgInfo['height'],
                                'pwidth' => $imgInfo['p_width'],
                                'pheight' => $imgInfo['p_height'],
                                'name' => $imgInfo['name'],
                                'origName' => secure_html($imgInfo['orig_name']),
                                'description' => secure_html($imgInfo['description']),

                                'flags' => array(
                                    'hasPreview' => $imgInfo['preview'],
                                ),
                            );

                            if ($imgInfo['preview']) {
                                $tiEntry['purl'] = ($imgInfo['storage']?$config['attach_url']:$config['images_url']).'/'.$imgInfo['folder'].'/thumb/'.$imgInfo['name'];
                            }

                            $tiVars['entries'] []= $tiEntry;
                        }

                        // TWIG based variables
                        $tvars['vars']['p']['xfields'][$k]['entries'] = $tiVars['entries'];
                        $tvars['vars']['p']['xfields'][$k]['count'] = count($tiVars['entries']);

                        $xv = $xtImages->render($tiVars);

                        $tvars['vars']['p']['xfields'][$k]['value'] = $xv;
                        $tvars['vars']['[xvalue_'.$k.']'] = $xv;

                    } else {
                        // TWIG based variables
                        $tvars['vars']['p']['xfields'][$k]['value'] = '';
                        $tvars['vars']['p']['xfields'][$k]['count'] = 0;
                        $tvars['vars']['p']['xfields'][$k]['entries'] = array();

                        // General variables
                        $tvars['regx']["#\[xfield_".$kp."\](.*?)\[/xfield_".$kp."\]#is"] = '';
                        $tvars['regx']["#\[nxfield_".$kp."\](.*?)\[/nxfield_".$kp."\]#is"] = '$1';
                        $tvars['vars']['[xvalue_'.$k.']'] = '';
                    }
                } else {
                    $tvars['regx']["#\[xfield_".$kp."\](.*?)\[/xfield_".$kp."\]#is"] = ($xfk == "")?"":"$1";
                    $tvars['regx']["#\[nxfield_".$kp."\](.*?)\[/nxfield_".$kp."\]#is"] = ($xfk == "")?"$1":"";

                    // Process `HTML` support feature
                    if ((!$v['html_support'])&&(($v['type'] == 'textarea')||($v['type'] == 'text'))) {
                        $xfk = str_replace("<","&lt;",$xfk);
                    }

                    // Parse BB code [if required]
                    if ($config['use_bbcodes'] and $v['bb_support']) {
                        $xfk = $parse-> bbcodes($xfk);
                    }

                    // Process formatting
                    if (($v['type'] == 'textarea') and (!$v['noformat'])) {
                        $xfk = (str_replace("\n","<br/>\n",$xfk).(mb_strlen($xfk, 'UTF-8') ? '<br/>' : ''));
                    }
                    $tvars['vars']['p']['xfields'][$k]['value'] = $xfk;
                    $tvars['vars']['[xvalue_'.$k.']'] = $xfk;
                }
            }

        // Show table if we have it
        if (isset($xf['tdata']) and is_array($xf['tdata']) and isset($fields['#table']) and ($fields['#table'] == 1)) {
            // Yes, we have table. Display it!

            // Prepare conversion table
            $conversionConfig = array(
                    '[entries]' => '{% for entry in entries %}',
                    '[/entries]' => '{% endfor %}',
            );

            $xrecs = array();
            $npp = 1;
            foreach ($mysql->select("select * from ".prefix."_xfields where (linked_ds = 1) and (linked_id = ".db_squote($newsID).") order by id", 1) as $trec) {
                $xrec = array(
                    'num' => ($npp++),
                    'id' => $trec['id'],
                    'flags' => array(),
                );

                foreach ($xf['tdata'] as $tid => $tval) {
                    // Skip disabled
                    if ($tval['disabled'])
                        continue;

                    // Populate field data
                    $drec = unserialize($trec['xfields']);
                    $xrec['field_'.$tid] = $drec[$tid];
                    $xrec['flags']['field_'.$tid] = ($drec[$tid] != '')?1:0;

                    $conversionConfig['{entry_field_'.$tid.'}'] = '{{ entry.field_'.$tid.' }}';
                }

                // Process filters (if any)
                if (isset($PFILTERS['xfields']) and is_array($PFILTERS['xfields']))
                    foreach ($PFILTERS['xfields'] as $k => $v) { $v->showTableEntry($newsID, $SQLnews, $trec, $xrec); }

                $xrecs []= $xrec;
            }

            // Search for news.tdata.tpl template file
            $tpath = plugin_locateTemplates('xfields', array('news.tdata'));

            // Show table
            $templateName = $tpath['news.tdata'].'news.tdata.tpl';
            $twigLoader->setConversion($templateName, $conversionConfig);

            $xt = $twig->loadTemplate($templateName);
            $tvars['vars']['plugin_xfields_table'] = $xt->render(array('entries' => $xrecs));

            $tvars['vars']['p']['xfields']['_table']['countRec'] = count($xrecs);
            $tvars['vars']['p']['xfields']['_table']['data'] = $xrecs;

        } else {
            $tvars['vars']['plugin_xfields_table'] = '';
            $tvars['vars']['p']['xfields']['_table']['countRec']	= 0;
        }

        $SQLnews['content'] = $content;
    }
}

// Manage uprofile modifications
if (pluginIsActive('uprofile')) {
    
    // Load CORE Plugin
    $cPlugin = CPlugin::instance();
    // preload required libraries
    $cPlugin->loadLibrary('uprofile', 'lib');

    class XFieldsUPrifileFilter extends p_uprofileFilter
    {

        function __construct()
        {
            Lang::loadPlugin('xfields', 'admin', '', ':');
        }

        function editProfileForm($userID, $SQLrow, &$tvars)
        {
            global $catz, $mysql, $config, $twig, $twigLoader, $DSlist, $userROW;
            
            // Load config
            $xf = xf_configLoad();
            if (!is_array($xf))
                return false;

            // Fetch xfields data
            $xdata = xf_decode($SQLrow['xfields']);
            if (!is_array($xdata))
                return false;

            $output = '';
            $xfEntries = array();
            $xfList = array();

            foreach ($xf['users'] as $id => $data) {
                if ($data['disabled'])
                    continue;

                //print "FLD: [$id]<br>\n";
                $xfEntry = array(
                    'title' => $data['title'],
                    'id' => $id,
                    'value' => !empty($xdata[$id]) ? $xdata[$id] : null,
                    'secure_value' => !empty($xdata[$id]) ? secure_html($xdata[$id]) : null,
                    'data' => $data,
                    'required' => __('xfields:fld_'.($data['required']?'required':'optional')),
                    'flags' => array(
                        'required' => $data['required']?true:false,
                    ),
                );
                switch ($data['type']) {
                    case 'checkbox':
                        $val = '<input type="checkbox" id="form_xfields_'.$id.'" name="xfields['.$id.']" title="'.$data['title'].'" value="1" '.($data['default']?'checked="checked"':'').'"/>';
                        $xfEntry['input'] = $val;
                        break;
                    case 'text':
                        $val = '<input type="text" name="xfields['.$id.']" id="form_xfields_'.$id.'" title="'.$data['title'].'" value="'.secure_html($xdata[$id]).'" class="form-control" />';
                        $xfEntry['input'] = $val;
                        break;
                    case 'select':
                        $val = '<select name="xfields['.$id.']" id="form_xfields_'.$id.'" class="form-control">';
                        if (!$data['required']) $val .= '<option value="">&nbsp;</option>';
                        if (is_array($data['options']))
                            foreach ($data['options'] as $k => $v) {
                                $val .= '<option value="'.secure_html((!empty($data['storekeys'])) ? $k : $v ).'"'.
                                (((isset($xdata[$id]) and isset($data['storekeys']) and ($xdata[$id] == $k)) or (empty($data['storekeys']) and ($xdata[$id] == $v))) ? ' selected' : '').
                                '>'.$v.'</option>';
                            }
                        $val .= '</select>';
                        $xfEntry['input'] = $val;
                        break;
                    case 'textarea':
                        $val = '<textarea rows="5" name="xfields['.$id.']" id="form_xfields_'.$id.'" class="form-control">'.$xdata[$id].'</textarea>';
                        $xfEntry['input'] = $val;
                        break;
                    case 'images':
                        // First - show already attached images
                        $iCount = 0;
                        $input = '';
                        $tVars = array( 'images' => array());
                        $images = (isset($SQLrow['#images']) and is_array($SQLrow['#images'])) ? $SQLrow['#images'] : $userROW['#images'];
                        if (is_array($images)) {
                            foreach ($images as $irow) {
                                // Skip images, that are not related to current field
                                if (($irow['plugin'] != 'xfields') or ($irow['pidentity'] != $id))
                                    continue;

                                // Show attached image
                                $iCount++;
                                $tImage = array(
                                    'number' => $iCount,
                                    'id' => $id,
                                    'preview' => array(
                                        'width' => $irow['p_width'],
                                        'height' => $irow['p_height'],
                                        'url' 		=> $config['attach_url'].'/'.$irow['folder'].'/thumb/'.$irow['name'],
                                    ),
                                    'image' => array(
                                        'id' => $irow['id'],
                                        'number' => $iCount,
                                        'url' => $config['attach_url'].'/'.$irow['folder'].'/'.$irow['name'],
                                        'width' => $irow['width'],
                                        'height' => $irow['height'],
                                    ),
                                    'description' => secure_html($irow['description']),
                                    'flags' => array(
                                        'preview' => $irow['preview']?true:false,
                                        'exist' => true,
                                    ),
                                );
                                $tVars['images'][] = $tImage;
                            }
                        }

                        // Second - show entries for allowed number of attaches
                        for ($i = $iCount+1; $i <= intval($data['maxCount']); $i++) {
                            $tImage = array(
                                'number' => $i,
                                'id' => $id,
                                'flags' => array(
                                    'exist' => false,
                                ),
                            );
                            $tVars['images'][] = $tImage;
                        }

                        // Make template
                        $xt = $twig->loadTemplate('plugins/xfields/tpl/ed_entry.image.tpl');
                        $val = $xt->render($tVars);
                        $xfEntry['input'] = $val;
                        break;
                    default:
                        continue;

                }
                $xfEntries[$data['extends']][] = $xfEntry;
                $xfList[$id] = $xfEntry;

            }

            // Prepare configuration array
            $tVars = array();

            // Area 0 should always be configured
            if (!isset($xfEntries[0])) {
                $xfEntries[0] = array();
            }

            foreach ($xfEntries as $k => $v) {
                // Check if we have template for specific area, elsewhere - use basic [0] template
                $templateName = 'plugins/xfields/tpl/uprofile.edit.'.(file_exists(root.'plugins/xfields/tpl/uprofile.edit.'.$k.'.tpl')?$k:'0').'.tpl';

                $xt = $twig->loadTemplate($templateName);
                $tVars['entries'] = $v;
                $tVars['entryCount'] = count($v);
                $tVars['extends'] = $k;

                // Render block
                $render = $xt->render($tVars);
                //$tvars['plugin_xfields_'.$k] .= $render;
                $tvars['p']['xfields'][$k] .= $render;
            }
            $tvars['p']['xfields']['fields'] = $xfList;

            return 1;

        }

        function editProfile($userID, $SQLrow, &$SQLnew)
        {
            global $config, $mysql, $DSlist;

            //print "<pre>editProfile() POST VARS: ".var_export($_POST, true)."</pre>";

            // Load config
            $xf = xf_configLoad();
            if (!is_array($xf))
                return 1;

            $rcall = $_POST['xfields'];
            if (!is_array($rcall)) $rcall = array();

            // Decode previusly stored data
            $oldFields = xf_decode($SQLrow['xfields']);

            // Manage attached images
            xf_modifyAttachedImages($DSlist['users'], $userID, $xf, $SQLrow['#images']);

            $xdata = array();
            //print "XF[users]: <pre>".var_export($xf['users'], true)."</pre>";
            // Scan fields and check if we have attached images for fields with type 'images'
            $haveImages = false;
            foreach ($xf['users'] as $fid => $fval) {
                if ($fval['type'] == 'images') {
                    $haveImages = true;
                    break;
                }
            }

            if ($haveImages) {
                // Get real ID's of attached images and print here
                $idlist = array();
                foreach ($mysql->select("select id, plugin, pidentity from ".prefix."_images where (linked_ds = ".$DSlist['users'].") and (linked_id = ".db_squote($userID).")") as $irec) {
                    if ($irec['plugin'] == 'xfields') {
                        $idlist[$irec['pidentity']] []= $irec['id'];
                    }
                }

                // Scan for fields that should be configured to have attached images
                foreach ($xf['users'] as $fid => $fval) {
                    if (($fval['type'] == 'images') and (is_array($idlist[$fid]))) {
                        $xdata[$fid] = join(",", $idlist[$fid]);
                    }
                }
            }

            foreach ($xf['users'] as $id => $data) {
                // Attached images are processed in special way
                if ($data['type'] == 'images') {
                    continue;
                }

                // Skip disabled fields
                if ($data['disabled']) {
                    $xdata[$id] = $SQLrow[$id];
                    continue;
                }

                if ($rcall[$id] != '') {
                    $xdata[$id] = $rcall[$id];
                } else if ($data['required']) {
                    msg(array('type' => 'danger', 'message' => str_replace('{field}', $id, __('xfields:msge_emptyrequired'))));
                    return 0;
                }
                // Check if we should save data into separate SQL field
                if ($data['storage'])
                    $SQLnew['xfields_'.$id] = $rcall[$id];
            }

            $SQLnew['xfields'] = xf_encode($xdata);

            return 1;
        }

        function showProfile($userID, $SQLrow, &$tvars)
        {
            global $mysql, $config, $twig, $twigLoader;

            // Try to load config. Stop processing if config was not loaded
            if (($xf = xf_configLoad()) === false) return;

            $fields = xf_decode($SQLrow['xfields']);

            // Check if we have at least one `image` field and load TWIG template if any
            if (is_array($xf['users'])) {
                foreach ($xf['users'] as $k => $v) {
                    if ($v['type'] == 'images') {

                        // Yes, we have it!
                        $conversionParams = array();
                        $imagesTemplateFileName = 'plugins/xfields/tpl/profile.show.images.tpl';
                        if (isset($conversionConfig))
                            $twigLoader->setConversion($imagesTemplateFileName, $conversionConfig);
                        $xtImages = $twig->loadTemplate($imagesTemplateFileName);
                        break;
                    }
                }
            }

            // Show extra fields if we have it
            if (is_array($xf['users'])) {
                foreach ($xf['users'] as $k => $v) {

                    // Skip disabled fields
                    if ($v['disabled']) {
                        continue;
                    }

                    $kp = preg_quote($k, "#");
                    $xfk = isset($fields[$k])?$fields[$k]:'';

                    // Our behaviour depends on field type
                    if ($v['type'] == 'images') {
                        // Check if there're attached images
                        if ($xfk and count($ilist = explode(',', $xfk)) and count($imglist = $mysql->select("select * from ".prefix."_images where id in (".$xfk.")"))) {
                            //print "-xGotIMG[$k]";
                            // Yes, get list of images
                            $imgInfo = $imglist[0];
                            $tvars['regx']["#\[xfield_".$kp."\](.*?)\[/xfield_".$kp."\]#is"] = '$1';
                            $tvars['regx']["#\[nxfield_".$kp."\](.*?)\[/nxfield_".$kp."\]#is"] = '';

                            $iname = ($imgInfo['storage']?$config['attach_url']:$config['files_url']).'/'.$imgInfo['folder'].'/'.$imgInfo['name'];
                            $tvars['vars']['[xvalue_'.$k.']'] = $iname;


                            // Scan for images and prepare data for template show
                            $tiVars = array(
                                'fieldName' => $k,
                                'fieldTitle' => secure_html($v['title']),
                                'fieldType' => $v['type'],
                                'entriesCount' => count($imglist),
                                'entries' => array(),
                                'execStyle' => $mode['style'],
                                'execPlugin' => isset($mode['plugin']) ? $mode['plugin'] : null,
                            );
                            foreach ($imglist as $imgInfo) {
                                $tiEntry = array(
                                    'url' => ($imgInfo['storage']?$config['attach_url']:$config['images_url']).'/'.$imgInfo['folder'].'/'.$imgInfo['name'],
                                    'width' => $imgInfo['width'],
                                    'height' => $imgInfo['height'],
                                    'pwidth' => $imgInfo['p_width'],
                                    'pheight' => $imgInfo['p_height'],
                                    'name' => $imgInfo['name'],
                                    'origName' => secure_html($imgInfo['orig_name']),
                                    'description' => secure_html($imgInfo['description']),

                                    'flags' => array(
                                        'hasPreview' => $imgInfo['preview'],
                                    ),
                                );

                                if ($imgInfo['preview']) {
                                    $tiEntry['purl'] = ($imgInfo['storage']?$config['attach_url']:$config['images_url']).'/'.$imgInfo['folder'].'/thumb/'.$imgInfo['name'];
                                }

                                $tiVars['entries'] []= $tiEntry;
                            }

                            // TWIG based variables
                            $tvars['p']['xfields'][$k]['entries'] = $tiVars['entries'];
                            $tvars['p']['xfields'][$k]['count'] = count($tiVars['entries']);

                            $xv = $xtImages->render($tiVars);

                            $tvars['p']['xfields'][$k]['value'] = $xv;
                            //$tvars['vars']['[xvalue_'.$k.']'] = $xv;



                        } else {
                            $tvars['regx']["#\[xfield_".$kp."\](.*?)\[/xfield_".$kp."\]#is"] = '';
                            $tvars['regx']["#\[nxfield_".$kp."\](.*?)\[/nxfield_".$kp."\]#is"] = '$1';

                        }
                    } else {
                        $tvars['regx']["#\[xfield_".$kp."\](.*?)\[/xfield_".$kp."\]#is"] = ($xfk == "")?"":"$1";
                        $tvars['regx']["#\[nxfield_".$kp."\](.*?)\[/nxfield_".$kp."\]#is"] = ($xfk == "")?"$1":"";
                        $tvars['vars']['[xvalue_'.$k.']'] = ($v['type'] == 'textarea')?'<br/>'.(str_replace("\n","<br/>\n",$xfk).(mb_strlen($xfk, 'UTF-8') ? '<br/>' : '')):$xfk;
                        // 12345

                        // Process `HTML` support feature
                        if ((empty($v['html_support'])) and (($v['type'] == 'textarea') or ($v['type'] == 'text'))) {
                            $xfk = str_replace("<","&lt;",$xfk);
                        }

                        // Parse BB code [if required]
                        if ($config['use_bbcodes'] and $v['bb_support']) {
                            $xfk = $parse-> bbcodes($xfk);
                        }

                        // Process formatting
                        if (($v['type'] == 'textarea') and (!$v['noformat'])) {
                            $xfk = (str_replace("\n","<br/>\n",$xfk).(mb_strlen($xfk, 'UTF-8')?'<br/>':''));
                        }
                        // TWIG based variables
                        $tvars['p']['xfields'][$k]['value'] = $xfk;
                    }
                    $tvars['p']['xfields'][$k]['type'] = $v['type'];
                    $tvars['p']['xfields'][$k]['title'] = secure_html($v['title']);
                }
            }
        }
    }
    pluginRegisterFilter('plugin.uprofile','xfields', new XFieldsUPrifileFilter);
}

class XFieldsFilterAdminCategories extends FilterAdminCategories
{

    function __construct()
    {
        Lang::loadPlugin('xfields', 'admin', '', ':');
    }

    function addCategory(&$tvars, &$SQL)
    {
        $SQL['xf_group'] = $_REQUEST['xf_group'];
        return 1;
    }

    function addCategoryForm(&$tvars)
    {

        // Get config
        $xf = xf_configLoad();

        // Prepare select
        $ms = '<select name="xf_group" class="form-control"><option value="">'.__('xfields:categories.all').'</option>';
        if (isset($xf['grp.news'])) {
            foreach ($xf['grp.news'] as $k => $v) {
                $ms .= '<option value="'.$k.'">'.$k.' ('.$v['title'].')</option>';
            }
        }
        $ms .= '</select>';

        $tvars['extend'] .= '<div class="form-group"><div class="col-sm-5">'.__('xfields:categories.group').'<span class="help-block">'.__('xfields:categories.group#desc').'</span></div><div class="col-sm-7">'.$ms.'</div></div>';
        return 1;
    }

    function editCategoryForm($categoryID, $SQL, &$tvars)
    {

        // Get config
        $xf = xf_configLoad();

        // Prepare select
        $ms = '<select name="xf_group" class="form-control"><option value="">'.__('xfields:categories.all').'</option>';
        foreach ($xf['grp.news'] as $k => $v) {
            $ms .= '<option value="'.$k.'"'.(($SQL['xf_group'] == $k)?' selected="selected"':'').'>'.$k.' ('.$v['title'].')</option>';
        }
        $ms .= '</select>';

        $tvars['extend'] .= '<div class="form-group"><div class="col-sm-5">'.__('xfields:categories.group').'<span class="help-block">'.__('xfields:categories.group#desc').'</span></div><div class="col-sm-7">'.$ms.'</div></div>';
        
        return 1;
    }

    function editCategory($categoryID, $SQL, &$SQLnew, &$tvars)
    {
        $SQLnew['xf_group'] = $_REQUEST['xf_group'];
        return 1;
    }
}

class XFieldsCoreFilter extends CFilter
{

    function __construct()
    {
        Lang::loadPlugin('xfields', 'admin', '', ':');
    }

    function registerUserForm(&$tvars)
    {
        // Load config
        $xf = xf_configLoad();
        if (!is_array($xf) or !isset($xf['users']) or !is_array($xf['users']))
            return 1;

        foreach ($xf['users'] as $k => $v) {
            if ($v['regpage'] and !$v['disabled']) {
                //print "$k: <pre>".var_export($v, true)."</pre>";
                $tEntry = array(
                    'name' => 'xfield_'.$k,
                    'title' => $v['title'],
                );
                switch ($v['type']) {
                    case 'text':
                        $tEntry['type'] = 'input';
                        $tEntry['value'] = $v['default'];

                        break;
                    case 'textarea':
                        $tEntry['type'] = 'text';
                        $tEntry['value'] = $v['default'];
                        break;
                    case 'select':
                        $tEntry['type'] = 'select';
                        if ($v['required']) {
                            $tEntry['values'] = $v['options'];
                        } else {
                            $tEntry['values'] = array('' => '') + $v['options'];
                        }
                        $tEntry['value'] = $v['default'];
                        break;

                }
                $tvars['entries'] []= mkParamLine($tEntry);
            }
        }
        return 1;
    }

    function registerUserNotify($userID, $userRec)
    {
        global $mysql;

        // Load config
        $xf = xf_configLoad();
        if (!is_array($xf) or !isset($xf['users']) or !is_array($xf['users']))
            return 1;

        $xdata = array();
        $SQL = array();
        foreach ($xf['users'] as $k => $v) {
            if ($v['regpage'] and !$v['disabled']) {

                switch ($v['type']) {
                    case 'text':
                    case 'textarea':
                    case 'select':
                        $xdata[$k] = $_POST['xfield_'.$k];
                        if ($v['storage'])
                            $SQL['xfields_'.$k] = $xdata[$k];
                        break;
                }
            }
        }
        $SQL['xfields'] = xf_encode($xdata);

        $SQ = array();
        foreach ($SQL as $sk => $sv) {
            $SQ []= $sk.'='.db_squote($sv);
        }

        $mysql->query("update ".uprefix."_users set ".join(",", $SQ)." where id = ".intval($userID));

        return 1;
    }

}

pluginRegisterFilter('news','xfields', new XFieldsNewsFilter);
pluginRegisterFilter('core.registerUser', 'xfields', new XFieldsCoreFilter);
register_admin_filter('categories', 'xfields', new XFieldsFilterAdminCategories);

