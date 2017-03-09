<?php

//
// Copyright (C) 2006-2012 Next Generation CMS (http://ngcms.ru/)
// Name: image_managment.class.php
// Description: Images upload managment
// Author: Vitaly Ponomarev
//

// ======================================================================= //
// Image managment class //
// ======================================================================= //
class image_managment{
	function image_managment(){
		return;
	}

	// Get image size. Return an array with params:
	// index 0 - image type (same as in getimagesize())
	// index 1 - image width
	// index 2 - image height
	function get_size($fname){
		if (is_array($info = @getimagesize($fname))) {
			return array($info[2], $info[0], $info[1]);
		}
		return NULL;
	}

	// Params:
	//	rpc			- flag if we're called via RPC call
	function create_thumb($dir, $file, $sizeX, $sizeY, $quality = 0, $param){
		
		$fname = $dir.'/'.$file;

		//print "CALL create_thumb($dir, $file, $sizeX, $sizeY)<br>\n";

		// Check if we have a directory for thumb
		if (!is_dir($dir.'/thumb')) {
			if (!@mkdir($dir.'/thumb', 0777)) {
				if ($param['rpc']) {
					return array('status' => 0, 'errorCode' => 351, 'errorText' => __('upload.error.sysperm.thumbdir'));
				}
				msg(array('type' => 'danger', 'message' => __('upload.error.sysperm.thumbdir')));
				return false;
			}
		}

		// Check if file exists and we can get it's image size
		if (!file_exists($fname) || !is_array($sz=@getimagesize($fname))) {
			if ($param['rpc']) {
				return array('status' => 0, 'errorCode' => 352, 'errorText' => __('upload.error.open').$fname);
			}
			msg(array('type' => 'danger', 'message' => __('upload.error.open')));
			return false;
		}
		$origX		= $sz[0];
		$origY		= $sz[1];
		$origType	= $sz[2];

		if (!(($sizeX>0) && ($sizeY>0) && ($origX>0) && ($origY>0))) {
			if ($param['rpc']) {
				return array('status' => 0, 'errorCode' => 353, 'errorText' => 'Unable to determine image size');
			}

			return false;
		}

		// Calculate resize factor
		$factor = max ($origX / $sizeX, $origY / $sizeY);

		// Don't enlarge picture without need
		if ($factor < 1) $factor = 1;

		// Check if we can open this type of image and open it
		$cmd = 'imagecreatefrom';
		switch ($origType) {
			case 1: $cmd .= 'gif';	break;
			case 2: $cmd .= 'jpeg';	break;
			case 3: $cmd .= 'png';	break;
			case 6: $cmd .= 'bmp';	break;
		}

		if (!$cmd || !function_exists($cmd)) {
			if ($param['rpc']) {
				return array('status' => 0, 'errorCode' => 354, 'errorText' => str_replace('{func}', $cmd, __('upload.error.libformat')));
			}
			msg(array('type' => 'danger', 'message' => str_replace('{func}', $cmd, __('upload.error.libformat'))));
			return;
		}

		switch ($origType) {
			case 1: $img = @imagecreatefromgif($fname);	break;
			case 2: $img = @imagecreatefromjpeg($fname);	break;
			case 3: $img = @imagecreatefrompng($fname);	break;
			case 6: $img = @imagecreatefrombmp($fname);	break;
		}

		if (!$img) {
			if ($param['rpc']) {
				return array('status' => 0, 'errorCode' => 355, 'errorText' => __('upload.error.open'));
			}
			msg(array('type' => 'danger', 'message' => __('upload.error.open')));
			return false;
		}

		// Calculate thumb size and create an empty object for it
		$newX = round($origX / $factor);
		$newY = round($origY / $factor);

		$newimg = imagecreatetruecolor($newX, $newY);

		// Prepare for transparency // NON-ALPHA transparency
		$oTColor = imagecolortransparent($img);
		if ($oTColor >= 0 && $oTColor < imagecolorstotal($img)) {
			$TColor = imagecolorsforindex($img, $oTColor);
			$nTColor = imagecolorallocate($newimg, $TColor['red'], $TColor['green'], $TColor['blue']);
			imagefill($newimg, 0, 0, $nTColor);
			imagecolortransparent($newimg, $nTColor);
		} else {
			// Check for ALPHA transparency in PNG
			if ($origType == 3) {
				imagealphablending($newimg, false);
				$nTColor = imagecolorallocatealpha($newimg, 0,0,0, 127);
				imagefill($newimg, 0, 0, $nTColor);
				imagesavealpha($newimg, true);
			}
		}

		// Resize image
		imagecopyresampled($newimg, $img, 0,0,0,0,$newX, $newY, $origX, $origY);

		// Try to write resized image
		switch ($origType) {
			case 1: $res = @imagegif($newimg, $dir.'/thumb/'.$file);		break;
			case 2: $res = @imagejpeg($newimg, $dir.'/thumb/'.$file, ($quality>=10 && $quality<=100)?$quality:80);		break;
			case 3: $res = @imagepng($newimg, $dir.'/thumb/'.$file);		break;
			case 6: $res = @imagebmp($newimg, $dir.'/thumb/'.$file);		break;
		}

		// Set correct permissions to file
		@chmod($dir.'/thumb/'.$file, 0644);

		if (!$res) {
			if ($param['rpc']) {
				return array('status' => 0, 'errorCode' => 356, 'errorText' => __('upload.error.thumbcreate'));
			}
			msg(array('type' => 'danger', 'message' => __('upload.error.thumbcreate')));
			return false;
		}
		if ($param['rpc']) {
			return array('status' => 1, 'errorCode' => 0, 'errorText' => __('upload.complete'), 'data' => array('x' => $newX, 'y' => $newY));
		}
		return array($newX, $newY);
	}

	// Transformate original image
	// * image			- filename of original image
	// * stamp			- FLAG if we need to add a stamp
	// * resize			- Array for image resize
	// ** x					- size X
	// ** y					- size Y
	// ** stampfile		- filename of stamp file
	// ** stamp_transparency - %% of transparency of added stamp [ default: 40 ]
	// ** stamp_noerror	- don't generate an error if it was not possible to add stamp
	// * shadow			- FLAG if we need to add a shadow
	// * outquality		- with what quality we should write resulting file (for JPEG) [ default: 80 ]
	// * outfile		- filename to write a result [ default: original file ]
	// * rpc			- flag shows if call is made via RPC call
	function image_transform($param){
	//function add_stamp($image, $stamp, $transparency = 40, $quality = 80){
		global $config;

		// LOAD ORIGINAL IMAGE
		// Check if file exists and we can get it's image size
		if (!file_exists($param['image']) || !is_array($sz=@getimagesize($param['image']))) {
			if ($param['rpc']) {
				return array('status' => 0, 'errorCode' => 401, 'errorText' => __('upload.error.open').' '.$param['image']);
			}
			msg(array('type' => 'danger', 'message' => __('upload.error.open')));
			return 0;
		}

		$origX	= $sz[0];
		$origY	= $sz[1];
		$origType	= $sz[2];

		// Check if we can open this type of image and open it
		$cmd = 'imagecreatefrom';
		switch ($origType) {
			case 1: $cmd .= 'gif';	break;
			case 2: $cmd .= 'jpeg';	break;
			case 3: $cmd .= 'png';	break;
			case 6: $cmd .= 'bmp';	break;
		}

		if (!$cmd || !function_exists($cmd)) {
			if ($param['rpc']) {
				return array('status' => 0, 'errorCode' => 402, 'errorText' => str_replace('{func}', $cmd, __('upload.error.libformat')));
			}
			msg(array('type' => 'danger', 'message' => str_replace('{func}', $cmd, __('upload.error.libformat'))));
			return;
		}

		switch ($origType) {
			case 1: $img = @imagecreatefromgif($param['image']);	break;
			case 2: $img = @imagecreatefromjpeg($param['image']);	break;
			case 3: $img = @imagecreatefrompng($param['image']);	break;
			case 6: $img = @imagecreatefrombmp($param['image']);	break;
		}

		if (!$img) {
			if ($param['rpc']) {
				return array('status' => 0, 'errorCode' => 403, 'errorText' => __('upload.error.open'));
			}
			msg(array('type' => 'danger', 'message' => __('upload.error.open')));
			return;
		}

		// Check if resize of original file is requested
		if (isset($param['resize']) && is_array($param['resize']) && ($param['resize']['x'] > 0) && ($param['resize']['y'] > 0)) {
			// Calculate ratio and new X/Y sizes
			$ratio = min ($param['resize']['x']/$origX, $param['resize']['y']/$origY);
			$newX = round($origX * $ratio);
			$newY = round($origY * $ratio);

			// Create image area
			$newImg = imagecreatetruecolor($newX, $newY);

			// Preserver transparency
			if (($origType == 1) || ($origType == 3)) {
				imagecolortransparent($newImg, imagecolorallocatealpha($newImg, 0, 0, 0, 127));
				imagealphablending($newImg, false);
				imagesavealpha($newImg, true);
			}

			// Resize image
 			imagecopyresampled($newImg, $img, 0, 0, 0, 0, $newX, $newY, $origX, $origY);

			// Save new information into current image
			$img	= $newImg;
			$origX	= $newX;
			$origY	= $newY;
		}

		if ($param['stamp']) {
			// LOAD STAMP IMAGE
			if (!file_exists($param['stampfile']) || !is_array($sz=@getimagesize($param['stampfile']))) {
				if (!$param['stamp_noerror']) {
					if ($param['rpc']) {
						return array('status' => 0, 'errorCode' => 404, 'errorText' => __('upload.error.openstamp'));
					}
					msg(array('type' => 'danger', 'message' => __('upload.error.openstamp')));
				}
				return 0;
			}

			$stampX	= $sz[0];
			$stampY	= $sz[1];
			$stampType	= $sz[2];

			// Check if we can open this type of image and open it
			$cmd = 'imagecreatefrom';
			switch ($origType) {
				case 1: $cmd .= 'gif';	break;
				case 2: $cmd .= 'jpeg';	break;
				case 3: $cmd .= 'png';	break;
				case 6: $cmd .= 'bmp';	break;
			}

			if (!$cmd || !function_exists($cmd)) {
				if ($param['rpc']) {
					return array('status' => 0, 'errorCode' => 402, 'errorText' => str_replace('{func}', $cmd, __('upload.error.libformat')));
				}
				msg(array('type' => 'danger', 'message' => str_replace('{func}', $cmd, __('upload.error.libformat'))));
				return;
			}

			switch ($stampType) {
				case 1: $stamp = @imagecreatefromgif($param['stampfile']);	break;
				case 2: $stamp = @imagecreatefromjpeg($param['stampfile']);	break;
				case 3: $stamp = @imagecreatefrompng($param['stampfile']);	break;
				case 6: $stamp = @imagecreatefrombmp($param['stampfile']);	break;
			}

			if (!$stamp) {
				if (!$param['stamp_noerror']) {
					if ($param['rpc']) {
						return array('status' => 0, 'errorCode' => 405, 'errorText' => __('upload.error.openstamp'));
					}
					msg(array('type' => 'danger', 'message' => __('upload.error.openstamp')));
				}
				return;
			}

			// BOTH FILES ARE LOADED
			$destX = $origX - $stampX - 10;
			$destY = $origY - $stampY - 10;
			if (($destX<0)||($destY<0)) {
				if (!$param['stamp_noerror']) {
					if ($param['rpc']) {
						return array('status' => 0, 'errorCode' => 406, 'errorText' => __('upload.error.stampsize'));
					}
					msg(array('type' => 'danger', 'message' => __('upload.error.stampsize')));
				}
				return;
			}

			if (($param['stamp_transparency'] < 1) || ($param['stamp_transparency'] > 100)) {
				$param['stamp_transparency'] = 40;
			}

			if ($stampType == 3)
				$this->imagecopymerge_alpha($img, $stamp, $destX, $destY, 0, 0, $stampX, $stampY, $param['stamp_transparency']);
			else
				imageCopyMerge($img, $stamp, $destX, $destY, 0, 0, $stampX, $stampY, $param['stamp_transparency']);
		}

		$newX = $origX;
		$newY = $origY;
		if ($param['shadow']) {
			$newX			=	$origX + 5;
			$newY			=	$origY + 5;
			$newimg			=	imagecreatetruecolor($newX, $newY);

			$background		=	array("r" => 255, "g" => 255, "b" => 255);
			$step_offset	=	array("r" => ($background["r"] / 10), "g" => ($background["g"] / 10), "b" => ($background["b"] / 10));
			$current_color	=	$background;

			for ($i = 0; $i <= 5; $i++) {
				$colors[$i] = @imagecolorallocate($newimg, round($current_color["r"]), round($current_color["g"]), round($current_color["b"]));
				$current_color["r"] -= $step_offset["r"];
				$current_color["g"] -= $step_offset["g"];
				$current_color["b"] -= $step_offset["b"];
			}

			imagefilledrectangle($newimg, 0,0, $newX, $newY, $colors[0]);

			for ($i = 0; $i <= 5; $i++) {
				@imagefilledrectangle($newimg, 5, 5, $newX - $i, $newY - $i, $colors[$i]);
			}
			imagecopymerge($newimg, $img, 0, 0, 0, 0, $origX, $origY, 100);
			$img = $newimg;
		}

		// WRITE A RESULT FILE
		if (($param['outquality'] < 10)||($param['outquality'] > 100)) {
			$param['outquality'] = 80;
		}

		if (!$param['outfile']) $param['outfile'] = $param['image'];

		switch ($origType) {
			case 1: $res = @imagegif($img, $param['outfile']);		break;
			case 2: $res = @imagejpeg($img, $param['outfile'], $param['outquality']);		break;
			case 3: $res = @imagepng($img, $param['outfile']);		break;
			case 6: $res = @imagebmp($img, $param['outfile']);		break;
		}
		if (!$res) {
			if ($param['rpc']) {
				return array('status' => 0, 'errorCode' => 407, 'errorText' => __('upload.error.addstamp'));
			}
			msg(array('type' => 'danger', 'message' => __('upload.error.addstamp')));
			return;
		}

		if ($param['rpc']) {
			return array('status' => 1, 'errorCode' => 0, 'errorText' => __('upload.complete'), 'data' => array('x' => $newX, 'y' => $newY));
		}
		return array($newX, $newY);
	}
	function imagecopymerge_alpha($dst_im, $src_im, $dst_x, $dst_y, $src_x, $src_y, $src_w, $src_h, $pct){
		if(!isset($pct)){
			return false;
		}
		$pct /= 100;
		// Get image width and height
		$w = imagesx( $src_im );
		$h = imagesy( $src_im );
		// Turn alpha blending off
		imagealphablending( $src_im, false );
		// Find the most opaque pixel in the image (the one with the smallest alpha value)
		$minalpha = 127;
		for( $x = 0; $x < $w; $x++ )
		for( $y = 0; $y < $h; $y++ ){
			$alpha = ( imagecolorat( $src_im, $x, $y ) >> 24 ) & 0xFF;
			if( $alpha < $minalpha ){
				$minalpha = $alpha;
			}
		}
		//loop through image pixels and modify alpha for each
		for( $x = 0; $x < $w; $x++ ){
			for( $y = 0; $y < $h; $y++ ){
				//get current alpha value (represents the TANSPARENCY!)
				$colorxy = imagecolorat( $src_im, $x, $y );
				$alpha = ( $colorxy >> 24 ) & 0xFF;
				//calculate new alpha
				if( $minalpha !== 127 ){
					$alpha = 127 + 127 * $pct * ( $alpha - 127 ) / ( 127 - $minalpha );
				} else {
					$alpha += 127 * $pct;
				}
				//get the color index with new alpha
				$alphacolorxy = imagecolorallocatealpha( $src_im, ( $colorxy >> 16 ) & 0xFF, ( $colorxy >> 8 ) & 0xFF, $colorxy & 0xFF, $alpha );
				//set pixel with the new color + opacity
				if( !imagesetpixel( $src_im, $x, $y, $alphacolorxy ) ){
					return false;
				}
			}
		}
		// The image copy
		imagecopy($dst_im, $src_im, $dst_x, $dst_y, $src_x, $src_y, $src_w, $src_h);
	}
}