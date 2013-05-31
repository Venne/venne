<?php

return array(
	'nette' => array(
		'debugger' => array(
			'edit' => '',
			'browser' => '',
			'email' => '',
			'strictMode' => NULL,
		),
		'application' => array(
			'catchExceptions' => NULL,
			'debugger' => FALSE,
		),
		'routing' => array(
			'debugger' => FALSE,
		),
		'container' => array(
			'debugger' => FALSE,
		),
		'security' => array(
			'debugger' => FALSE,
		),
		'session' => array(
			'autoStart' => FALSE,
			'expiration' => '',
		),
		'xhtml' => NULL,
	),
	'venne' => array(
		'session' => array(
			'savePath' => '',
		),
		'stopwatch' => array(
			'debugger' => FALSE,
		),
	),
	'doctrine' => array(
		'debugger' => FALSE,
		'cacheClass' => NULL,
	),
);
