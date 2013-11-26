<?php

/**
 * This file is part of the Venne:CMS (https://github.com/Venne)
 *
 * Copyright (c) 2011, 2012 Josef Kříž (http://www.josef-kriz.cz)
 *
 * For the full copyright and license information, please view
 * the file license.txt that was distributed with this source code.
 */

namespace CmsModule\Content\Macros;

use DoctrineModule\Repositories\BaseRepository;
use Nette\Latte\CompileException;
use Nette\Latte\Compiler;
use Nette\Latte\MacroNode;
use Nette\Utils\Strings;

/**
 * @author Josef Kříž <pepakriz@gmail.com>
 */
class MediaMacro extends \Nette\Latte\Macros\MacroSet
{

	/** @var BaseRepository */
	protected static $fileRepository;

	/** @var array */
	protected static $imageExtensions = array('jpeg', 'png', 'gif');


	/**
	 * @static
	 * @param Compiler $compiler
	 * @return \Nette\Latte\Macros\MacroSet|void
	 */
	public static function install(Compiler $compiler)
	{
		$me = new static($compiler);

		// file
		$me->addMacro('file', array($me, 'macroFile'));
		$me->addMacro('fhref', NULL, NULL, function (MacroNode $node, $writer) use ($me) {
			return ' ?> href="<?php ' . $me->macroFile($node, $writer) . ' ?>"<?php ';
		});


		// image
		$me->addMacro('img', array($me, 'macroImage'));
		$me->addMacro('image', array($me, 'macroImage'));
		$me->addMacro('ihref', NULL, NULL, function (MacroNode $node, $writer) use ($me) {
			return ' ?> href="<?php ' . $me->macroImage($node, $writer) . ' ?>"<?php ';
		});
		$me->addMacro('src', NULL, NULL, function (MacroNode $node, $writer) use ($me) {
			return ' ?> src="<?php ' . $me->macroImage($node, $writer) . ' ?>"<?php ';
		});

		return $me;
	}


	/**
	 * @static
	 * @param MacroNode $node
	 * @param $writer
	 * @return mixed
	 */
	public static function macroFile(MacroNode $node, $writer)
	{
		return $writer->write('echo $basePath . \CmsModule\Content\Macros\MediaMacro::proccessFile(%node.word)');
	}


	/**
	 * @static
	 * @param MacroNode $node
	 * @param $writer
	 * @return mixed
	 */
	public static function macroImage(MacroNode $node, $writer)
	{
		return $writer->write('echo $basePath . \CmsModule\Content\Macros\MediaMacro::proccessImage(%node.word, %node.array)');
	}


	/**
	 * @static
	 * @param MacroNode $node
	 * @param $writer
	 * @return mixed
	 */
	public static function proccessFile($path)
	{
		return "/public/media/{$path}";
	}


	/**
	 * @static
	 * @param MacroNode $node
	 * @param $writer
	 * @return mixed
	 */
	public static function proccessImage($path, $args = array())
	{
		$size = isset($args['size']) ? $args['size'] : 'default';
		$format = isset($args['format']) ? $args['format'] : 'default';
		$type = isset($args['type']) ? $args['type'] : 'default';

		$ext = new \SplFileInfo($path);
		$ext = str_replace('jpg', 'jpeg', Strings::lower($ext->getExtension()));

		if (array_search($ext, self::$imageExtensions) === false || ($type !== 'default' && array_search($type, self::$imageExtensions) === false)) {
			throw new CompileException("Bad extension of file '{$path}'. You can use only: " . implode(', ', self::$imageExtensions));
		}

		if ($format == 'default' && ($type == 'default' || $type == $ext) && $size == 'default') {
			return "/public/media/{$path}";
		}

		return "/public/media/_cache/{$size}/{$format}/{$type}/{$path}";
	}


	/**
	 * @param BaseRepository $fileRepository
	 */
	public static function setFileRepository($fileRepository)
	{
		self::$fileRepository = $fileRepository;
	}
}

