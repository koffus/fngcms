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

function ParseQueries($sql) {
	$matches		= array();
	$output			= array();
	$queries		= explode(';', $sql);
	$query_count	= sizeof($queries);
	$sql			= '';

	for ($i = 0; $i < $query_count; $i++) {
		if (($i != ($query_count - 1)) or (strlen($queries[$i]) > 0)) {
			$total_quotes = preg_match_all("/'/", $queries[$i], $matches);
			$escaped_quotes = preg_match_all("/(?<!\\\\)(\\\\\\\\)*\\\\'/", $queries[$i], $matches);
			$unescaped_quotes = $total_quotes - $escaped_quotes;

			if (($unescaped_quotes % 2) == 0) {
				$output[] = $queries[$i];
				$queries[$i] = "";
			}
			else {
				$temp = $queries[$i].';';
				$queries[$i] = "";
				$complete_stmt = false;

				for ($j = $i + 1; (!$complete_stmt && ($j < $query_count)); $j++) {
					$total_quotes = preg_match_all("/'/", $queries[$j], $matches);
					$escaped_quotes = preg_match_all("/(?<!\\\\)(\\\\\\\\)*\\\\'/", $queries[$j], $matches);
					$unescaped_quotes = $total_quotes - $escaped_quotes;

					if (($unescaped_quotes % 2) == 1) {
						$output[] = $temp . $queries[$j];
						$queries[$j] = "";
						$temp = "";
						$complete_stmt = true;
						$i = $j;
					}
					else {
						$temp .= $queries[$j].';';
						$queries[$j] = "";
					}
				}
			}
		}
	}
	return $output;
}

//
// Modify data request
function systemDboModify() {
	global $config, $mysql, $catz;

	// Check for permissions
	if (!checkPermission(array('plugin' => '#admin', 'item' => 'dbo'), null, 'modify')) {
		msg(array('type' => 'danger', 'message' => __('perm.denied')));
		ngSYSLOG(array('plugin' => '#admin', 'item' => 'dbo', 'ds_id' => $id), array('action' => 'modify'), null, array(0, 'SECURITY.PERM'));
		return false;
	}

	// Check for security token
	if ((!isset($_REQUEST['token']))||($_REQUEST['token'] != genUToken('admin.dbo'))) {
		msg(array('type' => 'danger', 'title' => __('error.security.token'), 'message' => __('error.security.token#desc')));
		ngSYSLOG(array('plugin' => '#admin', 'item' => 'dbo', 'ds_id' => $id), array('action' => 'modify'), null, array(0, 'SECURITY.TOKEN'));
		return false;
	}

	// Update message counters
	if ($_REQUEST['cat_recount']) {
		// Обновляем счётчики в категориях
		$ccount = array();
		$nmap = '';
		foreach ($mysql->select("select id, catid, postdate, editdate from ".prefix."_news where approve=1") as $row) {
			$ncats = 0;
			foreach (explode(',',$row['catid']) as $key) {
			 if (!$key) { continue; }
					$ncats++;
			 $nmap .= '('.$row['id'].','.$key.',from_unixtime('.(($row['editdate']>$row['postdate'])?$row['editdate']:$row['postdate']).')),';
				if (!$ccount[$key]) { $ccount[$key] = 1; } else { $ccount[$key]+=1; }
			}
			if (!$ncats) {
				$nmap .= '('.$row['id'].',0,from_unixtime('.(($row['editdate']>$row['postdate'])?$row['editdate']:$row['postdate']).')),';
			}
		}

		// Update table `news_map`
		$mysql->query("truncate table ".prefix."_news_map");

		if (strlen($nmap))
			$mysql->query("insert into ".prefix."_news_map (newsID, categoryID, dt) values ".substr($nmap,0,-1));

		// Update category news counters
		foreach ($catz as $key) {
			$mysql->query("update ".prefix."_category set posts = ".intval(getIsSet($ccount[$key['id']]))." where id = ".$key['id']);
		}

		// Check if we can update comments counters
		$haveComments = $mysql->table_exists(prefix."_comments")?true:false;

		if ($haveComments) {
			foreach ($mysql->select("select n.id, count(c.id) as cid from ".prefix."_news n left join ".prefix."_comments c on c.post=n.id group by n.id") as $row) {
				$mysql->query("update ".prefix."_news set com=".$row['cid']." where id = ".$row['id']);
			}
		}

	 	// Обновляем счетчик постов у юзеров
	 	$mysql->query("update ".prefix."_users set news = 0".($haveComments?", com = 0":""));
	 	foreach ($mysql->select("select author_id, count(*) as cnt from ".prefix."_news group by author_id") as $row) {
	 		$mysql->query("update ".uprefix."_users set news=".$row['cnt']." where id = ".$row['author_id']);
	 	}

		if ($haveComments) {
		 	// Обновляем счетчик комментариев у юзеров
		 	foreach ($mysql->select("select author_id, count(*) as cnt from ".prefix."_comments group by author_id") as $row) {
		 		$mysql->query("update ".uprefix."_users set com=".$row['cnt']." where id = ".$row['author_id']);
		 	}
		}
		// Обновляем кол-во приложенных файлов/изображений к новостям
		$mysql->query("update ".prefix."_news set num_files = 0, num_images = 0");
		foreach ($mysql->select("select linked_id, count(id) as cnt from ".prefix."_files where (storage=1) and (linked_ds=1) group by linked_id") as $row) {
			$mysql->query("update ".prefix."_news set num_files = ".db_squote($row['cnt'])." where id = ".db_squote($row['linked_id']));
		}

		foreach ($mysql->select("select linked_id, count(id) as cnt from ".prefix."_images where (storage=1) and (linked_ds=1) group by linked_id") as $row) {
			$mysql->query("update ".prefix."_news set num_images = ".db_squote($row['cnt'])." where id = ".db_squote($row['linked_id']));
		}

	 	msg(array('message' => __('dbo')['msgo_cat_recount']));
	}

	// Delete specific backup file
	if (getIsSet($_REQUEST['delbackup'])) {
	 $filename = str_replace('/','', $_REQUEST['filename']);
		if (!$filename) {
			msg(array('type' => 'danger', 'message' => __('dbo')['msge_delbackup']));
		}
		else {
			@unlink(root."backups/".$filename.".gz");
			msg(array('message' => sprintf(__('dbo')['msgo_delbackup'], $filename)));
		}
	}

	// MASS: Check/Repair/Optimize tables
	if ($_REQUEST['masscheck'] or $_REQUEST['massrepair'] or $_REQUEST['massoptimize']) {
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
					
					$result = $mysql->record($mode." table `".$tables[$i]."`");
					if ( $result['Msg_text'] == "2 clients are using or haven't closed the table properly" ) {
						$result['Msg_text'] = __('dbo')['chk_no'];
					}
					$slist []= $tables[$i].' &#8594; '. secure_html($result['Msg_text']);
				} else {
					$slist []= $tables[$i].' &#8594; '.secure_html($result['Msg_text']);
				}
			}
			msg(array('title' => __('dbo')['msgo_'.$mode], 'message' => '<small>'.join("<br/>", $slist).'</small>'));
		}
	}

	// MASS: Convert cp1251 to utf8
	if ($_REQUEST['massconvert']) {
		$time = microtime(true);

		$db = $config['dbname'];
		$login = $config['dbuser'];
		$passw = $config['dbpasswd'];
		$host = $config['dbhost'];

		$res = mysql_connect($host, $login, $passw);
		mysql_select_db($db);
		mysql_query('SET NAMES utf8;');
		$rs = mysql_query('SHOW TABLES;');
		$error = mysql_error();
		if ( strlen($error) > 0 ) {
			$msg_error [] = secure_html($error.' - LINE '.__LINE__); //the notorious 'command out of synch' message :(
		}
		while ( ($row=mysql_fetch_assoc($rs))!==false ) {

			$time1 = microtime(true);
			$table_name = $row['Tables_in_'.$db];
			$query = 'SHOW CREATE TABLE '.$table_name;

			$row_create = mysql_query($query);
			$error = mysql_error();
			if ( strlen($error) > 0 ) {
				$msg_error [] = secure_html($error.' - LINE '.__LINE__);
			}
			$row1 = mysql_fetch_assoc($row_create);

			if ( strpos($row1['Create Table'], 'DEFAULT CHARSET=utf8' ) !== false) {
				$slist [] = 'Table '.$table_name.' - skipped';
				continue;
			}

			$create_table_scheme = str_ireplace('cp1251', 'utf8', $row1['Create Table']); // CREATE TABLE SCHEME
			$create_table_scheme = str_ireplace('ENGINE=MyISAM', 'ENGINE=InnoDB', $create_table_scheme);
			$create_table_scheme .= ' COLLATE utf8_general_ci';

			$query = 'RENAME TABLE '.$table_name.' TO '.$table_name.'_tmp_export'; // RENAME TABLE;
			mysql_query($query);
			$error = mysql_error();
			if ( strlen($error) > 0 ) {
				$msg_error [] = secure_html($error.' - LINE '.__LINE__);
				break;
			}

			$query = $create_table_scheme;
			mysql_query($query);
			$error = mysql_error();
			if ( strlen($error) > 0 ) {
				$msg_error [] = secure_html($error.' - LINE '.__LINE__);
				break;
			}

			$query = 'ALTER TABLE '.$table_name.' DISABLE KEYS';
			mysql_query($query);
			$error = mysql_error();
			if ( strlen($error) > 0 ) {
				$msg_error [] = secure_html($error.' - LINE '.__LINE__);
				break;
			}

			$query = 'INSERT INTO '.$table_name.' SELECT * FROM '.$table_name.'_tmp_export';
			mysql_query($query);
			$error = mysql_error();
			if ( strlen($error) > 0 ) {
				$msg_error [] = secure_html($error.' - LINE '.__LINE__);
				break;
			}

			$query = 'DROP TABLE '.$table_name.'_tmp_export';
			mysql_query($query);
			$error = mysql_error();
			if ( strlen($error) > 0 ) {
				$msg_error [] = secure_html($error.' - LINE '.__LINE__);
				break;
			}

			$time3 = microtime(true);
			$query = 'ALTER TABLE '.$table_name.' ENABLE KEYS';
			mysql_query($query);
			$error = mysql_error();
			if ( strlen($error) > 0 ) {
				$msg_error [] = secure_html($error.' - LINE '.__LINE__);
				break;
			}

			$slist [] = 'Enable keys to <b>'.$table_name.'</b>: '.sprintf("%.4f", (microtime(true) - $time3)). ' sec. '.
				'Converted: '.sprintf("%.4f", (microtime(true) - $time1)). ' sec.';

		}

		$slist [] = 'Total time: '.sprintf("%.4f", (microtime(true) - $time));
		msg( array('type' => 'info', 'title' => __('dbo')['msgo_'.$mode], 'message' => join("<br>", $slist)) );
		if ( is_array($msg_error) )
			msg( array('type' => 'danger', 'title' => __('dbo')['msgo_'.$mode], 'message' => join("<br>", $msg_error)) );

		mysql_free_result($rs);
	}
	
	// MASS: Delete tables
	if (getIsSet($_REQUEST['massdelete'])) {
	 $tables = getIsSet($_REQUEST['tables']);
		if (!$tables) {
			msg(array('type' => 'danger', 'title' => __('dbo')['msge_tables'], 'message' => __('dbo')['msgi_tables']));
		} else {
			for($i = 0, $sizeof = sizeof($tables); $i < $sizeof; $i++) {
				if ($mysql->table_exists($tables[$i])) {
					$mysql->query("drop table `".$tables[$i]."`");
					msg(array('message' => sprintf(__('dbo')['msgo_delete'], $tables[$i])));
				} else {
					msg(array('message' => sprintf(__('dbo')['msgi_noexist'], $tables[$i], secure_html($result['Msg_text']))));
				}
			}
		}
	}

	// MASS: Backup tables
	if (getIsSet($_REQUEST['massbackup'])) {
	 $tables = getIsSet($_REQUEST['tables']);
		if (!$tables) {
			msg(array('type' => 'danger', 'title' => __('dbo')['msge_tables'], 'message' => __('dbo')['msgi_tables']));
		} else {
			$date = date("Y_m_d_H_i", time());
			$date2 = Lang::retDate("d Q Y - H:i", time());

			$filename = root."backups/backup_".$date.(($_REQUEST['gzencode'])?".gz":".sql");
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
	if (getIsSet($_REQUEST['massdelbackup'])) {
		$backup_dir = opendir(root.'backups');
		while($bf = readdir($backup_dir)) {
			if (($bf == '.')||($bf == '..'))
				continue;

			@unlink (root.'backups/'.$bf);
		}
		msg(array('message' => __('dbo')['msgo_massdelb']));
	}

	// RESTORE DB backup
	if (getIsSet($_REQUEST['restore'])) {
	 $filename = str_replace('/','', $_REQUEST['filename']);
		if (!$filename) {
			msg(array('type' => 'danger', 'title' => __('dbo')['msge_restore'], 'message' => __('dbo')['msgi_restore']));
		} else {
			$fp = gzopen(root . 'backups/' . $filename.'.gz', "r");

			while (!gzeof($fp)) {
				$query .= gzread($fp, 10000);
			}
			gzclose($fp);
			$queries = ParseQueries($query);

			for ($i = 0; $i < sizeof($queries); $i++) {
				$sql = trim($queries[$i]);

				if (!empty($sql)) {
					$mysql->query($sql);
				}
			}
			msg(array('message' => __('dbo')['msgo_restore']));
		}
	}
}

//
// List tables
function systemDboForm() {
	global $mysql, $twig, $config, $PHP_SELF;

	// Check for permissions
	if (!checkPermission(array('plugin' => '#admin', 'item' => 'dbo'), null, 'details')) {
		msg(array('type' => 'danger', 'message' => __('perm.denied')));
		ngSYSLOG(array('plugin' => '#admin', 'item' => 'dbo', 'ds_id' => $id), array('action' => 'details'), null, array(0, 'SECURITY.PERM'));
		return false;
	}

	$tableList = array();
	foreach($mysql->select("SHOW TABLES FROM `".$config['dbname']."` LIKE '".prefix."_%'", 0) as $table) {
		$info		= $mysql->record("SHOW TABLE STATUS LIKE '".$table[0]."'");

		$tableInfo = array(
			'table'		=> $info['Name'],
			'rows'		=> $info['Rows'],
			'data'		=> Formatsize($info['Data_length'] + $info['Index_length'] + $info['Data_free']),
			'overhead'	=> ($info['Data_free'] > 0) ? '<span style="color:red;">'.Formatsize($info['Data_free']).'</span>' : 0,
		);

		$tableList []= $tableInfo;

	}

	$tVars = array(
		'php_self'	=> $PHP_SELF,
		'tables'	=> $tableList,
		'restore'	=> MakeDropDown(ListFiles(root . 'backups', 'gz'), 'filename', ''),
		'token'		=> genUToken('admin.dbo'),
	);

	$xt = $twig->loadTemplate('skins/default/tpl/dbo.tpl');
	echo $xt->render($tVars);
}

//
// Main loop
if(isset($_REQUEST['subaction']) && $_REQUEST['subaction'])
	switch ($_REQUEST['subaction']) {
		case 'modify': systemDboModify(); break;
	}

systemDboForm();
