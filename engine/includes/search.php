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
include_once root.'includes/news.php';

function search_news() {
	global $catz, $catmap, $mysql, $config, $userROW, $template, $twig, $twigLoader, $parse, $PFILTERS, $SYSTEM_FLAGS, $TemplateCache;

	$author		= isset($_REQUEST['author']) ? secure_html($_REQUEST['author']) : false;
	$catid		= isset($_REQUEST['catid']) ? intval(secure_html($_REQUEST['catid'])) : false;
	$postdate	= isset($_REQUEST['postdate']) ? secure_html($_REQUEST['postdate']) : false;
	$search		= isset($_REQUEST['search']) ? secure_html($_REQUEST['search']) : false;
	$orderby	= isset($_REQUEST['orderby']) ? secure_html($_REQUEST['orderby']) : false;
	$page		= isset($_REQUEST['page']) ? intval(secure_html($_REQUEST['page'])) : false;
	$submit		= isset($_REQUEST['submit']) ? true : false;

	$SYSTEM_FLAGS['info']['title']['group'] = __('search.title');

	// PREPARE FILTER RULES FOR NEWS SHOWER
	$filter = array();
	
	// AUTHOR
	if ( $author ) {
		array_push($filter, array('DATA', 'author', '=', $author));
	}

	// CATEGORY
	if ( $catid ) {
		array_push($filter, array('DATA', 'category', '=', $catid));
	}

	// POST DATE
	if ( $postdate and preg_match('#^(\d{4})(\d{2})$#', $postdate, $dv) ) {
		if ( ($dv[1] >= 1970)&&($dv[1] <= 2100)&&($dv[2] >=1)&&($dv[2] <= 12) ) {
			array_push($filter, array('OR',
				array('DATA', 'postdate', 'BETWEEN', array(mktime(0,0,0,$dv[2],1,$dv[1]), mktime(23,59,59,$dv[2],date("t",mktime(0,0,0,$dv[2],1,$dv[1])),$dv[1]))),
			));
		}
	}

	// TEXT
	$search_array = array();
	if ( $search ) {
		$search_words = trim(str_replace(array('<', '>', '%', '$', '#'), '', mb_substr($search, 0, 64, 'UTF-8')));
		$search_words = preg_split('/[ \,\.]+/', $search_words, -1, PREG_SPLIT_NO_EMPTY);

		foreach ($search_words as $s) {
			if ( mb_strlen($s, 'UTF-8') > 2 ) {
				array_push($search_array,
					array('OR',
							array('DATA', 'title', 'like', '%'.$mysql->db_quote($s).'%'),
							array('DATA', 'content', 'like', '%'.$mysql->db_quote($s).'%'),
					));
			} else {
				unset($s);
			}
		}

		if ( count($search_array) > 1 ) {
			array_unshift($search_array, 'AND');
		}
		if ( count($search_array) == 1 ) {
			$search_array = $search_array[0];
		}
		if ( count($search_array) > 0 ) {
			array_push($filter, $search_array);
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
	$paginationParams = array(
		'pluginName' => 'search',
		'xparams' => array(
			'search' => $search,
			'author' => $author,
			'catid' => $catid,
			'postdate' => $postdate,
			'newsOrder' => $orderby,
			),
		'paginator' => array('page', 1, false));

	// Configure display params
	$callingParams = array(
		'style' => 'short',
		'searchFlag' => true,
		'extendedReturn' => true,
		'extendedReturnData' => true,
		'entendedReturnPagination' => false,
		'customCategoryTemplate' => false,
		'newsOrder' => $orderby,
		);
	if ( $page ) {
		$callingParams['page'] = $page;
	}

	// Preload template configuration variables
	templateLoadVariables();
	// Check if template requires extracting embedded images
	$tplVars = $TemplateCache['site']['#variables'];
	if ( isset($tplVars['configuration']) and is_array($tplVars['configuration']) and isset($tplVars['configuration']['extractEmbeddedItems']) and $tplVars['configuration']['extractEmbeddedItems'] ) {
		$callingParams['extractEmbeddedItems'] = true;
	}

	// Call SEARCH only if search words are entered
	$tableVars = array();
	if ( count($search_array) ) {
		$tableVars = news_showlist($filter, $paginationParams, $callingParams);
	} else {
		$tableVars = array('count' => 0, 'data' => false);
	}

	// Now let's show SEARCH basic template
	$tableVars['form_url'] = generateLink('search', '', array());
	$tableVars['author'] = $author;
	$tableVars['search'] = $search;
	$tableVars['searchSettings'] = (isset($_COOKIE['searchSettings']) and $_COOKIE['searchSettings']) ? '  checked="checked"' : '';
	$tableVars['pagination'] = $tableVars['pages']['output'];
	$tableVars['flags'] = array(
		'found'			=> (count($search_array) and $tableVars['count']) ? 1 : 0,
		'notfound'	=> (count($search_array) and !$tableVars['count']) ? 1 : 0,
		'error'	=> ( ($submit and $search and !count($search_array)) or ($submit and empty($search)) ) ? 1 : 0,
		);

	// Make category list
	$tableVars['catlist'] = makeCategoryList( array ('name' => 'catid', 'selected' => $catid, 'doempty' => 1));

	// Make month list
	$mnth_list = explode(',', __('months'));
	foreach ( $mysql->select("SELECT month(from_unixtime(postdate)) as month, year(from_unixtime(postdate)) as year, COUNT(id) AS cnt FROM " . prefix . "_news WHERE approve = '1' GROUP BY year, month ORDER BY year DESC, month DESC") as $row ) {
		$pd_value = sprintf("%04u%02u",$row['year'],$row['month']);
		$pd_text = $mnth_list[$row['month']-1].' '.$row['year'];
		$tableVars['datelist'] .= '<option value="'.$pd_value.'"'.(($pd_value == $postdate)?' selected':'').'>'.$pd_text.'</option>';
	}

	// Make News order list
	$tableVars['orderlist'] = '<option value="">'.__('search.order_default').'</option>';
	foreach (array('id desc', 'id asc', 'postdate desc', 'postdate asc', 'title desc', 'title asc') as $v) {
		$vx = str_replace(' ','_',$v);
		$tableVars['orderlist'] .= '<option value="'.$v.'"'.($orderby == $v ? ' selected="selected"' : '').'>'.__('search.order_'.$vx).'</option>';
	}

	// Results of search
	$sTemplateName =  tpl_dir.$config['theme'] . '/search.table.tpl';
	$twigLoader->setDefaultContent($sTemplateName, '{% for entry in data %}{{ entry }}{% else %}' . __('search.notfound') .'{% endfor %} {{ pagination }}');
	$xt = $twig->loadTemplate($sTemplateName);
	$template['vars']['mainblock'] .= $xt->render($tableVars);
}