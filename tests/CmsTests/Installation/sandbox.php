<?php

$dir = TEMP_DIR;

return array(
	'appDir'        => $dir . '/app',
	'configDir'     => $dir . '/config',
	'logDir'        => $dir . '/log',
	'dataDir'       => $dir . '/data',
	'libsDir'       => dirname(dirname(dirname(__DIR__))) . '/vendor',
	'tempDir'       => $dir . '/temp',
	'wwwDir'        => $dir . '/www',
	'wwwCacheDir'   => $dir . '/cache',
	'publicDir'		=> $dir . '/public',
	'resourcesDir'  => $dir . '/resources',
	'modulesDir'	=> $dir . '/modules',
);
