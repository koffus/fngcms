<?php

//
// Copyright (C) 2006-2017 BixBite CMS (http://bixbite.site/)
// Name: syscron.php
// Description: Entry point for maintanance (cron) external calls
// Author: BBCMS project team
//

// Load CORE
@include_once 'engine/core.php';

// Run CRON tasks
$cron->run(true);

// Terminate execution of script
coreNormalTerminate();
