<?php

return array(
	'nette' => array(
		'debugger' => array(
			'edit' => 'a',
			'browser' => 'b',
			'email' => 'c@c.cc',
			'strictMode' => TRUE,
		),
		'application' => array(
			'catchExceptions' => TRUE,
			'debugger' => TRUE,
		),
		'routing' => array(
			'debugger' => TRUE,
		),
		'container' => array(
			'debugger' => TRUE,
		),
		'security' => array(
			'debugger' => TRUE,
		),
		'session' => array(
			'autoStart' => TRUE,
			'expiration' => '+1 year',
		),
		'xhtml' => TRUE,
	),
	'venne' => array(
		'session' => array(
			'savePath' => 'f',
		),
		'stopwatch' => array(
			'debugger' => TRUE,
		),
	),
	'doctrine' => array(
		'debugger' => TRUE,
		'cacheClass' => NULL,
	),
);
