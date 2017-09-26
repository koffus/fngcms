<?php

/*
 * sitemap for BixBite CMS (http://bixbite.site/)
 * Copyright (C) 2010 Alexey N. Zhukov (http://digitalplace.ru), kt2k (http://kt2k.ru/)
 * http://digitalplace.ru
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or (at
 * your option) any later version.
 *
 * This program is distributed in the hope that it will be useful, but
 * WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU
 * General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA 02111-1307, USA.
 *
 */
if (!defined('BBCMS')) die ('HAL');

register_plugin_page('sitemap','','generateSitemap', 0);

function generateSitemap() {
	global $template, $twig, $mysql, $config, $parse, $catz, $SYSTEM_FLAGS, $TemplateCache;

	Lang::loadPlugin('sitemap', 'site', '', ':');

	$page = 1;
	if(isset($_GET['page'])) $page = intval($_GET['page']);

	if (pluginGetVariable('sitemap', 'cache')){
		$cacheData = cacheRetrieveFile('sitemap_'.$page.'.txt', pluginGetVariable('sitemap', 'cache_expire'), 'sitemap');
		if ($cacheData != false){
			# we got data from cache. Return it and stop
			$template['vars']['mainblock'] .= $cacheData;
			return 0;
		}
	}

	# news per page. Thanks, capitan :)
	$news_per_page = intval(pluginGetVariable('sitemap', 'news_per_page')) ? intval(pluginGetVariable('sitemap', 'news_per_page')) : 200;
	# range of messages
	$limit = 'LIMIT '.($page - 1) * $news_per_page.', '.$news_per_page;
	# count of all news
	$countNews = $mysql->result('SELECT COUNT(*) FROM '.prefix.'_news');

	$news = $mysql->select('SELECT n.title, n.postdate, n.views,'.(pluginIsActive('comments') ? " n.com, " : "").' n.catid, n.id, n.alt_name, c.name, c.alt, c.parent, c.posorder, c.poslevel FROM '.prefix.'_news AS n LEFT JOIN '.prefix.'_category c on n.catid = c.id WHERE `approve` = 1 ORDER BY posorder, catid, pinned DESC, postdate DESC, editdate DESC '.$limit);

	foreach ($news as $row) {
		if ($cu_c <> $row['name']) {
			$link_rss = '';
			if (pluginIsActive('rss_export')){
				$link_rss = generateLink('rss_export', 'category', array('category' => $row['alt']));
				$link_rss = '<a href="'.$link_rss.'">'.__('sitemap:label_rss').'</a>';
			}
			$tEntry['cat_'.$row['catid']]['cat_id'] = $row['catid'];
			$tEntry['cat_'.$row['catid']]['cat_link'] = GetCategories($row['catid']);
			foreach ($catz as $cat_item){
				if($cat_item['id'] == $row['catid']) {
					$tEntry['cat_'.$row['catid']]['cat_info'] = $cat_item;
				}
			}
			$cu_c = $row['name'];
		}

		$tEntry['news_'.$row['id']]['news_id'] = $row['id'];
		$tEntry['news_'.$row['id']]['news_title'] = $row['title'];
		$tEntry['news_'.$row['id']]['news_date'] = $row['postdate'];
		$tEntry['news_'.$row['id']]['news_views'] = $row['views'];
		$tEntry['news_'.$row['id']]['news_comms'] = $row['com'];
		$tEntry['news_'.$row['id']]['news_cat'] = GetCategories($row['catid']);
		$tEntry['news_'.$row['id']]['news_link'] = News::generateLink(array('catid' => $row['catid'], 'alt_name' => $row['alt_name'], 'id' => $row['id'], 'postdate' => $row['postdate']), false, 0, true);
	}

	$countStatic = $mysql->result("select COUNT(*) from ".prefix."_static ");
	$countCatz = count($catz);

	$pages_count = ceil($countNews / $news_per_page);

	if($pages_count == $page) {
		foreach ($catz as $cat_item){
			if($cat_item['posts'] == "0") {
				$tEntry['cat_'.$cat_item['id']]['cat_id'] = $cat_item['id'];
				$tEntry['cat_'.$cat_item['id']]['cat_link'] = GetCategories($cat_item['id']);
				$tEntry['cat_'.$cat_item['id']]['cat_info'] = $cat_item;
			}
		}

		$static = $mysql->select("select * from ".prefix."_static order by title");

		foreach ($static as $row) {
			$tEntry['static_'.$row['id']]['static_id'] = $row['id'];
			$tEntry['static_'.$row['id']]['static_alt'] = $row['alt_name'];
			$tEntry['static_'.$row['id']]['static_title'] = $row['title'];
			$tEntry['static_'.$row['id']]['static_date'] = $row['postdate'];
			$link = checkLinkAvailable('static', '')?
			generateLink('static', '', array('altname' => $row['alt_name'], 'id' => $row['id']), array(), false, true):
			generateLink('core', 'plugin', array('plugin' => 'static'), array('altname' => $row['alt_name'], 'id' => $row['id']), false, true);
			$tEntry['static_'.$row['id']]['static_link'] = $link;
		}
	}

	$paginationParams = array('pluginName' => 'sitemap', 'params' => array(), 'xparams' => array(), 'paginator' => array('page', 0, false));

	unset($tVars);

	# generate pagination if count of pages > 1
	if ($pages_count > 1) {
		templateLoadVariables(true);
		$navigations = $TemplateCache['site']['#variables']['navigation'];
		$tVars['pagination'] = generatePagination($page, 1, $pages_count, 9, $paginationParams, $navigations);
		# set plugin title
		$SYSTEM_FLAGS['info']['title']['group'] = str_replace('{page}', $page, __('sitemap:title_multiple'));
		$SYSTEM_FLAGS['meta']['description'] = home_title . '. ' . str_replace('{page}', $page, __('sitemap:title_multiple'));
	} else {
		$tVars['pagination'] = '';
		# set plugin title
		$SYSTEM_FLAGS['info']['title']['group'] = __('sitemap:title_single');
		$SYSTEM_FLAGS['meta']['description'] = home_title . '. ' . __('sitemap:title_single');
	}
	// Удаляем все слова меньше 3-х символов
	$keywords = preg_replace('#\b[\d\w]{1,3}\b#iu', '', $SYSTEM_FLAGS['meta']['description']);
	// Удаляем знаки препинания
	$keywords = preg_replace('#[^\d\w ]+#iu', '', $keywords);
	// Удаляем лишние пробельные символы
	$keywords = preg_replace('#[\s]+#iu', ' ', $keywords);
	// Заменяем пробелы на запятые
	$keywords = preg_replace('#[\s]#iu', ',', $keywords);
	// Выводим для леньтяев
	$SYSTEM_FLAGS['meta']['keywords'] = mb_strtolower(trim($keywords, ','));

	$tVars['entries'] = isset($tEntry)?$tEntry:'';
		$tVars['counts'] = array ('countCatz' => $countCatz,
		'countNews' => $countNews,
		'countStatic' => $countStatic
		);
	$tVars['news_per_page'] = $news_per_page;
	$tVars['pages_count'] = $pages_count;
	$tVars['page'] = $page;

	if (pluginGetVariable('sitemap', 'cache'))
		cacheStoreFile('sitemap_'.$page.'.txt', $result, 'sitemap');


	$tpath = plugin_locateTemplates('sitemap', array('sitemap'));
	$template['vars']['mainblock'] .= $twig->render($tpath['sitemap'].'sitemap.tpl', $tVars);
}
