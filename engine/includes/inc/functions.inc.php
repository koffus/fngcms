<?php

//
// Copyright (C) 2006-2016 Next Generation CMS (http://ngcms.ru/)
// Name: functions.php
// Description: Common system functions
// Author: Vitaly Ponomarev, Alexey Zinchenko
//

// Protect against hack attempts
if (!defined('NGCMS')) die ('HAL');

//Проверяем переменную
function getIsSet(&$result)
{
    return isset($result) ? $result : null;
}

// SQL security string escape
function db_squote($string)
{
    global $mysql;
    if (is_array($string)) {
        return false;
    }
    return $mysql->db_quote($string);
}

function db_dquote($string)
{
    global $mysql;
    if (is_array($string)) {
        return false;
    }
    return '"' . $mysql->db_quote($string) . '"';
}

//
// HTML & special symbols protection
function secure_html($string)
{

    if (is_string($string)) {
        $string = htmlspecialchars($string, ENT_QUOTES, 'UTF-8');
        $string = str_replace(array('{', '<', '>', '"', "'"), array('&#123;', '&lt;', '&gt;', '&#34;', '&#039;'), $string);
    } elseif (is_array($string)) {
        $string = array_map('secure_html', $string);
    }
    return trim($string);
}

function formatSize($bytes)
{

    if ($bytes >= 1073741824) {
        $bytes = number_format($bytes / 1073741824, 2) . ' GB';
    } elseif ($bytes >= 1048576) {
        $bytes = number_format($bytes / 1048576, 2) . ' MB';
    } elseif ($bytes >= 1024) {
        $bytes = number_format($bytes / 1024, 2) . ' kB';
    } elseif ($bytes > 1) {
        $bytes = $bytes . ' B';
    } elseif ($bytes == 1) {
        $bytes = $bytes . ' B';
    } else {
        $bytes = '0 B';
    }
    return $bytes;
}

function DirSize($directory)
{

    if (!is_dir($directory))
        return -1;
    $size = 0;

    if ($dir = opendir($directory)) {
        while (($dirfile = readdir($dir)) !== false) {
            if (is_link($directory . '/' . $dirfile) or $dirfile == '.' or $dirfile == '..') {
                continue;
            }
            if (is_file($directory . '/' . $dirfile)) {
                $size += filesize($directory . '/' . $dirfile);
            } elseif (is_dir($directory . '/' . $dirfile)) {
                $dirSize = dirsize($directory . '/' . $dirfile);
                if ($dirSize >= 0) {
                    $size += $dirSize;
                } else {
                    return -1;
                }
            }
        }
        closedir($dir);
    }
    return $size;
}

// Scans directory and returns it's size and file count
// Return array with size, count
function directoryWalk($dir, $blackmask = null, $whitemask = null, $returnFiles = true, $execTimeLimit = 0)
{
    $tStart = microtime(true);
    if (!is_dir($dir)) return array(-1, -1);

    $size = 0;
    $count = 0;
    $flag = 0;
    $path = array($dir);
    $wpath = array();
    $files = array();
    $od = array();
    $dfile = array();
    $od[1] = opendir($dir);

    while (count($path)) {
        if (($count % 100) == 0) {
            $tNow = microtime(true);
            if (($execTimeLimit > 0) and (($tNow - $tStart) >= $execTimeLimit)) {
                return array($size, $count, $files, true);

            }
        }

        $level = count($path);
        $sd = join("/", $path);
        $wsd = join("/", $wpath);
        while (($dfile[$level] = readdir($od[$level])) !== false) {
            if (is_link($sd . '/' . $dfile[$level]) or $dfile[$level] == '.' or $dfile[$level] == '..')
                continue;

            if (is_file($sd . '/' . $dfile[$level])) {
                // Check for black list

                $size += filesize($sd . '/' . $dfile[$level]);
                if ($returnFiles)
                    $files [] = ($wsd ? $wsd . '/' : '') . $dfile[$level];
                $count++;
            } elseif (is_dir($sd . '/' . $dfile[$level])) {
                array_push($path, $dfile[$level]);
                array_push($wpath, $dfile[$level]);
                $od[$level + 1] = opendir(join("/", $path));
                $flag = 1;
                break;
            }
        }
        if ($flag) {
            $flag = 0;
            continue;
        }
        array_pop($path);
        array_pop($wpath);
    }
    return array($size, $count, $files, false);
}

//
// Generate a list of smilies to show
function Smilies($insert_location, $break_location = false, $area = false)
{
    global $config, $tpl;

    if ($config['use_smilies']) {
        $smilies = explode(',', $config['smilies']);

        // For smilies in comments, try to use 'smilies.tpl' from site template
        $templateDir = (($insert_location == 'comments') and is_readable(tpl_dir . $config['theme'] . '/smilies.tpl')) ? tpl_dir . $config['theme'] : tpl_actions;

        $i = 0;
        $output = '';
        foreach ($smilies as $null => $smile) {
            $i++;
            $smile = trim($smile);

            $tvars['vars'] = array(
                'area' => $area ? $area : "''",
                'smile' => $smile
            );

            $tpl->template('smilies', $templateDir);
            $tpl->vars('smilies', $tvars);
            $output .= $tpl->show('smilies');

            if (($break_location > 0) and (!$i % $break_location)) {
                $output .= "<br />";
            }
        }
        return $output;
    }
}

function BBCodes($area = false, $template = false)
{
    global $config, $tpl, $mod;

    if ($config['use_bbcodes']) {

        Lang::load('bbcodes');

        $tvars['vars'] = array(
            'area' => $area ? $area : "''",
        );

        if (defined('ADMIN')) {
            if ($template and !in_array($template, array('pmmes', 'editcom', 'news', 'static')))
                return false;
            $tvars['regx']['#\[news\](.+?)\[/news\]#is'] = ($mod == 'news') ? '$1' : '';
            $tvars['regx']['#\[perm\](.+?)\[/perm\]#is'] = ($mod == 'static' or $mod == 'news') ? '$1' : '';
            $tplDir = tpl_actions;
        } else {
            $tplDir = tpl_site;
        }

        $tpl->template('bbcodes', $tplDir);
        $tpl->vars('bbcodes', $tvars);
        return $tpl->show('bbcodes');
    }
}

function phphighlight($content = '')
{

    $f = array('<br>', '<br />', '<p>', '&lt;', '&gt;', '&amp;', '&#124;', '&quot;', '&#036;', '&#092;', '&#039;', '&nbsp;', '\"');
    $r = array("\n", "\n", "\n", '<', '>', '&', '\|', '"', '$', '', '\'', '', '"');
    $content = str_replace($f, $r, $content);
    $content = highlight_string($content, true);

    return $content;
}

function Padeg($n, $s)
{
    $n = abs($n);
    $a = explode(',', $s);
    $l1 = $n - ((int)($n / 10)) * 10;
    $l2 = $n - ((int)($n / 100)) * 100;
    if ("11" <= $l2 and $l2 <= "14") {
        $e = $a[2];
    } else {
        if ($l1 == "1") {
            $e = $a[0];
        }
        if ("2" <= $l1 and $l1 <= "4") {
            $e = $a[1];
        }
        if (("5" <= $l1 and $l1 <= "9") or $l1 == "0") {
            $e = $a[2];
        }
    }
    if ($e == '') {
        $e = $a[0];
    }
    return ($e);
}

function MakeRandomPassword()
{
    global $config;

    return substr(md5($config['crypto_salt'] . uniqid(rand(), 1)), 0, 10);
}

function EncodePassword($pass)
{
    $pass = md5(md5($pass));

    return $pass;
}

function initGZipHandler()
{
    global $config;

    if ($config['use_gzip'] == "1" and extension_loaded('zlib') and function_exists('ob_gzhandler')) {
        @ob_start('ob_gzhandler');
    }
}

//
// Perform BAN check
// $ip		- IP address of user
// $act		- action type ( 'users', 'comments', 'news',... )
// $subact	- subaction type ( for comments this may be 'add' )
// $userRec	- record of user (in case of logged in)
// $name	- name entered by user (in case it was entered)
function checkBanned($ip, $act, $subact, $userRec, $name)
{
    global $mysql;

    $check_ip = sprintf("%u", ip2long($ip));

    // Currently we use limited mode. Try to find row
    if ($ban_row = $mysql->record("select * from " . prefix . "_ipban where addr_start <= " . db_squote($check_ip) . " and addr_stop >= " . db_squote($check_ip) . " order by netlen limit 1")) {
        // Row is found. Let's check for event type. STATIC CONVERSION
        $mode = 0;
        if (($act == 'users') and ($subact == 'register')) {
            $mode = 1;
        } else if (($act == 'users') and ($subact == 'auth')) {
            $mode = 2;
        } else if (($act == 'comments') and ($subact == 'add')) {
            $mode = 3;
        }
        if (($locktype = intval(substr($ban_row['flags'], $mode, 1))) > 0) {
            $mysql->query("update " . prefix . "_ipban set hitcount=hitcount+1 where id=" . db_squote($ban_row['id']));
            return $locktype;
        }
    }
    return 0;
}

//
// Perform FLOOD check
// $mode	- WORKING MODE ( 0 - check only, 1 - update )
// $ip		- IP address of user
// $act		- action type ( 'comments', 'news',... )
// $subact	- subaction type ( for comments this may be 'add' )
// $userRec	- record of user (in case of logged in)
// $name	- name entered by user (in case it was entered)
function checkFlood($mode, $ip, $act, $subact, $userRec, $name)
{
    global $mysql, $config;

    // Return if flood protection is disabled
    if (!$config['flood_time']) {
        return 0;
    }

    $this_time = time() + ($config['date_adjust'] * 60) - $config['flood_time'];

    // If UPDATE mode is used - update data
    if ($mode) {
        $this_time = time() + ($config['date_adjust'] * 60);
        $mysql->query("insert into " . prefix . "_flood (ip, id) values (" . db_squote($ip) . ", " . db_squote($this_time) . ") on duplicate key update id=" . db_squote($this_time));
        return 0;
    }

    // Delete expired records
    $mysql->query("DELETE FROM " . prefix . "_flood WHERE id < " . db_squote($this_time));

    // Check if we have record
    if ($mysql->record("SELECT * FROM " . prefix . "_flood WHERE id > " . db_squote($this_time) . " AND ip = " . db_squote($ip) . " limit 1")) {
        // Flood found
        return 1;
    }
    return 0;
}

function checkIP()
{
    if (getenv('REMOTE_ADDR')) {
        return getenv('REMOTE_ADDR');
    } elseif ($_SERVER['REMOTE_ADDR']) {
        return $_SERVER['REMOTE_ADDR'];
    }
    return 'unknown';
}

function sendEmailMessage($to, $subject, $message, $filename = false, $mail_from = false, $ctype = 'text/html')
{
    global $config;

    // Init $mail client
    $mail = new PHPMailer();
    $mail->CharSet = 'UTF-8';

    // Fill `sender` field
    $mail->FromName = 'NGCMS sender';
    if ($config['mailfrom_name']) {
        $mail->FromName = $config['mailfrom_name'];
    }
    if ($mail_from) {
        $mail->From = $mail_from;
    } else if ($config['mailfrom']) {
        $mail->From = $config['mailfrom'];
    } else {
        $mail->From = 'mailbot@' . str_replace('www.', '', $_SERVER['SERVER_NAME']);
    }

    $mail->Subject = $subject;
    $mail->Body = $message;
    $mail->ContentType = $ctype;
    $mail->AddAddress($to, $to);
    if (($filename !== false) and (is_file($filename))) {
        $mail->AddAttachment($filename);
    }

    // Select delivery transport
    switch ($config['mail_mode']) {
        default:
        case 'mail':
            $mail->isMail();
            break;
        case 'sendmail':
            $mail->isSendmail();
            break;
        case 'smtp':
            if (!$config['mail']['smtp']['host'] or !$config['mail']['smtp']['port']) {
                $mail->isMail();
                break;
            }
            $mail->isSMTP();
            $mail->Host = $config['mail']['smtp']['host'];
            $mail->Port = $config['mail']['smtp']['port'];
            $mail->SMTPAuth = ($config['mail']['smtp']['auth']) ? true : false;
            $mail->Username = $config['mail']['smtp']['login'];
            $mail->Password = $config['mail']['smtp']['pass'];
            $mail->SMTPSecure = $config['mail']['smtp']['secure'];
            break;
    }

    return $mail->Send();
}

//
// Load variables from template
// $die - flag: generate die() in case when file is not found (else - return false)
// $loadMode - flag:
//			0 - use SITE template
//			1 - use ADMIN PANEL template
function templateLoadVariables($die = false, $loadMode = 0)
{
    global $TemplateCache;

    if (isset($TemplateCache[$loadMode ? 'admin' : 'site']['#variables']))
        return true;

    $filename = ($loadMode ? tpl_actions : tpl_site) . 'variables.ini';
    if (!is_file($filename)) {
        if ($die) {
            die('Internal error: cannot locate Template Variables file');
        }
        return false;
    }
    $TemplateCache[$loadMode ? 'admin' : 'site']['#variables'] = parse_ini_file($filename, true);
    //print "<pre>".var_export($TemplateCache, true)."</pre>";
    return true;
}

//
// Generate info / error message
// $mode - working mode
//			0 - use SITE template
//			1 - use ADMIN PANEL template
// $disp - flag [display mode]:
//		 -1 - automatic mode
//			0 - add into mainblock
//			1 - print
//			2 - return as result
function msg($params, $mode = 0, $disp = -1)
{
    global $config, $tpl, $template, $PHP_SELF, $TemplateCache;

    // Set AUTO mode if $disp == -1
    if ($disp == -1)
        $mode = ($PHP_SELF == 'admin.php') ? 1 : 0;

    if (!templateLoadVariables(false, $mode)) {
        die('Internal system error: ' . var_export($params, true));
    }

    // Choose working mode
    $type = isset($params['type']) ? $params['type'] : 'success';
    $title = isset($params['title']) ? $params['title'] : __($type);
    $message = isset($params['message']) ? $params['message'] : '';

    $tmvars = array('vars' => array(
        'id' => rand(8, 88),
        'type' => $type,
        'title' => trim(db_squote('<b>'.$title.'</b><br />'), "'"),
        'message' => trim(db_squote($message), "'"),
    ));
    $message = $tpl->vars($TemplateCache[$mode ? 'admin' : 'site']['#variables']['messages']['msg'], $tmvars, array('inline' => true));

    switch ($disp) {
        case 0:
            $template['vars']['mainblock'] .= $message;
            break;
        case 1:
            print $message;
            break;
        case 2:
            return $message;
            break;
        default:
            if ($mode) {
                print $message;
            } else {
                $template['vars']['mainblock'] .= $message;
            }
            break;
    }
}

function OrderList($value, $showDefault = false)
{
    global $catz;

    $output = '<select name="orderby" class="form-control">';
    if ($showDefault)
        $output .= '<option value="">' . __('order_default') . '</option>' . "\n";
    foreach (array('id desc', 'id asc', 'postdate desc', 'postdate asc', 'title desc', 'title asc', 'rating desc', 'rating asc') as $v) {
        $vx = str_replace(' ', '_', $v);
        $output .= '<option value="' . $v . '"' . (($value == $v) ? ' selected="selected"' : '') . '>' . __('order_' . $vx) . '</option>' . "\n";
    }
    $output .= '</select>';
    return $output;
}

//
// Return a list of files
// $path		- путь по которому искать файлы
// $ext			- [scalar/array] расширение (одно или массивом) файла
// $showExt		- флаг: показывать ли расширение [0 - нет, 1 - показывать, 2 - использовать в значениях]
// $silentError		- не выводить сообщение об ошибке
// $returnNullOnError	- возвращать NULL при ошибке
function ListFiles($path, $ext, $showExt = 0, $silentError = 0, $returnNullOnError = 0)
{

    $list = array();
    if (!is_array($ext))
        $ext = array($ext);

    if (!($handle = opendir($path))) {
        if (!$silentError)
            echo "<p>ListFiles($path) execution error: Can't open directory</p>";
        if ($returnNullOnError)
            return;
        return array();
    }

    while (($file = readdir($handle)) !== false) {
        // Skip reserved words
        if (($file == '.') or ($file == '..')) continue;

        // Check file against all extensions
        foreach ($ext as $e) {
            if ($e == '') {
                if (strpos($file, '.') === false) {
                    $list[$file] = $file;
                    break;
                }
            } else {
                if (preg_match('#^(.+?)\.' . $e . '$#', $file, $m)) {
                    $list[($showExt == 2) ? $file : $m[1]] = $showExt ? $file : $m[1];
                    break;
                }
            }
        }

    }
    closedir($handle);
    return $list;
}

function ListDirs($folder, $category = false, $alllink = true, $elementID = '')
{

    switch ($folder) {
        case 'files':
            $wdir = files_dir;
            break;
        case 'images':
            $wdir = images_dir;
            break;
        default:
            return false;
    }

    $select = '<select ' . ($elementID ? 'id="' . $elementID . '" ' : '') . 'name="category" class="form-control">' . ($alllink ? '<option value="">- ' . __(all) . ' -</option>' : '');

    if (($dir = @opendir($wdir)) === FALSE) {
        msg(array(
            'type' => 'error',
            'title' => str_replace('{dirname}', $wdir, __('error.nodir#desc')),
            'message' => str_replace('{dirname}', $wdir, __('error.nodir'))),
            1);
        return false;
    }

    $filelist = array();
    while ($file = readdir($dir)) {
        $filelist[] = $file;
    }

    natcasesort($filelist);
    reset($filelist);

    foreach ($filelist as $file) {
        if (is_dir($wdir . "/" . $file) and $file != "." and $file != "..")
            $select .= '<option value="' . $file . '"' . ($category == $file ? ' selected' : '') . '>' . $file . '</option>';
    }
    $select .= '</select>';

    return $select;
}

function MakeDropDown($options, $name, $selected = "FALSE")
{
    $output = '<select name="' . $name . '" class="form-control">';
    foreach ($options as $k => $v)
        $output .= '<option value="' . $k . '"' . (($selected == $k) ? ' selected="selected"' : '') . '>' . $v . '</option>';
    $output .= '</select>';

    return $output;
}

// Return plugin dir
function GetPluginDir($name)
{
    global $EXTRA_CONFIG;

    // Load CORE Plugin
    $cPlugin = CPlugin::instance();
    // Load plugin list
    $extras = $cPlugin->getList();
    
    if (!$extras[$name]) {
        return 0;
    }
    return extras_dir . '/' . $extras[$name]['dir'];
}

function GetPluginLangDir($name)
{
    global $config;
    $lang_dir = GetPluginDir($name) . '/lang';
    if (!$lang_dir) {
        return 0;
    }
    if (is_dir($lang_dir . '/' . $config['default_lang'])) {
        $lang_dir = $lang_dir . '/' . $config['default_lang'];
    } else if (is_dir($lang_dir . '/english')) {
        $lang_dir = $lang_dir . '/english';
    } else if (is_dir($lang_dir . '/russian')) {
        $lang_dir = $lang_dir . '/russian';
    }
    return $lang_dir;
}
// makeCategoryList - make <SELECT> list of categories
// Params: set via named array
// * name 		- name field of <SELECT>
// * selected 		- ID of category to be selected or array of IDs to select (in list mode)
// * skip 		- ID of category to skip or array of IDs to skip
// * skipDisabled	- skip disabled areas
// * doempty 		- add empty category to the beginning ("no category"), value = 0
// * greyempty		- show empty category as `grey`
// * doall 		- add category named "ALL" to the beginning, value is empty
// * allMarker		- marker value for `doall`
// * dowithout		- add "Without category" after "ALL", value = 0
// * nameval 		- use DB field "name" instead of ID in HTML option value
// * resync 		- flag, if set - we make additional lookup into database for new category list
// * checkarea	 	- flag, if set - generate a list of checkboxes instead of <SELECT>
// * class 		- HTML class name
// * style 		- HTML style
// * disabledarea	- mark all entries (for checkarea) as disabled [for cases when extra categories are not allowed]
// * noHeader		- Don't write header (<select>..</select>) in output
// * returnOptArray	- FLAG: if we should return OPTIONS (with values) array instead of data
function makeCategoryList($params = array())
{
    global $catz, $mysql;

    $optList = array();

    if (!isset($params['skip'])) {
        $params['skip'] = array();
    }
    if (!is_array($params['skip'])) {
        $params['skip'] = $params['skip'] ? array($params['skip']) : array();
    }
    $name = array_key_exists('name', $params) ? $params['name'] : 'category';

    $out = '';
    if (!isset($params['checkarea']) or !$params['checkarea']) {
        if (empty($params['noHeader'])) {
            $out = '<select name="' . $name . '" id="catmenu"' .
                ((isset($params['style']) and (trim($params['style']))) ? ' style="' . $params['style'] . '"' : '') .
                ((isset($params['class']) and (trim($params['class']))) ? ' class="' . $params['class'] . '" class="form-control"' : ' class="form-control"') .
                '>';
        }
        if (isset($params['doempty']) and $params['doempty']) {
            $out .= '<option ' . (((isset($params['greyempty']) and $params['greyempty'])) ? 'style="background: #c41e3a;" ' : '') . 'value="0">' . __('no_cat') . '</option>';
            $optList [] = array('k' => 0, 'v' => __('no_cat'));
        }
        if (isset($params['doall']) and $params['doall']) {
            $out .= '<option value="' . (isset($params['allmarker']) ? $params['allmarker'] : '') . '">' . __('sh_all') . '</option>';
            $optList [] = array('k' => (isset($params['allmarker']) ? $params['allmarker'] : ''), 'v' => __('sh_all'));
        }
        if (isset($params['dowithout']) and $params['dowithout']) {
            $out .= '<option value="0"' . (((!is_null($params['selected'])) and ($params['selected'] == 0)) ? ' selected' : '') . '>' . __('sh_empty') . '</option>';
            $optList [] = array('k' => 0, 'v' => __('sh_empty'));
        }
    }
    if (isset($params['resync']) and $params['resync']) {
        $catz = array();
        foreach ($mysql->select("select * from `" . prefix . "_category` order by posorder asc", 1) as $row) {
            $catz[$row['alt']] = $row;
            $catmap[$row['id']] = $row['alt'];
        }
    }

    foreach ($catz as $k => $v) {
        if (in_array($v['id'], $params['skip'])) {
            continue;
        }
        if (isset($params['skipDisabled']) and $params['skipDisabled'] and (trim($v['alt_url']))) {
            continue;
        }
        if (isset($params['checkarea']) and $params['checkarea']) {
            $out .= str_repeat('&#8212; ', $v['poslevel']) .
                '<label><input type="checkbox" name="' .
                $name .
                '_' .
                $v['id'] .
                '" value="1"' .
                ((isset($params['selected']) and is_array($params['selected']) and in_array($v['id'], $params['selected'])) ? ' checked' : '') .
                ((((trim($v['alt_url'])) or (isset($params['disabledarea']) and $params['disabledarea']))) ? ' disabled="disabled"' : '') .
                '/> ' .
                $v['name'] .
                '</label><br/>';
        } else {
            $out .= '<option value="' . ((isset($params['nameval']) and $params['nameval']) ? $v['name'] : $v['id']) . '"' . ((isset($params['selected']) and ($v['id'] == $params['selected'])) ? ' selected' : '') . (trim($v['alt_url']) ? ' disabled style="background: #c41e3a;"' : '') . '>' . str_repeat('&#8212; ', $v['poslevel']) . $v['name'] . '</option>';
            $optList [] = array('k' => ((isset($params['nameval']) and $params['nameval']) ? $v['name'] : $v['id']), 'v' => str_repeat('&#8212; ', $v['poslevel']) . $v['name']);
        }
    }
    if (!isset($params['checkarea']) or !$params['checkarea']) {
        if (empty($params['noHeader'])) {
            $out .= '</select>';
        }
    }

    if (isset($params['returnOptArray']) and $params['returnOptArray'])
        return $optList;

    return $out;
}

function resolveCatNames($idList, $split = ', ')
{
    global $catz, $catmap;

    $inames = array();
    foreach ($idList as $id) {
        if (isset($catmap[$id])) {
            $inames [] = $catz[$catmap[$id]]['name'];
        }
    }
    return join($split, $inames);
}

function GetCategories($catid, $plain = false, $firstOnly = false)
{
    global $catz, $catmap;

    $catline = array();
    $cats = is_array($catid) ? $catid : explode(',', $catid);

    if (count($cats) and $firstOnly) {
        $cats = array($cats[0]);
    }
    foreach ($cats as $v) {
        if (isset($catmap[$v])) {
            $row = $catz[$catmap[$v]];
            $catline[] = ($plain) ? $row['name'] : '<a href="' . generateLink('news', 'by.category', array('category' => $row['alt'], 'catid' => $row['id'])) . "\">" . $row['name'] . '</a>';
        }
    }

    return ($catline ? implode(", ", $catline) : '');
}

function makeCategoryInfo($ctext)
{
    global $catz, $catmap, $config;

    $list = array();
    $cats = is_array($ctext) ? $ctext : explode(',', $ctext);

    foreach ($cats as $v) {
        if (isset($catmap[$v])) {
            $row = $catz[$catmap[$v]];
            $url = generateLink('news', 'by.category', array('category' => $row['alt'], 'catid' => $row['id']));
            $record = array(
                'id' => $row['id'],
                'level' => $row['poslevel'],
                'alt' => $row['alt'],
                'name' => $row['name'],
                'info' => $row['info'],
                'url' => $url,
                'text' => '<a href="' . $url . '">' . $row['name'] . '</a>',
            );
            if ($row['icon_id'] and $row['icon_folder']) {
                $record['icon'] = array(
                    'url' => $config['attach_url'] . '/' . $row['icon_folder'] . '/' . $row['icon_name'],
                    'purl' => $row['icon_preview'] ? ($config['attach_url'] . '/' . $row['icon_folder'] . '/thumb/' . $row['icon_name']) : '',
                    'width' => $row['icon_width'],
                    'height' => $row['icon_height'],
                    'pwidth' => $row['icon_pwidth'],
                    'pheight' => $row['icon_pheight'],
                    'isExtended' => true,
                    'hasPreview' => $row['icon_preview'] ? true : false,
                );
            } else if ($row['icon']) {
                $record['icon'] = array(
                    'url' => $row['icon'],
                    'isExtended' => false,
                    'hasPreview' => false,
                );
            }

            $list [] = $record;
        }
    }

    return $list;
}

//
// New category menu generator
function generateCategoryMenu($treeMasterCategory = null, $flags = array())
{
    global $mysql, $catz, $config, $CurrentHandler, $SYSTEM_FLAGS, $TemplateCache, $twig, $twigLoader;

    // Load template variables
    templateLoadVariables(true);
    $markers = $TemplateCache['site']['#variables']['category_tree'];

    if (!isset($markers['class.active']))
        $markers['class.active'] = 'active_cat';

    if (!isset($markers['class.inactive']))
        $markers['class.inactive'] = '';

    if (!isset($markers['mark.default']))
        $markers['mark.default'] = '&#8212;';

    $tVars = array();
    $tEntries = array();
    $tIDs = array();

    $treeSelector = array(
        'defined' => false,
        'id' => 0,
        'skipDefined' => false,
        'started' => false,
        'level' => 0,
    );

    if (!is_null($treeMasterCategory) and preg_match('#^(\:){0,1}(\d+)$#', $treeMasterCategory, $m)) {
        $treeSelector['defined'] = true;
        $treeSelector['skipDefined'] = $m[1] ? true : false;
        $treeSelector['id'] = intval($m[2]);
    }

    foreach ($catz as $k => $v) {
        if (!substr($v['flags'], 0, 1)) continue;

        // If tree selector is active - skip unwanted entries
        if ($treeSelector['defined']) {
            if ($treeSelector['started']) {
                if ($v['poslevel'] <= $treeSelector['level']) {
                    break;
                }
            } else {
                if ($v['id'] == $treeSelector['id']) {
                    $treeSelector['started'] = true;
                    $treeSelector['level'] = $v['poslevel'];

                    if ($treeSelector['skipDefined'])
                        continue;
                } else {
                    continue;
                }
            }
        }

        $tEntry = array(
            'id' => $v['id'],
            'cat' => $v['name'],
            'link' => ($v['alt_url'] == '') ? generateLink('news', 'by.category', array('category' => $v['alt'], 'catid' => $v['id'])) : $v['alt_url'],
            'mark' => isset($markers['mark.level.' . $v['poslevel']]) ? $markers['mark.level.' . $v['poslevel']] : str_repeat($markers['mark.default'], $v['poslevel']),
            'level' => $v['poslevel'],
            'info' => $v['info'],
            'counter' => $v['posts'],
            'icon' => $v['icon'],

            'flags' => array(
                'active' => (isset($SYSTEM_FLAGS['news']['currentCategory.id']) and ($v['id'] == $SYSTEM_FLAGS['news']['currentCategory.id'])) ? true : false,
                'counter' => ($config['category_counters'] and $v['posts']) ? true : false,
            )
        );
        $tEntries [] = $tEntry;
        $tIDs [] = $v['id'];
    }

    // Update `hasChildren` and `closeLevel_X` flags for items
    for ($i = 0; $i < count($tEntries); $i++) {
        $tEntries[$i]['flags']['hasChildren'] = true;
        if (($i == (count($tEntries) - 1)) or ($tEntries[$i]['level'] >= $tEntries[$i + 1]['level'])) {
            // Mark that this is last item in this level
            $tEntries[$i]['flags']['hasChildren'] = false;
            // Mark all levels that are closed after this item
            if ($i == (count($tEntries) - 1)) {
                for ($x = 0; $x <= $tEntries[$i]['level']; $x++) {
                    $tEntries[$i]['flags']['closeLevel_' . $x] = true;
                }
            } else {
                for ($x = $tEntries[$i + 1]['level']; $x <= $tEntries[$i]['level']; $x++) {
                    $tEntries[$i]['flags']['closeLevel_' . $x] = true;
                }
            }
            if (isset($tEntries[$i + 1]['level']) and ($tEntries[$i]['level'] > $tEntries[$i + 1]['level']))
                $tEntries[$i]['closeToLevel'] = intval($tEntries[$i + 1]['level']);
        }
    }

    if (isset($flags['returnData'])) {
        return $flags['onlyID'] ? $tIDs : $tEntries;
    }

    $tVars['entries'] = $tEntries;
    $xt = $twig->loadTemplate('news.categories.tpl');
    return $xt->render($tVars);
}

// make an array for filtering from text line like 'abc-def,dfg'
function generateCategoryArray($categories)
{
    global $catz;

    $carray = array();
    foreach (explode(',', $categories) as $v) {
        $xa = array();
        foreach (explode("-", $v) as $n) {
            if (is_array($catz[trim($n)]))
                array_push($xa, $catz[trim($n)]['id']);
        }
        if (count($xa))
            array_push($carray, $xa);
    }
    return $carray;
}

//
// make a SQL filter for specified array
function generateCategoryFilter()
{

}

// Fetch metatags rows
function GetMetatags()
{
    global $config, $SYSTEM_FLAGS;

    if (!$config['meta'])
        return;

    $meta['description'] = $config['description'];
    $meta['keywords'] = $config['keywords'];

    if (isset($SYSTEM_FLAGS['meta']['description']) and (trim($SYSTEM_FLAGS['meta']['description'])))
        $meta['description'] = $SYSTEM_FLAGS['meta']['description'];

    if (isset($SYSTEM_FLAGS['meta']['keywords']) and (trim($SYSTEM_FLAGS['meta']['keywords'])))
        $meta['keywords'] = $SYSTEM_FLAGS['meta']['keywords'];

    $result = (trim($meta['description'])) ? '<meta name="description" content="' . secure_html($meta['description']) . "\" />\n" : '';
    $result .= (trim($meta['keywords'])) ? '<meta name="keywords" content="' . secure_html($meta['keywords']) . "\" />\n" : '';

    return $result;
}

// Generate pagination block
function generatePaginationBlock($current, $start, $end, $paginationParams, $navigations, $intlink = false)
{
    $result = '';
    for ($j = $start; $j <= $end; $j++) {
        if ($j == $current) {
            $result .= str_replace('%page%', $j, $navigations['current_page']);
        } else {
            $result .= str_replace('%page%', $j, str_replace('%link%', generatePageLink($paginationParams, $j, $intlink), $navigations['link_page']));
        }
    }
    return $result;
}

//
// Generate navigations panel ( like: 1.2.[3].4. ... 25 )
// $current				- current page
// $start				- first page in navigations
// $end					- last page in navigations
// $maxnav				- maximum number of navigtions to show
// $paginationParams	- pagination params [ for function generatePageLink() ]
// $intlink				- generate all '&' as '&amp;' if value is set
function generatePagination($current, $start, $end, $maxnav, $paginationParams, $navigations, $intlink = false)
{
    $pages_count = $end - $start + 1;
    $pages = '';

    if ($pages_count > $maxnav) {
        // We have more than 10 pages. Let's generate 3 parts
        $sectionSize = floor($maxnav / 3);

        // Section size should be not less 1 item
        if ($sectionSize < 1)
            $sectionSize = 1;

        // Situation #1: 1,2,3,4,[5],6 ... 128
        if ($current < ($sectionSize * 2)) {
            $pages .= generatePaginationBlock($current, 1, $sectionSize * 2, $paginationParams, $navigations, $intlink);
            $pages .= $navigations['dots'];
            $pages .= generatePaginationBlock($current, $pages_count - $sectionSize, $pages_count, $paginationParams, $navigations, $intlink);
        } elseif ($current > ($pages_count - $sectionSize * 2 + 1)) {
            $pages .= generatePaginationBlock($current, 1, $sectionSize, $paginationParams, $navigations, $intlink);
            $pages .= $navigations['dots'];
            $pages .= generatePaginationBlock($current, $pages_count - $sectionSize * 2 + 1, $pages_count, $paginationParams, $navigations, $intlink);
        } else {
            $pages .= generatePaginationBlock($current, 1, $sectionSize, $paginationParams, $navigations, $intlink);
            $pages .= $navigations['dots'];
            $pages .= generatePaginationBlock($current, $current - 1, $current + 1, $paginationParams, $navigations, $intlink);
            $pages .= $navigations['dots'];
            $pages .= generatePaginationBlock($current, $pages_count - $sectionSize, $pages_count, $paginationParams, $navigations, $intlink);
        }
    } else {
        // If we have less then $maxnav pages
        $pages .= generatePaginationBlock($current, 1, $pages_count, $paginationParams, $navigations, $intlink);
    }
    return $pages;
}

//
// Print output HTTP headers
//
function printHTTPheaders()
{
    global $SYSTEM_FLAGS;

    foreach ($SYSTEM_FLAGS['http.headers'] as $hkey => $hvalue) {
        @header($hkey . ': ' . $hvalue);
    }
}

//
// Generate SecureToken for protection from CSRF attacks
//
function genUToken($identity = '')
{
    global $userROW, $config;

    $line = $identity;
    if (isset($userROW))
        $line .= $userROW['id'] . $userROW['authcookie'];

    if (isset($config['UUID']))
        $line .= $config['UUID'];

    return md5($line);
}

// Check if user $user have access to identity $identity with mode $mode
// $identity - array with element characteristics
// 	* plugin	- id of plugin
//	* item		- id of item in plugin
// * ds		- id of Date Source (if applicable)
//	* ds_id		- id of item from DS (if applicable)
// $user - user record or null if access is checked for current user
// $mode - access mode:
//		'view'
//		'details'
//		'modify'/
//		.. here can be any other modes, but view/details/modify are most commonly used
// $way	 - way for content access
//			'rpc' - via rpc
//			'' - default access via site
function checkPermission($identity, $user = null, $mode = '', $way = '')
{
    global $userROW, $PERM;

    //$xDEBUG = true;
    $xDEBUG = false;

    if ($xDEBUG) {
        print "checkPermission[" . $identity['plugin'] . "," . $identity['item'] . "," . $mode . "] = ";
    }

    // Determine user's groups
    $uGroup = (isset($user) and isset($user['status'])) ? $user['status'] : $userROW['status'];

    // Check if permissions for this group exists. Break if no.
    if (!isset($PERM[$uGroup])) {
        if ($xDEBUG) {
            print " => FALSE[1]<br/>\n";
        }
        return false;
    }

    //return true;

    // Now let's check for possible access
    // - access group
    $ag = '';
    if (isset($PERM[$uGroup][$identity['plugin']])) {
        // Plugin found
        $ag = $identity['plugin'];
    } elseif (isset($PERM[$uGroup]['*'])) {
        // Perform default action
        $ag = '*';
    } else {
        // No such group [plugin] and no default action, return FALSE
        if ($xDEBUG) {
            print " => FALSE[2]<br/>\n";
        }
        return false;
    }
    if ($xDEBUG) {
        print "[AG=$ag]";
    }
    // - access item
    $ai = '';
    if (isset($PERM[$uGroup][$ag][$identity['item']]) and ($PERM[$uGroup][$ag][$identity['item']] !== NULL)) {
        // Plugin found
        $ai = $identity['item'];
    } elseif (isset($PERM[$uGroup][$ag]['*']) and ($PERM[$uGroup][$ag]['*'] !== NULL)) {
        // Perform default action
        $ai = '*';
    } else {
        // No such group [plugin] and no default action, return FALSE
        if ($xDEBUG) {
            print " => FALSE[3]<br/>\n";
        }
        return false;
    }

    if ($xDEBUG) {
        print "[AI=$ai]";
    }

    // Ok, now we located item and can return requested mode
    $mList = is_array($mode) ? $mode : array($mode);
    $mStatus = array();

    foreach ($mList as $mKey) {
        // The very default - DENY
        $iStatus = false;
        if (isset($PERM[$uGroup][$ag]) and isset($PERM[$uGroup][$ag][$ai]) and isset($PERM[$uGroup][$ag][$ai][$mKey]) and ($PERM[$uGroup][$ag][$ai][$mKey] !== NULL)) {
            // Check specific mode
            $iStatus = $PERM[$uGroup][$ag][$ai][$mKey];
        } else if (isset($PERM[$uGroup][$ag]) and isset($PERM[$uGroup][$ag][$ai]) and isset($PERM[$uGroup][$ag][$ai]['*']) and ($PERM[$uGroup][$ag][$ai]['*'] !== NULL)) {
            // Ckeck '*' under specifig Group/Item
            $iStatus = $PERM[$uGroup][$ag][$ai]['*'];
        } else if (isset($PERM[$uGroup][$ag]) and isset($PERM[$uGroup][$ag]['*']) and isset($PERM[$uGroup][$ag]['*']['*']) and ($PERM[$uGroup][$ag]['*']['*'] !== NULL)) {
            // Check '*' under specific Group
            $iStatus = $PERM[$uGroup][$ag]['*']['*'];
        } else if (isset($PERM[$uGroup]['*']) and isset($PERM[$uGroup]['*']['*']) and isset($PERM[$uGroup]['*']['*']['*']) and ($PERM[$uGroup]['*']['*']['*'] !== NULL)) {
            // Check '*' under current UserGroupID
            $iStatus = $PERM[$uGroup]['*']['*']['*'];
        }
        $mStatus[$mKey] = $iStatus;
    }

    if ($xDEBUG) {
        print " => " . var_export($mStatus, true) . "<br/>\n";
    }

    // Now check return mode and return
    return is_array($mode) ? $mStatus : $mStatus[$mode];
}

// Load user groups
function loadGroups()
{
    global $UGROUP, $config;

    $UGROUP = array();
    if (is_file(confroot . 'ugroup.php')) {
        include confroot . 'ugroup.php';
        $UGROUP = $confUserGroup;
    }

    // Fill default groups if not specified
    if (!isset($UGROUP[1])) {
        $UGROUP[1] = array(
            'identity' => 'admin',
            'langName' => array(
                'russian' => 'Администратор',
                'english' => 'Administrator',
            ),
        );
        $UGROUP[2] = array(
            'identity' => 'editor',
            'langName' => array(
                'russian' => 'Редактор',
                'english' => 'Editor',
            ),
        );
        $UGROUP[3] = array(
            'identity' => 'journalist',
            'langName' => array(
                'russian' => 'Журналист',
                'english' => 'Journalist',
            ),
        );
        $UGROUP[4] = array(
            'identity' => 'commentator',
            'langName' => array(
                'russian' => 'Комментатор',
                'english' => 'Commentator',
            ),
        );
//		$UGROUP[5] = array(
//			'identity' => 'tester',
//			'langName' => array(
//				'russian' => 'Тестировщик',
//				'english' => 'Tester',
//			),
//		);
    }

    // Initialize name according to current selected language
    foreach ($UGROUP as $id => $v) {
        $UGROUP[$id]['name'] = (isset($UGROUP[$id]['langName'][$config['default_lang']])) ? $UGROUP[$id]['langName'][$config['default_lang']] : $UGROUP[$id]['identity'];
    }

}

// Load permissions
function loadPermissions()
{
    global $PERM, $confPerm, $confPermUser;

    // 1. Load DEFAULT permission file.
    // * if not exists - allow everything for group = 1, other's are restricted
    $PERM = array();
    if (is_file(confroot . 'perm.default.php')) {
        include confroot . 'perm.default.php';
        $PERM = $confPerm;
    } else {
        $PERM = array('1' => array('*' => array('*' => array('*' => true))));
    }

    // 2. Load user specific config file
    // If configuration file exists
    $confPermUser = array();
    if (is_file(confroot . 'perm.php')) {
        // Try to load it
        include confroot . 'perm.php';
    }

    // Scan user's permissions
    if (is_array($confPermUser))
        foreach ($confPermUser as $g => $ginfo) {
            if (is_array($ginfo))
                foreach ($ginfo as $p => $ainfo) {
                    if (is_array($ainfo))
                        foreach ($ainfo as $r => $rinfo) {
                            if (is_array($rinfo))
                                foreach ($rinfo as $i => $ivalue) {
                                    $PERM[$g][$p][$r][$i] = $ivalue;
                                }
                        }
                }
        }
}

// SAVE updated user-defined permissions
function saveUserPermissions()
{
    global $confPermUser;

    $line = '<?php' . "\n// NGCMS User defined permissions ()\n";
    $line .= '$confPermUser = ' . var_export($confPermUser, true) . "\n;\n?>";

    $fcHandler = @fopen(confroot . 'perm.php', 'w');
    if ($fcHandler) {
        fwrite($fcHandler, $line);
        fclose($fcHandler);
        return true;
    }
    return false;
}

// Generate record in System LOG for security audit and logging of changes
// $identity - array of params for identification if object
// 	* plugin	- id of plugin
//	* item		- id of item in plugin
// 	* ds		- id of Date Source (if applicable)
//	* ds_id		- id of item from DS (if applicable)
// $action	- array of params to identify action
//	* action	- id of action
//	* list		- list of changed fields
// $user	- user record or null if access is checked for current user
// $status	- array of params to identify resulting status
//	* [0]	- state [ 0 - fail, 1 - ok ]
//	* [1]	- text value CODE of error (if have error)
function ngSYSLOG($identity, $action, $user, $status)
{
    global $ip, $mysql, $userROW, $config;

    if (empty($config['syslog']) or !$config['syslog'])
        return false;

    $sVars = array(
        'dt' => 'now()',
        'ip' => db_squote($ip),
        'plugin' => db_squote($identity['plugin']),
        'item' => db_squote($identity['item']),
        'ds' => intval($identity['ds']),
        'ds_id' => intval($identity['ds_id']),
        'action' => db_squote($action['action']),
        'alist' => db_squote(serialize($action['list'])),
        'userid' => is_array($user) ? intval($user['id']) : (($user === NULL) ? intval($userROW['id']) : 0),
        'username' => is_array($user) ? db_squote($user['name']) : (($user === NULL) ? db_squote($userROW['name']) : db_squote($user)),
        'status' => intval($status[0]),
        'stext' => db_squote($status[1]),
    );
    //print "<pre>".var_export($sVars, true)."</pre>";
    $mysql->query("insert into " . prefix . "_syslog (" . join(",", array_keys($sVars)) . ") values (" . join(",", array_values($sVars)) . ")");
    //$mysql->query("insert into ".prefix."_syslog (dt, ip, plugin, item, ds, ds_id, action, alist, userid, username, status, stext) values (now(), ".db_squote($ip).",");
    //print "<pre>ngSYSLOG: ".var_export($identity, true)."\n".var_export($action, true)."\n".var_export($user, true)."\n".var_export($status, true)."</pre>";
}

// Generate error "PAGE NOT FOUND"
function error404()
{
    global $config, $twig, $template, $SYSTEM_FLAGS;

    @header($_SERVER['SERVER_PROTOCOL'] . ' 404 Not Found');
    switch ($config['404_mode']) {
        // HTTP error 404
        case 2:
            exit;

        // External error template
        case 1:
            $xt = $twig->loadTemplate('404.external.tpl');
            echo $xt->render(array());
            exit;

        // Internal error template
        case 0:
        default:
            $xt = $twig->loadTemplate('404.internal.tpl');
            $template['vars']['mainblock'] = $xt->render(array());

            $SYSTEM_FLAGS['info']['title']['group'] = __('404.title');
    }
}

// HANDLER: Exceptions
function ngExceptionHandler($exception)
{
    @require(root . '/data/errors/ng_exception_handler.php');
}

// HANDLER: Errors
function ngErrorHandler($code, $message, $file, $line)
{
    /* if (0 == error_reporting())
        return;
    print "ERROR: [$code]($message)[$line]($file)<br/>\n"; */
}

// HANDLER: Shutdown
function ngShutdownHandler()
{
    @require(root . '/data/errors/ng_shutdown_handler.php');
}

function TwigEngineMSG($type, $text, $info = '')
{
    $cfg = array('type' => $type);
    if ($text)
        $cfg['text'] = __($text);
    if ($info)
        $cfg['info'] = $info;
    return msg($cfg, 0, 2);
}

function twigGetCategoryTree($masterCategory = null, $flags = array())
{
    if (!is_array($flags))
        $flags = array();

    if (!isset($flags['returnData']))
        $flags['returnData'] = true;

    return generateCategoryMenu($masterCategory, $flags);
}

function twigLocalPath($context)
{
    return $context['_templatePath'];
}

// Software generated fatal error
function ngFatalError($title, $description = '')
{
    @require(root . '/data/errors/ng_fatal_error.php');
}

function twigIsLang($lang)
{
    global $config;

    return ($config['default_lang'] == $lang);
}

function twigGetLang()
{
    global $config;

    return $config['default_lang'];
}

// Allow to have specific template configuration for different locations ($CurrentHandler global array)
// RULE is: <ENTRY1>[|<ENTRY2>[|<ENTRY3>...]]
// ENTRY1,2,.. is: <PLUGIN>[:<HANDLER>]
function twigIsHandler($rules)
{
    global $config, $CurrentHandler;

    $ruleCatched = false;
    foreach (preg_split("#\|#", $rules) as $rule) {
        if (preg_match("#^(.+?)\:(.+?)$#", $rule, $pt)) {
            // Specified: Plugin + Handler
            if (($pt[1] == $CurrentHandler['pluginName']) and ($pt[2] == $CurrentHandler['handlerName'])) {
                $ruleCatched = true;
                break;
            }
        } else if ($rule == $CurrentHandler['pluginName']) {
            $ruleCatched = true;
            break;
        }
    }
    return $ruleCatched;
}

function twigIsCategory($list)
{
    global $currentCategory, $catz, $catmap, $config, $CurrentHandler;

    //print "twigCall isCategory($list):<pre>".var_export($currentCategory, true)."</pre>";

    // Return if user is not reading any news
    if ($CurrentHandler['pluginName'] != 'news') return false;
    if (($CurrentHandler['handlerName'] == 'news') or ($CurrentHandler['handlerName'] == 'print')) return false;

    // Return false if we're not in category now
    if (!isset($currentCategory)) {
        return false;
    }

    // ****** Process modifiers ******
    if ($list == '') return true;
    if ($list == ':id') return $currentCategory['id'];
    if ($list == ':alt') return secure_html($currentCategory['alt']);
    if ($list == ':name') return secure_html($currentCategory['name']);
    if ($list == ':icon') return ($currentCategory['image_id'] and $currentCategory['icon_id']) ? 1 : 0;
    if ($list == ':icon.url') return $config['attach_url'] . '/' . $currentCategory['icon_folder'] . '/' . $currentCategory['icon_name'];
    if ($list == ':icon.width') return intval($currentCategory['icon_width']);
    if ($list == ':icon.height') return intval($currentCategory['icon_height']);
    if ($list == ':icon.preview') return ($currentCategory['image_id'] and $currentCategory['icon_id'] and $currentCategory['icon_preview']) ? 1 : 0;
    if ($list == ':icon.preview.url') return $config['attach_url'] . '/' . $currentCategory['icon_folder'] . '/thumb/' . $currentCategory['icon_name'];
    if ($list == ':icon.preview.width') return intval($currentCategory['icon_pwidth']);
    if ($list == ':icon.preview.height') return intval($currentCategory['icon_pheight']);


    foreach (preg_split("# *, *#", $list) as $key) {
        if ($key == '')
            continue;

        if (ctype_digit($key)) {
            if (isset($catmap[$key]) and is_array($currentCategory) and ($currentCategory['id'] == $key))
                return true;
        } else {
            if (isset($catz[$key]) and is_array($catz[$key]) and is_array($currentCategory) and ($currentCategory['alt'] == $key))
                return true;
        }

    }
    return false;
}

function twigIsNews($rules)
{
    global $catz, $catmap, $CurrentHandler, $SYSTEM_FLAGS, $CurrentCategory;

    //print "twigCall isNews($list):<pre>".var_export($SYSTEM_FLAGS['news'], true)."</pre>";

    // Return if user is not in news
    if ($CurrentHandler['pluginName'] != 'news') return false;
    if (($CurrentHandler['handlerName'] != 'news') and ($CurrentHandler['handlerName'] != 'print')) return false;
    if (!isset($SYSTEM_FLAGS['news']['db.id'])) return false;

    $ruleList = array('news' => array(), 'cat' => array(), 'mastercat' => array());
    $ruleCatched = false;

    // Pre-scan incoming data
    foreach (preg_split("#\|#", $rules) as $rule) {
        if (preg_match("#^(.+?)\:(.+?)$#", $rule, $pt)) {
            $ruleList[$pt[1]] = $ruleList[$pt[1]] + preg_split("# *, *#", $pt[2]);
        } else {
            $ruleList['news'] = $ruleList['news'] + preg_split("# *, *#", $rule);
        }
    }
    //print "isNews debug rules: <pre>".var_export($ruleList, true)."</pre>";
    foreach ($ruleList as $rType => $rVal) {
        //print "[SCAN TYPE: '$rType' with val: (".var_export($rVal, true).")]<br/>";
        switch ($rType) {
            // -- NEWS
            case 'news':
                if (!isset($SYSTEM_FLAGS['news']['db.id']))
                    continue;

                foreach ($rVal as $key) {
                    if (ctype_digit($key)) {
                        if ($SYSTEM_FLAGS['news']['db.id'] == $key)
                            return true;
                    } else {
                        if ($SYSTEM_FLAGS['news']['db.alt'] == $key)
                            return true;
                    }
                }
                break;

            // -- CATEGORY (master or any)
            case 'mastercat':
            case 'cat':
                if ((!isset($SYSTEM_FLAGS['news']['db.categories'])) or ($SYSTEM_FLAGS['news']['db.categories'] == ''))
                    continue;

                // List of categories from news
                foreach ($rVal as $key) {
                    if (ctype_digit($key)) {
                        if (($rType == 'mastercat') and ($SYSTEM_FLAGS['news']['db.categories'][0] == $key))
                            return true;
                        if (($rType == 'cat') and (in_array($key, $SYSTEM_FLAGS['news']['db.categories'])))
                            return true;
                    } else {
                        if (($rType == 'mastercat') and (is_array($catz[$key])) and ($SYSTEM_FLAGS['news']['db.categories'][0] == $catz[$key]['id']))
                            return true;
                        if (($rType == 'cat') and (is_array($catz[$key])) and (in_array($catz[$key]['id'], $SYSTEM_FLAGS['news']['db.categories'])))
                            return true;
                    }
                }
                break;
        }
    }
    return false;
}

// Check if current user has specified permissions
// RULE is: <ENTRY1>[|<ENTRY2>[|<ENTRY3>...]]
// ENTRY1,2,.. is: <PLUGIN>[:<HANDLER>]
function twigIsPerm($rules)
{

}

function twigIsSet($context, $val)
{
    //print "call TWIG::isSet(".var_export($context, true)." or ".var_export($val, true).");<br/>";
    //print "call TWIG::isSet(".var_export($val, true).");<br/>";
    if ((!isset($val)) or (is_array($val) and (count($val) == 0)))
        return false;
    return true;
}

function twigDebugValue($val)
{
    return "<b>debugValue:</b><pre>" . var_export($val, true) . "</pre>";
}

function twigDebugContext($context)
{
    return "<b>debugContext:</b><pre>" . var_export($context, true) . "</pre>";
}

// Notify kernel about script termination, used for statistics calculation
function coreNormalTerminate($mode = 0)
{
    global $mysql, $config, $userROW, $systemAccessURL;

    $timer = MicroTimer::instance();

    $exectime = $timer->stop();
    $now = localtime(time(), true);
    $now_str = sprintf("%04u-%02u-%02u %02u:%02u:00", ($now['tm_year'] + 1900), ($now['tm_mon'] + 1), $now['tm_mday'], $now['tm_hour'], (intval($now['tm_min'] / 15) * 15));

    // Common analytics
    if ($config['load_analytics']) {
        $cvar = ($mode == 0) ? "core" : (($mode == 1) ? "plugin" : "ppage");
        $mysql->query("insert into " . prefix . "_load (dt, hit_core, hit_plugin, hit_ppage, exec_core, exec_plugin, exec_ppage) values (" . db_squote($now_str) . ", " . (($mode == 0) ? 1 : 0) . ", " . (($mode == 1) ? 1 : 0) . " , " . (($mode == 2) ? 1 : 0) . ", " . (($mode == 0) ? $exectime : 0) . ", " . (($mode == 1) ? $exectime : 0) . ", " . (($mode == 2) ? $exectime : 0) . ") on duplicate key update hit_" . $cvar . " = hit_" . $cvar . " + 1, exec_" . $cvar . " = exec_" . $cvar . " + " . $exectime);
    }

    // DEBUG profiler
    if ($config['load_profiler'] > time()) {
        $trace = array(
            'queries' => $mysql->query_list,
            'events' => $timer->printEvents(1),
        );
        $mysql->query("insert into " . prefix . "_profiler (dt, userid, exectime, memusage, url, tracedata) values (now(), " . ((isset($userROW) and is_array($userROW)) ? $userROW['id'] : 0) . ", " . $exectime . ", " . sprintf("%7.3f", (memory_get_peak_usage() / 1024 / 1024)) . ", " . db_squote($systemAccessURL) . ", " . db_squote(serialize($trace)) . ")");
    }
}

// Generate user redirect call and terminate execution of CMS
function coreRedirectAndTerminate($location)
{
    if (headers_sent()) {
        echo '<script>document.location.href=' . $location . ';</script>';
    } else {
        @header('HTTP/1.1 302 Moved Permanently');
        @header("Location: " . $location);
        coreNormalTerminate();
        exit;
    }
}

// Update delayed news counters
function newsUpdateDelayedCounters()
{
    global $mysql;

    // Lock tables
    $mysql->query("lock tables " . prefix . "_news_view write, " . prefix . "_news write");

    // Read data and update counters
    foreach ($mysql->select("select * from " . prefix . "_news_view") as $vrec) {
        $mysql->query("update " . prefix . "_news set views = views + " . intval($vrec['cnt']) . " where id = " . intval($vrec['id']));
    }

    // Truncate view table
    //$mysql->query("truncate table ".prefix."_news_view");
    // DUE TO BUG IN MYSQL - USE DELETE + OPTIMIZE
    $mysql->query("delete from " . prefix . "_news_view");
    $mysql->query("optimize table " . prefix . "_news_view");

    // Unlock tables
    $mysql->query("unlock tables");

    return true;
}

// Delete old LOAD information, SYSLOG logging
function sysloadTruncate()
{
    global $mysql;

    // Store LOAD data only for 1 week
    $mysql->query("delete from " . prefix . "_load where dt < from_unixtime(unix_timestamp(now()) - 7*86400)");
    $mysql->query("optimize table " . prefix . "_load");

    // Store SYSLOG data only for 1 month
    $mysql->query("delete from " . prefix . "_syslog where dt < from_unixtime(unix_timestamp(now()) - 30*86400)");
    $mysql->query("optimize table " . prefix . "_syslog");

}

// Process CRON job calls
function core_cron($isSysCron, $handler)
{
    global $config;

    // Execute DB backup if automatic backup is enabled
    if (($handler == 'db_backup') and (isset($config['auto_backup'])) and $config['auto_backup']) {
        require_once root . 'fnc/AutoBackup.php';
        AutoBackup($isSysCron, false);
    }

    if ($handler == 'news_views') {
        newsUpdateDelayedCounters();
    }

    if ($handler == 'load_truncate') {
        sysloadTruncate();
    }
}

function coreUserMenu()
{
    global $userROW, $PFILTERS, $twigLoader, $twig, $template, $config, $SYSTEM_FLAGS, $TemplateCache;

    // Preload template configuration variables
    templateLoadVariables();

    // Use default <noavatar> file
    // - Check if noavatar is defined on template level
    $tplVars = $TemplateCache['site']['#variables'];
    $noAvatarURL = (isset($tplVars['configuration']) and is_array($tplVars['configuration']) and isset($tplVars['configuration']['noAvatarImage']) and $tplVars['configuration']['noAvatarImage']) ? (tpl_url . "/" . $tplVars['configuration']['noAvatarImage']) : (avatars_url . "/noavatar.png");

    // Preload plugins for usermenu
    loadActionHandlers('usermenu');

    // Load language file
    Lang::load('usermenu', 'site');

    // Prepare global params for TWIG
    $tVars = array();
    $tVars['flags']['isLogged'] = is_array($userROW) ? 1 : 0;

    // If not logged in
    if (!is_array($userROW)) {

        $tVars['flags']['loginError'] = (isset($SYSTEM_FLAGS['auth_fail'])) ? '$1' : '';
        $tVars['redirect'] = isset($SYSTEM_FLAGS['module.usermenu']['redirect']) ? $SYSTEM_FLAGS['module.usermenu']['redirect'] : $_SERVER['REQUEST_URI'];
        $tVars['reg_link'] = generateLink('core', 'registration');
        $tVars['lost_link'] = generateLink('core', 'lostpassword');
        $tVars['form_action'] = generateLink('core', 'login');
    } else {
        // User is logged in
        $tVars['profile_link'] = generateLink('uprofile', 'edit');
        $tVars['addnews_link'] = $config['admin_url'] . '/admin.php?mod=news&amp;action=add';
        $tVars['logout_link'] = generateLink('core', 'logout');
        $tVars['name'] = $userROW['name'];
        $tVars['phtumb_url'] = photos_url . '/' . (($userROW['photo'] != '') ? 'thumb/' . $userROW['photo'] : 'nophoto.gif');
        $tVars['home_url'] = home;

        // Generate avatar link
        $userAvatar = '';

        if ($config['use_avatars']) {
            if ($userROW['avatar']) {
                $userAvatar = avatars_url . "/" . $userROW['avatar'];
            } else {
                // If gravatar integration is active, show avatar from GRAVATAR.COM
                if ($config['avatars_gravatar']) {
                    $userAvatar = 'http://www.gravatar.com/avatar/' . md5(strtolower($userROW['mail'])) . '.jpg?s=' . $config['avatar_wh'] . '&d=' . urlencode($noAvatarURL);
                } else {
                    $userAvatar = $noAvatarURL;
                }
            }
        }
        $tVars['avatar_url'] = $userAvatar;
    }

    // Execute filters - add additional variables
    if (isset($PFILTERS['core.userMenu']) and is_array($PFILTERS['core.userMenu']))
        foreach ($PFILTERS['core.userMenu'] as $k => $v) {
            $v->showUserMenu($tVars);
        }

    $xt = $twig->loadTemplate('usermenu.tpl');
    $template['vars']['personal_menu'] = $xt->render($tVars);

    // Add special variables `personal_menu:logged` and `personal_menu:not.logged`
    $template['vars']['personal_menu:logged'] = is_array($userROW) ? $template['vars']['personal_menu'] : '';
    $template['vars']['personal_menu:not.logged'] = is_array($userROW) ? '' : $template['vars']['personal_menu'];
}

function coreSearchForm()
{
    global $twig, $template;

    Lang::load('search', 'site');

    $xt = $twig->loadTemplate(tpl_site . 'search.form.tpl');
    $template['vars']['search_form'] = $xt->render(array('form_url' => generateLink('search', '', array())));
}

// Return current news category
function getCurrentNewsCategory()
{
    global $currentCategory, $catz, $catmap, $config, $CurrentHandler, $SYSTEM_FLAGS;

    // Return if user is not reading any news
    if (('news' != $CurrentHandler['pluginName']) or (!isset($SYSTEM_FLAGS['news']['currentCategory.id'])))
        return false;

    // Return if user is not reading short/full news from categories
    if (('news' != $CurrentHandler['handlerName']) and ('print' != $CurrentHandler['handlerName']) and ('by.category' != $CurrentHandler['handlerName']))
        return false;

    return array(($CurrentHandler['handlerName'] == 'by.category') ? 'short' : 'full', $SYSTEM_FLAGS['news']['currentCategory.id'], $SYSTEM_FLAGS['news']['db.id']);
}

// Call plugin execution via TWIG
function twigCallPlugin($funcName, $params)
{
    global $TWIGFUNC;
    
    // Load CORE Plugin
    $cPlugin = CPlugin::instance();

    // Try to preload function if required
    if (!isset($TWIGFUNC[$funcName])) {
        if (preg_match("#^(.+?)\.(.+?)$#", $funcName, $m)) {
            $cPlugin->load($m[1], 'twig');
        }
    }

    if (!isset($TWIGFUNC[$funcName])) {
        print "ERROR :: callPlugin - no function [$funcName]<br/>\n";
        return;
    }

    return call_user_func($TWIGFUNC[$funcName], $params);
}

// Truncate HTML
function twigTruncateHTML($string, $len = 70, $finisher = '&nbsp;...')
{
    global $parse;

    return $parse->truncateHTML($string, $len, $finisher);
}

// Formats in a human readable form
function jsonFormatter($json)
{

    $result = '';
    $pos = 0;
    $strLen = mb_strlen($json, 'UTF-8');
    $indentStr = ' ';
    $newLine = "\n";
    $prevChar = '';
    $outOfQuotes = true;

    for ($i = 0; $i <= $strLen; $i++) {

        // Grab the next character in the string.
        $char = substr($json, $i, 1);

        // Are we inside a quoted string?
        if ($char == '"' and $prevChar != '\\') {
            $outOfQuotes = !$outOfQuotes;

            // If this character is the end of an element,
            // output a new line and indent the next line.
        } else if (($char == '}' or $char == ']') and $outOfQuotes) {
            $result .= $newLine;
            $pos--;
            for ($j = 0; $j < $pos; $j++) {
                $result .= $indentStr;
            }
        }

        // Add the character to the result string.
        $result .= $char;

        // If the last character was the beginning of an element,
        // output a new line and indent the next line.
        if (($char == ',' or $char == '{' or $char == '[') and $outOfQuotes) {
            $result .= $newLine;
            if ($char == '{' or $char == '[') {
                $pos++;
            }

            for ($j = 0; $j < $pos; $j++) {
                $result .= $indentStr;
            }
        }

        $prevChar = $char;
    }

    return $result;
}

function ngLoadCategories()
{
    global $mysql, $catz, $catmap;

    if (($result = cacheRetrieveFile('LoadCategories.dat', 86400)) === false) {
        $result = $mysql->select("select nc.*, ni.id as icon_id, ni.name as icon_name, ni.storage as icon_storage, ni.folder as icon_folder, ni.preview as icon_preview, ni.width as icon_width, ni.height as icon_height, ni.p_width as icon_pwidth, ni.p_height as icon_pheight from `" . prefix . "_category` as nc left join `" . prefix . "_images` ni on nc.image_id = ni.id order by nc.posorder asc", 1);
        cacheStoreFile('LoadCategories.dat', serialize($result));
    } else {
        $result = unserialize($result);
    }

    if (is_array($result)) {
        foreach ($result as $row) {
            $catz[$row['alt']] = $row;
            $catmap[$row['id']] = $row['alt'];
        }
    }
}

/**
 * Быстрый дебаг
 *
 * @param mixed $obj
 * @return string
 */
function dd($obj)
{
    if (is_array($obj) or is_object($obj)) {
        $obj = print_r($obj, true);
    }

    echo '<pre style="white-space: pre-wrap;">' . htmlentities($obj, ENT_QUOTES) . "</pre><br>\n";
}

// Выводит текущую дату в формате, выбранном в админке.
// А так же помещает её в тег, который динамически обновляется под клиента.
function cDate($date, $format = false, $relative = true, $itemprop = 'datePublished')
{

    if (!$format) $format = timestamp;//'j F Yг. в G:i';

    return '<time datetime="' . date('c', $date) . '" data-type="' . $format . '" data-relative="' . $relative . '">' . Lang::retDate($format, $date) . '</time>';
}

/**
 *
 * Не понятно, что это, зачем
 *
 */
$letters = array('%A8' => '%D0%81', '%B8' => '%D1%91', '%C0' => '%D0%90', '%C1' => '%D0%91', '%C2' => '%D0%92', '%C3' => '%D0%93', '%C4' => '%D0%94', '%C5' => '%D0%95', '%C6' => '%D0%96', '%C7' => '%D0%97', '%C8' => '%D0%98', '%C9' => '%D0%99', '%CA' => '%D0%9A', '%CB' => '%D0%9B', '%CC' => '%D0%9C', '%CD' => '%D0%9D', '%CE' => '%D0%9E', '%CF' => '%D0%9F', '%D0' => '%D0%A0', '%D1' => '%D0%A1', '%D2' => '%D0%A2', '%D3' => '%D0%A3', '%D4' => '%D0%A4', '%D5' => '%D0%A5', '%D6' => '%D0%A6', '%D7' => '%D0%A7', '%D8' => '%D0%A8', '%D9' => '%D0%A9', '%DA' => '%D0%AA', '%DB' => '%D0%AB', '%DC' => '%D0%AC', '%DD' => '%D0%AD', '%DE' => '%D0%AE', '%DF' => '%D0%AF', '%E0' => '%D0%B0', '%E1' => '%D0%B1', '%E2' => '%D0%B2', '%E3' => '%D0%B3', '%E4' => '%D0%B4', '%E5' => '%D0%B5', '%E6' => '%D0%B6', '%E7' => '%D0%B7', '%E8' => '%D0%B8', '%E9' => '%D0%B9', '%EA' => '%D0%BA', '%EB' => '%D0%BB', '%EC' => '%D0%BC', '%ED' => '%D0%BD', '%EE' => '%D0%BE', '%EF' => '%D0%BF', '%F0' => '%D1%80', '%F1' => '%D1%81', '%F2' => '%D1%82', '%F3' => '%D1%83', '%F4' => '%D1%84', '%F5' => '%D1%85', '%F6' => '%D1%86', '%F7' => '%D1%87', '%F8' => '%D1%88', '%F9' => '%D1%89', '%FA' => '%D1%8A', '%FB' => '%D1%8B', '%FC' => '%D1%8C', '%FD' => '%D1%8D', '%FE' => '%D1%8E', '%FF' => '%D1%8F',);
//$chars = array('%C2%A7' => '&#167;', '%C2%A9' => '&#169;', '%C2%AB' => '&#171;', '%C2%AE' => '&#174;', '%C2%B0' => '&#176;', '%C2%B1' => '&#177;', '%C2%BB' => '&#187;', '%E2%80%93' => '&#150;', '%E2%80%94' => '&#151;', '%E2%80%9C' => '&#147;', '%E2%80%9D' => '&#148;', '%E2%80%9E' => '&#132;', '%E2%80%A6' => '&#133;', '%E2%84%96' => '&#8470;', '%E2%84%A2' => '&#153;', '%C2%A4' => '&curren;', '%C2%B6' => '&para;', '%C2%B7' => '&middot;', '%E2%80%98' => '&#145;', '%E2%80%99' => '&#146;', '%E2%80%A2' => '&#149;');
// TEMPORARY SOLUTION AGAINST '&' quoting
$chars = array('%D0%86' => '[CYR_I]', '%D1%96' => '[CYR_i]', '%D0%84' => '[CYR_E]', '%D1%94' => '[CYR_e]', '%D0%87' => '[CYR_II]', '%D1%97' => '[CYR_ii]', '%C2%A7' => chr(167), '%C2%A9' => chr(169), '%C2%AB' => chr(171), '%C2%AE' => chr(174), '%C2%B0' => chr(176), '%C2%B1' => chr(177), '%C2%BB' => chr(187), '%E2%80%93' => chr(150), '%E2%80%94' => chr(151), '%E2%80%9C' => chr(147), '%E2%80%9D' => chr(148), '%E2%80%9E' => chr(132), '%E2%80%A6' => chr(133), '%E2%84%96' => '&#8470;', '%E2%84%A2' => chr(153), '%C2%A4' => '&curren;', '%C2%B6' => '&para;', '%C2%B7' => '&middot;', '%E2%80%98' => chr(145), '%E2%80%99' => chr(146), '%E2%80%A2' => chr(149));
$byary = array_flip($letters);

function convert($content)
{
    global $byary, $chars;

    $content = urlencode($content);
    $content = strtr($content, $byary);
    $content = strtr($content, $chars);
    $content = urldecode($content);

    return trim($content);
}
