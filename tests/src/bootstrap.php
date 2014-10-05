<?php

$autoloadPaths = array(
	__DIR__ . '/../../vendor/autoload.php',
	__DIR__ . '/../../../../autoload.php'
);

foreach ($autoloadPaths as $path) {
	if (is_file($path)) {
		require $path;
		break;
	}
}

if (!class_exists('Tester\Environment')) {
	echo 'Install Nette Tester using `composer update --dev`';
	exit(1);
}

// configure environment
Tester\Environment::setup();

// create temporary directory
define('TEMP_DIR', __DIR__ . '/../tmp/' . getmypid());
@mkdir(dirname(TEMP_DIR)); // @ - directory may already exist
Tester\Helpers::purge(TEMP_DIR);
