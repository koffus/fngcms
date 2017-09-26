<?php

function voting_rpc_manage($params)
{
    global $mysql, $tpl, $template, $SUPRESS_TEMPLATE_SHOW, $userROW, $ip;

    include_once(extras_dir.'/voting/voting.php');
	$votedList = (isset($_COOKIE['voting'])) ? (explode('|', $_COOKIE['voting'])) : array();

	// ========================================
	// MODE: Vote request
	if (isset($params['mode']) and ($params['mode'] == 'vote') and ($choice = intval($params['choice']))) {
        // Search for poll and poll line
        $sql = "select * from ".prefix."_voteline where id = $choice";
		if (($row = $mysql->record($sql)) and ($vrow = $mysql->record("select * from ".prefix."_vote where id = ".$row['voteid']))) {
			// Line was found
			// Check is user already took part in this poll (according to security model)
			$dup = 0;
			if ($secure = pluginGetVariable('voting','secure')) {
				$condition = (is_array($userROW))?"userid = ".$userROW['id']:"ip=".db_squote($ip);
				if ($mysql->record("select * from ".prefix."_votestat where voteid = ".$vrow['id']." and $condition limit 1")) {
					$dup = 1;
				}
			} else {
                $dup = (array_key_exists($row['id'], $votedList)) ? true : false;
            }
			// Report an error if user tries to take part twice
			if ($dup) {
				// Inform that vote is already accepted
                return array('status' => 0, 'errorCode' => 999, 'errorText' => __('voting:msg.already'));
			} else {
				$mysql->query("update ".prefix."_voteline set cnt=cnt+1 where id = ".$row['id']);
				// DONE. Vote accepted

				if ($secure) {
					$query = "insert into ".prefix."_votestat (userid, voteid, voteline, ip, dt) values (".(is_array($userROW)?$userROW['id']:'0').",".$vrow['id'].",".$row['id'].", '$ip', now() )";
					$mysql->query($query);
				} else {
                    array_push($votedList,$vrow['id']);
                    @setcookie('voting', implode("|",$votedList), time() + 3600 * 24 * 365, '/');
				}
				// Check returning mode for template
				//$retMode = ($flagPanel) ? 6 : 3;
				$content = plugin_showvote(6, $vrow['id']);
			}
		} else {
			// No such vote line
			return array('status' => 0, 'errorCode' => 999, 'errorText' => __('voting:msg.norec'));
		}
	} else if (isset($params['mode']) and ($params['mode'] == 'show') and ($voteid = intval($params['voteid']))) {
        //$retMode = ($flagPanel) ? 6 : 3;
		$content = plugin_showvote(6, $voteid);
	} else {
	 	// SHOW REQUEST
		$content = plugin_showvote(0, 0, 0, $votedList);
	}

    return array('status' => 1, 'errorCode' => 0, 'content' => $content);
}

rpcRegisterFunction('plugin.voting.update', 'voting_rpc_manage');
