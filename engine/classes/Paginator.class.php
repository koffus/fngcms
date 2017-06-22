<?php

//
// Copyright (C) 2006-2017 Next Generation CMS (http://ngcms.ru/)
// Name: Paginator.class.php
// Description: Paginator class
// Author: Vitaly Ponomarev, Alexey Zinchenko
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
    public function get($param)
    {

        global $tpl, $TemplateCache;

        if ($param['count'] < 2) return '';

        templateLoadVariables(true, 1);
        $nav = $TemplateCache['admin']['#variables']['navigation'];

        $tpl->template('pages', tpl_actions);

        // Prev page link
        if ($param['current'] > 1) {
            $prev = $param['current'] - 1;
            $tvars['regx']["'\[prev-link\](.*?)\[/prev-link\]'si"] = str_replace('%page%', "$1", str_replace('%link%', str_replace('%page%', $prev, $param['url']), $nav['prevlink']));
        } else {
            $prev = 0;
            $tvars['regx']["'\[prev-link\](.*?)\[/prev-link\]'si"] = '';
            $no_prev = true;
        }

        // ===[ TO PUT INTO CONFIG ]===
        $pages = '';
        if (isset($param['maxNavigations']) && ($param['maxNavigations'] > 3) && ($param['maxNavigations'] < 500)) {
            $maxNavigations = intval($param['maxNavigations']);
        } else {
            $maxNavigations = 8;
        }

        $sectionSize = floor($maxNavigations / 3);
        if ($param['count'] > $maxNavigations) {
            // We have more than 10 pages. Let's generate 3 parts
            // Situation #1: 1,2,3,4,[5],6 ... 128
            if ($param['current'] < ($sectionSize * 2)) {
                $pages .= $this->generateAdminNavigations($param['current'], 1, $sectionSize * 2, $param['url'], $nav);
                $pages .= $nav['dots'];
                $pages .= $this->generateAdminNavigations($param['current'], $param['count'] - $sectionSize, $param['count'], $param['url'], $nav);
            } elseif ($param['current'] > ($param['count'] - $sectionSize * 2 + 1)) {
                $pages .= $this->generateAdminNavigations($param['current'], 1, $sectionSize, $param['url'], $nav);
                $pages .= $nav['dots'];
                $pages .= $this->generateAdminNavigations($param['current'], $param['count'] - $sectionSize * 2 + 1, $param['count'], $param['url'], $nav);
            } else {
                $pages .= $this->generateAdminNavigations($param['current'], 1, $sectionSize, $param['url'], $nav);
                $pages .= $nav['dots'];
                $pages .= $this->generateAdminNavigations($param['current'], $param['current'] - 1, $param['current'] + 1, $param['url'], $nav);
                $pages .= $nav['dots'];
                $pages .= $this->generateAdminNavigations($param['current'], $param['count'] - $sectionSize, $param['count'], $param['url'], $nav);
            }
        } else {
            // If we have less then 10 pages
            $pages .= $this->generateAdminNavigations($param['current'], 1, $param['count'], $param['url'], $nav);
        }

        $tvars['vars']['pages'] = $pages;
        if ($prev + 2 <= $param['count']) {
            $next = $prev + 2;
            $tvars['regx']["'\[next-link\](.*?)\[/next-link\]'si"] = str_replace('%page%', "$1", str_replace('%link%', str_replace('%page%', $next, $param['url']), $nav['nextlink']));
        } else {
            $tvars['regx']["'\[next-link\](.*?)\[/next-link\]'si"] = '';
            $no_next = true;
        }
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
                $row['page'] = $j;
                $result .= str_replace('%page%', $j, str_replace('%link%', str_replace('%page%', $j, $link), $navigations['link_page']));
            }
        }
        return $result;
    }
}
