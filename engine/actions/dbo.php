<?php

//
// Copyright (C) 2006-2012 Next Generation CMS (http://ngcms.ru/)
// Name: dbo.php
// Description: Database managment
// Author: Vitaly Ponomarev, Alexey Zinchenko
//

// Protect against hack attempts
if (!defined('NGCMS')) die ('HAL');

// Load language
Lang::load('dbo', 'admin', 'dbo');

//
// Modify data request
function systemDboModify()
{
    global $config, $mysql, $catz;

    $id = (getIsSet($_REQUEST['id']))?intval($_REQUEST['id']):0;

    // Check for permissions
    if (!checkPermission(array('plugin' => '#admin', 'item' => 'dbo'), null, 'modify')) {
        msg(array('type' => 'danger', 'message' => __('perm.denied')));
        ngSYSLOG(array('plugin' => '#admin', 'item' => 'dbo', 'ds_id' => $id), array('action' => 'modify'), null, array(0, 'SECURITY.PERM'));
        return false;
    }

    // Check for security token
    if ((!isset($_REQUEST['token'])) or ($_REQUEST['token'] != genUToken('admin.dbo'))) {
        msg(array('type' => 'danger', 'title' => __('error.security.token'), 'message' => __('error.security.token#desc')));
        ngSYSLOG(array('plugin' => '#admin', 'item' => 'dbo', 'ds_id' => $id), array('action' => 'modify'), null, array(0, 'SECURITY.TOKEN'));
        return false;
    }

    // Load CORE Plugin
    $cPlugin = CPlugin::instance();

    // Update message counters
    if (getIsSet($_REQUEST['cat_recount']))
    {
        // Обновляем счётчики в категориях
        $ccount = array();
        $nmap = '';
        foreach ($mysql->select("SELECT id, catid, postdate, editdate FROM " . prefix . "_news WHERE approve=1") as $row) {
            $ncats = 0;
            foreach (explode(',', $row['catid']) as $key) {
                if (!$key) {
                    continue;
                }
                $ncats++;
                $nmap .= '(' . $row['id'] . ',' . $key . ',from_unixtime(' . (($row['editdate'] > $row['postdate']) ? $row['editdate'] : $row['postdate']) . ')),';
                if (empty($ccount[$key])) {
                    $ccount[$key] = 1;
                } else {
                    $ccount[$key] += 1;
                }
            }
            if (!$ncats) {
                $nmap .= '(' . $row['id'] . ',0,from_unixtime(' . (($row['editdate'] > $row['postdate']) ? $row['editdate'] : $row['postdate']) . ')),';
            }
        }

        // Update table `news_map`
        $mysql->query("truncate table " . prefix . "_news_map");

        if (strlen($nmap))
            $mysql->query("INSERT into " . prefix . "_news_map (newsID, categoryID, dt) values " . substr($nmap, 0, -1));

        // Update category news counters
        foreach ($catz as $key) {
            $mysql->query("UPDATE " . prefix . "_category SET posts = " . intval(getIsSet($ccount[$key['id']])) . " WHERE id = " . $key['id']);
        }

        // Check if we can update comments counters
        if ($cPlugin->isActive('comments')) {
            $haveComments = $mysql->table_exists(prefix . "_comments") ? true : false;

            if ($haveComments) {
                // Обновляем счетчик комментариев в новостях
                $rows = $mysql->select("SELECT n.id, count(c.id) AS cid FROM " . prefix . "_news n LEFT JOIN " . prefix . "_comments c on c.post=n.id  AND module='news' GROUP BY n.id");
                foreach ($rows as $row) {
                    $mysql->query("UPDATE " . prefix . "_news SET com=" . $row['cid'] . " WHERE id = " . $row['id']);
                }

                // Обновляем счетчик комментариев в плагине gallery
                if ($cPlugin->isActive('gallery')) {
                    $rows = $mysql->select("SELECT i.id, count(c.id) AS cid FROM " . prefix . "_images i LEFT JOIN " . prefix . "_comments c on c.post=i.id  AND module='news' GROUP BY i.id");
                    foreach ($rows as $row) {
                        $mysql->query("UPDATE " . prefix . "_images SET com=" . $row['cid'] . " WHERE id = " . $row['id']);
                    }
                }
            }

            if ($haveComments) {
                // ОбнУляем счетчик постов и комментариев у юзеров
                $mysql->query("UPDATE " . prefix . "_users SET news = 0, com = 0");
                // Обновляем счетчик комментариев у юзеров
                foreach ($mysql->select("select author_id, count(*) as cnt from " . prefix . "_comments group by author_id") as $row) {
                    $mysql->query("update " . uprefix . "_users set com=" . $row['cnt'] . " where id = " . $row['author_id']);
                }
            }
        } else {
            // ОбнУляем счетчик постов у юзеров
            $mysql->query("UPDATE " . prefix . "_users SET news = 0");
        }

        // Обновляем счетчик постов у юзеров
        foreach ($mysql->select("SELECT author_id, count(*) AS cnt FROM " . prefix . "_news GROUP BY author_id") as $row) {
            $mysql->query("UPDATE " . uprefix . "_users SET news=" . $row['cnt'] . " WHERE id = " . $row['author_id']);
        }

        // Обновляем кол-во приложенных файлов/изображений к новостям
        $mysql->query("update " . prefix . "_news set num_files = 0, num_images = 0");
        foreach ($mysql->select("select linked_id, count(id) as cnt from " . prefix . "_files where (storage=1) and (linked_ds=1) group by linked_id") as $row) {
            $mysql->query("update " . prefix . "_news set num_files = " . db_squote($row['cnt']) . " where id = " . db_squote($row['linked_id']));
        }

        foreach ($mysql->select("select linked_id, count(id) as cnt from " . prefix . "_images where (storage=1) and (linked_ds=1) group by linked_id") as $row) {
            $mysql->query("update " . prefix . "_news set num_images = " . db_squote($row['cnt']) . " where id = " . db_squote($row['linked_id']));
        }

        msg(array('message' => __('dbo')['msgo_cat_recount']));
    }

    // Delete specific backup file
    if (getIsSet($_REQUEST['delbackup']))
    {
        $filename = str_replace('/', '', $_REQUEST['filename']);
        if (!$filename) {
            msg(array('type' => 'danger', 'message' => __('dbo')['msge_delbackup']));
        } else {
            @unlink(root . "backups/" . $filename . ".gz");
            msg(array('message' => sprintf(__('dbo')['msgo_delbackup'], $filename)));
        }
    }

    // MASS: Check/Repair/Optimize tables
    if (getIsSet($_REQUEST['masscheck']) or getIsSet($_REQUEST['massrepair']) or getIsSet($_REQUEST['massoptimize']))
    {
        $mode = 'check';
        if ($_REQUEST['massrepair'])
            $mode = 'repair';
        if ($_REQUEST['massoptimize'])
            $mode = 'optimize';

        $tables = getIsSet($_REQUEST['tables']);
        if (!is_array($tables)) {
            msg(array('type' => 'danger', 'title' => __('dbo')['msge_tables'], 'message' => __('dbo')['msgi_tables']));
        } else {
            $slist = array();

            for ($i = 0, $sizeof = sizeof($tables); $i < $sizeof; $i++) {
                if ($mysql->table_exists($tables[$i])) {

                    $result = $mysql->record($mode . " table `" . $tables[$i] . "`");
                    if ($result['Msg_text'] == "2 clients are using or haven't closed the table properly") {
                        $result['Msg_text'] = __('dbo')['chk_no'];
                    }
                    $slist [] = $tables[$i] . ' &#8594; ' . secure_html($result['Msg_text']);
                } else {
                    $slist [] = $tables[$i] . ' &#8594; ' . secure_html($result['Msg_text']);
                }
            }
            msg(array('title' => __('dbo')['msgo_' . $mode], 'message' => '<small>' . join("<br/>", $slist) . '</small>'));
        }
    }

    // MASS: Convert cp1251 to utf8
    if (getIsSet($_REQUEST['massconvert']))
    {
        $mode = 'convert';
        $time = microtime(true);
        $msg_error = [];

        $db = $config['dbname'];
        $login = $config['dbuser'];
        $passw = $config['dbpasswd'];
        $host = $config['dbhost'];

        $mysqli = new mysqli($host, $login, $passw, $db);
        if ($mysqli->connect_error) {
            $msg_error[] = secure_html('Connect Error (' . $mysqli->connect_errno . ') ' . $mysqli->connect_error . ' - LINE ' . __LINE__);
        }

        $mysqli->query("SET NAMES 'utf8' COLLATE 'utf8_general_ci';");
        $rs = $mysqli->query("SHOW TABLES;");
        if ($mysqli->errno) {
            $msg_error[] = secure_html('Select Error (' . $mysqli->errno . ') ' . $mysqli->error . ' - LINE ' . __LINE__);
        }

        while ($row = mysqli_fetch_array($rs, MYSQLI_ASSOC)) {

            $time1 = microtime(true);
            $table_name = $row['Tables_in_' . $db];
            $row_create = $mysqli->query('SHOW CREATE TABLE ' . $table_name);
            if ($mysqli->errno) {
                $msg_error[] = secure_html('Select Error (' . $mysqli->errno . ') ' . $mysqli->error . ' - LINE ' . __LINE__);
            }

            $row1 = mysqli_fetch_array($row_create, MYSQLI_ASSOC);
            /*if (strpos($row1['Create Table'], 'DEFAULT CHARSET=utf8') !== false) {
                $slist [] = __('dbo')['table'] . ' ' . $table_name . __('dbo')['skipped'];
                continue;
            }*/

            // RENAME TABLE;
            $mysqli->query('RENAME TABLE ' . $table_name . ' TO ' . $table_name . '_tmp_export');
            if ($mysqli->errno) {
                $msg_error[] = secure_html('Select Error (' . $mysqli->errno . ') ' . $mysqli->error . ' - LINE ' . __LINE__);
                break;
            }

            // CREATE TABLE SCHEME
            $create_table_scheme = str_ireplace('cp1251', 'utf8', $row1['Create Table']);
            // ENGINE=MyISAM для импортируемых с другой версии таблиц
            $create_table_scheme = str_ireplace('ENGINE=InnoDB', 'ENGINE=MyISAM', $create_table_scheme);
            $create_table_scheme .= ' COLLATE utf8_general_ci';
            $mysqli->query($create_table_scheme);
            if ($mysqli->errno) {
                $msg_error[] = secure_html('Select Error (' . $mysqli->errno . ') ' . $mysqli->error . ' - LINE ' . __LINE__);
                break;
            }

            $mysqli->query('ALTER TABLE ' . $table_name . ' DISABLE KEYS');
            if ($mysqli->errno) {
                $msg_error[] = secure_html('Select Error (' . $mysqli->errno . ') ' . $mysqli->error . ' - LINE ' . __LINE__);
                break;
            }

            $mysqli->query('INSERT INTO ' . $table_name . ' SELECT * FROM ' . $table_name . '_tmp_export');
            if ($mysqli->errno) {
                $msg_error[] = secure_html('Select Error (' . $mysqli->errno . ') ' . $mysqli->error . ' - LINE ' . __LINE__);
                break;
            }

            $mysqli->query('DROP TABLE ' . $table_name . '_tmp_export');
            if ($mysqli->errno) {
                $msg_error[] = secure_html('Select Error (' . $mysqli->errno . ') ' . $mysqli->error . ' - LINE ' . __LINE__);
                break;
            }

            $time2 = microtime(true);
            $mysqli->query('ALTER TABLE ' . $table_name . ' ENABLE KEYS');
            if ($mysqli->errno) {
                $msg_error[] = secure_html('Select Error (' . $mysqli->errno . ') ' . $mysqli->error . ' - LINE ' . __LINE__);
                break;
            }

            $slist [] = 'Enable keys to <b>' . $table_name . '</b>: ' . sprintf("%.4f", (microtime(true) - $time2)) . ' sec. ' .
                'Converted: ' . sprintf("%.4f", (microtime(true) - $time1)) . ' sec.';

        }

        $time3 = microtime(true);
        $mysqli->query("ALTER DATABASE $db DEFAULT CHARACTER SET 'utf8';");
        if ($mysqli->errno) {
            $msg_error[] = secure_html('Select Error (' . $mysqli->errno . ') ' . $mysqli->error . ' - LINE ' . __LINE__);
            return;
        } else {
            $slist [] = "<br>Converted database <b>$db</b> to <b>utf8</b>: " . sprintf("%.4f", (microtime(true) - $time3)) . ' sec.';
        }

        msg(array('type' => 'success', 'title' => __('dbo')['msgo_' . $mode], 'message' => join("<br>", $slist) . '<hr>Total time: ' . sprintf("%.4f", (microtime(true) - $time))));
        if (count($msg_error))
            msg(array('type' => 'danger', 'title' => __('dbo')['msge_' . $mode], 'message' => join("<br>", $msg_error)));

        mysqli_free_result($rs);
    }

    // MASS: Delete tables
    if (getIsSet($_REQUEST['massdelete']))
    {
        $tables = getIsSet($_REQUEST['tables']);
        if (!$tables) {
            msg(array('type' => 'danger', 'title' => __('dbo')['msge_tables'], 'message' => __('dbo')['msgi_tables']));
        } else {
            for ($i = 0, $sizeof = sizeof($tables); $i < $sizeof; $i++) {
                if ($mysql->table_exists($tables[$i])) {
                    $mysql->query("drop table `" . $tables[$i] . "`");
                    msg(array('message' => sprintf(__('dbo')['msgo_delete'], $tables[$i])));
                } else {
                    msg(array('message' => sprintf(__('dbo')['msgi_noexist'], $tables[$i], secure_html($result['Msg_text']))));
                }
            }
        }
    }

    // MASS: Backup tables
    if (getIsSet($_REQUEST['massbackup']))
    {
        $tables = getIsSet($_REQUEST['tables']);
        if (!$tables) {
            msg(array('type' => 'danger', 'title' => __('dbo')['msge_tables'], 'message' => __('dbo')['msgi_tables']));
        } else {
            $date = date("Y_m_d_H_i", time());
            $date2 = Lang::retDate("d Q Y - H:i", time());

            $filename = root . "backups/backup_" . $date . (($_REQUEST['gzencode']) ? ".gz" : ".sql");
            dbBackup($filename, $_REQUEST['gzencode']);

            if ($_REQUEST['email_send']) {
                sendEmailMessage($config['admin_mail'], __('dbo')['title'], sprintf(__('dbo')['message'], $date2), $filename);
                @unlink($filename);
                msg(array('message' => __('dbo')['msgo_backup_m']));
            } else {
                msg(array('message' => __('dbo')['msgo_backup']));
            }
        }
    }

    //MASS: Delete backup files
    if (getIsSet($_REQUEST['massdelbackup']))
    {
        $backup_dir = opendir(root . 'backups');
        while ($bf = readdir($backup_dir)) {
            if (($bf == '.') or ($bf == '..'))
                continue;

            @unlink(root . 'backups/' . $bf);
        }
        msg(array('message' => __('dbo')['msgo_massdelb']));
    }

    // RESTORE DB backup
    if (getIsSet($_REQUEST['restore']))
{
        $filename = str_replace('/', '', $_REQUEST['filename']);
        if (file_exists($filename = root . 'backups/' . $filename . '.gz')) {
            $sql  = '';
            $gzp = @gzopen($filename, "r");
            if ($gzp) {
                if (!empty($_POST['cp1251']))
                    $mysql->query("SET NAMES 'cp1251'");
                while (!gzeof($gzp)) {
                    $sql .= gzgets($gzp, 4096);
                    if (';' == mb_substr(rtrim($sql), -1)) {
                        $mysql->query($sql);
                        $sql = '';
                    }
                }
                gzclose($gzp);
            }
            msg(array('message' => __('dbo')['msgo_restore']));
        } else {
            msg(array('type' => 'danger', 'title' => __('dbo')['msge_restore'], 'message' => __('dbo')['msgi_restore']));
        }
    }
}

//
// List tables
function systemDboForm()
{
    global $mysql, $twig, $config, $PHP_SELF;

    $id = (getIsSet($_REQUEST['id']))?intval($_REQUEST['id']):0;

    // Check for permissions
    if (!checkPermission(array('plugin' => '#admin', 'item' => 'dbo'), null, 'details')) {
        msg(array('type' => 'danger', 'message' => __('perm.denied')));
        ngSYSLOG(array('plugin' => '#admin', 'item' => 'dbo', 'ds_id' => $id), array('action' => 'details'), null, array(0, 'SECURITY.PERM'));
        return false;
    }

    $tableList = array();
    foreach ($mysql->select("SHOW TABLES FROM `" . $config['dbname'] . "` LIKE '" . prefix . "_%'", 0) as $table) {
        $info = $mysql->record("SHOW TABLE STATUS LIKE '" . $table[0] . "'");

        $tableInfo = array(
            'table' => $info['Name'],
            'rows' => $info['Rows'],
            'data' => formatSize($info['Data_length'] + $info['Index_length'] + $info['Data_free']),
            'overhead' => ($info['Data_free'] > 0) ? '<span style="color:red;">' . formatSize($info['Data_free']) . '</span>' : 0,
        );

        $tableList [] = $tableInfo;

    }

    $tVars = array(
        'php_self' => $PHP_SELF,
        'tables' => $tableList,
        'restore' => MakeDropDown(ListFiles(root . 'backups', 'gz'), 'filename', ''),
        'token' => genUToken('admin.dbo'),
    );

    $xt = $twig->loadTemplate('skins/default/tpl/dbo.tpl');
    echo $xt->render($tVars);
}

//
// Main loop
if (isset($_REQUEST['subaction']) and $_REQUEST['subaction'])
    switch ($_REQUEST['subaction']) {
        case 'modify':
            systemDboModify();
            break;
    }

systemDboForm();
