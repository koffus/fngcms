<?php

//
// Copyright (C) 2006-2014 Next Generation CMS (http://ngcms.ru/)
// Name: lib_admin.php
// Description: General function for site administration calls
// Author: Vitaly Ponomarev
//

//
// Mass news flags modifier
// $list		- array with news identities [only 1 field should be filled]
//		'id'	- list of IDs
//		'data'	- list of records (result of SELECT query from DB)
// $setValue	- what to change in table (array with field => value)
// $permCheck	- flag if permissions should be checked (0 - don't check, 1 - check if current user have required rights)
//
// Return value: number of successfully updated news
function massModifyNews($list, $setValue, $permCheck = true)
{
    global $mysql, $PFILTERS, $catmap, $userROW;

    // Check if we have anything to update
    if (!is_array($list))
        return -1;

    // Check for security token
    if ($permCheck and ((!isset($_REQUEST['token'])) or ($_REQUEST['token'] != genUToken('admin.news.edit')))) {
        msg(array('type' => 'danger', 'title' => __('error.security.token'), 'message' => __('error.security.token#desc')));
        return -1;
    }

    // Load permissions
    $perm = checkPermission(array('plugin' => '#admin', 'item' => 'news'), null, array(
        'personal.modify',
        'personal.modify.published',
        'personal.publish',
        'personal.unpublish',
        'personal.delete',
        'personal.delete.published',
        'personal.mainpage',
        'personal.pinned',
        'personal.customdate',
        'other.view',
        'other.modify',
        'other.modify.published',
        'other.publish',
        'other.unpublish',
        'other.delete',
        'other.delete.published',
        'other.html',
        'other.mainpage',
        'other.pinned',
        'other.customdate',
    ));

    $nList = array();
    $nData = array();

    $results = array();
    $recList = array();

    if (isset($list['data'])) {
        $recList = $list['data'];
    } elseif (isset($list['id'])) {
        $SNQ = array();
        foreach ($list['id'] as $id)
            $SNQ [] = db_squote($id);

        $recList = $mysql->select("select * from " . prefix . "_news where id in (" . join(", ", $SNQ) . ")");
    } else {
        return array();
    }

    // Scan RECORDS and prepare output
    foreach ($recList as $rec) {
        // SKIP records if user has not enougt permissions
        if ($permCheck) {
            $isOwn = ($rec['author_id'] == $userROW['id']) ? 1 : 0;
            $permGroupMode = $isOwn ? 'personal' : 'other';

            // Manage `PUBLISHED` field
            $ic = 0;
            if (isset($setValue['approve'])) {
                if ((($rec['approve'] == 1) and ($setValue['approve'] != 1) and (!$perm[$permGroupMode . '.unpublish'])) or
                    (($rec['approve'] < 1) and ($setValue['approve'] == 1) and (!$perm[$permGroupMode . '.publish']))
                ) {
                    $results [] = '#' . $rec['id'] . ' (' . secure_html($rec['title']) . ') - ' . __('perm.denied');
                    continue;
                }
                $ic++;
            }

            // Manage `MAINPAGE` flag
            if (isset($setValue['mainpage'])) {
                if (!$perm[$permGroupMode . '.mainpage']) {
                    $results [] = '#' . $rec['id'] . ' (' . secure_html($rec['title']) . ') - ' . __('perm.denied');
                    continue;
                }
                $ic++;
            }

            // Check if we have other options except MAINPAGE/APPROVE
            if (count($setValue) > $ic) {
                if (!$perm[$permGroupMode . '.modify' . (($rec['approve'] == 1) ? '.published' : '')]) {
                    $results [] = '#' . $rec['id'] . ' (' . secure_html($rec['title']) . ') - ' . __('perm.denied');
                    continue;
                }
            }

//			if (($rec['status'] > 1) and ($rec['author_id'] != $userROW['id']))
//				continue;
        }
        $results [] = '#' . $rec['id'] . ' (' . secure_html($rec['title']) . ') - Ok';

        $nList[] = $rec['id'];
        $nData[$rec['id']] = $rec;
    }

    if (!count($nList))
        return $results;

    // Convert $setValue into SQL string
    $sqllSET = array();
    foreach ($setValue as $k => $v)
        $sqllSET[] = $k . " = " . db_squote($v);

    $sqlSET = join(", ", $sqllSET);

    // Call plugin filters
    if (is_array($PFILTERS['news']))
        foreach ($PFILTERS['news'] as $k => $v) {
            $v->massModifyNews($nList, $setValue, $nData);
        }

    $mysql->query("UPDATE " . prefix . "_news SET $sqlSET WHERE id in (" . join(", ", $nList) . ")");

    // Some activity if we change APPROVE flag for news
    if (isset($setValue['approve'])) {
        // Update user's news counters
        foreach ($nData as $nid => $ndata) {
            if (($ndata['approve'] == 1) and ($setValue['approve'] != 1)) {
                $mysql->query("update " . uprefix . "_users set news=news-1 where id = " . intval($ndata['author_id']));
            } else if (($ndata['approve'] != 1) and ($setValue['approve'] == 1)) {
                $mysql->query("update " . uprefix . "_users set news=news+1 where id = " . intval($ndata['author_id']));
            }
        }

        // DeApprove news
        if ($setValue['approve'] < 1) {
            // Count categories & counters to decrease - we have this news currently in _news_map because this news are marked as published
            foreach ($mysql->select("select categoryID, count(newsID) as cnt from " . prefix . "_news_map where newsID in (" . join(", ", $nList) . ") and categoryID > 0 group by categoryID") as $crec) {
                $mysql->query("update " . prefix . "_category set posts=posts-" . intval($crec['cnt']) . " where id = " . intval($crec['categoryID']));
            }

            // Delete news map
            $mysql->query("delete from " . prefix . "_news_map where newsID in (" . join(", ", $nList) . ")");
        } else if ($setValue['approve'] == 1) {
            // Approve news
            $clist = array();
            foreach ($nData as $nr) {
                // Skip already published news
                if ($nr['approve'] == 1) continue;

                // Calculate list
                $ncats = 0;
                foreach (explode(',', $nr['catid']) as $cid) {
                    if (!isset($catmap[$cid])) continue;
                    $clist[$cid]++;
                    $ncats++;
                    $mysql->query("insert into " . prefix . "_news_map (newsID, categoryID, dt) values (" . intval($nr['id']) . ", " . intval($cid) . ", from_unixtime(" . (($nr['editdate'] > $nr['postdate']) ? $nr['editdate'] : $nr['postdate']) . "))");
                }
                // Also put news without category into special category with ID = 0
                if (!$ncats) {
                    $mysql->query("insert into " . prefix . "_news_map (newsID, categoryID, dt) values (" . intval($nr['id']) . ", 0, from_unixtime(" . (($nr['editdate'] > $nr['postdate']) ? $nr['editdate'] : $nr['postdate']) . "))");
                }
            }
            foreach ($clist as $cid => $cv) {
                $mysql->query("update " . prefix . "_category set posts=posts+" . intval($cv) . " where id = " . intval($cid));
            }
        }
    }

    // Call plugin filters [ NOTIFY ABOUT MODIFICATION ]
    if (is_array($PFILTERS['news']))
        foreach ($PFILTERS['news'] as $k => $v) {
            $v->massModifyNewsNotify($nList, $setValue, $nData);
        }

    //return count($nList);
    return $results;
}

//
// Mass news delete function
// $list		- array with news identities
// $permCheck	- flag if permissions should be checked (0 - don't check, 1 - check if current user have required rights)
//
// Return value: number of successfully updated news
function massDeleteNews($list, $permCheck = true)
{
    global $mysql, $PFILTERS, $userROW;

    // Load CORE Plugin
    $cPlugin = CPlugin::instance();

    $selected_news = $_REQUEST['selected_news'];

    // Check for security token
    if ($permCheck and (!isset($_REQUEST['token'])) or ($_REQUEST['token'] != genUToken('admin.news.edit'))) {
        msg(array('type' => 'danger', 'title' => __('error.security.token'), 'message' => __('error.security.token#desc')));
        return;
    }

    if ((!is_array($list)) or (!count($list))) {
        msg(array('type' => 'danger', 'title' => __('msge_selectnews'), 'message' => __('msgi_selectnews')));
        return;
    }

    // Load permissions
    $perm = checkPermission(array('plugin' => '#admin', 'item' => 'news'), null, array(
        'personal.delete',
        'personal.delete.published',
        'other.delete',
        'other.delete.published',
    ));

    $results = array();

    // Scan list of news to be deleted
    foreach ($list as $id) {
        // Fetch news
        if (!is_array($nrow = $mysql->record("select * from " . prefix . "_news where id = " . db_squote($id)))) {
            // Skip ID's of non-existent news
            continue;
        }

        // Check for permissions
        $isOwn = ($nrow['author_id'] == $userROW['id']) ? 1 : 0;
        $permGroupMode = $isOwn ? 'personal' : 'other';

        if ((!$perm[$permGroupMode . '.delete' . (($nrow['approve'] == 1) ? '.published' : '')]) and $permCheck) {
            $results [] = '#' . $nrow['id'] . ' (' . secure_html($nrow['title']) . ') - ' . __('perm.denied');
            continue;
        }

        if (isset($PFILTERS['news']) and is_array($PFILTERS['news'])) {
            foreach ($PFILTERS['news'] as $k => $v) {
                $v->deleteNews($nrow['id'], $nrow);
            }
        }

        // Update counters only if news is published
        if ($nrow['approve'] == 1) {
            if ($nrow['catid']) {
                $oldcatsql = array();
                foreach (explode(',', $nrow['catid']) as $key) {
                    $oldcatsql[] = "id = " . db_squote($key);
                }
                $mysql->query("update " . prefix . "_category set posts=posts-1 where " . implode(" or ", $oldcatsql));
            }

            // Update user's posts counter
            if ($nrow['author_id']) {
                $mysql->query("update " . uprefix . "_users set news=news-1 where id=" . $nrow['author_id']);
            }
        }

        $mysql->query("delete from " . prefix . "_news where id=" . db_squote($nrow['id']));
        $mysql->query("delete from " . prefix . "_news_map where newsID = " . db_squote($nrow['id']));

        // Notify plugins about news deletion
        if (is_array($PFILTERS['news']))
            foreach ($PFILTERS['news'] as $k => $v) {
                $v->deleteNewsNotify($nrow['id'], $nrow);
            }

        // Delete attached news/files if any
        $fmanager = new FileManagment();
        // ** Files
        foreach ($mysql->select("select * from " . prefix . "_files where (storage=1) and (linked_ds=1) and (linked_id=" . db_squote($nrow['id']) . ")") as $frec) {
            $fmanager->file_delete(array('type' => 'file', 'id' => $frec['id']));
        }

        // ** Images
        foreach ($mysql->select("select * from " . prefix . "_images where (storage=1) and (linked_ds=1) and (linked_id=" . db_squote($nrow['id']) . ")") as $frec) {
            $fmanager->file_delete(array('type' => 'image', 'id' => $frec['id']));
        }

        $results [] = '#' . $nrow['id'] . ' (' . secure_html($nrow['title']) . ') - Ok';
    }
    msg(array('title' => __('news')['msgo_deleted'], 'message' => join("<br/>\n", $results)));
}

// Generate backup for table list. If no list is given - backup ALL tables with system prefix
function dbBackup($fname, $gzmode, $tlist = '')
{
    global $mysql;

    if ($gzmode and (!function_exists('gzopen')))
        $gzmode = 0;

    if ($gzmode)
        $fh = gzopen($fname, "w");
    else
        $fh = fopen($fname, "w");

    if ($fh === false)
        return 0;

    // Generate a list of tables for backup
    if (!is_array($tlist)) {
        $tlist = array();

        foreach ($mysql->select("show tables like '" . prefix . "_%'", 0) as $tn)
            $tlist [] = $tn[0];
    }

    // Now make a header
    $out = "# " . str_repeat('=', 60) . "\n# Backup file for `Next Generation CMS`\n# " . str_repeat('=', 60) . "\n# DATE: " . gmdate("d-m-Y H:i:s", time()) . " GMT\n# VERSION: " . engineVersion . "\n#\n";
    $out .= "# List of tables for backup: " . join(", ", $tlist) . "\n#\n";

    // Write a header
    if ($gzmode) gzwrite($fh, $out);
    else            fwrite($fh, $out);

    // Now, let's scan tables
    foreach ($tlist as $tname) {
        // Fetch create syntax for table and after - write table's content
        if (is_array($csql = $mysql->record("show create table `" . $tname . "`", -1))) {
            $out = "\n#\n# Table `" . $tname . "`\n#\n";
            $out .= "DROP TABLE IF EXISTS `" . $tname . "`;\n";
            $out .= $csql[1] . ";\n";

            if ($gzmode) gzwrite($fh, $out);
            else            fwrite($fh, $out);

            // Now let's make content of the table
            $query = $mysql->query("select * from `" . $tname . "`");
            $rowNo = 0;
            while ($row = $mysql->fetch_row($query)) {
                $out = "insert into `" . $tname . "` values (";
                $rowNo++;
                $colNo = 0;
                foreach ($row as $v)
                    $out .= (($colNo++) ? ', ' : '') . db_squote($v);
                $out .= ");\n";

                if ($gzmode) gzwrite($fh, $out);
                else            fwrite($fh, $out);
            }

            $out = "# Total records: $rowNo\n";

            if ($gzmode) gzwrite($fh, $out);
            else            fwrite($fh, $out);
        } else {
            $out = "#% Error fetching information for table `$tname`\n";

            if ($gzmode) gzwrite($fh, $out);
            else            fwrite($fh, $out);
        }
    }
    if ($gzmode) gzclose($fh);
    else            fclose($fh);

    return 1;
}

// ======================================================================================================
// Add news
// ======================================================================================================
// $mode - calling mode - !!! NOT COMPATABLE
//  * 'no.meta' - disable metatags
//  * 'no.files' - disable files
//  * 'no.token' - do not check for security token
//  * 'no.editurl' - do now show URL (in admin panel) for edit news
function addNews($mode = array())
{
    global $mysql, $userROW, $parse, $PFILTERS, $config, $catz, $catmap;

    // Check for security token
    if (!isset($_POST['token']) or $_POST['token'] != genUToken('admin.news.add')) {
        msg(array('type' => 'danger', 'title' => __('error.security.token'), 'message' => __('error.security.token#desc')));
        return;
    }

    // Load permissions
    $perm = checkPermission(array('plugin' => '#admin', 'item' => 'news'), null, array(
        'add',
        'add.approve',
        'add.mainpage',
        'add.pinned',
        'add.favorite',
        'add.html',
        'personal.view',
        'personal.modify',
        'personal.modify.published',
        'personal.publish',
        'personal.unpublish',
        'personal.delete',
        'personal.delete.published',
        'personal.html',
        'personal.mainpage',
        'personal.pinned',
        'personal.catpinned',
        'personal.favorite',
        'personal.setviews',
        'personal.multicat',
        'personal.nocat',
        'personal.customdate',
        'personal.altname',
    ));

    // Check for modify permissions
    if (!$perm['add']) {
        msg(array('type' => 'danger', 'message' => __('perm.denied')));
        return 0;
    }

    /*
     * Now, prepare data
     */
    $SQL = array();
    $SQL['title'] = secure_html($_POST['title']);
    $SQL['alt_name'] = empty($_POST['alt_name']) ? $SQL['title'] : secure_html($_POST['alt_name']);
    // in any case, do new alt name in automatic mode
    $SQL['alt_name'] = $parse->translit($SQL['alt_name'], $config['news_translit']);
    $SQL['content'] = trim(str_replace("\r\n", "\n", $_POST['ng_news_content']));
    $_POST['description'] = isset($_POST['description']) ? secure_html(str_replace(["\r\n", "\n"], ' ', $_POST['description'])) : '';
    $_POST['keywords'] = isset($_POST['keywords']) ? secure_html($_POST['keywords']) : '';

    // Metatags (only for adding via admin panel)
    if ($config['meta']) {
        $SQL['description'] = $_POST['description'];
        $SQL['keywords'] = $_POST['keywords'];
    }

    $SQL['author'] = secure_html($userROW['name']);
    $SQL['author_id'] = intval($userROW['id']);

    $SQL['approve'] = (isset($_POST['approve']) and $perm['personal.publish']) ? intval($_POST['approve']) : 0;
    $SQL['mainpage'] = (isset($_POST['mainpage']) and $perm['personal.mainpage']) ? (intval($_POST['mainpage'])) : 0;
    $SQL['pinned'] = (isset($_POST['pinned']) and $perm['personal.pinned']) ? (intval($_POST['pinned'])) : 0;
    $SQL['catpinned'] = (isset($_POST['catpinned']) and $perm['personal.catpinned']) ? (intval($_POST['catpinned'])) : 0;
    $SQL['favorite'] = (isset($_POST['favorite']) and $perm['personal.favorite']) ? (intval($_POST['favorite'])) : 0;

    $_POST['flag_RAW'] = isset($_POST['flag_RAW']) ? intval($_POST['flag_RAW']) : 0;
    $_POST['flag_HTML'] = isset($_POST['flag_HTML']) ? intval($_POST['flag_HTML']) : 0;
    $_POST['postdate'] = secure_html($_POST['postdate']);
    $_POST['customdate'] = isset($_POST['customdate']) ? intval($_POST['customdate']) : 0;

    // Check title
    if ((!mb_strlen($SQL['title'], 'UTF-8') or !mb_strlen($SQL['content'], 'UTF-8')) and (!$config['news_without_content'])) {
        msg(array('type' => 'danger', 'title' => __('news')['msge_fields'], 'message' => __('news')['msgi_fields']));
        return 0;
    }

    // check for empty or duplicate alt_name
    if (empty($SQL['alt_name']) or is_array($mysql->record("select id from ".prefix."_news where alt_name = ".db_squote($SQL['alt_name'])." limit 1"))) {
        msg(array('type' => 'info', 'title' => __('news')['msge_alt_name'], 'message' => __('news')['msgi_alt_name']));
        $SQL['alt_name'] .= '_' . date("Y-m-d-H-i-s");
    }

    // Custom date[ only while adding via admin panel ]
    if ($_POST['customdate'] and $perm['personal.customdate']) {
        if (preg_match('#^(\d+)\.(\d+)\.(\d+) +(\d+)\:(\d+)$#', $_POST['postdate'], $m)) {
            $SQL['postdate'] = mktime($m[4], $m[5], 0, $m[2], $m[1], $m[3]) + ($config['date_adjust'] * 60);
        }
    } else {
        $SQL['postdate'] = time() + ($config['date_adjust'] * 60);
    }

    $SQL['editdate'] = $SQL['postdate'];

    // Fetch MASTER provided categories
    $catids = [];
    if (intval($_POST['category']) and isset($catmap[intval($_POST['category'])])) {
        $catids[intval($_POST['category'])] = 1;
    }

    // Fetch ADDITIONAL provided categories [if allowed]
    if ($perm['personal.multicat']) {
        foreach ($_POST as $k => $v) {
            if (preg_match('#^category_(\d+)$#', $k, $match) and $v and isset($catmap[intval($match[1])]))
                $catids[$match[1]] = 1;
        }
    }

    // Check if no categories specified and user can post news without categories
    if ((!count($catids)) and (!$perm['personal.nocat'])) {
        msg(array('type' => 'danger', 'title' => __('news')['error.nocat'], 'message' => __('news')['error.nocat#desc']));
        return 0;
    }

    $SQL['catid'] = implode(',', array_keys($catids));

    // Variable FLAGS is a bit-variable:
    // 0 = RAW mode [if set, no conversion "\n" => "<br />" will be done]
    // 1 = HTML enable [if set, HTML codes may be used in news]
    if ($perm['personal.html']) {
        $SQL['flags'] = ($_POST['flag_RAW'] ? 1 : 0) + ($_POST['flag_HTML'] ? 2 : 0);
    } else {
        $SQL['flags'] = 0;
    }

    // Dummy parameter for API call
    $tvars = array();

    executeActionHandler('addnews');

    $pluginNoError = 1;
    if (is_array($PFILTERS['news']))
        foreach ($PFILTERS['news'] as $k => $v) {
            if (!($pluginNoError = $v->addNews($tvars, $SQL))) {
                msg(array('type' => 'danger', 'message' => str_replace('{plugin}', $k, __('news')['msge_pluginlock'])));
                break;
            }
        }

    if (!$pluginNoError) {
        return 0;
    }

    $vnames = array();
    $vparams = array();
    foreach ($SQL as $k => $v) {
        $vnames[] = $k;
        $vparams[] = db_squote($v);
    }

    $mysql->query("insert into " . prefix . "_news (" . implode(",", $vnames) . ") values (" . implode(",", $vparams) . ")");
    $id = $mysql->result("SELECT LAST_INSERT_ID() as id");

    // Update category / user posts counter [ ONLY if news is approved ]
    if ($SQL['approve'] == 1) {
        if (count($catids)) {
            $mysql->query("update " . prefix . "_category set posts=posts+1 where id in (" . implode(", ", array_keys($catids)) . ")");
            foreach (array_keys($catids) as $catid) {
                $mysql->query("insert into " . prefix . "_news_map (newsID, categoryID, dt) values (" . db_squote($id) . ", " . db_squote($catid) . ", now())");
            }
        } else {
            $mysql->query("insert into " . prefix . "_news_map (newsID, categoryID, dt) values (" . db_squote($id) . ", 0, now())");
        }
        $mysql->query("update " . uprefix . "_users set news=news+1 where id=" . db_squote($SQL['author_id']));
    }

    // Now let's manage attached files
    if (true)
    {
        $fmanager = new FileManagment();
        $flagUpdateAttachCount = false;
        // Delete files (if needed)
        foreach ($_POST as $k => $v) {
            if (preg_match('#^delfile_(\d+)$#', $k, $match)) {
                $fmanager->file_delete(array('type' => 'file', 'id' => $match[1]));
                $flagUpdateAttachCount = true;
            }
        }
        // PREPARE a list for upload
        if (is_array($_FILES['userfile']['name'])) {
            foreach ($_FILES['userfile']['name'] as $i => $v) {
                if ($v == '') continue;
                $flagUpdateAttachCount = true;
                $up = $fmanager->file_upload(array('dsn' => true, 'linked_ds' => 1, 'linked_id' => $id, 'type' => 'file', 'http_var' => 'userfile', 'http_varnum' => $i));
                if (!is_array($up)) {
                    // Error uploading file
                    // ... show error message ...
                }
            }
        }
        // Update attach count if we need this
        $numFiles = $mysql->result("select count(*) as cnt from " . prefix . "_files where (storage=1) and (linked_ds=1) and (linked_id=" . db_squote($id) . ")");
        if ($numFiles) {
            $mysql->query("update " . prefix . "_news set num_files = " . intval($numFiles) . " where id = " . db_squote($id));
        }
        $numImages = $mysql->result("select count(*) as cnt from " . prefix . "_images where (storage=1) and (linked_ds=1) and (linked_id=" . db_squote($id) . ")");
        if ($numImages) {
            $mysql->query("update " . prefix . "_news set num_images = " . intval($numImages) . " where id = " . db_squote($id));
        }
    }

    // Notify plugins about adding new news
    if (isset($PFILTERS['news']) and is_array($PFILTERS['news'])) {
        foreach ($PFILTERS['news'] as $k => $v) {
            $v->addNewsNotify($tvars, $SQL, $id);
        }
    }

    executeActionHandler('addnews_');

    $msgInfo = array('title' => __('news')['msgo_added']);
    if ($perm['personal.modify']) {
        $msgInfo['message'] = sprintf(__('news')['msgi_added'], admin_url . '/admin.php?mod=news&action=edit&id=' . $id, admin_url . '/admin.php?mod=news');
    }
    msg($msgInfo);

    return 1;
}

// ======================================================================================================
// Edit news
// ======================================================================================================
// $mode - calling mode [ we can disable processing of some features/functions ]
//	*	'no.meta'	- disable changing metatags
//	*	'no.files'	- disable updating files
//	*	'no.token'	- do not check for security token
function editNews($mode = array())
{
    global $parse, $mysql, $config, $PFILTERS, $userROW, $catmap;

    // Check for security token
    if (!isset($_POST['token']) or $_POST['token'] != genUToken('admin.news.edit')) {
        msg(array('type' => 'danger', 'title' => __('error.security.token'), 'message' => __('error.security.token#desc')));
        return;
    }

    // Load permissions
    $perm = checkPermission(array('plugin' => '#admin', 'item' => 'news'), null, array(
        'personal.view',
        'personal.modify',
        'personal.modify.published',
        'personal.publish',
        'personal.unpublish',
        'personal.delete',
        'personal.delete.published',
        'personal.html',
        'personal.mainpage',
        'personal.pinned',
        'personal.catpinned',
        'personal.favorite',
        'personal.setviews',
        'personal.multicat',
        'personal.nocat',
        'personal.customdate',
        'personal.altname',
        'other.view',
        'other.modify',
        'other.modify.published',
        'other.publish',
        'other.unpublish',
        'other.delete',
        'other.delete.published',
        'other.html',
        'other.mainpage',
        'other.pinned',
        'other.catpinned',
        'other.favorite',
        'other.setviews',
        'other.multicat',
        'other.nocat',
        'other.customdate',
        'other.altname',
    ));

    // Try to find news that we're trying to edit
    $id = isset($_REQUEST['id']) ? intval($_REQUEST['id']) : 0;
    if (!$id or !is_array($row = $mysql->record("SELECT * FROM " . prefix . "_news WHERE id=" . db_squote($id)))) {
        msg(array('type' => 'danger', 'message' => __('news')['msge_not_found']));
        return;
    }

    // Check permissions
    $isOwn = ($row['author_id'] == $userROW['id']) ? 1 : 0;
    $permGroupMode = $isOwn ? 'personal' : 'other';
    if (!$perm[$permGroupMode . '.modify' . (($row['approve'] == 1) ? '.published' : '')]) {
        msg(array('type' => 'danger', 'message' => __('perm.denied')));
        return;
    }

    /*
     * Now, prepare data
     */
    $SQL = array();
    $SQL['title'] = secure_html($_POST['title']);
    $SQL['content'] = trim(str_replace("\r\n", "\n", $_POST['ng_news_content']));
    // Check title and content
    if ((!mb_strlen($SQL['title'], 'UTF-8') or !mb_strlen($SQL['content'], 'UTF-8')) and (!$config['news_without_content'])) {
        msg(array('type' => 'danger', 'title' => __('news')['msge_fields'], 'message' => __('news')['msgi_fields']));
        return -1;
    }

    // In any case, do new alt name in automatic mode
    // !$perm[$permGroupMode . '.altname']
    $SQL['alt_name'] = empty($_POST['alt_name']) ? $SQL['title'] : secure_html($_POST['alt_name']);
    $SQL['alt_name'] = $parse->translit($SQL['alt_name'], $config['news_translit']);
    // Check for empty or duplicate alt_name
    if (empty($SQL['alt_name']) or is_array($mysql->record("SELECT id FROM ".prefix."_news WHERE alt_name = ".db_squote($SQL['alt_name'])." AND id <> ".intval($id)." LIMIT 1"))) {
        msg(array('type' => 'info', 'title' => __('news')['msge_alt_name'], 'message' => __('news')['msgi_alt_name']));
        $SQL['alt_name'] .= '_' . date("Y-m-d-H-i-s");
    }
    if ($config['meta'] and isset($_POST['description'])) {
        $SQL['description'] = secure_html(str_replace(["\r\n", "\n"], ' ', $_POST['description']));
    }
    if ($config['meta'] and isset($_POST['keywords'])) {
        $SQL['keywords'] = secure_html($_POST['keywords']);
    }

    // Generate SQL old cats list
    $oldcatids = array();
    foreach (explode(',', $row['catid']) as $cat) {
        if (preg_match('#^(\d+)$#', trim($cat), $cmatch)) {
            $oldcatids[$cmatch[1]] = 1;
        }
    }
    // Fetch MASTER provided categories
    $catids = [];
    if (intval($_POST['category']) and isset($catmap[intval($_POST['category'])])) {
        $catids[intval($_POST['category'])] = 1;
    }
    // Fetch ADDITIONAL provided categories [if allowed]
    if ($perm[$permGroupMode . '.multicat']) {
        foreach ($_POST as $k => $v) {
            if (preg_match('#^category_(\d+)$#', $k, $match) and $v and isset($catmap[intval($match[1])]))
                $catids[$match[1]] = 1;
        }
    }
    // Check if no categories specified and user can post news without categories
    if (!count($catids) and !$perm[$permGroupMode . '.nocat']) {
        msg(array('type' => 'danger', 'title' => __('news')['error.nocat'], 'message' => __('news')['error.nocat#desc']));
        return 0;
    }
    $SQL['catid'] = implode(",", array_keys($catids));

    // Generate info about custom date news
    if ($perm[$permGroupMode . '.customdate']) {
        if (isset($_POST['setdate_custom'])) {
            if (preg_match('#^(\d+)\.(\d+)\.(\d+) +(\d+)\:(\d+)$#', secure_html($_POST['postdate']), $m)) {
                $SQL['postdate'] = mktime($m[4], $m[5], 0, $m[2], $m[1], $m[3]);
            }
        } elseif (isset($_POST['setdate_current'])) {
            $SQL['postdate'] = time() + ($config['date_adjust'] * 60);
        }
    }
    $SQL['editdate'] = time() + ($config['date_adjust'] * 60);

    // Change this parameters if user have enough access level
    $_POST['mainpage'] = isset($_POST['mainpage']) ? intval($_POST['mainpage']) : 0;
    $_POST['pinned'] = isset($_POST['pinned']) ? intval($_POST['pinned']) : 0;
    $_POST['catpinned'] = isset($_POST['catpinned']) ? intval($_POST['catpinned']) : 0;
    $_POST['favorite'] = isset($_POST['favorite']) ? intval($_POST['favorite']) : 0;
    
    $SQL['mainpage'] = ($perm[$permGroupMode . '.mainpage'] and $_POST['mainpage']) ? 1 : 0;
    $SQL['pinned'] = ($perm[$permGroupMode . '.pinned'] and $_POST['pinned']) ? 1 : 0;
    $SQL['catpinned'] = ($perm[$permGroupMode . '.catpinned'] and $_POST['catpinned']) ? 1 : 0;
    $SQL['favorite'] = ($perm[$permGroupMode . '.favorite'] and $_POST['favorite']) ? 1 : 0;
    
    $_POST['approve'] = isset($_POST['approve']) ? intval($_POST['approve']) : 0;

    switch ($_POST['approve']) {
        case -1:
            $SQL['approve'] = -1;
            break;
        case 0:
            $SQL['approve'] = 0;
            break;
        case 1:
            $SQL['approve'] = (($row['approve'] == 1) or (($row['approve'] < 1) and ($perm[$permGroupMode . '.publish']))) ? 1 : 0;
            break;
        default:
            $SQL['approve'] = 0;
    }

    // Variable FLAGS is a bit-variable:
    // 0 = RAW mode		[if set, no conversion "\n" => "<br />" will be done]
    // 1 = HTML enable	[if set, HTML codes may be used in news]
    $_POST['flag_RAW'] = isset($_POST['flag_RAW']) ? intval($_POST['flag_RAW']) : 0;
    $_POST['flag_HTML'] = isset($_POST['flag_HTML']) ? intval($_POST['flag_HTML']) : 0;
    $SQL['flags'] = ($perm[$permGroupMode . '.html']) ? (($_POST['flag_RAW'] ? 1 : 0) + ($_POST['flag_HTML'] ? 2 : 0)) : 0;

    if ($perm[$permGroupMode . '.setviews'] and isset($_POST['setViews'])) {
        $SQL['views'] = intval($_POST['views']);
    }

    // Load list of attached images/files
    $row['#files'] = $mysql->select("select *, date_format(from_unixtime(date), '%d.%m.%Y') as date from " . prefix . "_files where (linked_ds = 1) and (linked_id = " . db_squote($row['id']) . ')', 1);
    $row['#images'] = $mysql->select("select *, date_format(from_unixtime(date), '%d.%m.%Y') as date from " . prefix . "_images where (linked_ds = 1) and (linked_id = " . db_squote($row['id']) . ')', 1);

    // Dummy parameter for API call
    $tvars = array();

    executeActionHandler('editnews');

    $pluginNoError = 1;
    if (is_array($PFILTERS['news']))
        foreach ($PFILTERS['news'] as $k => $v) {
            if (!($pluginNoError = $v->editNews($id, $row, $SQL, $tvars))) {
                msg(array('type' => 'danger', 'message' => str_replace('{plugin}', $k, __('news')['msge_pluginlock'])));
                break;
            }
        }

    if (!$pluginNoError) {
        return;
    }

    $SQLparams = array();
    foreach ($SQL as $k => $v) {
        $SQLparams[] = $k . ' = ' . db_squote($v);
    }

    $mysql->query("update " . prefix . "_news set " . implode(", ", $SQLparams) . " where id = " . db_squote($id));

    // Update category posts counters
    if (($row['approve'] == 1) and sizeof($oldcatids)) {
        $mysql->query("update " . prefix . "_category set posts=posts-1 where id in (" . implode(",", array_keys($oldcatids)) . ")");
    }

    $mysql->query("delete from " . prefix . "_news_map where newsID = " . db_squote($id));

    // Check if we need to update user's counters [ only if news was or will be published ]
    if (($row['approve'] != $SQL['approve']) and (($row['approve'] == 1) or ($SQL['approve'] == 1))) {
        $mysql->query("update " . uprefix . "_users set news=news" . (($row['approve'] == 1) ? '-' : '+') . "1 where id=" . $row['author_id']);
    }

    if ($SQL['approve'] == 1) {
        if (sizeof($catids)) {
            $mysql->query("update " . prefix . "_category set posts=posts+1 where id in (" . implode(",", array_keys($catids)) . ")");
            foreach (array_keys($catids) as $catid) {
                $mysql->query("insert into " . prefix . "_news_map (newsID, categoryID, dt) values (" . db_squote($id) . ", " . db_squote($catid) . ", from_unixtime(" . intval($SQL['editdate']) . "))");
            }
        } else {
            $mysql->query("insert into " . prefix . "_news_map (newsID, categoryID, dt) values (" . db_squote($id) . ", 0, from_unixtime(" . intval($SQL['editdate']) . "))");
        }
    }

    // Now let's manage attached files
    if (true)
    {

        // Now let's manage attached files
        $fmanager = new FileManagment();

        $flagUpdateAttachCount = false;

        // Delete files (if needed)
        foreach ($_POST as $k => $v) {
            if (preg_match('#^delfile_(\d+)$#', $k, $match)) {
                $fmanager->file_delete(array('type' => 'file', 'id' => $match[1]));
                $flagUpdateAttachCount = true;
            }
        }

        // PREPARE a list for upload
        if (is_array($_FILES['userfile']['name'])) {
            foreach ($_FILES['userfile']['name'] as $i => $v) {
                if ($v == '')
                    continue;

                $flagUpdateAttachCount = true;
                //
                $up = $fmanager->file_upload(array('dsn' => true, 'linked_ds' => 1, 'linked_id' => $id, 'type' => 'file', 'http_var' => 'userfile', 'http_varnum' => $i));
                //print "OUT: <pre>".var_export($up, true)."</pre>";
                if (!is_array($up)) {
                    // Error uploading file
                    // ... show error message ...
                }

            }
        }

        // Update attach count if we need this
        $numFiles = $mysql->result("select count(*) as cnt from " . prefix . "_files where (storage=1) and (linked_ds=1) and (linked_id=" . db_squote($id) . ")");
        if ($numFiles != $row['num_files']) {
            $mysql->query("update " . prefix . "_news set num_files = " . intval($numFiles) . " where id = " . db_squote($id));
        }

        $numImages = $mysql->result("select count(*) as cnt from " . prefix . "_images where (storage=1) and (linked_ds=1) and (linked_id=" . db_squote($id) . ")");
        if ($numImages != $row['num_images']) {
            $mysql->query("update " . prefix . "_news set num_images = " . intval($numImages) . " where id = " . db_squote($id));
        }
    }

    // Notify plugins about news edit completion
    if (isset($PFILTERS['news']) and is_array($PFILTERS['news'])) {
        foreach ($PFILTERS['news'] as $k => $v) {
            $v->editNewsNotify($id, $row, $SQL, $tvars);
        }
    }

    // Fetch again news record
    if (is_array($row = $mysql->record("select id from " . prefix . "_news where id=" . db_squote($id)))) {
        msg(array('message' => __('news')['msgo_edited']));
    } else {
        msg(array('type' => 'danger', 'message' => 'Щто-то пожло не таг'));
    }

    return 1;
}

function admcookie_get()
{
    if (isset($_COOKIE['ng_adm']) and is_array($x = unserialize($_COOKIE['ng_adm'])))
        return $x;

    return array();
}

function admcookie_set($x = array())
{
    return setcookie('ng_adm', serialize($x), time() + 365 * 86400);
}

function showPreview()
{
    global $userROW, $EXTRA_CSS, $EXTRA_HTML_VARS, $PFILTERS, $tpl, $parse, $mysql, $config, $catmap;

    // Load permissions
    $perm = checkPermission(array('plugin' => '#admin', 'item' => 'news'), null, array(
        'personal.html',
    ));

    // Now, prepare data
    $SQL = array('id' => -1);
    $SQL['title'] = secure_html($_POST['title']);
    $SQL['alt_name'] = empty($_POST['alt_name']) ? $SQL['title'] : secure_html($_POST['alt_name']);
    // in any case, do new alt name in automatic mode
    $SQL['alt_name'] = $parse->translit($SQL['alt_name'], $config['news_translit']);
    $SQL['content'] = trim(str_replace("\r\n", "\n", $_POST['ng_news_content']));
    $SQL['author'] = secure_html($userROW['name']);
    $SQL['author_id'] = intval($userROW['id']);
    if ($config['meta']) {
        $SQL['description'] = secure_html(str_replace(["\r\n", "\n"], ' ', $_POST['description']));
        $SQL['keywords'] = secure_html($_POST['keywords']);
    }
    // This actions are allowed only for admins & Edtiors
    if (($userROW['status'] == 1) or ($userROW['status'] == 2)) {
        $SQL['mainpage'] = isset($_POST['mainpage']) ? intval($_POST['mainpage']) : 0;
        $SQL['approve'] = isset($_POST['mainpage']) ? intval($_POST['mainpage']) : 0;
        $SQL['favorite'] = isset($_POST['mainpage']) ? intval($_POST['mainpage']) : 0;
        $SQL['pinned'] = isset($_POST['mainpage']) ? intval($_POST['mainpage']) : 0;
    }
    $SQL['allow_com'] = isset($_POST['allow_com']) ? intval($_POST['allow_com']) : 0;

    // Calibre to date
    $_POST['postdate'] = secure_html($_POST['postdate']);
    $_POST['setdate_custom'] = isset($_POST['setdate_custom']) ? intval($_POST['setdate_custom']) : 0;
    if ($_POST['setdate_custom'] and preg_match('#^(\d+)\.(\d+)\.(\d+) +(\d+)\:(\d+)$#', $_POST['postdate'], $m)) {
        $SQL['postdate'] = mktime($m[4], $m[5], 0, $m[2], $m[1], $m[3]);
    } else {
        $SQL['postdate'] = time() + ($config['date_adjust'] * 60);
    }
    $SQL['editdate'] = '';

    // Variable FLAGS is a bit-variable:
    // 0 = RAW mode		[if set, no conversion "\n" => "<br />" will be done]
    // 1 = HTML enable	[if set, HTML codes may be used in news]
    $SQL['flag_RAW'] = isset($_POST['flag_RAW']) ? intval($_POST['allow_com']) : 0;
    $SQL['flag_HTML'] = isset($_POST['flag_HTML']) ? intval($_POST['flag_HTML']) : 0;
    $SQL['flags'] = $perm['personal.html'] ? (($SQL['flag_RAW'] ? 1 : 0) + ($SQL['flag_HTML'] ? 2 : 0)) : 0;

    // Fetch MASTER provided categories
    $catids = [];
    if (intval($_POST['category']) and isset($catmap[intval($_POST['category'])])) {
        $catids[intval($_POST['category'])] = 1;
    }
    // Fetch ADDITIONAL provided categories
    foreach ($_POST as $k => $v) {
        if (preg_match('#^category_(\d+)$#', $k, $match) and $v and isset($catmap[intval($_POST['category'])]))
            $catids[$match[1]] = 1;
    }
    $SQL['catid'] = implode(",", array_keys($catids));

    // Make a fantasy data
    $SQL['com'] = rand(88, 888);
    $SQL['views'] = rand(88, 888);
    $SQL['rating'] = '5';
    $SQL['votes'] = rand(88, 888);

    // Process plugin variables to make proper SQL filling
    $tvx = array();
    if (isset($PFILTERS['news']) and is_array($PFILTERS['news'])) {
        foreach ($PFILTERS['news'] as $k => $v) {
            $v->editNews(-1, $SQL, $SQL, $tvx);
        }
    }

    $tvx = array();
    $tvx['vars']['short'] = news_showone(-1, '', array('emulate' => $SQL, 'style' => 'short'));
    $tvx['vars']['full'] = news_showone(-1, '', array('emulate' => $SQL, 'style' => 'full'));

    // Fill extra CSS links
    foreach ($EXTRA_CSS as $css => $null)
        $EXTRA_HTML_VARS[] = array('type' => 'css', 'data' => $css);

    // Generate metatags
    $EXTRA_HTML_VARS[] = array('type' => 'plain', 'data' => GetMetatags());

    // Fill additional HTML vars
    $htmlrow = array();
    $dupCheck = array();
    foreach ($EXTRA_HTML_VARS as $htmlvar) {
        // Skip empty
        if (!$htmlvar['data'])
            continue;

        // Check for duplicated rows
        if (in_array($htmlvar['data'], $dupCheck))
            continue;
        $dupCheck[] = $htmlvar['data'];

        switch ($htmlvar['type']) {
            case 'css':
                $htmlrow[] = '<link href="' . $htmlvar['data'] . '" rel="stylesheet" />';
                break;
            case 'js':
                $htmlrow[] = '<script src="' . $htmlvar['data'] . '"></script>';
                break;
            case 'rss':
                $htmlrow[] = '<link href="' . $htmlvar['data'] . '" rel="alternate" type="application/rss+xml" title="RSS" />';
                break;
            case 'plain':
                $htmlrow[] = $htmlvar['data'];
                break;
        }
    }

    if (count($htmlrow))
        $tvx['vars']['htmlvars'] = join("\n", $htmlrow);

    $tpl->template('preview', tpl_actions);
    $tpl->vars('preview', $tvx);
    echo $tpl->show('preview');
}