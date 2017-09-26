<?php

// Protect against hack attempts
if (!defined('BBCMS')) die ('HAL');

class VarMgrNewsFilter extends NewsFilter {
	function showNews($newsID, $SQLnews, &$tvars) {

		$lang = Lang::get();
		$langShortMonths = Lang::$short_months;
		$langMonths = Lang::$months;

		if (pluginGetVariable('varmgr','extdate')) {

			$tvars['vars']['day']		= date('j',$SQLnews['postdate']);
			$tvars['vars']['day0']		= date('d',$SQLnews['postdate']);
			$tvars['vars']['month']		= date('n',$SQLnews['postdate']);
			$tvars['vars']['month0']	= date('m',$SQLnews['postdate']);
			$tvars['vars']['year']		= date('y',$SQLnews['postdate']);
			$tvars['vars']['year2']		= date('Y',$SQLnews['postdate']);

			$tvars['vars']['month_s'] = $langShortMonths[$tvars['vars']['month']-1];
			$tvars['vars']['month_l'] = $langMonths[$tvars['vars']['month']-1];

			if (pluginGetVariable('varmgr','newdate')) {
				$t = pluginGetVariable('varmgr','newdate');
				foreach (array('day', 'day0', 'month', 'month0', 'year', 'year2', 'month_text_short', 'month_text_long') as $k) {
					$t = str_replace('{'.$k.'}',$tvars['vars'][$k],$t);
				}
				$tvars['vars']['date'] = $t;
			}
		}
	}
}

pluginRegisterFilter('news','varmgr', new VarMgrNewsFilter);
