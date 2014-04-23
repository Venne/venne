<?php

/**
 * This file is part of the Venne:CMS (https://github.com/Venne)
 *
 * Copyright (c) 2011, 2012 Josef Kříž (http://www.josef-kriz.cz)
 *
 * For the full copyright and license information, please view
 * the file license.txt that was distributed with this source code.
 */

namespace Venne\Files\FileBrowser;

use Venne\Files\DirFormFactory;
use Venne\Files\FileFormFactory;

/**
 * @author Josef Kříž <pepakriz@gmail.com>
 */
class FileControlFactory
{

	/** @var DirFormFactory */
	protected $dirFormFactory;

	/** @var FileFormFactory */
	protected $fileFormFactory;


	/**
	 * @param FileFormFactory $fileForm
	 * @param DirFormFactory $dirForm
	 */
	public function __construct(
		FileFormFactory $fileForm,
		DirFormFactory $dirForm
	)
	{
		$this->fileFormFactory = $fileForm;
		$this->dirFormFactory = $dirForm;
	}


	/**
	 * @return FileControl
	 */
	public function create()
	{
		$control = new FileControl(
			$this->dirFormFactory,
			$this->fileFormFactory
		);
		return $control;
	}

}
