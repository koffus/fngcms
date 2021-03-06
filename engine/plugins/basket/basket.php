<?php

// Protect against hack attempts
if (!defined('BBCMS')) die ('HAL');

// Load CORE Plugin
$cPlugin = CPlugin::instance();
// preload required libraries
$cPlugin->loadLibrary('xfields', 'common');
$cPlugin->loadLibrary('feedback', 'common');
$cPlugin->regHtmlVar('js', admin_url.'/plugins/basket/js/basket.js');

//
// Отображение общей информации/остатков в корзине
function plugin_basket_total() {
    global $mysql, $twig, $userROW, $template;

    // Определяем условия выборки
    $filter = array();
    if (is_array($userROW)) {
        $filter []= '(user_id = '.db_squote($userROW['id']).')';
    }

    if (isset($_COOKIE['ngTrackID']) and ($_COOKIE['ngTrackID'] != '')) {
        $filter []= '(cookie = '.db_squote($_COOKIE['ngTrackID']).')';
    }

    // Считаем итоги
    $tCount = 0;
    $tPrice = 0;
    $res = $mysql->record("select count(*) as count, sum(price*count) as price from ".prefix."_basket where ".join(" or ", $filter), 1); // . " GROUP BY linked_id"
    if (count($filter) and is_array($res)) {
        $tCount = $res['count'];
        $tPrice = $res['price'];
    }

    // Готовим переменные
    $tVars = array(
        'count' => $tCount,
        'price' => $tPrice,
    );

    // Выводим шаблон
    $tpath = plugin_locateTemplates('basket', array('total'));
    $template['vars']['plugin_basket'] = $twig->render($tpath['total'].'/total.tpl', $tVars);
}

//
// Показать содержимое корзины
function plugin_basket_list(){
    global $mysql, $twig, $userROW, $template;

    // Определяем условия выборки
    $filter = array();
    if (is_array($userROW)) {
        $filter []= '(user_id = '.db_squote($userROW['id']).')';
    }

    if (isset($_COOKIE['ngTrackID']) and ($_COOKIE['ngTrackID'] != '')) {
        $filter []= '(cookie = '.db_squote($_COOKIE['ngTrackID']).')';
    }

    // Выполняем выборку
    $recs = array();
    $total = 0;
    if (count($filter)) {
        foreach ($mysql->select("select * from ".prefix."_basket where ".join(" or ", $filter), 1) as $rec) {
            $total += round($rec['price'] * $rec['count'], 2);
            $rec['sum'] = sprintf('%9.2f', round($rec['price'] * $rec['count'], 2));
            $rec['xfields'] = unserialize($rec['linked_fld']);
            unset($rec['linked_fld']);
            $recs []= $rec;
        }
    }

    $tVars = array(
        'recs' => count($recs),
        'entries' => $recs,
        'total' => sprintf('%9.2f', $total),
        'form_url' => generatePluginLink('feedback', null, array(), array('id' => intval(pluginGetVariable('basket', 'feedback_form')))),
    );

    // Выводим шаблон
    $tpath = plugin_locateTemplates('basket', array('list'));
    $template['vars']['mainblock'] .= $twig->render($tpath['list'].'/list.tpl', $tVars);
}

// Update basket content/counters
function plugin_basket_update() {
    global $mysql, $twig, $userROW, $template, $SUPRESS_TEMPLATE_SHOW;

    // Определяем условия выборки
    $filter = array();
    if (isset($userROW) and is_array($userROW)) {
        $filter []= '(user_id = '.db_squote($userROW['id']).')';
    }
    if (!empty($_COOKIE['ngTrackID'])) {
        $filter []= '(cookie = '.db_squote($_COOKIE['ngTrackID']).')';
    }
    // Scan POST params
    if (count($filter)) {
        foreach ($_POST as $k => $v) {
            if (preg_match('#^count_(\d+)$#', $k, $m)) {
                if (intval($v) < 1) {
                    $mysql->query("delete from ".prefix."_basket where (id = ".db_squote($m[1]).") and (".join(" or ", $filter).")");
                } else {
                    $mysql->query("update ".prefix."_basket set count = ".db_squote(intval($v))."where (id = ".db_squote($m[1]).") and (".join(" or ", $filter).")");
                }
            }
        }
    }
    // Redirect to basket page
    $SUPRESS_TEMPLATE_SHOW = true;
    coreRedirectAndTerminate(generatePluginLink('basket', null, array(), array(), false, true));
}

if (class_exists('XFieldsFilter') and class_exists('FeedbackFilter')) {

    // XFields filter
    class BasketXFieldsFilter extends XFieldsFilter {
        function showTableEntry($newsID, $SQLnews, $rowData, &$rowVars) {
            global $DSlist;

            // Определяем - работаем ли мы внутри строк таблиц
            if (!pluginGetVariable('basket', 'ntable_flag'))
                return;
            // Определяем режим работы - по всем строкам или по условию "поле из xfields не равно нулю"
            if (pluginGetVariable('basket', 'ntable_activated')) {
                if (!$rowData['xfields_'.pluginGetVariable('basket', 'ntable_xfield')])
                    return;
            }
            $rowVars['flags']['basket_allow'] = true;
            $rowVars['basket_link'] = generatePluginLink('basket', 'add', array('ds' => $DSlist['#xfields:tdata'], 'id' => $rowData['id']), array(), false, true);
            // Строку можно добавлять в корзину
            //print "rowData <pre>(".var_export($rowVars, true).")</pre><br/>\n";
        }
    }

    // Feedback filter
    class BasketFeedbackFilter extends FeedbackFilter {
        // Action executed when form is showed
        function onShow($formID, $formStruct, $formData, &$tvars) {
            global $userROW, $mysql, $twig;

            // Проверяем ID формы - данные корзины отображаются только в конкретной форме
            if (pluginGetVariable('basket', 'feedback_form') != $formID)
                return;
            // Определяем условия выборки
            $filter = array();
            if (isset($userROW) and is_array($userROW)) {
                $filter []= '(user_id = '.db_squote($userROW['id']).')';
            }
            if (!empty($_COOKIE['ngTrackID'])) {
                $filter []= '(cookie = '.db_squote($_COOKIE['ngTrackID']).')';
            }
            // Выполняем выборку
            $recs = array();
            $total = 0;
            if (count($filter)) {
                foreach ($mysql->select("select * from ".prefix."_basket where ".join(" or ", $filter)) as $rec) {
                    $total += round($rec['price'] * $rec['count'], 2);
                    $rec['sum'] = sprintf('%9.2f', round($rec['price'] * $rec['count'], 2));
                    $rec['xfields'] = unserialize($rec['linked_fld']);
                    unset($rec['linked_fld']);
                    $recs []= $rec;
                }
            }
            $tVars = array(
                'recs' => count($recs),
                'entries' => $recs,
                'total' => sprintf('%9.2f', $total),
            );
            // Выводим шаблон
            $tvars['plugin_basket'] .= $twig->render('plugins/basket/lfeedback.tpl', $tVars);
        }

        function onProcess($formID, $formStruct, $formData, $flagHTML, &$tvars) {
            global $userROW, $mysql, $twig;

            // Проверяем ID формы - данные корзины отображаются только в конкретной форме
            if (pluginGetVariable('basket', 'feedback_form') != $formID)
                return 1;
            // Определяем условия выборки
            $filter = array();
            if (isset($userROW) and is_array($userROW)) {
                $filter []= '(user_id = '.db_squote($userROW['id']).')';
            }
            if (!empty($_COOKIE['ngTrackID'])) {
                $filter []= '(cookie = '.db_squote($_COOKIE['ngTrackID']).')';
            }
            // Выполняем выборку
            $recs = array();
            $total = 0;
            if (count($filter)) {
                foreach ($mysql->select("select * from ".prefix."_basket where ".join(" or ", $filter)) as $rec) {
                    $total += round($rec['price'] * $rec['count'], 2);
                    $rec['sum'] = sprintf('%9.2f', round($rec['price'] * $rec['count'], 2));
                    $rec['xfields'] = unserialize($rec['linked_fld']);
                    unset($rec['linked_fld']);
                    $recs []= $rec;
                }
            }
            $bVars = array(
                'recs' => count($recs),
                'entries' => $recs,
                'total' => sprintf('%9.2f', $total),
            );
            // Выводим шаблон
            $tvars['plugin_basket'] = $twig->render('plugins/basket/lfeedback.tpl', $bVars);
        }
        // Action executed when post request is completed
        function onProcessNotify($formID){
            global $mysql, $userROW;

            // Определяем условия выборки
            $filter = array();
            if (isset($userROW) and is_array($userROW)) {
                $filter []= '(user_id = '.db_squote($userROW['id']).')';
            }
            if (!empty($_COOKIE['ngTrackID'])) {
                $filter []= '(cookie = '.db_squote($_COOKIE['ngTrackID']).')';
            }
            // Выполняем выборку
            if (count($filter)) {
                $mysql->query("delete from ".prefix."_basket where ".join(" or ", $filter));
            }
        }
    }

    register_plugin_page('basket','','plugin_basket_list', 0);
    register_plugin_page('basket','update','plugin_basket_update', 0);
    pluginRegisterFilter('xfields','basket', new BasketXFieldsFilter);
    pluginRegisterFilter('feedback','basket', new BasketFeedbackFilter);
} else {
    print('Basket error: XFields and Feedback plugins must be activated');
}

// Perform replacements while showing news
class BasketNewsFilter extends NewsFilter {
    // Show news call :: processor (call after all processing is finished and before show)
    function showNews($newsID, $SQLnews, &$tvars, $mode = array()) {
        global $DSlist;

        // Определяем - работаем ли мы внутри строк таблиц
        if (!pluginGetVariable('basket', 'news_flag')) {
            $tvars['regx']['#\[basket\](.*?)\[\/basket\]#is'] = '';
            return;
        }
        // Работаем. Определяем режим работы - по всем строкам или по условию "поле из xfields не равно нулю"
        if (pluginGetVariable('basket', 'news_activated')) {
            if (!$SQLnews['xfields_'.pluginGetVariable('basket', 'news_xfield')]) {

                $tvars['regx']['#\[basket\](.*?)\[\/basket\]#is'] = '';
                return;
            }
        }
        $tvars['regx']['#\[basket\](.*?)\[\/basket\]#is'] = '$1';
        $tvars['vars']['basket_link'] = generatePluginLink('basket', 'add', array('ds' => $DSlist['news'], 'id' => $SQLnews['id']), array(), false, true);
    }
}

pluginRegisterFilter('news','basket', new BasketNewsFilter);

// Вызов обработчика
registerActionHandler('index', 'plugin_basket_total');
