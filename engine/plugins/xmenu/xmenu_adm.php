<?php

//
// Admin panel handler
//

class xmenu_adm_categories extends FilterAdminCategories {
	function addCategoryForm(&$tvars) {
		$line = '';
		for ($i = 1; $i <= 9; $i++) {
			$line .= '<label><input type=checkbox value="1" name="xmenu['.$i.']"> <b>'.$i.'</b></label> &nbsp; ';
		}
		$tvars['extend'] = isset($tvars['extend']) ? $tvars['extend'] :'';
		$tvars['extend'] .= '<div class="form-group"><div class="col-sm-5">Номера блоков меню</div><div class="col-sm-7">' . $line . '</div></div>';
		return 1;
	}
	function addCategory(&$tvars, &$SQL) {
		$line = '';
		if (isset($_REQUEST['xmenu']) and is_array($_REQUEST['xmenu']))
			for ($i = 0; $i <= 9; $i++)
				$line .= ($_REQUEST['xmenu'][$i])?'#':'_';
		$SQL['xmenu'] = $line;

		return 1;
	}
	function editCategoryForm($categoryID, $SQL, &$tvars) {
		$line = '';
		$xmenu = $SQL['xmenu'];
		for ($i = 1; $i <= 9; $i++) {
			$line .= '<label><input type=checkbox value="1" name="xmenu['.$i.']"'.(($xmenu{$i-1} == '#')?' checked':'').'> <b>'.$i.'</b></label> &nbsp; ';
		}
        $tvars['extend'] = isset($tvars['extend']) ? $tvars['extend'] :'';
		$tvars['extend'] .= '<div class="form-group"><div class="col-sm-5">Номера блоков меню</div><div class="col-sm-7">' . $line . '</div></div>';
		return 1;
	}
	function editCategory($categoryID, $SQL, &$SQLnew, &$tvars) {
		$line = '';
		if (isset($_REQUEST['xmenu']) and is_array($_REQUEST['xmenu']))
			for ($i = 1; $i <= 9; $i++)
				$line .= ($_REQUEST['xmenu'][$i])?'#':'_';
		$SQLnew['xmenu'] = $line;

		return 1;
	}
}

register_admin_filter('categories', 'xmenu', new xmenu_adm_categories);
