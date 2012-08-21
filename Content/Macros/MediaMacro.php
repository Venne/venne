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

use Venne;
use Nette\Latte\MacroNode;
use Nette\Latte\Compiler;
use DoctrineModule\ORM\BaseRepository;

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
		$me->addMacro('fhref', NULL, NULL, function(MacroNode $node, $writer) use ($me)
		{
			return ' ?> href="<?php ' . $me->macroFile($node, $writer) . ' ?>"<?php ';
		});


		// image
		$me->addMacro('img', array($me, 'macroImage'));
		$me->addMacro('image', array($me, 'macroImage'));
		$me->addMacro('ihref', NULL, NULL, function(MacroNode $node, $writer) use ($me)
		{
			return ' ?> href="<?php ' . $me->macroImage($node, $writer) . ' ?>"<?php ';
		});
		$me->addMacro('src', NULL, NULL, function(MacroNode $node, $writer) use ($me)
		{
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
		$data = explode(',', $node->args);
		foreach ($data as &$value) {
			$value = trim($value);
		}

		$path = isset($data[0]) ? $data[0] : '';

		if (($entity = self::$fileRepository->findOneBy(array('path' => $path))) == NULL) {
			throw new \Nette\Latte\ParseException("File '{$path}' does not exist.");
		}

		return $writer->write("echo '{$entity->getFileUrl()}'");
	}


	/**
	 * @static
	 * @param MacroNode $node
	 * @param $writer
	 * @return mixed
	 */
	public static function macroImage(MacroNode $node, $writer)
	{
		$data = explode(',', $node->args);
		foreach ($data as &$value) {
			$value = trim($value);
		}

		$path = isset($data[0]) ? $data[0] : '';
		$size = isset($data[1]) && $data[1] ? $data[1] : 'default';
		$format = isset($data[2]) && $data[2] ? $data[2] : 'default';
		$type = isset($data[3]) && $data[3] ? $data[3] : 'default';

		if (($entity = self::$fileRepository->findOneBy(array('path' => $path))) == NULL) {
			throw new \Nette\Latte\ParseException("File '{$path}' does not exist.");
		}

		// orig file
		$ext = str_replace('jpg', 'jpeg', substr($entity->getName(), strrpos($entity->getName(), '.') + 1));

		if (array_search($ext, self::$imageExtensions) === false || array_search($type, self::$imageExtensions) === false) {
			throw new \Nette\Latte\ParseException("Bad extension of file '{$path}'. You can use only: " . implode(', ', self::$imageExtensions));
		}

		if ($format == 'default' && ($type == 'orig' || $type == $ext)) {
			return $writer->write("echo '{$entity->getFileUrl()}'");
		}

		return $writer->write("echo \$basePath . '/public/media/_cache/{$size}/{$format}/{$type}/{$entity->getPath()}'");
	}


	/**
	 * @param BaseRepository $fileRepository
	 */
	public static function setFileRepository($fileRepository)
	{
		self::$fileRepository = $fileRepository;
	}
}

