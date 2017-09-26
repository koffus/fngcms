<?php

//
// Copyright (C) 2017 FBixBite CMS (https://github.com/russsiq/fBixBite CMS/)
// Name: Paginator.class.php
// Description: Paginator class
// Author: RusiQ
//

class Paginator
{

    final function __construct()
    {

    }

    final function __destruct()
    {

    }

    // Generate page list for admin panel
    // * current - number of current page
    // * count - total count of pages
    // * url	 - URL of page, %page% will be replaced by page number
    // * maxNavigations - max number of navigation links
    public function get($params)
    {

        global $tpl, $TemplateCache, $config;

        if ($params['count'] < 2) return '';

        templateLoadVariables(true, 1);
        $nav = $TemplateCache['admin']['#variables']['navigation'];

        // Prev page link
        if ($params['current'] > 1) {
            $prev = $params['current'] - 1;
            $tvars['regx']["'\[prev-link\](.*?)\[/prev-link\]'si"] = str_replace('%page%', "$1", str_replace('%link%', str_replace('%page%', $prev, $params['url']), $nav['prevlink']));
        } else {
            $prev = 0;
            $tvars['regx']["'\[prev-link\](.*?)\[/prev-link\]'si"] = '';
            $no_prev = true;
        }

        // ===[ TO PUT INTO CONFIG ]===
        if (isset($params['maxNavigations']) and ($params['maxNavigations'] > 3) and ($params['maxNavigations'] < 500)) {
            $maxNavigations = intval($params['maxNavigations']);
        } else {
            $maxNavigations = !(empty($config['newsNavigationsAdminCount']) or $config['newsNavigationsAdminCount'] < 1) ? $config['newsNavigationsAdminCount'] : 8;
        }

        $pages = '';
        $sectionSize = floor($maxNavigations / 3);
        if ($params['count'] > $maxNavigations) {
            // We have more than 10 pages. Let's generate 3 parts
            // Situation #1: 1,2,3,4,[5],6 ... 128
            if ($params['current'] < ($sectionSize * 2)) {
                $pages .= $this->generateAdminNavigations($params['current'], 1, $sectionSize * 2, $params['url'], $nav);
                $pages .= $nav['dots'];
                $pages .= $this->generateAdminNavigations($params['current'], $params['count'] - $sectionSize, $params['count'], $params['url'], $nav);
            } elseif ($params['current'] > ($params['count'] - $sectionSize * 2)) {
                $pages .= $this->generateAdminNavigations($params['current'], 1, $sectionSize, $params['url'], $nav);
                $pages .= $nav['dots'];
                $pages .= $this->generateAdminNavigations($params['current'], $params['count'] - $sectionSize * 2 + 1, $params['count'], $params['url'], $nav);
            } else {
                $pages .= $this->generateAdminNavigations($params['current'], 1, $sectionSize, $params['url'], $nav);
                $pages .= $nav['dots'];
                $pages .= $this->generateAdminNavigations($params['current'], $params['current'] - 1, $params['current'] + 1, $params['url'], $nav);
                $pages .= $nav['dots'];
                $pages .= $this->generateAdminNavigations($params['current'], $params['count'] - $sectionSize, $params['count'], $params['url'], $nav);
            }
        } else {
            // If we have less then 10 pages
            $pages .= $this->generateAdminNavigations($params['current'], 1, $params['count'], $params['url'], $nav);
        }

        $tvars['vars']['pages'] = $pages;

        if ($prev + 2 <= $params['count']) {
            $next = $prev + 2;
            $tvars['regx']["'\[next-link\](.*?)\[/next-link\]'si"] = str_replace('%page%', "$1", str_replace('%link%', str_replace('%page%', $next, $params['url']), $nav['nextlink']));
        } else {
            $tvars['regx']["'\[next-link\](.*?)\[/next-link\]'si"] = '';
            $no_next = true;
        }

        $tpl->template('pages', tpl_actions);
        $tpl->vars('pages', $tvars);
        return $tpl->show('pages');

    }

    public function generateAdminNavigations($current, $start, $stop, $link, $navigations)
    {
        $result = '';
        //print "call generateAdminNavigations(current=".$current.", start=".$start.", stop=".$stop.")<br>\n";
        //print "Navigations: <pre>"; var_dump($navigations); print "</pre>";
        for ($j = $start; $j <= $stop; $j++) {
            if ($j == $current) {
                $result .= str_replace('%page%', $j, $navigations['current_page']);
            } else {
                $result .= str_replace('%page%', $j, str_replace('%link%', str_replace('%page%', $j, $link), $navigations['link_page']));
            }
        }
        return $result;
    }
}
