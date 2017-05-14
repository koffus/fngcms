<?php

//
// Copyright (C) 2006-2013 Next Generation CMS (http://ngcms.ru/)
// Name: search.php
// Description: News search
// Author: Vitaly Ponomarev
//

// Protect against hack attempts
if (!defined('NGCMS')) die ('HAL');

Lang::load('search', 'site');

//
// Make search
//
include_once root.'includes/news.php';

function search_news(){
	global $catz, $catmap, $mysql, $config, $userROW, $tpl, $parse, $template, $PFILTERS, $SYSTEM_FLAGS, $TemplateCache;

	$_REQUEST['author'] = secure_html($_REQUEST['author']);
	$_REQUEST['catid'] = intval(secure_html($_REQUEST['catid']));
	$_REQUEST['postdate'] = secure_html($_REQUEST['postdate']);
	$_REQUEST['search'] = secure_html($_REQUEST['search']);
	$_REQUEST['orderby'] = secure_html($_REQUEST['orderby']);
	$_REQUEST['page'] = intval(secure_html($_REQUEST['page']));
	$submit = isset($_REQUEST['submit']) ? true : false;

	$SYSTEM_FLAGS['info']['title']['group'] = __('search.title');

	// PREPARE FILTER RULES FOR NEWS SHOWER
	$filter = array();
	
	// AUTHOR
	if ( $_REQUEST['author'] ) {
		array_push($filter, array('DATA', 'author', '=', $_REQUEST['author']));
	}

	// CATEGORY
	if ( $_REQUEST['catid'] ) {
		array_push($filter, array('DATA', 'category', '=', $_REQUEST['catid']));
	}

	// POST DATE
	if ( $_REQUEST['postdate'] and preg_match('#^(\d{4})(\d{2})$#', $_REQUEST['postdate'], $dv) ) {
		if ( ($dv[1] >= 1970)&&($dv[1] <= 2100)&&($dv[2] >=1)&&($dv[2] <= 12) ) {
			array_push($filter, array('OR',
				array('DATA', 'postdate', 'BETWEEN', array(mktime(0,0,0,$dv[2],1,$dv[1]), mktime(23,59,59,$dv[2],date("t",mktime(0,0,0,$dv[2],1,$dv[1])),$dv[1]))),
			));
		}
	}

	// TEXT
	$search = array();
	if ( $_REQUEST['search'] ) {
		$search_words = trim(str_replace(array('<', '>', '%', '$', '#'), '', mb_substr($_REQUEST['search'], 0, 64, 'UTF-8')));
		$search_words = preg_split('/[ \,\.]+/', $search_words, -1, PREG_SPLIT_NO_EMPTY);

		foreach ($search_words as $s) {
			if ( mb_strlen($s, 'UTF-8') > 2 ) {
				array_push($search,
					array('OR',
							array('DATA', 'title', 'like', '%'.$mysql->db_quote($s).'%'),
							array('DATA', 'content', 'like', '%'.$mysql->db_quote($s).'%'),
					));
				$search_array[] = $s;
			} else {
				unset($s);
			}
		}

		if ( count($search) > 1 ) {
			array_unshift($search, 'AND');
		}
		if ( count($search) == 1 ) {
			$search = $search[0];
		}
		if ( count($search) > 0 ) {
			array_push($filter, $search);
		}
	}

	if ( count($filter) > 1 ) {
		array_unshift($filter, 'AND');
	}
	if ( count($filter) == 1 ) {
		$filter = $filter[0];
	}

	//print "FILTER: <pre>".var_export($filter, true)."</pre>\n";
	loadActionHandlers('news');
	loadActionHandlers('news:search');

	// Configure pagination
	$paginationParams = array('pluginName' => 'search', 'xparams' => array('search' => $_REQUEST['search'], 'author' => $_REQUEST['author'], 'catid' => $_REQUEST['catid'], 'postdate' => $_REQUEST['postdate'], 'newsOrder' =>$_REQUEST['orderby']), 'paginator' => array('page', 1, false));

	// Configure News order
	$orderlist = '<option value="">'.__('search.order_default').'</option>';
	foreach (array('id desc', 'id asc', 'postdate desc', 'postdate asc', 'title desc', 'title asc') as $v) {
		$vx = str_replace(' ','_',$v);
		$orderlist.='<option value="'.$v.'"'.((secure_html($_REQUEST['orderby'])==$v)?' selected="selected"':'').'>'.__('search.order_'.$vx).'</option>';
	}

	// Configure display params
	$callingParams = array('style' => 'short','searchFlag' => true, 'extendedReturn' => true, 'customCategoryTemplate' => false, 'newsOrder' =>$_REQUEST['orderby']);
	if ( $_REQUEST['page'] ) {
		$callingParams['page'] = $_REQUEST['page'];
	}

	// Preload template configuration variables
	templateLoadVariables();
	// Check if template requires extracting embedded images
	$tplVars = $TemplateCache['site']['#variables'];
	if ( isset($tplVars['configuration']) and is_array($tplVars['configuration']) and isset($tplVars['configuration']['extractEmbeddedItems']) and $tplVars['configuration']['extractEmbeddedItems'] ) {
		$callingParams['extractEmbeddedItems'] = true;
	}

	// Call SEARCH only if search words are entered
	if ( count($search_array) ) {
		$found = news_showlist($filter, $paginationParams, $callingParams);
	} else {
		$found = array('count' => 0, 'data' => false);
	}

	// Now let's show SEARCH basic template
	$tpl -> template('search.table', tpl_dir.$config['theme']);
	$tvars = array();
	$tvars['vars']['author'] = secure_html($_REQUEST['author']);
	$tvars['vars']['search'] = secure_html($_REQUEST['search']);
	$tvars['vars']['orderlist'] = $orderlist;

	$tvars['vars']['count'] = $found['count'];
	$tvars['vars']['form_url'] = generateLink('search', '', array());

	$tvars['regx']['#\[found\](.+?)\[/found\]#is'] = (isset($_REQUEST['search']) and count($search_array) and $found['count'])?'$1':'';
	$tvars['regx']['#\[notfound\](.+?)\[/notfound\]#is'] = (isset($_REQUEST['search']) and count($search_array) and !$found['count'])?'$1':'';
	$tvars['regx']['#\[error\](.+?)\[/error\]#is'] = ($submit and isset($_REQUEST['search']) and !count($search_array))?'$1':'';

	// Make category list
	$tvars['vars']['catlist'] = makeCategoryList( array ('name' => 'catid', 'selected' => $_REQUEST['catid'], 'doempty' => 1));

	// Results of search
	$tvars['vars']['entries'] = $found['data'];
	// Make month list
	$mnth_list = explode(',', __('months'));
	foreach ( $mysql->select("SELECT month(from_unixtime(postdate)) as month, year(from_unixtime(postdate)) as year, COUNT(id) AS cnt FROM " . prefix . "_news WHERE approve = '1' GROUP BY year, month ORDER BY year DESC, month DESC") as $row ) {

		$pd_value = sprintf("%04u%02u",$row['year'],$row['month']);
		$pd_text = $mnth_list[$row['month']-1].' '.$row['year'];

		$tvars['vars']['datelist'] .= "<option value=\"".$pd_value."\"".(($pd_value == $_REQUEST['postdate'])?' selected':'').">".$pd_text."</option>";
	}

	$tpl -> vars('search.table', $tvars);
	$template['vars']['mainblock'] .= $tpl -> show('search.table');
}