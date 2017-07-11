<?php

//
// Generate BACKUP of DB

// * $delayed - flag if call should be delayed for 30 mins (for cases of SYSCRON / normal calls)
function AutoBackup($delayed = false, $force = false) {
	global $config;

	$backupFlagFile		= root."cache/last_backup.tmp";
	$backupMarkerFile	= root."cache/last_backup_marker.tmp";

	// Load `Last Backup Date` from $backupFlagFile
	$last_backup	= intval(@file_get_contents($backupFlagFile));
	$time_now		= time();

	// Force backup if requested
	if ($force) {
		$last_backup = 0;
	}

	// Check if last backup was too much time ago
	if ($time_now > ($last_backup + $config['auto_backup_time'] * 3600 + ($delayed?30*60:0))) {
		// Yep, we need a backup.
		// ** Manage marker file
		$flagDoProcess = false;

		//->Try to create marker
		if (($fm = fopen($backupMarkerFile, 'x')) !== FALSE) {
			// Created, write CALL time
			fwrite($fm, $time_now);
			fclose($fm);

			$flagDoProcess = true;
		} else {
			// Marker already exists, check creation time
			$markerTime	= intval(@file_get_contents($backupMarkerFile));

			// TTL for marker is 5 min
			if ($time_now > ($markerTime + 180)) {
				// Delete OLD marker, create ours
				if (unlink($backupMarkerFile) and (($fm = fopen($backupMarkerFile, 'x')) !== FALSE)) {
					// Created, write CALL time
					fwrite($fm, $time_now);
					fclose($fm);

					$flagDoProcess = true;
				}
			}
		}

		// Do not run if another session is running
		if (!$flagDoProcess) {
			return;
		}

		// Try to open temp file for writing
		$fx = is_file($backupFlagFile)?@fopen($backupFlagFile,"r+"):@fopen($backupFlagFile,"w+");
		if ($fx) {
			$filename	= root."backups/backup_".date("Y_m_d_H_i", $time_now).".gz";

 	// Load library
	 require_once(root.'/includes/inc/lib_admin.php');

			// We need to create file with backup
	 	dbBackup($filename, 1);

			rewind($fx);
			fwrite($fx, $time_now);
			ftruncate($fx,ftell($fx));
		}

		// Delete marker
		@unlink($backupMarkerFile);
	}
}
