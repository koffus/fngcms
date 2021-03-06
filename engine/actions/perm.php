<?php

//
// Copyright (C) 2006-2017 BixBite CMS (http://bixbite.site/)
// Name: perm.php
// Description: Permission manager
// Author: Vitaly Ponomarev
//

// Protect against hack attempts
if (!defined('BBCMS')) die ('HAL');

Lang::load('perm', 'admin');

$pManager = new PermissionRuleManager();
$pManager->load();

// Preconfigure list of groups from global group list
$grp = array();
{
    foreach ($UGROUP as $id => $v) {
        $grp[$id] = array('id' => $id, 'title' => $v['name']);
    }
}

// Show list of current permissions
function showList($grp) {
    global $PERM, $pManager, $twig, $userROW, $catz;

    // ACCESS ONLY FOR ADMIN
    if ($userROW['status'] > 1) {
        msg(array('type' => 'danger', 'message' => __('perm.denied')));
        return;
    }

    $data = array();
    $dvalue = array();
    $nl1 = 0;
    foreach ($pManager->getList() as $kb => $vb) {
        $nl1++;
        $dBlock = array(
            'id' => $kb,
            'title' => (isset($vb['title']) and $vb['title'])?$vb['title']:'',
            'description' => (isset($vb['description']) and $vb['description'])?$vb['description']:'',
            'items' => array(),
        );

        if (is_array($vb['items'])) {
            $nl2 = 0;
            foreach ($vb['items'] as $ka => $va) {
                $nl2++;
                $dArea = array(
                    'id' => $ka,
                    'title' => (isset($va['title']) and $va['title'])?$va['title']:'',
                    'description' => (isset($va['description']) and $va['description'])?$va['description']:'',
                    'items' => array(),
                );

                if (is_array($va['items'])) {
                    $nl3 = 0;
                    foreach ($va['items'] as $ke => $ve) {
                        $nl3++;

                        // Check for type = categories
                        $isCategories = false;
                        if (isset($ve['type']) and (preg_match('#^listCategoriesSelector#', $ve['type'], $match))) {
                            // [[ CATEGORIES ]]
                            $isCategories = true;

                            $dEntry	= array(
                                'id'				=> $ke,
                                'title'				=> (isset($ve['title']) and $ve['title'])?$ve['title']:'',
                                'description' => (isset($ve['description']) and $ve['description'])?$ve['description']:'',
                                'type'				=> 'listCategoriesSelector',
                                'name'				=> str_replace('.', ':', $kb.'|'.$ka.'|'.$ke),
                                'generatedOptions' => makeCategoryList(array('skipDisabled' => true, 'noHeader' => true, 'doall' => true, 'allmarker' => '*', 'returnOptArray' => true)),
                                'uniqId' => 'id'.$nl1.'_'.$nl2.'_'.$nl3,
                            );
                            //$dArea['items'] []= $dEntry;
                            //continue;
                        } else {
                            // [[ NORMAL SELECT ]]
                            $dEntry = array(
                                'id' => $ke,
                                'title' => (isset($ve['title']) and $ve['title'])?$ve['title']:'',
                                'description' => (isset($ve['description']) and $ve['description'])?$ve['description']:'',
                                'perm' => array(),
                                'name' => $kb.'|'.$ka.'|'.$ke,
                                'type' => '',
                            );
                        }

                        // Avoid PHP bug/feature - it replaces "." into "_". Let's use ':' instead
                        $dEntry['name'] = str_replace('.', ':', $dEntry['name']);

                        if(is_array($grp))
                            foreach ($grp as $kg) {
                                if(isset($PERM[$kg['id']][$kb][$ka][$ke]) and $PERM[$kg['id']][$kb][$ka][$ke])
                                    $x = $PERM[$kg['id']][$kb][$ka][$ke];
                                else
                                    $x = '';

                                if ($isCategories) {
                                    $catArray = array();
                                    foreach (explode(',', $x) as $cx) {
                                        $catArray[$cx] = true;
                                    }
                                    $dvalue[$dEntry['name'].'|'.$kg['id']] = $catArray;
                                    $dEntry['perm'][$kg['id']] = $catArray; //$PERM[$kg['id']][$kb][$ka][$ke];
                                } else {
                                    $dEntry['perm'][$kg['id']] = $x; //$PERM[$kg['id']][$kb][$ka][$ke];
                                    $dvalue[$dEntry['name'].'|'.$kg['id']] = (!isset($PERM[$kg['id']][$kb][$ka][$ke]) or ($PERM[$kg['id']][$kb][$ka][$ke] === NULL))?-1:($x?1:0);
                                }
                            }

                        $dArea['items'] []= $dEntry;
                    }
                }
                $dBlock['items'] []= $dArea;
            }

        }
        $data []= $dBlock;
    }

//print "<pre><select size='10' multiple='multiple'>".makeCategoryList(array('skipDisabled' => true, 'noHeader' => true))."</select></pre>";
    // Print template
    $tVars = [
        'CONFIG' => $data,
        'PERM' => $PERM,
        'GRP' => $grp,
        'DEFAULT_JSON' => json_encode($dvalue),
        'DEFAULT' => $dvalue,
        'token' => genUToken('admin.perm'),
    ];

    print $twig->render(tpl_actions . 'perm/list.tpl', $tVars);
}

function displayPermValue($value, $type) {

    if ($type == 'listCategoriesSelector') {
        return $value;
    }

    if ($value == -1) return '--';
    if ($value == 0) return __('noa');
    if ($value == 1) return __('yesa');

}

function updateConfig() {
    global $userROW, $PERM, $confPerm, $confPermUser, $pManager, $twig, $grp;
    //print "Incoming POST: <pre>".var_export($_POST, true)."</pre>";
    // ACCESS ONLY FOR ADMIN
    if ( ($userROW['status'] > 1) ) {
        msg(array('type' => 'danger', 'message' => __('perm.denied')));
        return;
    }

    // Check for security token
    if ( (!isset($_REQUEST['token'])) or ($_REQUEST['token'] != genUToken('admin.perm')) ) {
        msg(array('type' => 'danger', 'title' => __('error.security.token'), 'message' => __('error.security.token#desc')));
        return;
    }

    $pList = $pManager->getList();
    $updateList = array();

    //print "<pre>".var_export($_POST, true)."</pre>";
    //print "Scan update..<br/>";
    //print "EX:<pre>".var_export($PERM[2]['#admin']['news'], true)."</pre>";
    foreach ($_POST as $k => $v) {
        // Avoid PHP bug/feature - it replaces '.' into '_'. Let's use ':' instead
        $k = str_replace(':', '.', $k);

        if (!preg_match("#^(.+?)\|(.*?)\|(.+?)\|(\d+)$#", $k, $m))
            continue;

        //print "Rec [$k]<pre>".var_export($m, true)."</pre><br/>";
        if (isset($pList[$m[1]]['items'][$m[2]]['items'][$m[3]])) {
            $itemType = $pList[$m[1]]['items'][$m[2]]['items'][$m[3]]['type'];
            $itemSubType = '';
            if (preg_match('!^(.+?)#(.+?)$!', $itemType, $null)) {
                $itemType = $null[1];
                $itemSubType = $null[2];
            }
            $itemIsCategories = ($itemType == 'listCategoriesSelector')?true:false;
            //print "[$itemType,$itemSubType,$itemIsCategories]";
            // TYPE: listCategoriesSelector - own processing
            if ($itemIsCategories) {
                if (is_array($v)) {
                    if (in_array('*', $v)) {
                        $v = '*';
                    } else {
                        $v = join(",",$v);
                    }
                }
            }

            $markValue = 99;
            if (!isset($PERM[$m[4]][$m[1]][$m[2]][$m[3]]) or ($PERM[$m[4]][$m[1]][$m[2]][$m[3]] === NULL)) {
                $markValue = -1;
            } else {
                if ($itemIsCategories) {
                    $markValue = ($PERM[$m[4]][$m[1]][$m[2]][$m[3]]);
                } else {
                    $markValue = ($PERM[$m[4]][$m[1]][$m[2]][$m[3]])?1:0;
                }
            }
            if ($markValue != $v) {
                // Save information about updates
                $updateList [] = array(
                    'id' => $m[1] .' &#8594; '.$m[2].' &#8594; '.$m[3],
                    'group' => $m[4],
                    'title' => $pList[$m[1]]['items'][$m[2]]['items'][$m[3]]['title'],
                    'type' => $itemType,
                    'old' => $markValue,
                    'new' => $v,
                    'displayNew' => displayPermValue($v, $itemType),
                    'displayOld' => displayPermValue($markValue, $itemType),
                );

                //print "> $k: ".$markValue.' => '.$v."<br/>";

                // Found changed record
                // - check if new value is equal to default value
                if (
                    (($v == -1) and !isset($confPerm[$m[4]][$m[1]][$m[2]][$m[3]])) ||
                    (($v == 1) and isset($confPerm[$m[4]][$m[1]][$m[2]][$m[3]]) and $confPerm[$m[4]][$m[1]][$m[2]][$m[3]]) ||
                    (($v == 0) and isset($confPerm[$m[4]][$m[1]][$m[2]][$m[3]]) and !$confPerm[$m[4]][$m[1]][$m[2]][$m[3]])
                ) {
                    //print "DELETE OVERRIDEN $k<br/>\n";
                    unset($confPermUser[$m[4]][$m[1]][$m[2]][$m[3]]);
                    // -- delete overrided $confPermUser record
                } else {
                    // -- SAVE new value for $confPermUser record
                    if ($itemIsCategories) {
                        $confPermUser[$m[4]][$m[1]][$m[2]][$m[3]] = $v;
                    } else {
                        $confPermUser[$m[4]][$m[1]][$m[2]][$m[3]] = ($v == -1)?NULL:($v?true:false);
                    }
                    //print "SAVE NEW $k->$v<br/>\n";
                }

                //print "(".isset($PERM[$m[4]][$m[1]][$m[2]][$m[3]]).",".($PERM[$m[4]][$m[1]][$m[2]][$m[3]] === NULL).") "; print var_export($PERM[$m[4]][$m[1]][$m[2]][$m[3]]);
            }
        }
    }

    $execResult = saveUserPermissions();

    $tVars = [
        'updateList' => $updateList,
        'GRP' => $grp,
        'execResult' => $execResult,
    ];

    print $twig->render(tpl_actions . 'perm/result.tpl', $tVars);
}

//
//
if (($_SERVER['REQUEST_METHOD'] == "POST") and isset($_POST['save']) and ($_POST['save'] == 1)) {
    updateConfig();
} else {
    showList($grp);
//	showList(array('1' => array('id' => 1, 'title' => 'Администратор')));
}
