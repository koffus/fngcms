<?php

// Protect against hack attempts
if (!defined('BBCMS')) die ('HAL');

register_plugin_page('guestbook', '', 'guestbook_list');
register_plugin_page('guestbook', 'edit', 'guestbook_edit');
register_plugin_page('guestbook', 'social', 'guestbook_social');

Lang::loadPlugin('guestbook', 'site');

switch ($action) {
    case 'add' :
        msg_add_submit();
        break;
    case 'edit' :
        msg_edit_submit();
        break;
    case 'delete' :
        msg_delete_submit();
        break;
}

/*
* Add message submit callback
*/
function msg_add_submit()
{
    global $template, $tpl, $twig, $userROW, $ip, $config, $mysql, $SYSTEM_FLAGS, $TemplateCache;

    $errors = array();

    // anonymous user
    if (!is_array($userROW)) {

        $_POST['author'] = secure_html($_POST['author']);
        if (!mb_strlen($_POST['author'], 'UTF-8')) {
            $errors[] = __('guestbook:error_req_name');
        }

        // Check captcha
        if (pluginGetVariable('guestbook', 'ecaptcha')) {

            require_once(root . "/plugins/guestbook/lib/recaptchalib.php");
            $publickey = pluginGetVariable('guestbook', 'public_key');
            $privatekey = pluginGetVariable('guestbook', 'private_key');

            $resp = recaptcha_check_answer($privatekey, $_SERVER["REMOTE_ADDR"], $_POST["recaptcha_challenge_field"], $_POST["recaptcha_response_field"]);

            if (!$resp->is_valid) {
                // What happens when the CAPTCHA was entered incorrectly
                $errors[] = __('guestbook:error_req_code');
            }
        }
    }

    $message = secure_html($_POST['content']);

    // check for links
    preg_match("~^(?:(?:https?|ftp|telnet)://(?:[a-z0-9_-]{1,32}(?::[a-z0-9_-]{1,32})?@)?)?(?:(?:[a-z0-9-]{1,128}\.)+(?:ru|su|com|net|org|mil|edu|arpa|gov|biz|info|aero|inc|name|[a-z]{2})|(?!0)(?:(?!0[^.]|255)[0-9]{1,3}\.){3}(?!0|255)[0-9]{1,3})(?:/[a-z0-9.,_@%&?+=\~/-]*)?(?:#[^ '\"&]*)?$~i", $message, $find_url);
    if (isset($find_url[0])) {
        $errors[] = __('guestbook:error_nolinks');
    }

    preg_match_all("@((https?://)?([-\w]+\.[-\w\.]+)+\w(:\d+)?(/([-\w/_\.]*(\?\S+)?)?)*)@", $message, $find_url);
    if ($find_url[0]) {
        $errors[] = __('guestbook:error_nolinks');
    }

    // check message length
    $minl = pluginGetVariable('guestbook', 'minlength');
    $maxl = pluginGetVariable('guestbook', 'maxlength');

    // check if message is not empty
    if (!mb_strlen(trim($_POST['content']), 'UTF-8')) {
        $errors[] = __('guestbook:error_req_text') . ' ' . str_replace(array('{minl}', '{maxl}'), array($minl, $maxl), __('guestbook:error_length_text'));
    }

    if ((mb_strlen($message, 'UTF-8') < $minl) or (mb_strlen($message, 'UTF-8') > $maxl)) {
        $errors[] = str_replace(array('{minl}', '{maxl}'), array($minl, $maxl), __('guestbook:error_length_text'));
    }

    $message = str_replace("\r\n", "<br />", $message);

    // author
    $author = (is_array($userROW)) ? $userROW['name'] : $_POST['author'];

    // status
    $status = pluginGetVariable('guestbook', 'approve_msg');

    // get fields
    $data = $mysql->select("select * from " . prefix . "_guestbook_fields");
    $fields = array();

    $fmail = array();
    foreach ($data as $num => $value) {
        $fields[$value['id']] = intval($value['required']);
        $fmail[] = array(
            'name' => $value['name'],
            'value' => secure_html($_POST[$value['id']]),
            );
    }

    $time = time() + ($config['date_adjust'] * 60);

    $new_rec = array(
        'postdate' => db_squote($time),
        'message' => db_squote($message),
        'author' => db_squote($author),
        'ip' => db_squote($ip),
        'status' => db_squote($status)
        );

    foreach ($fields as $fid => $freq) {
        if (!empty($_POST[$fid])) {
            $_POST[$fid] = secure_html($_POST[$fid]);
            $new_rec[$fid] = db_squote($_POST[$fid]);
        } elseif ($freq === 1) {
            $errors[] = __('guestbook:error_field_required');
        } else {
            $new_rec[$fid] = "''";
        }
    }

    // get social images ID
    $social = array();
    if (strlen(trim($_POST['Vkontakte_id']))) {
        $social['Vkontakte'] = $_POST['Vkontakte_id'];
    }
    if (strlen(trim($_POST['Facebook_id']))) {
        $social['Facebook'] = $_POST['Facebook_id'];
    }
    if (strlen(trim($_POST['Google_id']))) {
        $social['Google'] = $_POST['Google_id'];
    }
    if (strlen(trim($_POST['Instagram_id']))) {
        $social['Instagram'] = $_POST['Instagram_id'];
    }
    $new_rec['social'] = db_squote(serialize($social));

    if (!count($errors)) {
        $mysql->query("INSERT INTO " . prefix . "_guestbook (" . implode(', ', array_keys($new_rec)) . ") values (" . implode(', ', array_values($new_rec)) . ")");

        $success[] = ($status == 1) ? __('guestbook:success_add_wo_approve') : $success_msg = __('guestbook:success_add');

        // send email
        $tpath = plugin_locateTemplates('guestbook', array('mail_success'));

        $send_email = pluginGetVariable('guestbook', 'send_email');

        $tVars = array(
            'time' => $time,
            'message' => $message,
            'author' => $author,
            'ip' => $ip,
            'fields' => $fmail
            );

        $mailBody = $twig->render($tpath['mail_success'] . 'mail_success.tpl', $tVars);
        $mailSubject = __('guestbook:mailSubject');

        $send_email_array = explode(',', $send_email);
        foreach ($send_email_array as $email) {
            sendEmailMessage($email, $mailSubject, $mailBody, $filename = false, $mail_from = false, $ctype = 'text/html');
        }
        $url = checkLinkAvailable('guestbook', '') ?
        generatePluginLink('guestbook', '', array('act' => 'add'), array()) :
        generateLink('core', 'plugin', array('plugin' => 'guestbook'), array('add' => 1));
        @header("Location: " . $url);
    } else {
        _guestbook_clear_session();
        $_SESSION['guestbook_errors'] = $errors;
    }
}

/*
* Edit message submit callback
*/
function msg_edit_submit()
{
    global $template, $tpl, $userROW, $ip, $config, $mysql, $twig;

    $id = secure_html($_REQUEST['id']);
    $author = secure_html($_REQUEST['author']);
    $message = secure_html($_REQUEST['content']);
    $answer = secure_html($_REQUEST['answer']);
    $message = str_replace("\r\n", "<br />", $message);

    if (empty($author) or empty($message)) {
        $errors[] = __('guestbook:error_field_required');
    }

    // get fields
    $fdata = $mysql->select("SELECT * FROM " . prefix . "_guestbook_fields");

    $upd_rec = array(
        'message' => db_squote($message),
        'answer' => db_squote($answer),
        'author' => db_squote($author)
        );

    // collect fields data
    foreach ($fdata as $fnum => $frow) {
        if (!empty($_REQUEST[$frow['id']])) {
            $upd_rec[$frow['id']] = db_squote($_REQUEST[$frow['id']]);
        } elseif (intval($frow['required']) === 1) {
            $errors[] = __('guestbook:error_field_required');
        } else {
            $upd_rec[$frow['id']] = "''";
        }
    }

    // prepare query
    $upd_str = '';
    $count = 0;
    foreach ($upd_rec as $k => $v) {
        $upd_str .= $k . '=' . $v;
        $count++;
        if ($count < count($upd_rec)) {
            $upd_str .= ', ';
        }
    }

    if (!count($errors)) {
        $mysql->query('UPDATE ' . prefix . '_guestbook SET ' . $upd_str . ' WHERE id = \'' . intval($id) . '\' ');
        $url = checkLinkAvailable('guestbook', '') ?
            generatePluginLink('guestbook', '', array('act' => 'upd'), array()) :
            generatePluginLink('core', 'plugin', array('plugin' => 'guestbook'), array('upd' => 1));
    } else {
        $url = checkLinkAvailable('guestbook', 'edit') ?
        generatePluginLink('guestbook', 'edit', array('id' => $id), array('error' => 1)) :
        generateLink('core', 'plugin', array('plugin' => 'guestbook', 'handler' => 'edit'), array('id' => $id, 'error' => 1));
        _guestbook_clear_session();
        $_SESSION['guestbook_errors'] = $errors;
    }
    @header("Location: " . $url);
}

/*
* Delete message submit callback
*/
function msg_delete_submit()
{
    global $userROW, $mysql, $template;

    if (is_array($userROW) and ($userROW['status'] == "1")) {
        if (!is_array($mysql->record("SELECT id FROM " . prefix . "_guestbook WHERE id=" . db_squote(intval($_REQUEST['id']))))) {
            $template['vars']['mainblock'] .= __('guestbook:error_entry_notfound');
            return;
        }
        $mysql->query("DELETE FROM " . prefix . "_guestbook WHERE id = " . intval($_REQUEST['id']));
        $url = checkLinkAvailable('guestbook', '') ?
            generatePluginLink('guestbook', '', array('act' => 'del'), array()) :
            generateLink('core', 'plugin', array('plugin' => 'guestbook'), array('del' => 1));
        @header("Location: " . $url);
    }
}

/*
* List messages page
*/
function guestbook_list($params = array())
{
    global $template, $tpl, $twig, $userROW, $ip, $config, $mysql, $SYSTEM_FLAGS, $TemplateCache, $CurrentHandler;

    $SYSTEM_FLAGS['info']['title']['group'] = __('guestbook:title');

    require_once(root . "/plugins/guestbook/lib/recaptchalib.php");
    $publickey = pluginGetVariable('guestbook', 'public_key');
    $privatekey = pluginGetVariable('guestbook', 'private_key');

    // ADD notication
    if ((isset($params['act']) and $params['act'] == 'add') or (isset($_REQUEST['add']) and $_REQUEST['add'])) {
        $success_add[] = (pluginGetVariable('guestbook', 'approve_msg')) ? __('guestbook:success_add_wo_approve') : __('guestbook:success_add');
    }

    // EDIT notication
    if ((isset($params['act']) and $params['act'] == 'upd') or (isset($_REQUEST['upd']) and $_REQUEST['upd'])) {
        $success_add[] = __('guestbook:success_edit');
    }

    // DELETE notication
    if ((isset($params['act']) and $params['act'] == 'del') or (isset($_REQUEST['del']) and $_REQUEST['del'])) {
        $success_add[] = __('guestbook:success_delete');
    }

    $errors = array();
    if ((!empty($_SESSION['guestbook_errors']) )) {
        $errors = array_merge($errors, $_SESSION['guestbook_errors']);
    }

    // pagination
    $perpage = intval(pluginGetVariable('guestbook', 'perpage'));

    if (($perpage < 1) or ($perpage > 5000)) {
        $perpage = 10;
    }

    $page = intval(isset($CurrentHandler['params']['page']) ? $CurrentHandler['params']['page'] : (isset($_REQUEST['page']) ? $_REQUEST['page'] : 0));

    if ($page < 1) $page = 1;
    if (!$start) $start = ($page - 1) * $perpage;

    $total_count = $mysql->result("SELECT COUNT(*) AS num FROM " . prefix . "_guestbook WHERE status = 1");

    $PagesCount = ceil($total_count / $perpage);

    $paginationParams = checkLinkAvailable('guestbook', '') ?
        array('pluginName' => 'guestbook', 'pluginHandler' => '', 'params' => array(), 'xparams' => array(), 'paginator' => array('page', 0, false)) :
        array('pluginName' => 'core', 'pluginHandler' => 'plugin', 'params' => array('plugin' => 'guestbook'), 'xparams' => array(), 'paginator' => array('page', 1, false));

    $tpath = plugin_locateTemplates('guestbook', array(':'));
    $navigations = parse_ini_file($tpath[':'] . '/variables.ini', true);

    $order = pluginGetVariable('guestbook', 'order');

    // get fields
    $fields = $mysql->select("select * from " . prefix . "_guestbook_fields");
    $tEntries = array();

    foreach ($fields as $fNum => $fRow) {
        $tEntry = array(
            'id' => $fRow['id'],
            'name' => $fRow['name'],
            'placeholder' => $fRow['placeholder'],
            'default_value' => $fRow['default_value'],
            'required' => intval($fRow['required'])
            );
        $tEntries[$fRow['id']] = $tEntry;
    }

    $tVars = array(
        'entries' => _guestbook_records($order, $start, $perpage),
        'pages' => generatePagination($page, 1, $PagesCount, 10, $paginationParams, $navigations),
        'total_count' => $total_count,
        'perpage' => $perpage,
        'errors' => $errors,
        'success' => $success_add,
        'ip' => $ip,
        'smilies' => (pluginGetVariable('guestbook', 'usmilies')) ? Smilies('', 10) : "",
        'bbcodes' => (pluginGetVariable('guestbook', 'ubbcodes')) ? BBCodes() : "",
        'use_captcha' => (pluginGetVariable('guestbook', 'ecaptcha')),
        'captcha' => (pluginGetVariable('guestbook', 'ecaptcha') and !(is_array($userROW))) ? recaptcha_get_html($publickey) : '',
        'use_guests' => (!is_array($userROW) and !pluginGetVariable('guestbook', 'guests')),
        'fields' => $tEntries
        );

    $tpath = plugin_locateTemplates('guestbook', array('guestbook.list'), pluginGetVariable('guestbook', 'skin'));
    $template['vars']['mainblock'] .= $twig->render($tpath['guestbook.list'] . 'guestbook.list.tpl', $tVars);

    _guestbook_clear_session();
}

function _guestbook_clear_session(){
    unset($_SESSION['guestbook_errors']);
}

/*
* Records list helper
*/
function _guestbook_records($order, $start, $perpage)
{
    global $mysql, $tpl, $userROW, $config, $parse;

    foreach ($mysql->select("SELECT * FROM " . prefix . "_guestbook WHERE status = 1 ORDER BY id {$order} LIMIT {$start}, {$perpage}") as $row) {
        if (pluginGetVariable('guestbook', 'usmilies')) {
            $row['message'] = $parse->smilies($row['message']);
        }
        if (pluginGetVariable('guestbook', 'ubbcodes')) {
            $row['message'] = $parse->bbcodes($row['message']);
        }

        $editlink = checkLinkAvailable('guestbook', 'edit') ?
            generatePluginLink('guestbook', 'edit', array('id' => $row['id']), array()) :
            generateLink('core', 'plugin', array('plugin' => 'guestbook', 'handler' => 'edit'), array('id' => $row['id']));

        $dellink = generateLink('core', 'plugin', array('plugin' => 'guestbook'), array('action' => 'delete', 'id' => $row['id']));
        $comnum++;

        // get fields
        $data = $mysql->select("select * from " . prefix . "_guestbook_fields");
        $fields = array();

        foreach ($data as $num => $value) {
            $fields[$value['id']] = $value['name'];
        }

        $comment_fields = array();
        foreach ($fields as $fid => $fname) {
            $comment_fields[$fid] = array(
                'id' => $fid,
                'name' => $fname,
                'value' => $row[$fid],
                );
        }

        // set date format
        $date_format = pluginGetVariable('guestbook', 'date');
        if (empty($date_format)) {
            $date_format = 'j F Y';
        }

        // get social data
        $social = unserialize($row['social']);
        $profiles = array();
        foreach ($social as $name => $id) {
            $img = $mysql->record("SELECT name, description FROM " . prefix . "_images WHERE id = {$id}");
            $profiles[$name] = array(
                'photo' => $config['images_url'] . '/' . $img['name'],
                'link' => $img['description'],
                );
        }

        $comments[] = array(
            'id' => $row['id'],
            'date' => Lang::retDate($date_format, $row['postdate']),
            'message' => $row['message'],
            'answer' => $row['answer'],
            'author' => $row['author'],
            'ip' => $row['ip'],
            'comnum' => $comnum,
            'edit' => $editlink,
            'del' => $dellink,
            'fields' => $comment_fields,
            'social' => $profiles
            );
    }
    return $comments;
}

/*
* Edit message page
*/
function guestbook_edit()
{
    global $template, $tpl, $userROW, $ip, $config, $mysql, $twig, $CurrentHandler;

    $id = intval(isset($CurrentHandler['params']['id']) ? $CurrentHandler['params']['id'] : (isset($_REQUEST['id']) ? secure_html($_REQUEST['id']) : ''));

    $tpath = plugin_locateTemplates('guestbook', array('guestbook.edit'));

    // admin permission is required to edit messages
    if (is_array($userROW) and $userROW['status'] == "1") {

        // get fields
        $fdata = $mysql->select("SELECT * FROM " . prefix . "_guestbook_fields");

        if (!is_array($row = $mysql->record("SELECT * FROM " . prefix . "_guestbook WHERE id=" . db_squote(intval($id))))) {
            $tVars = array(
                'error' => __('guestbook:error_no_entry')
                );

            $template['vars']['mainblock'] .= $twig->render($tpath['guestbook.edit'] . 'guestbook.edit.tpl', $tVars);
            return;
        }

        $row['message'] = str_replace("<br />", "\r\n", $row['message']);
        $row['answer'] = str_replace("<br />", "\r\n", $row['answer']);

        // output fields data
        $tFields = array();
        foreach ($fdata as $fnum => $frow) {
                $tField = array(
                'id' => $frow['id'],
                'name' => $frow['name'],
                'placeholder' => $frow['placeholder'],
                'default_value' => $frow['default_value'],
                'required' => intval($frow['required']),
                'value' => $row[$frow['id']]
                );
            $tFields[] = $tField;
        }

        // Error notification
        $error = (isset($_REQUEST['error']) and $_REQUEST['error']) ? __('guestbook:error_field_required') : '';

        $tVars = array(
            'author' => $row['author'],
            'answer' => $row['answer'],
            'message' => $row['message'],
            'id' => $row['id'],
            'fields' => $tFields,
            'error' => $error
            );

        $template['vars']['mainblock'] .= $twig->render($tpath['guestbook.edit'] . 'guestbook.edit.tpl', $tVars);

    } else {

    $tVars = array(
        'error' => __('guestbook:error_no_permission')
        );

    $template['vars']['mainblock'] .= $twig->render($tpath['guestbook.edit'] . 'guestbook.edit.tpl', $tVars);
    }
}

/*
* Block display callback
*/
function guestbook_block($params)
{
    global $CurrentHandler, $twig, $config;

    $count = ($params['count'] > 0) ? intval($params['count']) : 10;

    $tVars = array(
        'entries' => _guestbook_records('DESC', 0, $count),
        );

    $tpath = plugin_locateTemplates('guestbook', array('guestbook.block'));
    return $twig->render($tpath['guestbook.block'] . 'guestbook.block.tpl', $tVars);
}

function guestbook_social()
{
    global $config, $template, $tpl, $mysql;

    Session::init();

    $providers = array('Vkontakte', 'Facebook', 'Google', 'Instagram');

    $auth_config = array(
        "base_url" => home . "/engine/plugins/guestbook/lib/hybridauth/",
        "providers" => array(
            "Vkontakte" => array(
                "enabled" => true,
                "keys" => array("id" => pluginGetVariable('guestbook', 'vk_client_id'), "secret" => pluginGetVariable('guestbook', 'vk_client_secret')),
                ),
            "Facebook" => array(
                "enabled" => true,
                "keys" => array("id" => pluginGetVariable('guestbook', 'facebook_client_id'), "secret" => pluginGetVariable('guestbook', 'facebook_client_secret')),
                "scope" => "email",
                "display" => "popup",
                "auth_type" => "reauthenticate"
                ),
            "Google" => array(
                "enabled" => true,
                "keys" => array("id" => pluginGetVariable('guestbook', 'google_client_id'), "secret" => pluginGetVariable('guestbook', 'google_client_secret')),
                "scope" => "https://www.googleapis.com/auth/userinfo.profile",
                "display" => "popup",
                "approval_prompt" => "force"
                ),
            "Instagram" => array(
                "enabled" => true,
                "keys" => array("id" => pluginGetVariable('guestbook', 'instagram_client_id'), "secret" => pluginGetVariable('guestbook', 'instagram_client_secret')),
                ),
            )
        );

    if (isset($_GET['provider']) and in_array($_GET['provider'], $providers)) {

        $provider = $_GET['provider'];
        require_once($_SERVER['DOCUMENT_ROOT'] . '/engine/plugins/guestbook/lib/hybridauth/Hybrid/Auth.php');

        try {
            $hybridauth = new Hybrid_Auth($auth_config);
            $adapter = $hybridauth->authenticate($provider);
        } catch (Exception $e) {
            echo "<script>self.close();</script>\n";
        }

        $user_profile = $adapter->getUserProfile();

        // print_r($user_profile);
        // exit;

        $profile = ($provider == 'Instagram') ? 'https://www.instagram.com/' . $user_profile->username : $user_profile->profileURL;
        $photo = $user_profile->photoURL;

        if (!empty($photo)) {

            // Prevent duplicate uploads
            $exist = $mysql->record("SELECT id FROM " . prefix . "_images WHERE description = " . db_squote($profile) . " LIMIT 1");

            if (!empty($exist['id'])) {
                $rowID = $exist;
            } else {
                addToFiles('newavatar', $photo);

                // UPLOAD AVATAR
                if ($_FILES['newavatar']['name']) {

                    $imanage = new ImageManagment();

                    $fname = $provider . '_' . time() . '_' . strtolower($_FILES['newavatar']['name']);
                    if (!strpos($fname, '.jpg')) {
                        $fname .= '.jpg';
                    }
                    $ftmp = $_FILES['newavatar']['tmp_name'];

                    $mysql->query("INSERT INTO " . prefix . "_images (name, orig_name, description, folder, date, owner_id, category) VALUES ("
                    . db_squote($fname) . ", "
                    . db_squote($fname) . ", "
                    . db_squote($profile) . ", '', unix_timestamp(now()), '1', '0')");

                    $rowID = $mysql->record("select LAST_INSERT_ID() as id");

                    if (copy($ftmp, $config['images_dir'] . $fname)) {
                        $sz = $imanage->get_size($config['images_dir'] . $fname);
                        $mysql->query("update " . prefix . "_images set width=" . db_squote($sz['1']) . ", height=" . db_squote($sz['2']) . " where id = " . db_squote($rowID['id']) . " ");
                    }
                }
            }
            $adapter->logout();

            echo "<script>window.opener.document.getElementById('" . $provider . "_li').className += 'active'; " .
            "window.opener.document.getElementById('" . $provider . "_id').value = " . $rowID['id'] . "; self.close();</script>\n";
        }
    }
}

/**
* Add to $_FILES from external url
*/
function addToFiles($key, $url)
{

    $tempName = tempnam(ini_get('upload_tmp_dir'), 'upload_');
    $originalName = basename(parse_url($url, PHP_URL_PATH));

    $imgRawData = file_get_contents($url);
    file_put_contents($tempName, $imgRawData);
    $info = getimagesize($tempName);

    $_FILES[$key] = array(
        'name' => $originalName,
        'type' => $info['mime'],
        'tmp_name' => $tempName,
        'error' => 0,
        'size' => strlen($imgRawData),
        );

    //return $_FILES[$key];
}

twigRegisterFunction('guestbook', 'show', 'guestbook_block');
