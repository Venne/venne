<?php

/** @var $loader Composer\Autoload\ClassLoader */
$loader = require_once dirname(__DIR__) . '/vendor/autoload.php';

// create and run application
$configurator = new \Venne\Config\Configurator(__DIR__, $loader);
$configurator->enableDebugger();
$configurator->enableLoader();
$configurator->getContainer()->application->run();
