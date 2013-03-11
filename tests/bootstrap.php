<?php

// uncomment this line if you must temporarily take down your site for maintenance
// require '.maintenance.php';

$root = dirname(dirname(dirname(dirname(__DIR__))));

require_once $root . '/vendor/autoload.php';

$configurator = new \Venne\Testing\Configurator($root . '/app');
$configurator->enableDebugger();
$configurator->enableLoader();