<?php

//
// Copyright (C) 2006-2013 Next Generation CMS (http://ngcms.ru/)
// Name: categories.php
// Description: Category management
// Author: Vitaly Ponomarev
//

// Protect against hack attempts
if (!defined('NGCMS')) die ('HAL');

// Load library
@include_once(root . 'actions/categories.rpc.php');

Lang::load('categories', 'admin');

function listSubdirs($dir) {

    $list = array();
    if ($h = @opendir($dir)) {
        while (($fn = readdir($h)) !== false) {
            if (($fn != '.') and ($fn != '..') and is_dir($dir . '/' . $fn))
                array_push($list, $fn);
        }
        closedir($h);
    };

    return $list;
}

// ////////////////////////////////////////////////////////////////////////////
// Processing functions :: form for adding category
// ///////////////////////////////////////////////////////////////////////////
//
function admCategoryAddForm(){
    global $mysql, $twig, $mod, $PHP_SELF, $config, $AFILTERS;

    // Check for permissions
    if (!checkPermission(array('plugin' => '#admin', 'item' => 'categories'), null, 'modify')) {
        msg(array('type' => 'danger', 'message' => __('perm.denied')));
        return;
    }

    $tpl_list = '<option value="">* '.__('cat_tpldefault')." *</option>\n";
    foreach (listSubdirs(tpl_site.'ncustom/') as $k) {
        $tpl_list .= '<option value="'.secure_html($k).'"'.'>'.secure_html($k)."</option>\n";
    }

    $templateMode = '';
    foreach (array('0', '1', '2') as $k => $v) {
        $templateMode .= '<option value="'.$k.'"'.(($k == intval(substr(getIsSet($row['flags']), 2, 1)))?' selected="selected"':'').'>'.__('template_mode.'.$v).'</option>';
    }

    $tVars = array(
        'php_self' => $PHP_SELF,
        'parent' => makeCategoryList(array('name' => 'parent', 'doempty' => 1, 'resync' => ($_POST['action']?1:0))),
        'orderlist' => OrderList(''),
        'token' => genUToken('admin.categories'),
        'tpl_list' => $tpl_list,
        'template_mode' => $templateMode,
        'flags' => array(
            'haveMeta' => $config['meta']?1:0,
        ),
    );

    if (is_array($AFILTERS['categories'])) {
        foreach ($AFILTERS['categories'] as $k => $v) {
            $v->addCategoryForm($tVars);
        }
    }

    $xt = $twig->loadTemplate('skins/default/tpl/categories/add.tpl');
    echo $xt->render($tVars);
}

// ////////////////////////////////////////////////////////////////////////////
// Processing functions :: add new category
// ///////////////////////////////////////////////////////////////////////////
//
function admCategoryAdd() {
    global $mysql, $mod, $parse, $config, $AFILTERS;

    $SQL = array();
    $SQL['name'] = secure_html($_POST['name']);
    $SQL['info'] = $_POST['info'];
    $SQL['alt'] = trim($_POST['alt']);
    $SQL['parent'] = intval($_POST['parent']);
    $SQL['icon'] = $_POST['icon'];
    $SQL['alt_url'] = $_POST['alt_url'];
    $SQL['orderby'] = $_POST['orderby'];
    $SQL['tpl'] = $_POST['tpl'];
    $SQL['number'] = intval($_POST['number']);

    $SQL['flags'] = intval($_POST['cat_show'])?'1':'0';
    $SQL['flags'] .= (string) (abs(intval($_POST['show_link'])<=2)?abs(intval($_POST['show_link'])):'0');
    $SQL['flags'] .= (string) (abs(intval($_POST['template_mode'])<=2)?abs(intval($_POST['template_mode'])):'0');

    //$category = intval($_POST['category']);

    // Check for permissions
    if (!checkPermission(array('plugin' => '#admin', 'item' => 'categories'), null, 'modify')) {
        msg(array('type' => 'danger', 'message' => __('perm.denied')));
        return;
    }

    // Check for security token
    if ((!isset($_POST['token']))||($_POST['token'] != genUToken('admin.categories'))) {
        msg(array('type' => 'danger', 'title' => __('error.security.token'), 'message' => __('error.security.token#desc')));
        return;
    }

    if (!$SQL['name']) {
        msg(array('type' => 'danger', 'title' => __('msge_name'), 'message' => __('msgi_name')));
        return;
    }

    // IF alt name is set:
    if ( trim($SQL['alt']) ) {
        // - check for allowed chars
        if (!$parse->nameCheck($SQL['alt'])) {
            // ERROR
            msg(array('type' => 'danger', 'title' => __('category.err.wrongalt'), 'message' => __('category.err.wrongalt#desc')));
            return;
        }

        // - check for duplicate alt name
        if (is_array($mysql->record("select * from ".prefix."_category where lower(alt) = ".db_squote($SQL['alt'])))) {
            msg(array('type' => 'danger', 'title' => __('category.err.dupalt'), 'message' => __('category.err.dupalt#desc')));
            return;
        }
    } else {
        // alt name was not set, generate new alt name in automatic mode
        $SQL['alt'] = strtolower($parse->translit($SQL['name']));

        $i = '';
        while ( is_array($mysql->record("select id from ".prefix."_category where alt = ".db_squote($SQL['alt'].$i)." limit 1")) ) {
            $i++;
        }
        $SQL['alt'] = $SQL['alt'].$i;
    }

    if ($config['meta']) {
        $SQL['description'] = $_POST['description'] ? secure_html($_POST['description']) : '';
        $SQL['keywords'] = $_POST['keywords'] ? secure_html($_POST['keywords']) : '';
    }

    $pluginNoError = 1;
    if (is_array($AFILTERS['categories']))
        foreach ($AFILTERS['categories'] as $k => $v) {
            if (!($pluginNoError = $v->addCategory($tvars, $SQL))) {
                msg(array('type' => 'danger', 'message' => str_replace('{plugin}', $k, __('msge_pluginlock'))));
                break;
            }
        }

    if (!$pluginNoError) {
        return 0;
    }

    $SQLout = array();
    foreach ($SQL as $k => $v)
        $SQLout[$k] = db_squote($v);
    
    cacheStoreFile('LoadCategories.dat', '');
    // Add new record into SQL table
    $mysql->query("insert into ".prefix."_category (".join(", ", array_keys($SQLout)).") values (".join(", ", array_values($SQLout)).")");
    $rowID = $mysql->record("select LAST_INSERT_ID() as id");

    $fmanager = new FileManagment();
    $imanager = new ImageManagment();

    // Check if new image was attached
    if (isset($_FILES) and isset($_FILES['image']) and is_array($_FILES['image']) and isset($_FILES['image']['error']) and ($_FILES['image']['error'] == 0)) {
        // new file is uploaded
        $up = $fmanager->file_upload(array('dsn' => true, 'linked_ds' => 2, 'linked_id' => $rowID['id'], 'type' => 'image', 'http_var' => 'image', 'http_varnum' => 0));
        //print "OUT: <pre>".var_export($up, true)."</pre>";
        if (is_array($up)) {
            // Image is uploaded. Let's update image params
            //print "<pre>UPLOADED. ret:".var_export($up, true)."</pre>";

            $img_width = 0;
            $img_height = 0;
            $img_preview = 0;
            $img_pwidth = 0;
            $img_pheight = 0;

            if (is_array($sz = $imanager->get_size($config['attach_dir'].$up[2].'/'.$up[1]))) {
                //print "<pre>IMG SIZE. ret:".var_export($sz, true)."</pre>";

                $img_width = $sz[1];
                $img_height = $sz[2];

                $tsz = intval($config['thumb_size']);
                if (($tsz < 10)||($tsz > 1000)) $tsz = 150;
                $thumb = $imanager->create_thumb($config['attach_dir'].$up[2], $up[1], $tsz,$tsz, $config['thumb_quality']);
                if ($thumb) {
                    $img_preview = 1;
                    $img_pwidth = $thumb[0];
                    $img_pheight = $thumb[1];
                    //print "<pre>THUMB CREATED. ret:".var_export($thumb, true)."</pre>";
                }
            }

            // Update table 'images'
            $mysql->query("update ".prefix."_images set width=".db_squote($img_width).", height=".db_squote($img_height).", preview=".db_squote($img_preview).", p_width=".db_squote($img_pwidth).", p_height=".db_squote($img_pheight)." where id = ".db_squote($up[0]));

            // Update table 'categories'
            $mysql->query("update ".prefix."_category set image_id = ".db_squote($up[0])." where id = ".db_squote($rowID['id']));
        }

    }

    // Report about adding new category
    msg(array('message' => __('msgo_added')));
}

// ////////////////////////////////////////////////////////////////////////////
// Processing functions :: form for editing category
// ///////////////////////////////////////////////////////////////////////////
//
function admCategoryEditForm(){
    global $mysql, $mod, $config, $twig, $AFILTERS, $PHP_SELF;

    // Check for permissions
    $permModify = checkPermission(array('plugin' => '#admin', 'item' => 'categories'), null, 'modify');
    $permDetails = checkPermission(array('plugin' => '#admin', 'item' => 'categories'), null, 'details');

    if (!$permModify and !$permDetails) {
        msg(array('type' => 'danger', 'message' => __('perm.denied')));
        return;
    }

    $catid = intval($_REQUEST['catid']);
    if (!is_array($row=$mysql->record("select nc.*, ni.id as icon_id, ni.name as icon_name, ni.storage as icon_storage, ni.folder as icon_folder, ni.preview as icon_preview, ni.width as icon_width, ni.height as icon_height, ni.p_width as icon_pwidth, ni.p_height as icon_pheight from `".prefix."_category` as nc left join `".prefix."_images` ni on nc.image_id = ni.id where nc.id = ".db_squote($catid)." order by nc.posorder asc", 1))) {
        msg(array('type' => 'danger', 'title' => __('msge_id'), 'message' => sprintf(__('msgi_id'), $PHP_SELF.'?mod=categories')));
        return;
    }

    $tpl_list = '<option value="">* '.__('cat_tpldefault')." *</option>\n";
    foreach (listSubdirs(tpl_site.'ncustom/') as $k) {
        $tpl_list .= '<option value="'.secure_html($k).'"'.(($row['tpl'] == $k)?' selected="selected"':'').'>'.secure_html($k)."</option>\n";
    }

    $showLink = '';
    foreach (array('always', 'ifnews', 'never') as $k => $v) {
        $showLink .= '<option value="'.$k.'"'.(($k == intval(substr($row['flags'], 1, 1)))?' selected="selected"':'').'>'.__('link.'.$v).'</option>';
    }

    $templateMode = '';
    foreach (array('0', '1', '2') as $k => $v) {
        $templateMode .= '<option value="'.$k.'"'.(($k == intval(substr($row['flags'], 2, 1)))?' selected="selected"':'').'>'.__('template_mode.'.$v).'</option>';
    }

    $tVars = array(
        'php_self' => $PHP_SELF,
        'parent' => makeCategoryList(array('name' => 'parent', 'selected' => $row['parent'], 'skip' => $row['id'], 'doempty' => 1)),
        'catid' => $row['id'],
        'name' => $row['name'],
        'alt' => secure_html($row['alt']),
        'alt_url' => secure_html($row['alt_url']),
        'orderlist' => OrderList($row['orderby'], true),
        'description' => $row['description'],
        'keywords' => $row['keywords'],
        'icon' => secure_html($row['icon']),
        'tpl_value' => secure_html($row['tpl']),
        'number' => $row['number'],
        'show_link' => $showLink,
        'template_mode' => $templateMode,
        'tpl_list' => $tpl_list,
        'info' => secure_html($row['info']),
        'token' => genUToken('admin.categories'),
        'flags' => array(
            'haveMeta' => $config['meta']?1:0,
            'canModify' => $permModify?1:0,
            'showInMenu' => (substr($row['flags'],0, 1))?1:0,
            'haveAttach' => $row['icon_id'],
        ),
    );

    if ($row['icon_id']) {
        $tVars['attach_url'] = $config['attach_url'].'/'.$row['icon_folder'].'/'.($row['icon_preview']?'thumb/':'').$row['icon_name'];
    }

    if (is_array($AFILTERS['categories']))
        foreach ($AFILTERS['categories'] as $k => $v) { $v->editCategoryForm($catid, $row, $tVars); }

    $xt = $twig->loadTemplate('skins/default/tpl/categories/edit.tpl');
    echo $xt->render($tVars);

}

// ////////////////////////////////////////////////////////////////////////////
// Processing functions :: edit category
// ///////////////////////////////////////////////////////////////////////////
//
function admCategoryEdit(){
    global $mysql, $config, $parse, $catz, $catmap, $AFILTERS;

    //print "<pre>POST DATA:\n".var_export($_POST, true)."\n\nFILES: ".var_export($_FILES, true)."</pre>";

    $SQL = array();
    $SQL['name'] = secure_html($_POST['name']);
    $SQL['info'] = $_POST['info'];
    $SQL['alt'] = trim($_POST['alt']);
    $SQL['parent'] = intval($_POST['parent']);
    $SQL['icon'] = $_POST['icon'];
    $SQL['alt_url'] = $_POST['alt_url'];
    $SQL['orderby'] = $_POST['orderby'];
    $SQL['tpl'] = $_POST['tpl'];
    $SQL['number'] = intval($_POST['number']);

    $SQL['flags'] = intval($_POST['cat_show'])?'1':'0';
    $SQL['flags'] .= (string) (abs(intval($_POST['show_link'])<=2)?abs(intval($_POST['show_link'])):'0');
    $SQL['flags'] .= (string) (abs(intval($_POST['template_mode'])<=2)?abs(intval($_POST['template_mode'])):'0');

    $catid = intval($_POST['catid']);

    // Check for permissions
    if (!checkPermission(array('plugin' => '#admin', 'item' => 'categories'), null, 'modify')) {
        msg(array('type' => 'danger', 'message' => __('perm.denied')));
        return;
    }

    // Check for security token
    if ((!isset($_POST['token']))||($_POST['token'] != genUToken('admin.categories'))) {
        msg(array('type' => 'danger', 'title' => __('error.security.token'), 'message' => __('error.security.token#desc')));
        return;
    }

    if (!$SQL['name'] or !$catid or (!is_array($SQLold = $catz[$catmap[$catid]]))) {
        msg(array('type' => 'danger', 'title' => __('msge_name'), 'message' => __('msgi_name')));
        return;
    }

    if (!$catid or (!is_array($SQLold = $catz[$catmap[$catid]]))) {
        msg(array('type' => 'danger', 'title' => __('msge_id'), 'message' => __('msgi_id')));
        return;
    }

    // Check alt name in case it was changed
    if ($SQL['alt'] != $catid) {
        // - check for allowed chars
        if (!$parse->nameCheck($SQL['alt'])) {
            // ERROR
            msg(array('type' => 'danger', 'title' => __('category.err.wrongalt'), 'message' => __('category.err.wrongalt#desc')));
            return;
        }

        // - check for duplicate alt name
        if (is_array($mysql->record("select * from ".prefix."_category where (id <> ".db_squote($catid).") and (lower(alt) = ".db_squote($SQL['alt']).")"))) {
            msg(array('type' => 'danger', 'title' => __('category.err.dupalt'), 'message' => __('category.err.dupalt#desc')));
            return;
        }

    }

    if ($config['meta']) {
        $SQL['description']	= $_POST['description'] ? secure_html($_POST['description']) : '';
        $SQL['keywords']	= $_POST['keywords'] ? secure_html($_POST['keywords']) : '';
    }

    $fmanager = new FileManagment();
    $imanager = new ImageManagment();

    // Check is existent image should be deleted
    if (isset($_POST['image_del']) and ($SQLold['image_id'])) {
        $fmanager->file_delete(array('type' => 'image', 'id' => $SQLold['image_id']));
        $SQL['image_id'] = 0;
    }

    // Check if new image was attached
    if (isset($_FILES) and (!$SQL['image_id']) and isset($_FILES['image']) and is_array($_FILES['image']) and isset($_FILES['image']['error']) and ($_FILES['image']['error'] == 0)) {
        // new file is uploaded
        $up = $fmanager->file_upload(array('dsn' => true, 'linked_ds' => 2, 'linked_id' => $catid, 'type' => 'image', 'http_var' => 'image', 'http_varnum' => 0));
        //print "OUT: <pre>".var_export($up, true)."</pre>";
        if (is_array($up)) {
            // Image is uploaded. Let's update image params
            //print "<pre>UPLOADED. ret:".var_export($up, true)."</pre>";

            $img_width = 0;
            $img_height = 0;
            $img_preview = 0;
            $img_pwidth = 0;
            $img_pheight = 0;

            if (is_array($sz = $imanager->get_size($config['attach_dir'].$up[2].'/'.$up[1]))) {
                //print "<pre>IMG SIZE. ret:".var_export($sz, true)."</pre>";

                $img_width = $sz[1];
                $img_height = $sz[2];

                $tsz = intval($config['thumb_size']);
                if (($tsz < 10)||($tsz > 1000)) $tsz = 150;
                $thumb = $imanager->create_thumb($config['attach_dir'].$up[2], $up[1], $tsz,$tsz, $config['thumb_quality']);
                if ($thumb) {
                    $img_preview = 1;
                    $img_pwidth = $thumb[0];
                    $img_pheight = $thumb[1];
                    //print "<pre>THUMB CREATED. ret:".var_export($thumb, true)."</pre>";
                }
            }

            // Update SQL records
            $mysql->query("update ".prefix."_images set width=".db_squote($img_width).", height=".db_squote($img_height).", preview=".db_squote($img_preview).", p_width=".db_squote($img_pwidth).", p_height=".db_squote($img_pheight)." where id = ".db_squote($up[0]));
            $SQL['image_id'] = $up[0];
        }

    }

    $pluginNoError = 1;
    if (is_array($AFILTERS['categories']))
        foreach ($AFILTERS['categories'] as $k => $v) {
            if (!($pluginNoError = $v->editCategory($catid, $SQLold, $SQL, $tvars))) {
                msg(array('type' => 'danger', 'message' => str_replace('{plugin}', $k, __('msge_pluginlock'))));
                break;
            }
        }

    if (!$pluginNoError) {
        return 0;
    }

    $SQLout = array();
    foreach ($SQL as $var => $val)
        $SQLout []= '`'.$var.'` = '.db_squote($val);

    cacheStoreFile('LoadCategories.dat', '');

    $mysql->query("update ".prefix."_category set ".join(", ", $SQLout)." where id=".db_squote($catid));
    msg(array('message' => __('msgo_saved')));
}

// ////////////////////////////////////////////////////////////////////////////
// MAIN ACTION
// ///////////////////////////////////////////////////////////////////////////
//

if ($action == 'edit') {
    admCategoryEditForm();
} elseif ($action == 'add') {
    admCategoryAddForm();
} else {
    $dosort = 1;
    switch ($action) {
        case "doadd" : admCategoryAdd(); break;
        case "remove": category_remove(); break;
        case "doedit": admCategoryEdit(); break;
        default: $dosort = 0;
    }
    if ($dosort) { admCategoryReorder(); }
    admCategoryList();
}