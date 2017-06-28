<?php

//
// Configuration file for plugin
//

// Protect against hack attempts
if (!defined('NGCMS')) die ('HAL');

// Load lang files
Lang::loadPlugin($plugin, 'config', '', '', ':');

//
// Prepare configuration parameters
$xfEnclosureValues = array( '' => '');

// IF plugin 'XFIELDS' is enabled - load it to prepare `enclosure` integration
if (getPluginStatusActive('xfields')) {
	include_once(root."/plugins/xfields/xfields.php");

	// Load XFields config
	if (is_array($xfc=xf_configLoad())) {
		foreach ($xfc['news'] as $fid => $fdata) {
			$xfEnclosureValues[$fid] = $fid.' ('.$fdata['title'].')';
		}
	}
}

// For example - find 1st category with news for demo URL
$demoCategory = '';
foreach ($catz as $scanCat) {
	if ($scanCat['posts'] > 0) {
		$demoCategory = $scanCat['alt'];
		break;
	}
}

// Fill configuration parameters
$cfg = array('description' => 'Плагин экспорта новостей в формате RSS<br>Полная лента новостей доступна по адресу: <b>'.generatePluginLink('rss_export', '', array(), array(), true, true).(($demoCategory != '')?'</b><br/>Лента новостей для категории <i>'.$catz[$demoCategory]['name'].'</i>: <b>'.generatePluginLink('rss_export', 'category', array('category' => $demoCategory), array(), true, true).'</b>':''));

$cfgX = array();
	array_push($cfgX, array(
		'name' => 'feed_title_format',
		'title' => 'Формат заголовка ленты новостей',
		'descr' => '<code>Сайт</code> - использовать заголовок сайта<br><code>Сайт+Категория</code> - использовать заголовок сайта+название категории (при выводе новостей из конкретной категории)<br><code>Ручной</code> - заголовок определяется Вами',
		'type' => 'select',
		'values' => array('site' => 'Сайт', 'site_title' => 'Сайт+Категория', 'handy' => 'Ручной'),
		'value' => pluginGetVariable('rss_export','feed_title_format'),
		));
	array_push($cfgX, array(
		'name' => 'feed_title_value',
		'title' => 'Ваш заголовок ленты новостей',
		'descr' => 'Заголовок используется в случае выбора формата <b>"ручной"</b> в качестве заголовка ленты','type' => 'input',  'value' => pluginGetVariable('rss_export','feed_title_value'),
		));
	array_push($cfgX, array(
		'name' => 'news_title',
		'title' => 'Формат заголовка новости',
		'descr' => '<code>Название</code> - в заголовке указывается только название новости<br><code>Категория :: Название</code> - В заголовке указывается как категория так и название новости','type' => 'select',
		'values' => array('0' => 'Название', '1' => 'Категория :: Название'), value => pluginGetVariable('rss_export','news_title'),
		));
	array_push($cfgX, array(
		'name' => 'news_count',
		'title' => 'Кол-во новостей для публикации в ленте','type' => 'input', value => pluginGetVariable('rss_export','news_count'),
		));
	array_push($cfgX, array(
		'name' => 'use_hide',
		'title' => 'Обрабатывать тег <b>[hide] ... [/hide]</b>',
		'descr' => '<code>Да</code> - текст отмеченный тегом <b>hide</b> не отображается<br><code>Нет</code> - текст отмеченный тегом <b>hide</b> отображается',
		'type' => 'select',
		'values' => array('0' => 'Нет', '1' => 'Да'), value => pluginGetVariable('rss_export','use_hide'),
		));
	array_push($cfgX, array(
		'name' => 'content_show',
		'title' => 'Вид отображения новости',
		'descr' => 'Вам необходимо указать какая именно информация будет отображаться внутри новости, экспортируемой через RSS','type' => 'select',
		'values' => array('0' => 'короткая+длинная', '1' => 'только короткая', '2' => 'только длинная'), value => pluginGetVariable('rss_export','content_show'),
		));
	array_push($cfgX, array(
		'name' => 'truncate',
		'title' => 'Обрезать выводимую информацию',
		'descr' => 'Кол-во символов до которых будет обрезаться выводимая в ленте информация.<br/>Значение по умолчанию: <code>0</code> - не обрезать','type' => 'input', 'value' => intval(pluginGetVariable('rss_export','truncate')),
		));
	array_push($cfgX, array(
		'name' => 'delay',
		'title' => 'Отсрочка вывода новостей в ленту',
		'descr' => 'Вы можете задать время (<code>в минутах</code>) на которое будет откладываться вывод новостей в RSS ленту','type' => 'input', value => pluginGetVariable('rss_export','delay'),
		));
array_push($cfg, array(
	'mode' => 'group',
	'title' => __('group.config'),
	'entries' => $cfgX,
	));

$cfgX = array();
array_push($cfgX, array(
		'name' => 'xfEnclosureEnabled',
		'title' => "Генерация поля 'Enclosure' используя данные плагина xfields",
		'descr' => "<code>Да</code> - включить генерацию<br /><code>Нет</code> - отключить генерацию</small>",
		'type' => 'select',
		'values' => array('1' => 'Да', '0' => 'Нет'),
		'value' => intval(pluginGetVariable($plugin,'xfEnclosureEnabled')),
		));
array_push($cfgX, array(
		'name' => 'xfEnclosure',
		'title' => "ID поля плагина <b>xfields</b>, которое будет использоваться для генерации поля <b>Enclosure</b>",
		'type' => 'select',
		'values' => $xfEnclosureValues,
		'value' => pluginGetVariable($plugin,'xfEnclosure'),
		));
array_push($cfg, array(
	'mode' => 'group',
	'title' => 'Генерация поля <b>enclosure</b>',
	'entries' => $cfgX));

$cfgX = array();
	array_push($cfgX, array(
		'name' => 'cache',
		'title' => __('cache'),
		'descr' => __('cache#desc'),
		'type' => 'select',
		'values' => array('1' => __('yesa'), '0' => __('noa')),
		'value' => intval(pluginGetVariable($plugin, 'cache')) ? intval(pluginGetVariable($plugin, 'cache')) : 1,
		));
	array_push($cfgX, array(
		'name' => 'cacheExpire',
		'title' => __('cacheExpire'),
		'descr' => __('cacheExpire#desc'),
		'type' => 'input',
		'value' => intval(pluginGetVariable($plugin, 'cacheExpire')) ? intval(pluginGetVariable($plugin, 'cacheExpire')) : 60,
		));
array_push($cfg, array(
	'mode' => 'group',
	'title' => __('group.cache'),
	'entries' => $cfgX,
	));

// RUN
if (isset($_REQUEST['action']) and $_REQUEST['action'] == 'commit') {
	// If submit requested, do config save
	commit_plugin_config_changes($plugin, $cfg);
}

generate_config_page($plugin, $cfg);
