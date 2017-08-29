<?php

//
// Configuration file for plugin
//

// Protect against hack attempts
if (!defined('NGCMS')) die ('HAL');

// Load lang files
Lang::loadPlugin($plugin, 'config', '', ':');

// Micro sRouter =)
switch ($action)
{
    case 'list':
        if (!empty($subaction)) {
            if (empty($_REQUEST['selected_comments']) or !count($_REQUEST['selected_comments'])) {
                msg(array('type' => 'danger', 'message' => __('comments:msg.selectcom')));
            } else {
                switch($subaction) {
                    case 'mass_approve':
                        commentsUpdateAction($plugin, $action, $subaction);
                        break;
                    case 'mass_forbidden':
                        commentsUpdateAction($plugin, $action, $subaction);
                        break;
                    case 'mass_delete':
                        commentsDeleteAction($plugin, $action);
                        break;
                }
            }
        }
        commentsListAction($plugin, $action);
        break;

    case 'dell':
        commentsDeleteAction($plugin, $action);
        commentsListAction($plugin, $action);
        break;

    case 'edit':
    case 'edit_submit':
        commentsEditAction($plugin, $action);
        break;

    case 'widget_list':
    case 'widget_edit_submit':
    case 'widget_dell':
        widgetListAction($plugin, $action);
        break;

    case 'widget_add':
        widgetEditAction($plugin, $action);
        break;

    default:
        commentsConfigAction($plugin, $action);
        break;
}

// Set default values if values are not set [for new variables]
$commentsDefaultConfig = [
    'moderate' => 1,
    'global_default' => 1,
    'default_news' => 2,
    'default_categories' => 2,
    'multipage' => 1,
    'inform_admin' => 1,
    'inform_author' => 1,
    'minlen' => 4,
    'maxlen' => 500,
    ];
foreach ($commentsDefaultConfig as $k => $v ) {
    if (pluginGetVariable($plugin, $k) == null)
        pluginSetVariable($plugin, $k, $v);
}

// Configuration page for plugin Comments
function commentsConfigAction($plugin, $action)
{
// Fill configuration parameters
    $cfg = array(
        'description' => __($plugin.':description'),
        'navigation' => array(
            array('class' => 'active','href' => 'admin.php?mod=extra-config&plugin=comments','title' => __('group.config')),
            array('href' => 'admin.php?mod=extra-config&plugin=comments&action=list','title' => __('comments:nav.list')),
            //array('href' => 'admin.php?mod=extra-config&plugin=comments&action=widget_list','title' => __('comments:nav.widgeList')),
        ),
        'submit' => array(
            array('type' => 'default'),
            array('type' => 'reinstall'),
            //array('type' => 'clearCacheFiles'),
            //array('class' => 'btn btn-primary','href' => 'admin.php?mod=extra-config&plugin=comments&stype=install','title' => 'Переустановить плагин'),
            //array('class' => 'btn btn-danger','href' => 'admin.php?mod=extra-config&plugin=comments&stype=deinstall','title' => 'Удалить плагин'),
        )
    );

    $cfgX = array();
        array_push($cfgX, array(
            'name' => 'regonly',
            'title' => __($plugin.':regonly'),
            'descr' => __($plugin.':regonly#desc'),
            'type' => 'select',
            'values' => array( '0' => __('noa'), '1' => __('yesa')),
            'value' => intval(pluginGetVariable($plugin,'regonly'))
            ));
        array_push($cfgX, array(
            'name' => 'moderate',
            'title' => 'Модерация комментариев',
            'descr' => '<code>Да</code> - использовать модерацию<br><code>Нет</code> - комментарии будут публиковаться без одобрения автора статьи или администратора<br>После изменения опции обновляйте счетчик новостей в управлении <a href="admin.php?mod=dbo" target="_blank">Базой Данных</a>',
            'type' => 'select',
            'values' => array( '0' => __('noa'), '1' => __('yesa')),
            'value' => intval(pluginGetVariable($plugin,'moderate'))
            ));
        array_push($cfgX, array(
            'name' => 'guest_edup_lock',
            'title' => "Запретить гостям использовать <b>email</b>&#39;ы зарегистрированных пользователей",
            'descr' => '<code>Да</code> - гости не смогут в качестве email адреса указывать адрес уже зарегистрированных пользоваталей<br/><code>Нет</code> - гость может использовать любой валидный email адрес',
            'type' => 'select',
            'values' => array('0' => __('noa'), '1' => __('yesa')),
            'value' => intval(pluginGetVariable($plugin,'guest_edup_lock')),
            ));
        array_push($cfgX, array(
            'name' => 'backorder',
            'title' => "Очередность отображения комментариев",
            'descr' => "<code>Прямая</code> - отображение в порядке добавления<br/><code>Обратная</code> - самые новые показываются первыми",
            'type' => 'select',
            'values' => array('0' => 'Прямая', '1' => 'Обратная'),
            'value' => intval(pluginGetVariable($plugin,'backorder')),
            ));
        array_push($cfgX, array(
            'name' => 'minlen',
            'title' => "Минимальный размер",
            'descr' => "Укажите минимальное кол-во символов для комментариев (например: <code>10</code>)",
            'type' => 'input',
            'value' => pluginGetVariable($plugin, 'minlen'),
            ));
        array_push($cfgX, array(
            'name' => 'maxlen',
            'title' => "Максимальный размер",
            'descr' => "Укажите максимальное кол-во символов для комментариев (например: <code>200</code>; <code>0</code> - не ограничивать)",
            'type' => 'input',
            'value' => pluginGetVariable($plugin, 'maxlen'),
            ));
        array_push($cfgX, array(
            'name' => 'maxwlen',
            'title' => "Автоурезание слов в комментариях",
            'descr' => "В случае превышения заданного числа, в слово будет автоматически будет добавляться пробел (например: <code>50</code>)",
            'type' => 'input', 'value' => pluginGetVariable($plugin, 'maxwlen'),
            ));
        array_push($cfgX, array(
            'name' => 'multi',
            'title' => "Разрешить множественные комментарии",
            'descr' => "<code>Да</code> - пользователь может оставлять последовательно несколько комментариев<br/><code>Нет</code> - пользователю запрещено размещать последовательно несколько комментариев (необходимо дождаться комментария другого пользователя)",
            'type' => 'select',
            'values' => array('0' => __('noa'), '1' => __('yesa')),
            'value' => intval(pluginGetVariable($plugin,'multi')),
            ));
        array_push($cfgX, array(
            'name' => 'author_multi',
            'title' => "Разрешить множественные комментарии <u>для автора</u>",
            'descr' => "<code>Да</code> - автор может оставлять последовательно несколько комментариев<br/><code>Нет</code> - автору запрещено размещать последовательно несколько комментариев",
            'type' => 'select',
            'values' => array('0' => __('noa'), '1' => __('yesa')),
            'value' => intval(pluginGetVariable($plugin,'author_multi')),
            ));
        array_push($cfgX, array(
            'name' => 'timestamp',
            'title' => "Формат отображения даты/времени",
            'descr' => 'Помощь по работе функции: <a href="http://php.net/date/" target="_blank">php.net/date</a><br/>Значение по умолчанию: <code>j.m.Y - H:i</code>',
            'type' => 'input',
            'value' => pluginGetVariable($plugin,'timestamp'),
            ));
    array_push($cfg, array(
        'mode' => 'group',
        'title' => __('group.general'),
        'entries' => $cfgX,
        ));

    $cfgX = array();
        array_push($cfgX, array(
            'name' => 'localSource',
            'title' => __('localSource'),
            'descr' => __('localSource#desc'),
            'type' => 'select',
            'values' => array('0' => __('localSource_0'), '1' => __('localSource_1'),),
            'value' => intval(pluginGetVariable($plugin, 'localSource')) ? intval(pluginGetVariable($plugin, 'localSource')) : '0',
            ));
    array_push($cfg, array(
        'mode' => 'group',
        'title' => __('group.source'),
        'entries' => $cfgX,
        ));

    $cfgX = array();
        array_push($cfgX, array(
            'name' => 'global_default',
            'title' => "По умолчанию комментарии",
            'descr' => '<code>разрешены</code> - будут разрешены в новости если они явно не запрещены<br/><code>запрещены</code> - будут запрещены новости если они явно не разрешены',
            'type' => 'select',
            'values' => array('0' => 'запрещены', '1' => 'разрешены'),
            'value' => intval(pluginGetVariable($plugin,'global_default')),
            ));
        array_push($cfgX, array(
            'name' => 'default_news',
            'title' => "Значение доступности при добавлении новостей",
            'descr' => 'При добавлении новостей по умолчанию будет устанавливаться:<br/><code>запретить</code> - комментарии будут запрещены<br/><code>разрешить</code> - комментарии будут разрешены<br/><code>по умолчанию</code> - флаг разрешения/запрета комментариев будет браться из настроек главной категории новости',
            'type' => 'select',
            'values' => array('0' => 'запрещены', '1' => 'разрешены', '2' => 'по умолчанию'),
            'value' => intval(pluginGetVariable($plugin,'default_news')),
            ));
        array_push($cfgX, array(
            'name' => 'default_categories',
            'title' => "Значение доступности при добавлении категорий",
            'descr' => 'При добавлении категорий по умолчанию будет устанавливаться:<br/><code>запретить</code> - по умолчанию комментарии в новостях этой категории запрещены<br/><code>разрешить</code> - по умолчанию комментарии в этой категории будут разрешены<br/><code>по умолчанию</code> - флаг разрешения/запрета комментариев будет браться из параметра "по умолчанию комментарии"',
            'type' => 'select',
            'values' => array('0' => 'запрещены', '1' => 'разрешены', '2' => 'по умолчанию'),
            'value' => intval(pluginGetVariable($plugin,'default_categories')),
            ));
    array_push($cfg, array(
        'mode' => 'group',
        'title' => __('group.default'),
        'entries' => $cfgX,
        'toggle' => true,
        'toggle.mode' => 'hide',
        ));

    $cfgX = array();
        array_push($cfgX, array(
            'name' => 'multipage',
            'title' => "Использовать многостраничное отображение",
            'descr' => '<code>Да</code> - на странице новости будет отображаться только часть комментариев, остальные будут доступны на отдельной страничке<br/><code>Нет</code> - все комментарии будут отображаться на странице новости',
            'type' => 'select',
            'values' => array('0' => __('noa'), '1' => __('yesa')),
            'value' => intval(pluginGetVariable($plugin,'multipage')),
            ));
        array_push($cfgX, array(
            'name' => 'multi_mcount',
            'title' => "Кол-во комментариев на странице новости",
            'descr' => "Укажите кол-во комментариев, отображаемых на странице новости<br/>(<code>0</code> - не отображать ни одного комментария)",
            'type' => 'input',
            'value' => pluginGetVariable($plugin, 'multi_mcount'),
            ));
        array_push($cfgX, array(
            'name' => 'multi_scount',
            'title' => "Кол-во комментариев на странице с комментариями",
            'descr' => "Укажите кол-во комментариев, отображаемых на каждой странице с комментариями<br/>(<code>0</code> - отображать все на одной странице)",
            'type' => 'input',
            'value' => pluginGetVariable($plugin, 'multi_scount'),
            ));
    array_push($cfg, array(
        'mode' => 'group',
        'title' => __($plugin.':group.multipage'),
        'entries' => $cfgX,
        'toggle' => true,
        'toggle.mode' => 'hide',
        ));

    $cfgX = array();
        array_push($cfgX, array(
            'name' => 'inform_author',
            'title' => "Оповещать автора новости по email о новом комментарии",
            'descr' => "<code>Да</code> - при добавлении каждого комментария автор будет получать e-mail сообщение<br/><code>Нет</code> - автор не будет получать e-mail нотификаций",
            'type' => 'select',
            'values' => array('0' => __('noa'), '1' => __('yesa')),
            'value' => intval(pluginGetVariable($plugin,'inform_author')),
            ));
        array_push($cfgX, array(
            'name' => 'inform_admin',
            'title' => "Оповещать администратора о новом комментарии",
            'descr' => "<code>Да</code> - при добавлении каждого комментария администратор будет получать e-mail сообщение<br/><code>Нет</code> - администратор(ы) не будет получать e-mail нотификаций",
            'type' => 'select',
            'values' => array('0' => __('noa'), '1' => __('yesa')),
            'value' => intval(pluginGetVariable($plugin,'inform_admin')),
            ));
    array_push($cfg, array(
        'mode' => 'group',
        'title' => __($plugin.':group.inform'),
        'entries' => $cfgX,
        'toggle' => true,
        'toggle.mode' => 'hide',
        ));
        
    $cfgX = array();
        array_push($cfgX, array(
            'name' => 'rebuild', 
            'title' => __('rebuild'),
            'descr' => __('rebuild#desc'),
            'type' => 'select', 
            'value' => 0, 
            'values' => array('1' => __('yesa'), '0' => __('noa')),
            'nosave' => 1
            ));
    array_push($cfg, array(
        'mode' => 'group',
        'title' => __('group.rebuild'),
        'entries' => $cfgX,
        ));

    // RUN
    if ('commit' == $action) {
        // Rebuild index table
        if ($_REQUEST['rebuild']) {
            commentsUpdateCounters($plugin, $action);
            msg(array('message' => __('rebuild.done')));
        } else {
            // If submit requested, do config save
            commit_plugin_config_changes($plugin, $cfg);
        }
    }

    generate_config_page($plugin, $cfg);
}

function commentsListAction($plugin, $action)
{
    global $config, $mysql, $twig, $parse;

    // Load CORE Plugin
    $cPlugin = CPlugin::instance();

    // Fill configuration parameters
    $cfg = array(
        'action' => 'admin.php?mod=extra-config&plugin=comments&action=list',
        'navigation' => array(
            array('href' => 'admin.php?mod=extra-config&plugin=comments','title' => __('group.config')),
            array('class' => 'active','href' => 'admin.php?mod=extra-config&plugin=comments&action=list','title' => __('comments:nav.list')),
            //array('href' => 'admin.php?mod=extra-config&plugin=comments&action=widget_list','title' => __('comments:nav.widgeList')),
        ),
        'submit' => array(
            //array('type' => 'default'),
            //array('class' => 'btn btn-primary','href' => 'admin.php?mod=extra-config&plugin=comments&action=update','title' => __('comments:button_update')),
        )
    );

    //
    // Always validate and prepare incoming local data
    $module = !empty($_REQUEST['modules']) ? secure_html($_REQUEST['modules']) : 'news';
    $approve = (isset($_REQUEST['approve']) and '' != trim($_REQUEST['approve'])) ? intval($_REQUEST['approve']) : '0'; // empty(0) is empty!!!
    $author = !empty($_REQUEST['author']) ? secure_html($_REQUEST['author']) : '';
    $page = !empty($_REQUEST['page']) ? intval($_REQUEST['page']) : 1;

    // Load admin page based cookies to Records Per Page
    $admCookie = admcookie_get();
    $fRPP = isset($_REQUEST['rpp']) ? intval($_REQUEST['rpp']) : intval($admCookie['comments']['pp']);
    // Set default value for `Records Per Page` parameter
    if (($fRPP < 2) or ($fRPP > 2000)) $fRPP = 8;
    // Save into cookies current value
    $admCookie['comments']['pp'] = $fRPP;
    admcookie_set($admCookie);

    // Make sorting optins
    $sortRows = $mysql->select('SELECT module FROM '.prefix.'_comments GROUP BY module');
    if (count($sortRows)) {
        foreach($sortRows as $row) {
            $modules[$row['module']] = ucfirst(secure_html($row['module']));
        }
        $tVars['modules'] = MakeDropDown($modules, 'modules', $module);
    } else {
        $tVars['modules'] = MakeDropDown([], 'modules');
    }
    $tVars['approve'] = MakeDropDown(array('0' => 'На модерации', '1' => 'Опубликованные'), 'approve', $approve);
    $tVars['author'] = $author;

    // Prepare filter rules for commets shower
    $commentsFilter = array();
    array_push($commentsFilter, 'approve=' . db_squote($approve));
    array_push($commentsFilter, 'module=' . db_squote($module));
    if (!empty($author)) {
        array_push($commentsFilter, 'author=' . db_squote($author));
    }
    $sqlQPart = 'from `'.prefix.'_comments` ' . (count($commentsFilter) ? "where ".implode(" AND ", $commentsFilter) : '') . ' order by id desc';

    $cnt = $mysql->record("select count(`id`) as cid ".$sqlQPart);
    $countNews = $cnt['cid'];
    $countPages = ceil($countNews / $fRPP);
    // If Count of pages is less that page we want to show - show last page
    if (($page > $countPages) and ($page > 1))
        $page = $countPages;

    $items = array();
    $rows = $mysql->select("select * ".$sqlQPart ." LIMIT ".(($page - 1) * $fRPP) . "," . $fRPP);
    foreach($rows as $row) {
        //
        // Always validate data

        // Parse text comment
        $text = $row['text'];
        if ($config['blocks_for_reg']) {$text = $parse->userblocks($text);}
        if ($config['use_bbcodes']) {$text = $parse->bbcodes($text);}
        if ($config['use_htmlformatter']) {$text = $parse->htmlformatter($text);}
        $text = $parse->truncateHTML($text, 100);
        if ($config['use_smilies']) {$text = $parse->smilies($text);}

        if ($row['author_id'] and $cPlugin->isActive('uprofile')) {
            $authorLink = checkLinkAvailable('uprofile', 'show') ?
                generateLink('uprofile', 'show', array('name' => $row['author'], 'id' => $row['author_id'])) :
                generateLink('core', 'plugin', array('plugin' => 'uprofile', 'handler' => 'show'), array('id' => $row['author_id']));
        } else {
            $authorLink = false;
        }

        // Prepare data for template
        $items[] = [
            'id' => intval($row['id']),
            'postID' => intval($row['post']),
            'module' => ucfirst(secure_html($row['module'])),
            'text' => $text,
            'postdate' => Lang::retDate('d.m.Y H:i:s', intval($row['postdate'])),
            'postdateStamp' => intval($row['postdate']),
            'isApprove' => intval($row['approve']),
            'parent' => intval($row['parent_id']),
            'author' => secure_html($row['author']),
            'authorID' => intval($row['author_id']),
            'authorLink' => $authorLink,
            'mail' => secure_html($row['mail']),
            'ip' => filter_var($row['ip'], FILTER_VALIDATE_IP) ? $row['ip'] : 'NaN',
        ];

    }
    //dd($_POST);
    $tVars['items'] = $items;
    $tVars['rpp'] = $fRPP;

    if (count($items) > 0) {
        $pagesss = new Paginator;
        $tVars['pagesss'] = $pagesss->get(
            array(
                'current' => $page,
                'count' => $countPages,
                'url' => admin_url.
                    '/admin.php?mod=extra-config&plugin=comments&action=list'.
                    '&modules='.$module.
                    '&approve='.$approve.
                    '&author='.$author.
                    ($fRPP?'&rpp='.$fRPP:'').
                    '&page=%page%'
            ));
    }

    $tpath = locatePluginTemplates(array('comments.list'), 'comments', 1, '', 'admin');
    array_push($cfg, array(
            'type' => 'flat',
            'input' => $twig->loadTemplate($tpath['comments.list'] . 'comments.list.tpl')->render($tVars)
            ));
    generate_config_page($plugin, $cfg);
}

// Edit comments
function commentsEditAction($plugin, $action)
{
    global $mysql, $twig, $config, $userROW, $PHP_SELF;

    // comment ID not isset
    if (empty($_REQUEST['comid'])) {
        msg(array('type' => 'danger', 'message' => __('comments:comid_not_found')));
        commentsListAction($plugin, $action);
        return;
    }

    $comid = intval($_REQUEST['comid']);

    // Find comment in DB
    if (!is_array($comment = $mysql->record("select * from `".prefix."_comments` where id = ".db_squote($comid)))) {
        msg(array('type' => 'danger', 'message' => __('comments:msg.not_found')));
        commentsListAction($plugin, $action);
        return;
    }

    //
    // Always validate data from DB
    $post = intval($comment['post']);
    $postdate = intval($comment['postdate']);
    $author = secure_html($comment['author']);
    $mail = secure_html($comment['mail']);
    $authorID = intval($comment['author_id']);
    $ip = filter_var($comment['ip'], FILTER_VALIDATE_IP) ? $comment['ip'] : 'NaN';
    $text = $comment['text'];
    $approve = intval($comment['approve']);
    $module = secure_html($comment['module']);
    $moderate = (1 == pluginGetVariable('comments', 'moderate')) ? true : false;

    // preparation of data for establishing a link to where this comment is posted
    $moduleRow = $mysql->record("SELECT * FROM `".prefix."_".$module."` WHERE id=".db_squote($post));
    if ('news' == $module) {
        $link = News::generateLink($moduleRow, false, 0, true);
    } elseif('images' == $module) {
        $link = generatePluginLink('gallery', 'image', array('gallery' => $moduleRow['folder'],'id' => $moduleRow['id'],'name' => $moduleRow['name']), [], 0, 1);
    } else {
        $link = home;
    }

    if (isset($_POST['subaction']) and $_POST['subaction'] == 'doeditcomment') {
        if (!trim($_POST['author']) or !trim($_POST['mail'])) {
            msg(array('type' => 'danger', 'message' => __('comments:msge_namefield')));
        } else {
            $text = str_replace("{","&#123;",str_replace("\r\n", "<br />", htmlspecialchars(trim($_POST['content']), ENT_COMPAT, 'UTF-8')));
            $approve = !isset($_POST['approve']) ? 0 : 1;
            if(0 != $authorID) {
                // comment is registered user
                $mysql->query("UPDATE `".prefix."_comments` SET text=".db_squote($text).", approve=".db_squote($approve)." WHERE id=".db_squote($comid));
                // Update counter for user, If change status approve for comment
                if ($approve != intval($comment['approve'])) {
                    if ($moderate and $approve) {
                        $mysql->query("update `".prefix."_users` set com=com+1 where id=".db_squote($authorID));
                    } elseif ($moderate and !$approve) {
                        $mysql->query("update `".prefix."_users` set com=com-1 where id=".db_squote($authorID));
                    }
                }
            } else {
                // comment is not registered user
                $author = secure_html($_POST['author']);
                $mail = secure_html($_POST['mail']);
                $mysql->query("
                    UPDATE `".prefix."_comments` SET 
                        text=".db_squote($text).", 
                        approve=".db_squote($approve).", 
                        author=".db_squote($author).", 
                        mail=".db_squote($mail)." 
                    WHERE id=".db_squote($comid)
                );
            }

            // Update comment counter in news, If change status approve for comment
            if ($approve != intval($comment['approve'])) {
                if ($moderate and $approve) {
                    $mysql->query("update `".prefix."_".$module."` set com=com+1 where id=".db_squote($post));
                } elseif ($moderate and !$approve) {
                    $mysql->query("update `".prefix."_".$module."` set com=com-1 where id=".db_squote($post));
                }
            }

            // inform_author
            if (isset($_POST['send_notice']) and '1' == $_POST['send_notice'] and $mail) {
                sendEmailMessage($mail, __('comments:comanswer'), sprintf(__('comments:notice_edit'), $userROW['name'], $text, $link), 'html');
            }
            msg(array('message' => __('comments:msgo_saved')));
        }
    }

    $tVars = array(
        'php_self' => $PHP_SELF,
        'bbcodes' => $config['use_bbcodes'] ? BBCodes() : '',
        'smilies' => $config['use_smilies'] ? Smilies('comments', 10) : '',
        'ip' => $ip,
        'author' => $author,
        'mail' => $mail,
        'text' => str_replace("<br />", "\r\n", $text),
        'postid' => $post,
        'comid' => $comid,
        'comdate' => Lang::retDate((pluginGetVariable('comments', 'timestamp') ? pluginGetVariable('comments', 'timestamp') : 'j.m.Y - H:i'), $postdate),
        'comdateStamp' => $postdate,
        'approve' => $approve,
        'link' => $link,
    );

    // Fill configuration parameters
    $cfg = array(
        'action' => 'admin.php?mod=extra-config&plugin=comments&action=edit&comid='.$comid,
        'navigation' => array(
            array('href' => 'admin.php?mod=extra-config&plugin=comments','title' => __('group.config')),
            array('class' => 'active','href' => 'admin.php?mod=extra-config&plugin=comments&action=list','title' => __('comments:nav.list')),
            array('href' => 'admin.php?mod=extra-config&plugin=comments&action=widget_list','title' => __('comments:nav.widgeList')),
        ),
        'submit' => array(
            //array('type' => 'default'),
            //array('class' => 'btn btn-primary','href' => 'admin.php?mod=extra-config&plugin=comments&action=update','title' => __('comments:button_update')),
        )
    );

    $tpath = locatePluginTemplates(array('comments.edit'), 'comments', 1, '', 'admin');
    array_push($cfg, array(
            'type' => 'flat',
            'input' => $twig->loadTemplate($tpath['comments.edit'] . 'comments.edit.tpl')->render($tVars)
            ));
    generate_config_page($plugin, $cfg);
}

// delete comment
function commentsDeleteAction($plugin, $action)
{
    global $mysql, $config;

    // comment ID not isset
    if (empty($_REQUEST['comid']) and empty($_REQUEST['selected_comments']) and !count($_REQUEST['selected_comments'])) {
        msg(['type' => 'danger', 'message' => __('comments:comid_not_found')]);
        return;
    }

    $commentsArray = !empty($_REQUEST['comid']) ? ['0' => $_REQUEST['comid']] : $_REQUEST['selected_comments'];
    // $sql = "SELECT * FROM `".prefix."_comments` WHERE id IN (" . implode(',', array_map('intval', $commentsArray)) . ") group by author_id";
    $moderate = (1 == pluginGetVariable('comments', 'moderate')) ? true : false;

    foreach ($commentsArray as $comid) {

        $comid = intval($comid);

        // Find comment in DB
        if (!is_array($comment = $mysql->record("select * from `".prefix."_comments` where id = ".db_squote($comid)))) {
            msg(['type' => 'danger', 'message' => __('comments:msg.not_found')]);
            continue;
        }

        //
        // Always validate data from DB
        $approve = intval($comment['approve']);
        $authorID = intval($comment['author_id']);
        $module = secure_html($comment['module']);
        $post = intval($comment['post']);

        // If there is no moderation OR if there is a moderation AND comment is approved
        if (!$moderate or ($moderate and $approve)) {
            $mysql->query("update `".prefix."_".$module."` set com=com-1 where id=".db_squote($post));
            $mysql->query("update `".uprefix."_users` set com=com-1 where id=".db_squote($authorID));
        }

        $mysql->query("delete from ".prefix."_comments where id=".db_squote($comid));
    }

    msg(['message' => ((count($commentsArray) > 1) ? __('comments:msg.deleted_all') : __('comments:msg.deleted'))]);
}

// ПЕРЕПИСАТЬ
// Mass function to approve or forbidden comment
function commentsUpdateAction($plugin, $action, $subaction)
{
    global $mysql, $config;

    $moderate = (1 == pluginGetVariable('comments', 'moderate')) ? true : false;
    if (!$moderate) {
        msg(['type' => 'info', 'message' => __('comments:msg.moderate_not_used')]);
        return;
    }
    $commentsNotFound = [];
    $commentsUpdated = [];

    // comment ID not isset
    if (empty($_REQUEST['comid']) and empty($_REQUEST['selected_comments']) and !count($_REQUEST['selected_comments'])) {
        msg(['type' => 'danger', 'message' => __('comments:comid_not_found')]);
        return;
    }

    $commentsArray = !empty($_REQUEST['comid']) ? ['0' => $_REQUEST['comid']] : $_REQUEST['selected_comments'];

    foreach ($commentsArray as $comid) {

        $comid = intval($comid);

        // Find comment in DB
        if (!is_array($comment = $mysql->record("select * from `".prefix."_comments` where id = ".db_squote($comid)))) {
            msg(['type' => 'danger', 'message' => "# $comid - " . __('comments:msg.not_found')]);
            $commentsNotFound[] = "# $comid - " . __('comments:msg.not_found');
            continue;
        }

        //
        // Always validate data from DB
        $approve = intval($comment['approve']);
        $authorID = intval($comment['author_id']);
        $module = secure_html($comment['module']);
        $post = intval($comment['post']);

        if ('mass_approve' == $subaction) {
            // If there is comment is NOT approved
            if (0 == $approve) {
                $mysql->query("UPDATE `".prefix."_".$module."` SET com=com+1 where id=".db_squote($post));
                if ($authorID)
                    $mysql->query("UPDATE `".uprefix."_users` SET com=com+1 where id=".db_squote($authorID));

                $mysql->query("UPDATE ".prefix."_comments SET approve='1' where id=".db_squote($comid));
                $commentsUpdated[] = "# $comid - " . __('comments:msg.'.$subaction);
            } elseif (1 == $approve) {
                $commentsUpdated[] = "# $comid - " . __('comments:msg.'.$subaction.'_already');
            }
        }

        if ('mass_forbidden' == $subaction) {
            // If there is comment is NOT approved
            if (0 == $approve) {
                $commentsUpdated[] = "# $comid - " . __('comments:msg.'.$subaction.'_already');
            } elseif (1 == $approve) {
                $mysql->query("UPDATE `".prefix."_".$module."` SET com=com-1 where id=".db_squote($post));
                if ($authorID)
                    $mysql->query("UPDATE `".uprefix."_users` SET com=com-1 where id=".db_squote($authorID));

                $mysql->query("UPDATE ".prefix."_comments SET approve='0' where id=".db_squote($comid));
                $commentsUpdated[] = "# $comid - " . __('comments:msg.'.$subaction);
            }
        }
    }

    if (count($commentsUpdated)) {
        msg(['message' => implode('<br />', $commentsUpdated)]);
    } elseif (count($commentsNotFound)) {
        msg(['type' => 'danger', 'message' => implode('<br />', $commentsNotFound)]);
    }
}

// Mass function to approve or forbidden comment
function commentsUpdateCounters($plugin, $action)
{
    global $mysql;

    // Load CORE Plugin
    $cPlugin = CPlugin::instance();

    $moderate = (1 == pluginGetVariable('comments', 'moderate')) ? true : false;
    $approve = ($moderate) ? " AND c.approve='1'": '';

    // Обновляем счетчик комментариев в новостях
    $rows = $mysql->select("SELECT n.id, count(c.id) AS cid FROM `".prefix."_news` n LEFT JOIN " . prefix . "_comments c on c.post=n.id AND c.module='news'".$approve." GROUP BY n.id");
    foreach ($rows as $row) {
        $mysql->query("UPDATE `".prefix."_news` SET com=" . $row['cid'] . " WHERE id = " . $row['id']);
    }

    // Обновляем счетчик комментариев в плагине gallery
    if ($cPlugin->isActive('gallery')) {
        $rows = $mysql->select("SELECT i.id, count(c.id) AS cid FROM `".prefix."_images` i LEFT JOIN " . prefix . "_comments c on c.post=i.id AND c.module='images'".$approve." GROUP BY i.id");
        foreach ($rows as $row) {
            $mysql->query("UPDATE `".prefix."_images` SET com=" . $row['cid'] . " WHERE id = " . $row['id']);
        }
    }

    // ОбнУляем счетчик комментариев у юзеров
    $approve = ($moderate) ? " WHERE approve='1'": '';
    $mysql->query("UPDATE " . prefix . "_users SET com = 0");
    // Обновляем счетчик комментариев у юзеров
    foreach ($mysql->select("select author_id, count(*) as cnt from `".prefix."_comments` ".$approve."group by author_id") as $row) {
        $mysql->query("update `".uprefix."_users` set com=" . $row['cnt'] . " where id = " . $row['author_id']);
    }

}