<?php

/**
 * This file is part of the Venne:CMS (https://github.com/Venne)
 *
 * Copyright (c) 2011, 2012 Josef Kříž (http://www.josef-kriz.cz)
 *
 * For the full copyright and license information, please view
 * the file license.txt that was distributed with this source code.
 */

namespace CmsModule\Content;

use DoctrineModule\Repositories\BaseRepository;
use Nette\InvalidArgumentException;
use Nette\Latte\MacroTokenizer;
use Nette\Object;

/**
 * @author Josef Kříž <pepakriz@gmail.com>
 */
class LayoutManager extends Object
{

	/** @var BaseRepository */
	protected $layoutRepository;


	/**
	 * @param BaseRepository $layoutRepository
	 */
	public function __construct(BaseRepository $layoutRepository)
	{
		$this->layoutRepository = $layoutRepository;
	}


	/**
	 * @param $file
	 * @return array
	 * @throws \Nette\InvalidArgumentException
	 */
	public function getElementsByFile($file)
	{
		if (!file_exists($file)) {
			throw new InvalidArgumentException("File '{$file}' does not exist.");
		}

		$ret = array();
		$tokenizer = new MacroTokenizer(file_get_contents($file));

		while (($word = $tokenizer->fetchWord()) !== FALSE) {
			if ($word === '{element') {
				$name = trim($tokenizer->fetchWord(), '}\'"');
				$id = trim($tokenizer->fetchWord(), '}\'"');
				$ret[$id] = $name;
			}
		}

		return $ret;
	}
}

