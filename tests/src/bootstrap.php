<?php

require __DIR__ . '/../../vendor/autoload.php';

// configure environment
Tester\Environment::setup();

// create temporary directory
define('TEMP_DIR', __DIR__ . '/../tmp/' . getmypid());
@mkdir(dirname(TEMP_DIR)); // @ - directory may already exist
Tester\Helpers::purge(TEMP_DIR);
