<?php

//
// Copyright (C) 2006-2012 Next Generation CMS (http://ngcms.ru/)
// Name: file_managment.class.php
// Description: Files upload managment
// Author: Vitaly Ponomarev
//

class file_managment {

	// CONSTRUCTOR
	function file_managment(){
		// Load additional LANG file
		Lang::load('files');

		return;
	}

	// from upload.class.php
	function get_item_dir($type){
		global $config;
		switch($type){
			case "image":	return $config['images_dir'];
			case "file":	return $config['files_dir'];
			case "avatar":	return $config['avatars_dir'];
			case "photo":	return $config['photos_dir'];
			default: return false;
		}
	}

	// Get limits
	function get_limits($type){
		global $config;

		$this->filetype = $type;
		switch($type){
			case "image":	$this->required_type = explode(',',str_replace(' ','',$config['images_ext']));
							$this->max_size = $config['images_max_size']*1024;
							$this->max_x	= intval($config['images_max_x']);
							$this->max_y	= intval($config['images_max_y']);
							$this->dim_act	= intval($config['images_dim_action']);
							$this->tname	= "images";
							$this->dname	= $config['images_dir'];
							$this->uname	= $config['images_url'];
							$this->tcat		= 0;
							break;
			case "file":	$this->required_type = explode(',',str_replace(' ','',$config['files_ext']));
							$this->max_size = $config['files_max_size']*1024;
							$this->tname	= "files";
							$this->dname	= $config['files_dir'];
							$this->uname	= $config['files_url'];
							$this->tcat		= 0;
							break;
			case "avatar":	$this->required_type = explode(',',str_replace(' ','',$config['images_ext']));
							$this->max_size = $config['avatar_max_size']*1024;
							$this->tname	= "images";
							$this->dname	= $config['avatars_dir'];
							$this->uname	= $config['avatars_url'];
							$this->tcat		= 1;
							break;
			case "photo":	$this->required_type = explode(',',str_replace(' ','',$config['images_ext']));
							$this->max_size = $config['photos_max_size']*1024;
							$this->tname	= "images";
							$this->dname	= $config['photos_dir'];
							$this->uname	= $config['photos_url'];
							$this->tcat		= 2;
							break;
			default: return false;
		}
		return true;
	}

	// fetch selected URL into temp directory
	function file_fetch_url($url){

		if ((!($tmpn = tempnam(ini_get('upload_tmp_dir'),'upload_')))||(!($f = fopen($tmpn, 'w')))) {
			msg(array('type' => 'danger', 'message' => __('upload.error.tempcreate')));
			return;
		}

		if ($data = @file_get_contents($url)) {
			// Data were read
			fwrite($f, $data);
			fclose($f);
			$filename = end(explode("/", $url));
			return array($tmpn, $filename, strlen($data));
		} else {
			// Unable to fetch content (URL)
		}
		return false;
	}

	// * type		- file type (image / file / avatar / photo)
	// * category	- category where to put file
	// * http_var	- name of HTTP variable to transfer file
	// * htt_varnum - number of file that is uploaded in group (via 1 variable)
	// * dsn		- FLAG: store data (files/images) in Data Storage Network (BTREE)
	// *** IS SET:
	// * linked_ds - id of data storage to link this file
	// * linked_id - id of item in data stodage to link this file
	// *** IS NOT SET:
	// * replace	- 'replace if present' flag
	// * randprefix	- add random prefix to file
	// * randname	- make a random file name
	// * manual		- manual upload mode. File name is sent via "manualfile"
	// * url			- upload URL instead of file
	// * manualfile	- file name for manual upload
	// * manualtmp		- TEMP file where manual uploaded file is [temporally] stored
	// * plugin		- ID of plugin that owns this file
	// * pidentity	- ID of plugin's identity that owns this file
	// * description- description for image
	// * rpc		- flag: if set, returning result is made in RPC style [ default - not set ]
	function file_upload($param){
		global $config, $mysql, $userROW;

		Lang::load('files');

		// Normalize category (to make it possible to have empty category)
		$wCategory = ( trim($param['category']) ) ? ($param['category'].'/') : '';

		//print "CALL file_upload -> upload(".$param['http_var']."//".$param['http_varnum'].")<br>\n<pre>"; var_dump($param); print "</pre><br>\n";

		$http_var		= getIsSet($param['http_var']);
		$http_varnum	= intval(getIsSet($param['http_varnum']));

		if ($param['manual']) {
			if ($param['url']) {
				if (is_array($fetch_result = $this->file_fetch_url($param['url']))) {
					$fname = $param['manualfile']?$param['manualfile']:$fetch_result[1];	// override file name if needed
					$ftmp = $fetch_result[0];
					$fsize = filesize($ftmp);
				} else {
					return 0;
				}
			} else {
				$fname = getIsSet($param['manualfile']);
				$ftmp = getIsSet($param['manualtmp']);
				$fsize = filesize($ftmp);
			}
		} else {
			if ((is_int($http_varnum))&&is_array($_FILES[$http_var]['name'])){
				$fname	= $_FILES[$http_var]['name'][$http_varnum];
				$fsize	= $_FILES[$http_var]['size'][$http_varnum];
				$ftype	= $_FILES[$http_var]['type'][$http_varnum];
				$ftmp	= $_FILES[$http_var]['tmp_name'][$http_varnum];
				$ferr	= $_FILES[$http_var]['error'][$http_varnum];
			} else {
				// in case of one upload we may set a manual filename
				$fname	= ($param['manualfile'])?$param['manualfile']:$_FILES[$http_var]['name'];
				$fsize	= $_FILES[$http_var]['size'];
				$ftype	= $_FILES[$http_var]['type'];
				$ftmp	= $_FILES[$http_var]['tmp_name'];
				$ferr	= $_FILES[$http_var]['error'];
			}
		}
		// Check limits
		if (!$this->get_limits($param['type'])) {
			if ($param['rpc']) {
				return array('status' => 0, 'errorCode' => 301, 'errorText' => str_replace('{fname}', $fname, __('upload.error.type')));
			} else {
				msg(array('type' => 'danger', 'message' => str_replace('{fname}', $fname, __('upload.error.type'))));
				return 0;
			}
		}

		//print "PROCESS: fname=".$fname."<br> fsize=".$fsize."<br>ftype=".$ftype."<br>ftmp=".$ftmp."<br>ferr=".$ferr."<br>this->dname=".$this->dname."<br/>\n";
		//print "Param: <pre>"; var_dump($_FILES); print "</pre><br>\n";

		// * File size
		if ($fsize > $this->max_size) {
			if ($param['rpc']) {
				return array('status' => 0, 'errorCode' => 302, 'errorText' => str_replace('{fname}', $fname, __('upload.error.size')), 'errorDescription' => str_replace('{size}', Formatsize($this->max_size), __('upload.error.size#info')));
			} else {
				msg(array('type' => 'danger', 'title' => str_replace('{fname}', $fname, __('upload.error.size')), 'message' => str_replace('{size}', Formatsize($this->max_size), __('upload.error.size#info'))));
				return 0;
			}
		}

		// Check for existance of temp file
		if (!$ftmp or !file_exists($ftmp)) {
			if (getIsSet($param['rpc'])) {
				return array('status' => 0, 'errorCode' => 303, 'errorText' => var_export($_FILES, true).str_replace('{fname}', $fname, __('upload.error.losttemp')));
			} else {
				msg(array('type' => 'danger', 'message' => str_replace('{fname}', $fname, __('upload.error.losttemp'))));
				return 0;
			}
		}

		// ** IMAGES :: Check maximum image size & resize if needed
		if ($param['type'] == 'image') {
			$im = new image_managment();
			$s = $im->get_size($ftmp);
			if (!is_array($s)) {
				if ($param['rpc']) {
					return array('status' => 0, 'errorCode' => 301, 'errorText' => str_replace('{fname}', $fname, __('upload.error.type')));
				} else {
					msg(array('type' => 'danger', 'message' => str_replace('{fname}', $fname, __('upload.error.type'))));
					return 0;
				}
			}

			// Check size
			if ((($this->max_x > 0) and ($s[1] > $this->max_x)) or (($this->max_y > 0) and ($s[2] > $this->max_y))) {
				// !! OVERSIZED !!
				if (!$this->dim_act) {
					// REJECT
					if ($param['rpc']) {
						return array('status' => 0, 'errorCode' => 317, 'errorText' => str_replace(array('{fname}', '{maxx}', '{maxy}'), array($fname, $this->max_x, $this->max_y), __('upload.error.imgsize')));
					} else {
						msg(array('type' => 'danger', 'message' => str_replace('{fname}', $fname, __('upload.error.imgsize'))));
						return 0;
					}
				}
				// Try to resize
				$resizeResult = $im->image_transform(array('rpc' => $param['rpc'], 'image' => $ftmp, 'resize' => array('x' => $this->max_x, 'y' => $this->max_y)));
				//return array('status' => 0, 'errorCode' => 999, 'errorText' => "X/MAXX::".$s[1]."/".$this->max_x.", Y/MAXY:".$s[2]."/".$this->max_y." NX/NY:".$resizeResult['data']['x']."/".$resizeResult['data']['y']);

				// Check results
				// ** RPC
				if ($param['rpc'] and ((!is_array($resizeResult)) or (!$resizeResult['status']))) {
					return $resizeResult;
				}
				// ** Normal call
				if (!is_array($resizeResult)) {
					msg(array('type' => 'danger', 'message' => str_replace('{fname}', $fname, __('upload.error.imgsize'))));
					return;
				}
			}
		}

		// Process file name
		$fil = explode(".", strtolower($fname));
		$ext = count($fil)?array_pop($fil):'';

		// * File type [ don't allow to upload PHP files in any case ]
		if ((array_search(strtolower($ext), $this->required_type) === FALSE)||(array_search(strtolower($ext), array('php', 'pht', 'phtml', 'php3', 'php4', 'php5')) !== FALSE)) {
			if ($param['rpc']) {
				return array('status' => 0, 'errorCode' => 304, 'errorText' => str_replace('{fname}', $fname, __('upload.error.ext')), 'errorDescription' => str_replace('{ext}', join(", ",$this->required_type), __('upload.error.ext#info')));
			} else {
				msg(array('type' => 'danger', 'title' => str_replace('{fname}', $fname, __('upload.error.ext')), 'message' => str_replace('{ext}', join(",",$this->required_type), __('upload.error.ext#info'))));
				return 0;
			}
		}

		// Manage multiple extensions
		if (!$config['allow_multiext']) {
			$fil = array(join("_", $fil));
		}

		$parse = new parse();

		$fil = trim(str_replace(array(' ','\\','/',chr(0)),array('_', ''),join(".",$fil)));
		$fil = $parse->translit($fil);

		$fname = $fil.($ext?'.'.$ext:'');

		// Save original file name
		$origFname = $fname;

		// DSN - Data Storage Network. Store data in BTREE if requested
		if ($param['dsn']) {
			// Check if directory for DSN exists
			$wDir = $config['attach_dir'];

			if (!is_dir($wDir)) {
				if ($param['rpc']) {
					return array('status' => 0, 'errorCode' => 305, 'errorText' => str_replace('{dir}', $wDir, __('upload.error.dsn')));
				} else {
					msg(array('type' => 'danger', 'message' => 'No access to DSN directory `'.$wDir.'`'));
					return 0;
				}
			}

			// Determine storage tree
			$fn_md5 = md5($fname);
			$dir1 = substr($fn_md5,0,2);
			$dir2 = substr($fn_md5,2,2);

			$wDir .= '/'.$dir1;
			if (!is_dir($wDir) and !@mkdir($wDir, 0777)) {
				if ($param['rpc']) {
					return array('status' => 0, 'errorCode' => 306, 'errorText' => str_replace('{dir}', $wDir, __('upload.error.dircreate')));
				} else {
					msg(array('type' => 'danger', 'message' => str_replace('{dir}', $wDir, __('upload.error.dircreate'))));
					return 0;
				}
			}

			$wDir .= '/'.$dir2;
			if (!is_dir($wDir) and !@mkdir($wDir, 0777)) {
				if ($param['rpc']) {
					return array('status' => 0, 'errorCode' => 307, 'errorText' => str_replace('{dir}', $wDir, __('upload.error.dircreate')));
				} else {
					msg(array('type' => 'danger', 'message' => str_replace('{dir}', $wDir, __('upload.error.dircreate'))));
					return 0;
				}
			}

			// Now let's find empty slot
			$i = 0;
			$xDir = '';
			while ($i < 999) {
				$i++;
				$xDir = sprintf("%03u", $i);
				if (is_dir($wDir.'/'.$xDir)) {
					$xDir = '';
					continue;
				}

				// Fine. Create this dir ... but check for simultaneous run
				if (!@mkdir($wDir.'/'.$xDir, 0777)) {
					if (is_dir($wDir.'/'.$xDir))
						continue;

					// Unable to create dir
					if ($param['rpc']) {
						return array('status' => 0, 'errorCode' => 308, 'errorText' => str_replace('{dir}', $wDir.'/'.$xDir, __('upload.error.dircreate')));
					} else {
						msg(array('type' => 'danger', 'message' => str_replace('{dir}', $wDir.'/'.$xDir, __('upload.error.dircreate'))));
						return 0;
					}
				} else {
					break;
				}
			}
			if (!$xDir) {
				if ($param['rpc']) {
					return array('status' => 0, 'errorCode' => 309, 'errorText' => str_replace('{dir}', $wDir, __('upload.error.dsn_no_slots')));
				} else {
					msg(array('type' => 'danger', 'message' => str_replace('{dir}', $wDir, __('upload.error.dsn_no_slots'))));
					return 0;
				}
			}

			$wDir .= '/'.$xDir;

			// Now let's upload file
			if ($param['manual']) {
				if (!copy($ftmp, $wDir.'/'.$fname)) {
					// Remove empty dir
					rmdir($wDir);

					// Delete file
					unlink($ftmp);

					if ($param['rpc']) {
						return array('status' => 0, 'errorCode' => 310, 'errorText' => __('upload.error.move'));
					} else {
						msg(array('type' => 'danger', 'message' => __('upload.error.move')));
						return 0;
					}
				}
			} else {
				if (!move_uploaded_file($ftmp, $wDir.'/'.$fname)) {
					// Remove empty dir
					rmdir($wDir);

					if ($param['rpc']) {
						return array('status' => 0, 'errorCode' => 311, 'errorText' => __('upload.error.move'). "(".$ftpm." => ".$this->dname.$wCategory.$fname.")");
					} else {
						msg(array('type' => 'danger', 'message' => __('upload.error.move'). '('.$ftpm.' => '.$this->dname.$wCategory.$fname.')'));
						return 0;
					}
				}
			}

			// Set correct permissions
			chmod($wDir.'/'.$fname, 0644);

			// Create record in SQL DB (or replace old)
			$mysql->query("insert into ".prefix."_".$this->tname." ".
				"(name, storage, orig_name, folder, date, user, owner_id, category, linked_ds, linked_id, plugin, pidentity, description) ".
				"values (".db_squote($fname).", 1,".db_squote($origFname).",".db_squote($dir1.'/'.$dir2.'/'.$xDir).", unix_timestamp(now()), ".db_squote($userROW['name']).",".db_squote($userROW['id']).", ".$this->tcat.", ".db_squote($param['linked_ds']).", ".db_squote($param['linked_id']).", ".db_squote($param['plugin']).", ".db_squote($param['pidentity']).", ".db_squote($param['description']).")");
			$rowID = $mysql->record("select LAST_INSERT_ID() as id");

			// SQL error
			if (!is_array($rowID)) {
				if ($param['rpc']) {
					return array('status' => 0, 'errorCode' => 312, 'errorText' => __('upload.error.sql'));
				} else {
					msg(array('type' => 'danger', 'message' => __('upload.error.sql')));
					return 0;
				}
			}
			if ($param['rpc']) {
				return array('status' => 1, 'errorCode' => 0, 'errorText' => __('upload.complete'), 'data' => array('id' => $rowID['id'], 'name' => $fname, 'location' => $dir1.'/'.$dir2.'/'.$xDir));
			} else {
				return array($rowID['id'], $fname, $dir1.'/'.$dir2.'/'.$xDir);
			}
		}

		// Create random prefix if requested
		$prefix = '';
		if ($param['randprefix']) {
			$try = 0;
			do {
				$prefix = sprintf("%04u",rand(1,9999));
				$try++;
			} while (($try < 100) and (file_exists($this->dname.$wCategory.$prefix.'_'.$fname) or (is_array($row = $mysql->record("select * from ".prefix."_".$this->tname." where name = ".db_squote($prefix.'_'.$fname)." and folder= ".db_squote($param['category']))))));

			if ($try == 100) {
				// Can't create RAND name - all values are occupied
				if ($param['rpc']) {
					return array('status' => 0, 'errorCode' => 313, 'errorText' => __('upload.error.rand'));
				} else {
					msg(array('type' => 'danger', 'message' => __('upload.error.rand')));
					return 0;
				}
			}
			$fname = $prefix.'_'.$fname;
		}

		$replace_id = 0;
		$row = '';
		// Now we have correct filename. Let's check for dups
		if (is_array($row = $mysql->record("select * from ".prefix."_".$this->tname." where name = ".db_squote($fname)." and folder= ".db_squote($param['category']))) or file_exists($this->dname.$wCategory.$fname)) {
			// Found file. Check if 'replace' flag is present and user have enough privilleges
			if ($param['replace']) {
				if (!(($row['user'] == $userROW['name']) or ($userROW['status'] == 1) or ($userROW['status'] == 2))) {
					if ($param['rpc']) {
						return array('status' => 0, 'errorCode' => 314, 'errorText' => __('upload.error.perm.replace'));
					} else {
						msg(array('type' => 'danger', 'message' => __('upload.error.perm.replace')));
						return 0;
					}
				}
			} else {
				if ($param['rpc']) {
					return array('status' => 0, 'errorCode' => 315, 'errorText' => __('upload.error.exists'), 'errorDescription' => __('upload.error.exists#info'));
				} else {
					msg(array('type' => 'danger', 'title' => __('upload.error.exists'), 'message' => __('upload.error.exists#info')));
					return 0;
				}
			}
			if (is_array($row))
				$replace_id = $row['id'];
		}

		// We're ready to move file into target directory
		if (!is_dir($this->dname.$param['category'])) {
			// SPECIAL processing for "default" category
			if ($param['category'] == 'default') {
				@mkdir($this->dname.$param['category'], 0777);
				if ($param['type'] == 'image') {
					@mkdir($this->dname.$subdirectory.'/thumb', 0777);
				}

			} else {
				// Category dir doesn't exists
				if ($param['rpc']) {
					return array('status' => 0, 'errorCode' => 316, 'errorText' => str_replace('{category}', $param['category'], __('upload.error.catnexists')));
				} else {
					msg(array('type' => 'danger', 'message' => str_replace('{category}', $param['category'], __('upload.error.catnexists'))));
					return 0;
				}
			}
		}

		// Now let's upload file
		if ($param['manual']) {
			if (!copy($ftmp, $this->dname.$wCategory.$fname)) {
				unlink($ftmp);

				if ($param['rpc']) {
					return array('status' => 0, 'errorCode' => 310, 'errorText' => __('upload.error.move'));
				} else {
					msg(array('type' => 'danger', 'message' => __('upload.error.move')));
					return 0;
				}
			}
		} else {
			if (!move_uploaded_file($ftmp, $this->dname.$wCategory.$fname)) {
				if ($param['rpc']) {
					return array('status' => 0, 'errorCode' => 310, 'errorText' => __('upload.error.move'). "(".$ftpm." => ".$this->dname.$wCategory.$fname.")");
				} else {
					msg(array('type' => 'danger', 'message' => __('upload.error.move'). '('.$ftpm.' => '.$this->dname.$wCategory.$fname.')'));
					return 0;
				}
			}
		}

		// Set correct permissions
		chmod($this->dname.$wCategory.$fname, 0644);

		// Create record in SQL DB (or replace old)
		if ($replace_id) {
			// Delete old THUMB (if exists)
			if ($row['preview'] and ($param['type'] == 'image')&& is_file($this->dname.$param['category'].'/thumb/'.$row['name'])) {
				@unlink($this->dname.$param['category'].'/thumb/'.$row['name']);
			}

			$mysql->query("update ".prefix."_".$this->tname." set ".
					"name= ".db_squote($fname).", ".
					"folder=".db_squote($param['category']).", ".
					"date=unix_timestamp(now()), ".
					"user=".db_squote($userROW['name']).", ".
					"owner_id=".db_squote($userROW['id']).
					(($param['type'] == 'image')?', preview = 0, p_width = 0, p_height = 0':'').
					" where id = ".$replace_id);
			if ($param['rpc']) {
				return array('status' => 1, 'errorCode' => 0, 'errorText' => __('upload.complete'), 'data' => array('id' => $replace_id, 'name' => $fname, 'category' => $wCategory));
			} else {
				return array($replace_id, $fname, $wCategory);
			}
		} else {
			$mysql->query("insert into ".prefix."_".$this->tname." (name, orig_name, folder, date, user, owner_id, category) values (".db_squote($fname).",".db_squote($origFname).",".db_squote($param['category']).", unix_timestamp(now()), ".db_squote($userROW['name']).",".db_squote($userROW['id']).", ".$this->tcat.")");
			$rowID = $mysql->record("select LAST_INSERT_ID() as id");

			// SQL error
			if (!is_array($rowID)) {
				if ($param['rpc']) {
					return array('status' => 0, 'errorCode' => 312, 'errorText' => __('upload.error.sql'));
				} else {
					msg(array('type' => 'danger', 'message' => __('upload.error.sql')));
					return 0;
				}
			}
			if ($param['rpc']) {
				return array('status' => 1, 'errorCode' => 0, 'errorText' => __('upload.complete'), 'data' => array('id' => $rowID['id'], 'name' => $fname, 'category' => $wCategory));
			} else {
				return array($rowID['id'], $fname, $wCategory);
			}
		}
	}

	// Delete file
	// * type		- file type (image / file / avatar / photo)
	// * category		- category from that file should be deleted
	// * id			- ID of file to delete
	// * name		- filename to delete [if no ID specified]
	function file_delete($param){
		global $mysql, $userROW, $config;

		// Check limits
		if (!$this->get_limits($param['type'])) {
			msg(array('type' => 'danger', 'message' => __('upload.error.type')));
			return 0;
		}

		// Find file
		if ($param['id']) {
			$limit = "id = ".db_squote($param['id']);
		} else {
			if (!$param['category']) $param['category'] = 'default';
			$limit = "name = ".db_squote($param['name'])." and folder =".db_squote($param['category']);
		}

		if (is_array($row = $mysql->record("select * from ".prefix."_".$this->tname." where ".$limit))) {
			// Check permissions
			if (!(($row['owner_id'] == $userROW['id'])||($userROW['status'] == 1)||($userROW['status'] == 2))) {
				msg(array('type' => 'danger', 'message' => __('upload.error.perm.delete')));
				return 0;
			}

			$storageDir = ($row['storage']?$config['attach_dir']:$this->dname).$row['folder'];

			// Check if thumb file exists & delete it
			if ($row['preview'] and file_exists($storageDir.'/thumb/'.$row['name'])) {
				if ( !@unlink($storageDir . '/thumb/' . $row['name']) ) {
					msg(array('type' => 'danger', 'message' => str_replace('{file}', $row['folder'].'/thumb/'.$row['name'], __('upload.error.delete'))));
				}
			}

			// Check if file file exists & delete it
			if (file_exists($storageDir.'/'.$row['name'])) {
				if (!@unlink($storageDir.'/'.$row['name'])) {
					msg(array('type' => 'danger', 'message' => str_replace('{file}', $row['folder'].'/'.$row['name'], __('upload.error.delete'))));
					return 0;
				}
				// Now try to delete empty storage directory [ ONLY for DSN ]
				if ($row['storage'])
					@rmdir($storageDir);
			}

			$mysql->query("delete from ".prefix."_".$this->tname." where id = ".db_squote($row['id']));
			return 1;
		} else {
			msg(array('type' => 'danger', 'message' => __('upload.error.nofile').', id='.$param['id']));
			return 0;
		}
	}

	// Rename a file within one category
	// * type			- file type (image / file / avatar / photo)
	// * category		- category where to put file
	// * move			- FLAG [ 1 - move mode, 0 - rename mode ]
	// * newcategory	- new category [ TO MOVE FILE ]
	// * id				- ID of file to delete
	// * name			- filename to rename [if no ID specified]
	// * newname		- new filename
	function file_rename($param) {
		global $mysql, $config, $parse;

		if (defined('DEBUG')) { print "CALL file_rename(): <pre>"; var_dump($param); print "</pre><br>\n"; }

		// Check limits
		if (!$this->get_limits($param['type'])) {
			msg(array('type' => 'danger', 'message' => __('upload.error.type')));
			return 0;
		}

		// Find file
		if (!$param['category']) $param['category'] = 'default';
		if ($param['move']) {
			if (!$param['newcategory']) $param['newcategory'] = 'default';
		}

		if ($param['id']) {
			$limit = "id = ".db_squote($param['id']);
		} else {
			$limit = "name = ".db_squote($param['name'])." and folder=".db_squote($param['category']);
		}

		if (is_array($row = $mysql->record("select * from ".prefix."_".$this->tname." where ".$limit))) {

			if ($param['move']) {
				if ($param['newcategory']) {
					$param['newcategory'] = trim(str_replace(array(' ','\\','/',chr(0)),array('_', ''),$param['newcategory']));
				} else {
					$param['newcategory'] = 'default';
				}
				if (!$param['newname']) $param['newname'] = $row['name'];
			}

			$newname = trim(str_replace(array(' ','\\','/',chr(0)),array('-', ''),$param['newname']));
			$nnames = explode('.', $newname);
			$ext = array_pop($nnames);
			if (array_search($ext, $this->required_type) === FALSE) {
				msg(array('type' => 'danger', 'title' => __('upload.error.ext'), 'message' => str_replace('{ext}', join(",",$this->required_type), __('upload.error.ext#info'))));
				return 0;
			}

			$newname = $parse->translit(implode(".",$nnames)).".".$ext;

			// Check for DUP
			if (is_array($mysql->record("select * from ".prefix."_".$this->tname." where folder=".db_squote($param['move']?$param['newcategory']:$row['folder'])." and name=".db_squote($newname)))) {
				msg(array('type' => 'danger', 'message' => __('upload.error.renexists')));
				return 0;
			}

			// Check if we have enough access and all required directories are created
			if (!is_writable($this->dname.$row['folder'].'/'.$row['name'])) {
				msg(array('type' => 'danger', 'message' => __('upload.error.sysperm.access')));
				return 0;
			}

			if ($param['move'] and !is_dir($this->dname.$param['newcategory'])) {
				msg(array('type' => 'danger', 'message' => str_replace('{category}', $param['newcategory'], __('upload.error.catnexists'))));
				return 0;
			}

			if ($param['move']) {
				// MOVE action
				if (copy($this->dname.$row['folder'].'/'.$row['name'], $this->dname.$param['newcategory'].'/'.$newname)) {
					unlink($this->dname.$row['folder'].'/'.$row['name']);
					$mysql->query("update ".prefix."_".$this->tname." set name=".db_squote($newname).", orig_name=".db_squote($newname).", folder=".db_squote($param['newcategory'])." where id = ".$row['id']);
					if (file_exists($this->dname.$row['folder'].'/thumb/'.$row['name'])) {
						copy($this->dname.$row['folder'].'/thumb/'.$row['name'], $this->dname.$param['newcategory'].'/thumb/'.$newname);
						unlink($this->dname.$row['folder'].'/thumb/'.$row['name']);
					}
					return 1;
				} else {
					msg(array('type' => 'danger', 'message' => __('upload.error.copy')));
					return 0;
				}
			} else {
				// RENAME action
				if (rename($this->dname.$row['folder'].'/'.$row['name'], $this->dname.$row['folder'].'/'.$newname)) {
					
					msg(array('message' => __('upload.renamed')));
					
					$mysql->query("update ".prefix."_".$this->tname." set name=".db_squote($newname).", orig_name=".db_squote($newname)." where id = ".$row['id']);
					if (file_exists($this->dname.$row['folder'].'/thumb/'.$row['name'])) {
						rename($this->dname.$row['folder'].'/thumb/'.$row['name'], $this->dname.$row['folder'].'/thumb/'.$newname);
					}
					return 1;
				}
			}

		}
		msg(array('type' => 'danger', 'message' => __('upload.error.rename')));
		return 0;
	}

	// Create new directory/category
	// * type		- file type (image / file / avatar / photo)
	// * category	- category where to put file
	function category_create($type, $category){
		global $parse;

		if (($dir = $this->get_item_dir($type)) === false) {
			print "No";
			return;
		}

		$category = $parse->translit(trim(str_replace(array(' ','\\','/',chr(0)),array('-', ''),$category)));

		if (is_dir($dir.$category)) {
			msg(array('type' => 'danger', 'title' => __('upload.error.catexists'), 'message' => __('upload.error.catexists#info')));
			return;
		}

		if (@mkdir($dir.$category,0777) and (($type != "image") or @mkdir($dir.$category.'/thumb', 0777))) {
			msg(array('message' => __('upload.catcreated')));
		} else {
			msg(array('type' => 'danger', 'message' => __('upload.error.catcreate')));
		}
	}

	// Delete a category
	// * type		- file type (image / file / avatar / photo)
	// * category	- category where to put file
	function category_delete($type, $category){
		global $mysql;

		if (($dir = $this->get_item_dir($type)) === false) {
			return;
		}
		$category = trim(str_replace(array(' ','\\','/',chr(0)),array('_', ''),$category));

		if ($category and is_dir($dir.$category)) {
			if ($this->count_dir($dir.$category)) {
				msg(array('type' => 'danger', 'message' => __('upload.error.catnotempty')));
				return;
			}
			if (is_dir($dir.$category.'/thumb')) {
				@rmdir($dir.$category.'/thumb');
			}

			if (@rmdir($dir.$category)) {
				msg(array('message' => __('upload.catdeleted')));
			} else {
				msg(array('type' => 'danger', 'message' => str_replace('{dir}', $dir.$category, __('upload.error.delcat'))));
			}
			return;
		}
		msg(array('message' => __('upload.catdeleted')));
	}

	function count_dir($dir){
		if ($d = @opendir($dir)) {
			$cnt = 0;
			while(($file = readdir($d)) !== false)
				if ($file != '.' and $file != '..' and is_file($dir.'/'.$file))
					$cnt++;
			closedir($d);
			return $cnt;
		}
		return false;
	}

}
