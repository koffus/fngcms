<?php

//
// Copyright (C) 2006-2012 Next Generation CMS (http://ngcms.ru/)
// Name: parse.class.php
// Description: Parsing and formatting routines
// Author: Vitaly Ponomarev, Alexey Zinchenko
//

class Parse
{
    function slashes($content)
    {
        return (get_magic_quotes_gpc()) ? $content : addslashes($content);
    }

    function userblocks($content)
    {
        global $config, $userROW;
        if (!$config['blocks_for_reg']) return $content;
        return preg_replace("#\[hide\]\s*(.*?)\s*\[/hide\]#is", is_array($userROW) ? "$1" : str_replace("{text}", __('not_logged'), __('not_logged_html')), $content);
    }

    function translit($content, $useTranslit = true)
    {
        global $config;

        $utf2enS = array('А' => 'a', 'Б' => 'b', 'В' => 'v', 'Г' => 'g', 'Ґ' => 'g', 'Д' => 'd', 'Е' => 'e', 'Ё' => 'jo', 'Є' => 'e', 'Ж' => 'zh', 'З' => 'z', 'И' => 'i', 'І' => 'i', 'Й' => 'J', 'Ї' => 'i', 'К' => 'k', 'Л' => 'l', 'М' => 'm', 'Н' => 'n', 'О' => 'o', 'П' => 'p', 'Р' => 'r', 'С' => 's', 'Т' => 't', 'У' => 'u', 'Ў' => 'u', 'Ф' => 'f', 'Х' => 'h', 'Ц' => 'c', 'Ч' => 'ch', 'Ш' => 'sh', 'Щ' => 'sz', 'Ъ' => '', 'Ы' => 'y', 'Ь' => '', 'Э' => 'e', 'Ю' => 'yu', 'Я' => 'ya');
        $utf2enB = array('а' => 'a', 'б' => 'b', 'в' => 'v', 'г' => 'g', 'ґ' => 'g', 'д' => 'd', 'е' => 'e', 'ё' => 'jo', 'є' => 'e', 'ж' => 'zh', 'з' => 'z', 'и' => 'i', 'і' => 'i', 'й' => 'j', 'ї' => 'i', 'к' => 'k', 'л' => 'l', 'м' => 'm', 'н' => 'n', 'о' => 'o', 'п' => 'p', 'р' => 'r', 'с' => 's', 'т' => 't', 'у' => 'u', 'ў' => 'u', 'ф' => 'f', 'х' => 'h', 'ц' => 'c', 'ч' => 'ch', 'ш' => 'sh', 'щ' => 'sz', 'ъ' => '', 'ы' => 'y', 'ь' => '', 'э' => 'e', 'ю' => 'yu', 'я' => 'ya');

        $content = (string) $content;
        $content = trim(strip_tags($content));
        $content = strtr($content, array('&quot;' => '', '&amp;' => '', 'µ' => 'u', '№' => 'num', '_-' => '-', ' - ' => '-', '.' => ''));
        
        if ($useTranslit) {
            $content = mb_strtolower($content, 'UTF-8');
            $content = strtr($content, $utf2enS);
            $content = strtr($content, $utf2enB);
        }

        //$content = str_replace(array("\n", "\r"), " ", $content);
        $content = preg_replace("/\s+/msu", "-", $content);
        $content = preg_replace("/[ ]+/u", "-", $content);
        $content = preg_replace("/-(-)+/u", "-", $content);
        $content = preg_replace("/[^A-Za-z0-9ёЁА-Яа-я\_\-\.]+/miu", '', $content);

        return trim($content);
    }

    // Scan URL and normalize it to convert to absolute path
    // Check for XSS
    function normalize_url($url)
    {
        if (((substr($url, 0, 1) == '"') and (substr($url, -1, 1) == '"')) ||
            ((substr($url, 0, 1) == "'") and (substr($url, -1, 1) == "'"))
        ) {
            $url = substr($url, 1, strlen($url) - 2);
        }

        // Check for XSS attack
        $urlXSS = str_replace(array(ord(0), ord(9), ord(10), ord(13), ' ', "'", '"', ";"), '', $url);
        if (preg_match('/^javascript:/is', $urlXSS)) {
            return false;
        }

        // Add leading "http://" if needed
        if (!preg_match("#^(http|ftp|https|news)\://#i", $url)) {
            $url = "http://" . $url;
        }
        return $url;
    }

    // Parse BB-tag params
    function parseBBCodeParams($paramLine)
    {

        // Start scanning
        // State:
        // 0 - waiting for name
        // 1 - scanning name
        // 2 - waiting for '='
        // 3 - waiting for value
        // 4 - scanning value
        // 5 - complete
        $state = 0;
        // 0 - no quotes activated
        // 1 - single quotes activated
        // 2 - double quotes activated
        $quotes = 0;

        $keyName = '';
        $keyValue = '';
        $errorFlag = 0;

        $keys = array();

        for ($sI = 0; $sI < strlen($paramLine); $sI++) {
            // act according current state
            $x = $paramLine{$sI};

            switch ($state) {
                case 0:
                    if ($x == "'") {
                        $quotes = 1;
                        $state = 1;
                        $keyName = '';
                    } else if ($x == "'") {
                        $quotes = 2;
                        $state = 1;
                        $keyName = '';
                    } else if ((($x >= 'A') and ($x <= 'Z')) or (($x >= 'a') and ($x <= 'z'))) {
                        $state = 1;
                        $keyName = $x;
                    }
                    break;
                case 1:
                    if ((($quotes == 1) and ($x == "'")) or (($quotes == 2) and ($x == '"'))) {
                        $quotes = 0;
                        $state = 2;
                    } else if ((($x >= 'A') and ($x <= 'Z')) or (($x >= 'a') and ($x <= 'z'))) {
                        $keyName .= $x;
                    } else if ($x == '=') {
                        $state = 3;
                    } else if (($x == ' ') or ($x == chr(9))) {
                        $state = 2;
                    } else {
                        $erorFlag = 1;
                    }
                    break;
                case 2:
                    if ($x == '=') {
                        $state = 3;
                    } else if (($x == ' ') or ($x == chr(9))) {
                        ;
                    } else {
                        $errorFlag = 1;
                    }
                    break;
                case 3:
                    if ($x == "'") {
                        $quotes = 1;
                        $state = 4;
                        $keyValue = '';
                    } else if ($x == '"') {
                        $quotes = 2;
                        $state = 4;
                        $keyValue = '';
                    } else if ((($x >= 'A') and ($x <= 'Z')) or (($x >= 'a') and ($x <= 'z'))) {
                        $state = 4;
                        $keyValue = $x;
                    }
                    break;
                case 4:
                    if ((($quotes == 1) and ($x == "'")) or (($quotes == 2) and ($x == '"'))) {
                        $quotes = 0;
                        $state = 5;
                    } else if (!$quotes and (($x == ' ') or ($x == chr(9)))) {
                        $state = 5;
                    } else {
                        $keyValue .= $x;
                    }
                    break;
            }

            // Action in case when scanning is complete
            if ($state == 5) {
                $keys [strtolower($keyName)] = $keyValue;
                $state = 0;
            }
        }

        // If we finished and we're in stete "scanning value" - register this field
        if ($state == 4) {
            $keys [strtolower($keyName)] = $keyValue;
            $state = 0;
        }

        // If we have any other state - report an error
        if ($state) {
            $errorFlag = 1; // print "EF ($state)[".$paramLine."].";
        }

        if ($errorFlag) {
            return -1;
        }
        return $keys;
    }

    function bbcodes($content)
    {
        global $config, $userROW, $SYSTEM_FLAGS;

        if (!$config['use_bbcodes']) return $content;

        // Special BB tag [code] - blocks all other tags inside
        while (preg_match("#\[code\](.+?)\[/code\]#ies", $content, $res)) {
            $content = str_replace($res[0], '<pre>' . str_replace(array('[', '<'), array('&#91;', '&lt;'), $res[1]) . '</pre>', $content);
        }

        //$content	= preg_replace("#\[code\](.+?)\[/code\]#is", "<pre>$1</pre>",$content);

        $content = preg_replace("#\[quote\]\s*(.*?)\s*\[/quote\]#is", "<blockquote><b>" . __('bb_quote') . "</b><br />$1</blockquote>", $content);
        $content = preg_replace("#\[quote=(.*?)\]\s*(.*?)\s*\[/quote\]#is", "<blockquote><b>$1</b> " . __('bb_wrote') . "<br />$2</blockquote>", $content);
        $content = preg_replace("#\[blockquote\](.+?)\[/blockquote\]#is", "<blockquote class='blockquote'>$1</blockquote>", $content);
        $content = preg_replace("#\[cite\](.+?)\[/cite\]#is", "<footer class='blockquote-footer'><cite>$1</cite></footer>", $content);

        $content = preg_replace("#\[acronym\]\s*(.*?)\s*\[/acronym\]#is", "<acronym>$1</acronym>", $content);
        $content = preg_replace('#\[acronym=([^\"]+?)\]\s*(.*?)\s*\[/acronym\]#is', "<acronym title=\"$1\">$2</acronym>", $content);

        $content = preg_replace("#\[email\]\s*([A-Z0-9._%+-]+@[A-Z0-9.-]+\.[A-Z]{2,20})\s*\[/email\]#i", "<a href=\"mailto:$1\">$1</a>", $content);
        $content = preg_replace("#\[email\s*=\s*\&quot\;([A-Z0-9._%+-]+@[A-Z0-9.-]+\.[A-Z]{2,20})\s*\&quot\;\s*\](.*?)\[\/email\]#i", "<a href=\"mailto:$1\">$2</a>", $content);
        $content = preg_replace("#\[email\s*=\s*([A-Z0-9._%+-]+@[A-Z0-9.-]+\.[A-Z]{2,20})\s*\](.*?)\[\/email\]#i", "<a href=\"mailto:$1\">$2</a>", $content);
        $content = preg_replace("#\[s\](.*?)\[/s\]#is", "<s>$1</s>", $content);
        $content = preg_replace("#\[b\](.+?)\[/b\]#is", "<b>$1</b>", $content);
        $content = preg_replace("#\[i\](.+?)\[/i\]#is", "<i>$1</i>", $content);
        $content = preg_replace("#\[u\](.+?)\[/u\]#is", "<u>$1</u>", $content);
        $content = preg_replace("#\[p\](.+?)\[/p\]#is", "<p>$1</p>", $content);
        $content = preg_replace("#\[ul\](.*?)\[/ul\]#is", "<ul>$1</ul>", $content);
        $content = preg_replace("#\[li\](.*?)\[/li\]#is", "<li>$1</li>", $content);
        $content = preg_replace("#\[ol\](.*?)\[/ol\]#is", "<ol>$1</ol>", $content);
        $content = preg_replace("#\[left\](.*?)\[/left\]#is", "<p style=\"text-align: left\">$1</p>", $content);
        $content = preg_replace("#\[right\](.*?)\[/right\]#is", "<p style=\"text-align: right\">$1</p>", $content);
        $content = preg_replace("#\[center\](.*?)\[/center\]#is", "<p style=\"text-align: center\">$1</p>", $content);
        $content = preg_replace("#\[justify\](.*?)\[/justify\]#is", "<p style=\"text-align: justify\">$1</p>", $content);
        $content = preg_replace("#\[br\]#is", "<br/>", $content);

        // Process spoilers
        while (preg_match("#\[spoiler\](.*?)\[/spoiler\]#is", $content, $null))
            $content = preg_replace("#\[spoiler\](.*?)\[/spoiler\]#is", '<div class="spoiler"><div class="sp-head"><b>' . __('bb_spoiler') . '</b></div><div class="sp-body">$1</div></div>', $content);

        while (preg_match("#\[spoiler=\"(.+?)\"\](.*?)\[/spoiler\]#is", $content, $null))
            $content = preg_replace("#\[spoiler=\"(.+?)\"\](.*?)\[/spoiler\]#is", '<div class="spoiler"><div class="sp-head"><b>$1</b></div><div class="sp-body">$2</div></div>', $content);
        while (preg_match("#\[spoiler=(.+?)\](.*?)\[/spoiler\]#is", $content, $null))
            $content = preg_replace("#\[spoiler=(.+?)\](.*?)\[/spoiler\]#is", '<div class="spoiler"><div class="sp-head"><b>$1</b></div><div class="sp-body">$2</div></div>', $content);

        // Process Images
        // Possible format:
        // '[img' + ( '=' + URL) + flags + ']' + alt + '[/url]'
        // '[img' + flags ']' + url + '[/url]'
        // Allower flags:
        // width
        // height
        // border
        // hspace
        // vspace
        // align: 'left', 'right', 'center'
        // class: anything
        // alt: anything
        // title: anything

        if (preg_match_all("#\[img(\=| *)(.*?)\](.*?)\[\/img\]#is", $content, $pcatch, PREG_SET_ORDER)) {
            $rsrc = array();
            $rdest = array();
            // Scan all IMG tags
            foreach ($pcatch as $catch) {

                // Init variables
                list ($line, $null, $paramLine, $alt) = $catch;
                array_push($rsrc, $line);

                // Check for possible error in case of using "]" within params/url
                // Ex: [url="file[my][super].avi" target="_blank"]F[I]LE[/url] is parsed incorrectly
                if ((strpos($alt, ']') !== false) and (strpos($alt, '"') !== false)) {
                    // Possible bracket error. Make deep analysis
                    $jline = $paramLine . ']' . $alt;
                    $brk = 0;
                    $jlen = strlen($jline);
                    for ($ji = 0; $ji < $jlen; $ji++) {
                        if ($jline[$ji] == '"') {
                            $brk = !$brk;
                            continue;
                        }

                        if ((!$brk) and ($jline[$ji] == ']')) {
                            // Found correct delimiter
                            $paramLine = substr($jline, 0, $ji);
                            $alt = substr($jline, $ji + 1);
                            break;
                        }
                    }
                }

                $outkeys = array();

                // Make a parametric line with url
                if (trim($paramLine)) {
                    // Parse params
                    $keys = $this->parseBBCodeParams((($null == '=') ? 'src=' : '') . $paramLine);
                } else {
                    // No params to scan
                    $keys = array();
                }

                // Get URL
                $urlREF = $this->validateURL((!isset($keys['src']) or !$keys['src']) ? $alt : $keys['src']);

                // Return an error if BB code is bad
                if ((!is_array($keys)) or ($urlREF === false)) {
                    array_push($rdest, '[INVALID IMG BB CODE]');
                    continue;
                }

                $keys['alt'] = $alt;

                // Now let's compose a resulting URL
                $outkeys [] = 'src="' . $urlREF . '"';

                // Now parse allowed tags and add it into output line
                foreach ($keys as $kn => $kv) {
                    switch ($kn) {
                        case 'width':
                        case 'height':
                        case 'hspace':
                        case 'vspace':
                        case 'border':
                            $outkeys[] = $kn . '="' . intval($kv) . '"';
                            break;
                        case 'align':
                            if (in_array(strtolower($kv), array('left', 'right', 'middle', 'top', 'bottom')))
                                $outkeys[] = $kn . '="' . strtolower($kv) . '"';
                            break;
                        case 'class':
                            $v = str_replace(array(ord(0), ord(9), ord(10), ord(13),/* ' ',*/ "'", '"', ";", ":", '<', '>', '&', '[', ']'), '', $kv);
                            $outkeys [] = $kn . '="' . $v . '"';
                            break;
                        case 'alt':
                        case 'title':
                            $v = str_replace(array('"', '[', ']', ord(0), ord(9), ord(10), ord(13), ":", '<', '>', '&'), array("'", '%5b', '%5d', ''), $kv);
                            $outkeys [] = $kn . '="' . $v . '"';
                            break;
                    }
                }
                // Fill an output replacing array
                array_push($rdest, "<img " . (implode(" ", $outkeys)) . ' />');
            }
            $content = str_replace($rsrc, $rdest, $content);
        }

        // Авто-подсветка URL'ов в тексте новости [ пользуемся обработчиком тега [url] ]
        $content = preg_replace("#(^|\s)((http|https|news|ftp)://\w+[^\s\[\]\<]+)#i", "$1[url]$2[/url]", $content);

        // Process URLS
        // Possible format:
        // '[url' + ( '=' + URL) + flags + ']' + Name + '[/url]'
        // '[url' + flags ']' + url + '[/url]'
        // Allower flags:
        // target: anything
        // class: anything
        // title: anything
        // external: yes/no - flag if link is opened via external page or not

        if (preg_match_all("#\[url(\=| *)(.*?)\](.*?)\[\/url\]#is", $content, $pcatch, PREG_SET_ORDER)) {
            $rsrc = array();
            $rdest = array();
            // Scan all URL tags
            foreach ($pcatch as $catch) {

                // Init variables
                list ($line, $null, $paramLine, $alt) = $catch;
                array_push($rsrc, $line);

                // Check for possible error in case of using "]" within params/url
                // Ex: [url="file[my][super].avi" target="_blank"]F[I]LE[/url] is parsed incorrectly
                if ((strpos($alt, ']') !== false) and (strpos($alt, '"') !== false)) {
                    // Possible bracket error. Make deep analysis
                    $jline = $paramLine . ']' . $alt;
                    $brk = 0;
                    $jlen = strlen($jline);
                    for ($ji = 0; $ji < $jlen; $ji++) {
                        if ($jline[$ji] == '""') {
                            $brk = !$brk;
                            continue;
                        }

                        if ((!$brk) and ($jline[$ji] == ']')) {
                            // Found correct delimiter
                            $paramLine = substr($jline, 0, $ji);
                            $alt = substr($jline, $ji + 1);
                            break;
                        }
                    }
                }

                $outkeys = array();

                // Make a parametric line with url
                if (trim($paramLine)) {
                    // Parse params
                    $keys = $this->parseBBCodeParams((($null == '=') ? 'href=' : '') . $paramLine);
                } else {
                    // No params to scan
                    $keys = array();
                }

                // Return an error if BB code is bad
                if (!is_array($keys)) {
                    array_push($rdest, '[INVALID URL BB CODE]');
                    continue;
                }

                // Check for EMPTY URL
                $urlREF = $this->validateURL((empty($keys['href']) ? $alt : $keys['href']));

                if ($urlREF === false) {
                    // EMPTY, SKIP
                    array_push($rdest, $alt);
                    continue;
                }

                // Now let's compose a resulting URL
                $outkeys [] = 'href="' . $urlREF . '"';

                // Check if we have external URL
                $flagExternalURL = false;

                $dn = parse_url($urlREF);
                if (strlen($dn['host']) and ($dn['host'] !== $_SERVER['HTTP_HOST'] or $dn['host'] !== $_SERVER['SERVER_NAME'])) {
                    $flagExternalURL = true;
                }

                // Check for rel=nofollow request for external links

                if ($config['url_external_nofollow'] and $flagExternalURL) {
                    $outkeys [] = 'rel="nofollow"';
                }

                if ($config['url_external_target_blank'] and $flagExternalURL and !isset($keys['target'])) {
                    $outkeys [] = 'target="_blank"';
                }

                // Now parse allowed tags and add it into output line
                foreach ($keys as $kn => $kv) {
                    switch ($kn) {
                        case 'class':
                        case 'target':
                            $v = str_replace(array(ord(0), ord(9), ord(10), ord(13), ' ', "'", '"', ";", ":", '<', '>', '&', '[', ']'), '', $kv);
                            $outkeys [] = $kn . '="' . $v . '"';
                            break;
                        case 'title':
                            $v = str_replace(array('"', '[', ']', ord(0), ord(9), ord(10), ord(13), ":", '<', '>', '&'), array("'", '%5b', '%5d', ''), $kv);
                            $outkeys [] = $kn . '="' . $v . '"';
                            break;
                    }
                }
                // Fill an output replacing array
                array_push($rdest, "<a " . (implode(" ", $outkeys)) . ">" . $alt . '</a>');
            }
            $content = str_replace($rsrc, $rdest, $content);
        }

        // Обработка кириллических символов для украинского языка
        $content = str_replace(array('[CYR_I]', '[CYR_i]', '[CYR_E]', '[CYR_e]', '[CYR_II]', '[CYR_ii]'), array('&#1030;', '&#1110;', '&#1028;', '&#1108;', '&#1031;', '&#1111;'), $content);

        while (preg_match("#\[color=([^\]]+)\](.+?)\[/color\]#ies", $content, $res)) {
            $nl = $this->color(array('style' => $res[1], 'text' => $res[2]));
            $content = str_replace($res[0], $nl, $content);
        }
        return $content;
    }

    function validateURL($url)
    {
        // Check for empty url
        if (trim($url) == '')
            return false;

        // Make replacement of dangerous symbols
        if (preg_match('#^(http|https|ftp)://(.+)$#', $url, $mresult))
            return $mresult[1] . '://' . str_replace(array(':', "'", '"', '[', ']'), array('%3A', '%27', '%22', '%5b', '%5d'), $mresult[2]);

        // Process special `magnet` links
        if (preg_match('#^(magnet\:\?)(.+)$#', $url, $mresult))
            return $mresult[1] . str_replace(array(' ', "'", '"'), array('%20', '%27', '%22'), $mresult[2]);

        return str_replace(array(':', "'", '"'), array('%3A', '%27', '%22'), $url);
    }

    function htmlformatter($content)
    {
        global $config;

        if (!$config['use_htmlformatter'])
            return $content;

        $content = preg_replace('|<br />\s*<br />|', "\n\n", $content);
        $content = str_replace(array("\r\n", "\r"), "\n", $content);
        $content = preg_replace("/\n\n+/", "\n\n", $content);
        $content = preg_replace('/\n/', "<br />", $content);
        $content = preg_replace('!<p>\s*(</?(?:table|thead|tbody|tr|td|th|div|dl|dd|dt|ul|ol|li|pre|select|form|blockquote|math|p|h[1-6])[^>]*>)\s*</p>!', "$1", $content);
        $content = preg_replace("|<p>(<li.+?)</p>|", "$1", $content);
        $content = preg_replace('|<p><blockquote([^>]*)>|i', "<blockquote$1><p>", $content);
        $content = str_replace('</blockquote></p>', '</p></blockquote>', $content);
        $content = preg_replace('!<p>\s*(</?(?:table|thead|tbody|tr|td|th|div|dl|dd|dt|ul|ol|li|pre|select|form|blockquote|math|p|h[1-6])[^>]*>)!', "$1", $content);
        $content = preg_replace('!(</?(?:table|thead|tbody|tr|td|th|div|dl|dd|dt|ul|ol|li|pre|select|form|blockquote|math|p|h[1-6])[^>]*>)\s*</p>!', "$1", $content);
        $content = preg_replace('|(?<!<br />)\s*\n|', "<br />\n", $content);
        $content = preg_replace('!(</?(?:table|thead|tbody|tr|td|th|div|dl|dd|dt|ul|ol|li|pre|select|form|blockquote|math|p|h[1-6])[^>]*>)\s*<br />!', "$1", $content);
        $content = preg_replace('!<br />(\s*</?(?:p|li|div|dl|dd|dt|th|pre|td|ul|ol)>)!', '$1', $content);
        $content = preg_replace_callback(
            "/<code>(.*?)<\/code>/s",
            function ($match) {
                return "phphighlight('" . $match[0] . "')";
            },
            $content
        );
        $content = str_replace("\n</p>\n", "</p>", $content);

        return $content;
    }

    function smilies($content)
    {
        global $config;

        if (!$config['use_smilies'])
            return $content;

        $smilies_arr = explode(',', $config['smilies']);
        foreach ($smilies_arr as $null => $smile) {
            $smile = trim($smile);
            $find[] = "':$smile:'";
            $replace[] = '<img class="smilies" alt="' . $smile . '" src="' . skins_url . '/smilies/' . $smile . '.gif" />';
        }
        return preg_replace($find, $replace, $content);
    }

    function nameCheck($name)
    {
        return preg_match('/[^A-Za-z0-9ёЁА-Яа-я\_\-]+/mu', $name);
    }

    function color($arr)
    {
        $style = $arr['style'];
        $text = $arr['text'];
        $style = str_replace('&quot;', '', $style);
        $style = preg_replace("/[&\(\)\.\%\[\]<>\'\"]/", '', preg_replace("#^(.+?)(?:;|$)#", "$1", $style));
        $style = preg_replace("/[^\d\w\#\s]/s", '', $style);

        return '<span style="color:' . $style . '">' . $text . '</span>';
    }

    // Functions for HTML truncator
    function joinAttributes($attributes)
    {
        $alist = array();
        foreach ($attributes as $aname => $aval) {
            $mark = (strpos($aval, '"') === FALSE) ? '"' : "'";
            $alist [] = $aname . '=' . $mark . $aval . $mark;
        }
        return join(' ', $alist);
    }

    function truncateHTML($text, $size = 50, $finisher = '&nbsp;...')
    {

        $text = preg_replace("/\>(\\x20|\t|\r|\n)+\</", '> <', $text);
        $text = strip_tags($text);
        $text = preg_replace('/([^\pL\pN\pP\pS\pZ])|([\xC2\xA0])/u', ' ', $text);
        $text = str_replace(' ', ' ', $text);
        $text = trim($text);

        if (mb_strlen($text, 'UTF-8') <= $size)
            return $text;

        $text = mb_substr($text, 0, $size, 'UTF-8');
        $text = trim($text, ' \xC2\xA0!,.-');

        if (strpos($text, ' ')) {
            $text = mb_substr($text, 0, mb_strrpos($text, ' ', 'UTF-8'), 'UTF-8');
            $text = trim($text, ' \xC2\xA0!,.-');
        }

        return $text . $finisher;

    }

    // Process [attach] BB code
    function parseBBAttach($content, $db, $templateVariables = array())
    {
        global $config;

        $dataCache = array();
        if (preg_match_all("#\[attach(\#\d+){0,1}\](.*?)\[\/attach\]#is", $content, $pcatch, PREG_SET_ORDER)) {
            $rsrc = array();
            $rdest = array();

            foreach ($pcatch as $catch) {

                // Find attach UID
                if (trim($catch[1])) {
                    $uid = substr($catch[1], 1);
                    $title = $catch[2];
                } else {
                    $uid = $catch[2];
                }

                if (is_numeric($uid)) {
                    array_push($rsrc, $catch[0]);
                    $rec = array();
                    if (is_array($dataCache[$uid])) {
                        $rec = $dataCache[$uid];
                    } else {
                        $rec = $db->record("select * from " . prefix . "_files where id = " . db_squote($uid));
                        if (is_array($rec)) {
                            $dataCache[$uid] = $rec;
                        }
                    }
                    if (is_array($rec)) {
                        // Generate file ULR
                        $fname = ($rec['storage'] ? $config['attach_dir'] : $config['files_dir']) . $rec['folder'] . '/' . $rec['name'];
                        $fsize = (file_exists($fname) and ($fsize = @filesize($fname))) ? formatSize($fsize) : 'n/a';

                        $params = array(
                            'url' => ($rec['storage'] ? $config['attach_url'] : $config['files_url']) . '/' . $rec['folder'] . '/' . $rec['name'],
                            'title' => ($title == '') ? $rec['orig_name'] : $title,
                            'size' => $fsize
                        );
                        array_push($rdest, str_replace(array('{url}', '{title}', '{size}'), array($params['url'], $params['title'], $params['size']), $templateVariables['bbcodes']['attach.format']));
                    } else {
                        array_push($rdest, $templateVariables['bbcodes']['attach.nonexist']);
                    }
                }
            }
            $content = str_replace($rsrc, $rdest, $content);
        }

        // Scan for separate {attach#ID.url}, {attach#ID.size}, {attach#ID.name}, {attach#ID.ext}
        if (preg_match_all("#\{attach\#(\d+)\.(url|size|name|ext|fname)\}#is", $content, $pcatch, PREG_SET_ORDER)) {
            $rsrc = array();
            $rdest = array();

            foreach ($pcatch as $catch) {
                if (is_numeric($uid = $catch[1])) {
                    array_push($rsrc, $catch[0]);
                    $rec = array();
                    if (is_array($dataCache[$uid])) {
                        $rec = $dataCache[$uid];
                    } else {
                        $rec = $db->record("select * from " . prefix . "_files where id = " . db_squote($uid));
                        if (is_array($rec)) {
                            $dataCache[$uid] = $rec;
                        }
                    }
                    if (is_array($rec)) {
                        // Generate file ULR
                        $fname = ($rec['storage'] ? $config['attach_dir'] : $config['files_dir']) . $rec['folder'] . '/' . $rec['name'];

                        // Decide what to do
                        switch ($catch[2]) {
                            case 'url':
                                array_push($rdest, ($rec['storage'] ? $config['attach_url'] : $config['files_url']) . '/' . $rec['folder'] . '/' . $rec['name']);
                                break;
                            case 'size':
                                array_push($rdest, (file_exists($fname) and ($fsize = @filesize($fname))) ? formatSize($fsize) : 'n/a');
                                break;
                            case 'name':
                                $dots = explode(".", $rec['orig_name']);
                                if (count($dots) > 1) {
                                    array_pop($dots);
                                }
                                array_push($rdest, join(".", $dots));
                                break;
                            case 'ext':
                                $dots = explode(".", $rec['orig_name']);
                                if (count($dots) > 1) {
                                    array_push($rdest, array_pop($dots));
                                } else {
                                    array_push($rdest, '');
                                }

                                break;
                            case 'fname':
                                array_push($rdest, $rec['orig_name']);
                                break;
                        }

                    } else {
                        array_push($rdest, $templateVariables['bbcodes']['attach.nonexist']);
                    }
                }
            }
            $content = str_replace($rsrc, $rdest, $content);
        }

        return $content;

    }
}
