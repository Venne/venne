<?php

/**
 * Test initialization and helpers.
 *
 * @author     David Grudl
 * @package    Grido\Test
 */


if ((!$loader = @include __DIR__ . '/../../vendor/autoload.php') && (!$loader = @include __DIR__ . '/../../../../autoload.php')) {
	echo 'Install Nette Tester using `composer update --dev`';
	exit(1);
}


// configure environment
Tester\Helpers::setup();
class_alias('Tester\Assert', 'Assert');
date_default_timezone_set('Europe/Prague');


// create temporary directory
define('TEMP_DIR', __DIR__ . '/../tmp/' . getmypid());
Tester\Helpers::purge(TEMP_DIR);


$_SERVER = array_intersect_key($_SERVER, array_flip(array('PHP_SELF', 'SCRIPT_NAME', 'SERVER_ADDR', 'SERVER_SOFTWARE', 'HTTP_HOST', 'DOCUMENT_ROOT', 'OS', 'argc', 'argv')));
$_SERVER['REQUEST_TIME'] = 1234567890;
$_ENV = $_GET = $_POST = array();


if (extension_loaded('xdebug')) {
	xdebug_disable();
	Tester\CodeCoverage\Collector::start(__DIR__ . '/coverage.dat');
}


function id($val) {
	return $val;
}

function run(Tester\TestCase $testCase) {
	$testCase->run(isset($_SERVER['argv'][1]) ? $_SERVER['argv'][1] : NULL);
}

class TestGlobal {

	private static $loader;

	public static function setLoader(Composer\Autoload\ClassLoader $loader)
	{
		self::$loader = $loader;
	}

	public static function getLoader()
	{
		return self::$loader;
	}
}

TestGlobal::setLoader($loader);

function getLoader()
{
	return TestGlobal::getLoader();
}

class Notes
{
	static public $notes = array();

	public static function add($message)
	{
		self::$notes[] = $message;
	}

	public static function fetch()
	{
		$res = self::$notes;
		self::$notes = array();
		return $res;
	}

}