<?php

//
// Copyright (C) 2006-2017 BixBite CMS (http://bixbite.site/)
// Name: users.php
// Description: manage users
// Author: Vitaly Ponomarev
//

// Protect against hack attempts
if (!defined('BBCMS')) die ('HAL');

Lang::load('users', 'admin');

// Load CORE Plugin
$cPlugin = CPlugin::instance();
$cPlugin->loadLibrary('uprofile', 'lib');

//
// Form: Edit user
function userEditForm(){
    global $mysql, $twig, $mod, $PFILTERS, $UGROUP, $PHP_SELF, $userROW, $config, $DSlist;

    $id = (getIsSet($_REQUEST['id']))?intval($_REQUEST['id']):0;

    // Determine user's permissions
    $perm = checkPermission(array('plugin' => '#admin', 'item' => 'users'), null, array('modify', 'details'));
    $permModify = $perm['modify'];
    $permDetails = $perm['details'];

    // Check for permissions
    if (!$perm['modify'] and !$perm['details']) {
        ngSYSLOG(array('plugin' => '#admin', 'item' => 'users', 'ds_id' => $id), array('action' => 'editForm'), null, array(0, 'SECURITY.PERM'));
        msg(array('type' => 'danger', 'message' => __('perm.denied')));
        return;
    }

    if (!($row = $mysql->record("select * from ".uprefix."_users where id=".db_squote($id)))) {
        ngSYSLOG(array('plugin' => '#admin', 'item' => 'users', 'ds_id' => $id), array('action' => 'editForm'), null, array(0, 'NOT.FOUND'));
        msg(array('type' => 'danger', 'message' => __('msge_not_found')));
        return;
    }

    $userROW['#images'] = $mysql->select("select *, date_format(from_unixtime(date), '%d.%m.%Y') as date from ".prefix."_images where (linked_ds = ".$DSlist['users'].") and (linked_id = ".db_squote($userROW['id']).')', 1);
    
    // Manage profile data [if needed]
    if (is_array($PFILTERS['plugin.uprofile']))
        foreach ($PFILTERS['plugin.uprofile'] as $k => $v) { $v->editProfileFormPre($row['id'], $row); }

    $status = '';
    foreach ($UGROUP as $ugID => $ugData) {
        $status .= ' <option value="'.$ugID.'"'.(($row['status'] == $ugID)?' selected':'').'>'.$ugID.' ('.$ugData['name'].')</option>';
    }

    //	Обрабатываем необходимые переменные для шаблона
    $tVars = array(
        'php_self' => $PHP_SELF,
        'name' => secure_html($row['name']),
        'regdate' => Lang::retDate("l, j F Y - H:i", $row['reg']),
        'com' => $row['com'],
        'news' => $row['news'],
        'status' => $status,
        'mail' => secure_html($row['mail']),
        'site' => secure_html($row['site']),
        'icq' => secure_html($row['icq']),
        'where_from' => secure_html($row['where_from']),
        'info' => secure_html($row['info']),
        'id' => $id,
        'last' => (empty($row['last'])) ? __('no_last') : Lang::retDate('l, j F Y - H:i', $row['last']),
        'ip' => $row['ip'],
        'token' => genUToken('admin.users'),
        'perm' => array(
            'modify' => $perm['modify']?1:0,
        ),
    );

    if (is_array($PFILTERS['plugin.uprofile']))
        foreach ($PFILTERS['plugin.uprofile'] as $k => $v) {

            $v->editProfileForm($row['id'], $row, $tVars);
        }

    ngSYSLOG(array('plugin' => '#admin', 'item' => 'users', 'ds_id' => $id), array('action' => 'editForm'), null, array(1));

    echo $twig->render(tpl_actions . 'users/edit.tpl', $tVars);
}

//
// Edit user's profile
function userEdit(){
    global $mysql, $mod;

    // Check for permissions
    if (!checkPermission(array('plugin' => '#admin', 'item' => 'users'), null, 'modify')) {
        msg(array('type' => 'danger', 'message' => __('perm.denied')));
        ngSYSLOG(array('plugin' => '#admin', 'item' => 'users', 'ds_id' => $id), array('action' => 'editForm'), null, array(0, 'SECURITY.PERM'));
        return;
    }

    // Check for security token
    if ((!isset($_REQUEST['token'])) or ($_REQUEST['token'] != genUToken('admin.users'))) {
        msg(array('type' => 'danger', 'title' => __('error.security.token'), 'message' => __('error.security.token#desc')));
        ngSYSLOG(array('plugin' => '#admin', 'item' => 'users', 'ds_id' => $id), array('action' => 'editForm'), null, array(0, 'SECURITY.TOKEN'));
        return;
    }

    $id = intval($_REQUEST['id']);

    // Check if user exists
    if (!($row = $mysql->record("select * from ".uprefix."_users where id=".db_squote($id)))) {
        msg(array('type' => 'danger', 'message' => __('msge_not_found')));
        ngSYSLOG(array('plugin' => '#admin', 'item' => 'users', 'ds_id' => $id), array('action' => 'editForm'), null, array(0, 'NOT.FOUND'));
        return;
    }

    $pass = ($_REQUEST['password']) ? EncodePassword($_REQUEST['password']) : '';

    // Prepare a list of changed params
    $cList = array();
    foreach (array('level', 'site', 'icq', 'where_from', 'info', 'mail') as $k) {
        if ($row[$k] != $_REQUEST[$k]) {
            $cList[$k] = array($row[$k], $_REQUEST[$k]);
        }
    }
    if ($pass) {
        $cList['pass'] = array('****', '****');
    }

    ngSYSLOG(array('plugin' => '#admin', 'item' => 'users', 'ds_id' => $id), array('action' => 'editForm', 'list' => $cList), null, array(1));

    $mysql->query("update ".uprefix."_users set `status`=".db_squote($_REQUEST['status']).", `site`=".db_squote($_REQUEST['site']).", `icq`=".db_squote($_REQUEST['icq']).", `where_from`=".db_squote($_REQUEST['where_from']).", `info`=".db_squote($_REQUEST['info']).", `mail`=".db_squote($_REQUEST['mail']).($pass?", `pass`=".db_squote($pass):'')." where id=".db_squote($row['id']));
    msg(array('message' => __('msgo_edituser')));
}

//
// Add new user
function userAdd(){
    global $mysql, $mod, $config;

    // Check for permissions
    if (!checkPermission(array('plugin' => '#admin', 'item' => 'users'), null, 'modify')) {
        msg(array('type' => 'danger', 'message' => __('perm.denied')));
        return;
    }

    // Check for security token
    if ((!isset($_REQUEST['token'])) or ($_REQUEST['token'] != genUToken('admin.users'))) {
        msg(array('type' => 'danger', 'title' => __('error.security.token'), 'message' => __('error.security.token#desc')));
        return;
    }

    $regusername	= trim($_REQUEST['regusername']);
    $regemail		= trim($_REQUEST['regemail']);
    $regpassword	= $_REQUEST['regpassword'];
    $reglevel		= $_REQUEST['reglevel'];

    if ((!$regusername)  or  (!mb_strlen(trim($regpassword), 'UTF-8'))  or  (!$regemail)) {
        msg(array('type' => 'danger', 'title' => __('msge_fields'), 'message' => __('msgi_fields')));
        return;
    }
    if ($mysql->record("select * from ".uprefix."_users where lower(name) = ".db_squote(strtolower($regusername))." or lower(mail)=".db_squote(strtolower($regemail)))) {
        msg(array('type' => 'danger', 'title' => __('msge_userexists'), 'message' => __('msgi_userexists')));
        return;
    }

    $add_time		= time() + ($config['date_adjust']*60);
    $regpassword	= EncodePassword($regpassword);

    $mysql->query("insert into ".uprefix."_users (name, pass, mail, status, reg) values (".db_squote($regusername).", ".db_squote($regpassword).", ".db_squote($regemail).", ".db_squote($reglevel).", ".db_squote($add_time).")");
    msg(array('message' => __('msgo_adduser')));
}

//
// Bulk action: activate selected users
function userMassActivate(){
    global $mysql;

    // Check for permissions
    if (!checkPermission(array('plugin' => '#admin', 'item' => 'users'), null, 'modify')) {
        msg(array('type' => 'danger', 'message' => __('perm.denied')), 1, 1);
        return;
    }

    // Check for security token
    if ((!isset($_REQUEST['token'])) or ($_REQUEST['token'] != genUToken('admin.users'))) {
        msg(array('type' => 'danger', 'title' => __('error.security.token'), 'message' => __('error.security.token#desc')));
        return;
    }

    $selected_users = getIsSet($_REQUEST['selected_users']);
    if (!$selected_users) {
        msg(array('type' => 'danger', 'title' => __('msge_select'), 'message' => __('msgi_select')));
        return;
    }
    foreach ($selected_users as $id) {
        $mysql->query("update ".uprefix."_users set activation='' where id=".db_squote($id));
    }
    msg(array('message' => __('msgo_activate')));
}

//
// Bulk action: LOCK selected users
function userMassLock(){
    global $mysql;

    // Check for permissions
    if (!checkPermission(array('plugin' => '#admin', 'item' => 'users'), null, 'modify')) {
        msg(array('type' => 'danger', 'message' => __('perm.denied')), 1, 1);
        return;
    }

    // Check for security token
    if ((!isset($_REQUEST['token'])) or ($_REQUEST['token'] != genUToken('admin.users'))) {
        msg(array('type' => 'danger', 'title' => __('error.security.token'), 'message' => __('error.security.token#desc')));
        return;
    }

    $selected_users = getIsSet($_REQUEST['selected_users']);
    if (!$selected_users) {
        msg(array('type' => 'danger', 'title' => __('msge_select'), 'message' => __('msgi_select')));
        return;
    }

    // Lock all users (excluding admins) and log them out!
    foreach ($selected_users as $id) {
        $mysql->query("update ".uprefix."_users set activation=".db_squote(MakeRandomPassword()).", authcookie='' where (id=".db_squote($id).") and (status <> 1)");
    }
    msg(array('message' => __('msgo_lock')));
}

//
// Bulk action: set status to selected users
function userMassSetStatus(){
    global $mysql, $userROW, $UGROUP;

    // Check for permissions
    if (!checkPermission(array('plugin' => '#admin', 'item' => 'users'), null, 'modify')) {
        msg(array('type' => 'danger', 'message' => __('perm.denied')), 1, 1);
        return;
    }

    // Check for security token
    if ((!isset($_REQUEST['token'])) or ($_REQUEST['token'] != genUToken('admin.users'))) {
        msg(array('type' => 'danger', 'title' => __('error.security.token'), 'message' => __('error.security.token#desc')));
        return;
    }

    $selected_users = getIsSet($_REQUEST['selected_users']);
    if (!$selected_users) {
        msg(array('type' => 'danger', 'title' => __('msge_select'), 'message' => __('msgi_select')));
        return;
    }

    // Determine status to set to
    // NOTE: we CAN'T set status `ADMIN` and we can't change STATUS for ADMINS
    $status = intval($_REQUEST['newstatus']);
    if (!isset($UGROUP[$status])  or  ($status <= 1)) {
        msg(array('type' => 'danger', 'message' => __('msg_massadm')));
        return;
    }

    // Lock all users (excluding admins)
    foreach ($selected_users as $id) {
        $mysql->query("update ".uprefix."_users set status=".db_squote($status)." where (id=".db_squote($id).") and (status <> 1)");
    }
    msg(array('message' => __('msgo_status')));
}

//
// Bulk action: delete selected users
function userMassDelete(){
    global $mysql, $userROW, $config;

    // Check for permissions
    if (!checkPermission(array('plugin' => '#admin', 'item' => 'users'), null, 'modify')) {
        msg(array('type' => 'danger', 'message' => __('perm.denied')), 1, 1);
        return;
    }

    // Check for security token
    if ((!isset($_REQUEST['token'])) or ($_REQUEST['token'] != genUToken('admin.users'))) {
        msg(array('type' => 'danger', 'title' => __('error.security.token'), 'message' => __('error.security.token#desc')));
        return;
    }

    $selected_users = getIsSet($_REQUEST['selected_users']);
    if (!$selected_users  or  !is_array($selected_users)) {
        msg(array('type' => 'danger', 'title' => __('msge_select'), 'message' => __('msgi_select')));
        return;
    }

    foreach ($selected_users as $id) {
        // Don't let us to delete ourselves
        if ($id == $userROW['id']) { continue; }

        // Fetch user's record
        if (is_array($urow = $mysql->record("select * from ".prefix."_users where id = ".db_squote($id)))) {
            // Do not delete admins
            if ($urow['status'] == 1) { continue; }

            // Check if user has his own photo or avatar
            if ( (trim($urow['avatar'])) and (file_exists($config['avatars_dir'].$urow['photo'])) )
                @unlink($config['avatars_dir'].$urow['avatar']);

            if ( (trim($urow['photo'])) and (file_exists($config['photos_dir'].$urow['photo'])) )
                @unlink($config['photos_dir'].$urow['photo']);

            $mysql->query("delete from ".uprefix."_users where id=".db_squote($id));
        }
    }
    msg(array('message' => __('msgo_deluser')));
}

//
// Bulk action: delete inactive (never logged in) users [but user should be registered for more than 1 day ago or who have 1+ news]
function userMassDeleteInactive(){
    global $mysql, $userROW;

    // Check for permissions
    if (!checkPermission(array('plugin' => '#admin', 'item' => 'users'), null, 'modify')) {
        msg(array('type' => 'danger', 'message' => __('perm.denied')), 1, 1);
        return;
    }

    // Check for security token
    if ((!isset($_REQUEST['token'])) or ($_REQUEST['token'] != genUToken('admin.users'))) {
        msg(array('type' => 'danger', 'title' => __('error.security.token'), 'message' => __('error.security.token#desc')));
        return;
    }

    $today = time();

    $mysql->query("DELETE FROM ".uprefix."_users WHERE ((last IS NULL) OR (last='')) AND ((reg + 86400) < $today) AND (news < 1)");
    msg(array('message' => __('msgo_delunact')));
}

//
// Show list of users
function userList(){
    global $mysql, $mod, $userROW, $UGROUP, $twig, $PHP_SELF;

    // Load CORE Plugin
    $cPlugin = CPlugin::instance();
    
    // Check for permissions
    if (!checkPermission(array('plugin' => '#admin', 'item' => 'users'), null, 'view')) {
        msg(array('type' => 'danger', 'message' => __('perm.denied')));
        return;
    }

    // Load admin page based cookies
    $admCookie = admcookie_get();

    // Determine user's permissions
    $permModify		= checkPermission(array('plugin' => '#admin', 'item' => 'users'), null, 'modify');
    $permDetails	= checkPermission(array('plugin' => '#admin', 'item' => 'users'), null, 'details');
    $permMassAction	= checkPermission(array('plugin' => '#admin', 'item' => 'users'), null, 'modify');

    // Sorting parameters
    $sortOrderMap = array(
        'i' => 'id',
        'id' => 'id desc',
        'n' => 'name',
        'nd' => 'name desc',
        'r' => 'reg',
        'rd' => 'reg desc',
        'l' => 'last',
        'ld' => 'last desc',
        'p' => 'news',
        'pd' => 'news desc',
        'g' => 'status',
        'gd' => 'status desc',
    );

    $inSort = (isset($_REQUEST['sort']) and (isset($sortOrderMap[$_REQUEST['sort']])))?$_REQUEST['sort']:'i';

    $sortLinkMap = array();
    foreach (array('i', 'n', 'r', 'l', 'p', 'g') as $kOrder) {
        $sRec = array();
        $sRec['isActive'] = (($inSort == $kOrder)  or  ($inSort == $kOrder.'d'))?1:0;
        if ($sRec['isActive']) {
            $sRec['sign'] = ($inSort == $kOrder)?'&#8595;&#8595;':'&#8593;&#8593;';
            $sRec['link'] = admin_url.'/admin.php?mod=users&action=list'.
                (isset($_REQUEST['name']) and $_REQUEST['name']?'&name='.htmlspecialchars($_REQUEST['name'], ENT_COMPAT | ENT_HTML401, 'UTF-8'):'').
                (isset($_REQUEST['rpp']) and $_REQUEST['rpp']?'&rpp='.intval($_REQUEST['rpp']):'').
                '&sort='.$kOrder.(($inSort == $kOrder)?'d':'');
        } else {
            $sRec['sign'] = '';
            $sRec['link'] = admin_url.'/admin.php?mod=users&action=list'.
                (isset($_REQUEST['name']) and $_REQUEST['name']?'&name='.htmlspecialchars($_REQUEST['name'], ENT_COMPAT | ENT_HTML401, 'UTF-8'):'').
                (isset($_REQUEST['rpp']) and $_REQUEST['rpp']?'&rpp='.intval($_REQUEST['rpp']):'').
                '&sort='.$kOrder;
        }
        $sortLinkMap[$kOrder] = $sRec;
    }

    $sortValue = (isset($_REQUEST['sort']) and isset($sortOrderMap[$_REQUEST['sort']]))?$sortOrderMap[$_REQUEST['sort']]:'id';
    $name = (isset($_REQUEST['name']) and trim($_REQUEST['name']))?("'%".$mysql->db_quote($_REQUEST['name'])."%'"):'';

    // Records Per Page
    // - Load
    $fRPP = (isset($_REQUEST['rpp']) and (trim($_REQUEST['rpp']))) ? intval($_REQUEST['rpp']) : intval($admCookie['users']['pp']);
    // - Set default value for `Records Per Page` parameter
    if (($fRPP < 2) or ($fRPP > 2000))
        $fRPP = 30;

    // - Save into cookies current value
    $admCookie['users']['pp'] = $fRPP;
    admcookie_set($admCookie);

    $pageNo = (isset($_REQUEST['page']) and $_REQUEST['page'])?intval($_REQUEST['page']):0;
    if (!$pageNo)
        $pageNo = 1;

    // FILTER (where) PARAMETERS
    $whereRules = array();
    if (mb_strlen($name, 'UTF-8')) {
        $whereRules []= 'name like '.$name;
    }
    if (isset($_REQUEST['group']) and (intval($_REQUEST['group']) > 0)) {
        $whereRules []= 'status = '.intval($_REQUEST['group']);
    }

    $queryFilter = count($whereRules)?'where '.join(" and ", $whereRules):'';
    $sql = "select * from ".uprefix."_users ".$queryFilter.' order by '.$sortValue.' '."limit ".(($pageNo-1)*$fRPP).", ".$fRPP;

    $tEntries = array();
    foreach ($mysql->select($sql) as $row) {
        $status = isset($UGROUP[$row['status']])?$UGROUP[$row['status']]['name']:('Unknown ['.$row['status'].']');

        $tEntry = array(
            'id' => $row['id'],
            'name' => $row['name'],
            'groupID' => $row['status'],
            'groupName' => $status,
            'cntNews' => $row['news'],
            'cntComments' => $row['com'],
            'regdate' => Lang::retDate('j F Y - H:i', $row['reg']),
            'lastdate' => (empty($row['last'])) ? __('no_last') : Lang::retDate('j F Y - H:i', $row['last']),
            'flags' => array(
                'isActive' => (!$row['activation']  or  $row['activation'] == '')?1:0,
            ),
        );

        $tEntries []= $tEntry;
    }

    $userCount	= $mysql->result("SELECT count(*) FROM ".uprefix."_users ".$queryFilter);
    $pageCount	= ceil($userCount / $fRPP);

    // Sorting flags
    //$linkSortOrders

    $paginator = new Paginator;
    $pagination = $paginator->get( array(
            'current' => $pageNo,
            'count' => $pageCount,
            'url' => admin_url.'/admin.php?mod=users&action=list'.
                (isset($_REQUEST['name']) and $_REQUEST['name']?'&name='.htmlspecialchars($_REQUEST['name'], ENT_COMPAT | ENT_HTML401, 'UTF-8'):'').
                (isset($_REQUEST['how']) and $_REQUEST['how']?'&how='.htmlspecialchars($_REQUEST['how'], ENT_COMPAT | ENT_HTML401, 'UTF-8'):'').
                (isset($_REQUEST['rpp']) and $_REQUEST['rpp']?'&rpp='.intval($_REQUEST['rpp']):'').
                '&page=%page%'
        ));

    $tUgroup = array();
    foreach ($UGROUP as $id => $grp) {
        $tUge		= array(
            'id' => $id,
            'identity' => $grp['identity'],
            'name' => $grp['name'],
        );

        $tUgroup []= $tUge;
    }

    $tVars	= array(
        'php_self' => $PHP_SELF,
        'rpp' => $fRPP,
        'name' => (isset($_REQUEST['name']) and $_REQUEST['name'])?htmlspecialchars($_REQUEST['name'], ENT_COMPAT | ENT_HTML401, 'UTF-8'):'',
        'token' => genUToken('admin.users'),
        'pagination' => $pagination,
        'ugroup' => $tUgroup,
        'entries' => $tEntries,
        'group' => isset($_REQUEST['group'])?intval($_REQUEST['group']):0,
        'sortLink' => $sortLinkMap,
        'localPrefix' => localPrefix,
        'flags' => 	array(
            'canModify' => $permModify?1:0,
            'canView' => $permDetails?1:0,
            'canMassAction' => $permMassAction?1:0,
            'haveComments' => $cPlugin->isInstalled('comments')?1:0,
            ),
        );

    echo $twig->render(tpl_actions . 'users/table.tpl', $tVars);

}

// ==============================================
// Actions
// ==============================================

if ($action == 'editForm') {
    userEditForm();
} else {
    switch ($action) {
        case 'edit'				: userEdit();				break;
        case 'add'				: userAdd();				break;
        case 'massActivate'		: userMassActivate();		break;
        case 'massLock'			: userMassLock();			break;
        case 'massSetStatus'	: userMassSetStatus();		break;
        case 'massDel'			: userMassDelete();			break;
        case 'massDelInactive'	: userMassDeleteInactive();	break;
    }
    userList();
}