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

use Kdyby\Doctrine\EntityDao;
use Venne\Files\AjaxFileUploaderControlFactory;
use Venne\Files\DirFormFactory;
use Venne\Files\FileFormFactory;

/**
 * @author Josef Kříž <pepakriz@gmail.com>
 */
class FileBrowserControlFactory
{

	/** @var string */
	protected $filePath;

	/** @var EntityDao */
	protected $dirDao;

	/** @var EntityDao */
	protected $fileDao;

	/** @var DirFormFactory */
	protected $dirFormFactory;

	/** @var FileFormFactory */
	protected $fileFormFactory;

	/** @var AjaxFileUploaderControlFactory */
	protected $ajaxFileUploaderFactory;

	/** @var FileControlFactory */
	protected $fileControlFactory;


	/**
	 * @param EntityDao $fileDao
	 * @param EntityDao $dirDao
	 * @param FileControlFactory $fileControlFactory
	 * @param FileFormFactory $fileForm
	 * @param DirFormFactory $dirForm
	 * @param AjaxFileUploaderControlFactory $ajaxFileUploaderFactory
	 */
	public function __construct(
		EntityDao $fileDao,
		EntityDao $dirDao,
		FileControlFactory $fileControlFactory,
		FileFormFactory $fileForm,
		DirFormFactory $dirForm,
		AjaxFileUploaderControlFactory $ajaxFileUploaderFactory
	)
	{
		$this->fileControlFactory = $fileControlFactory;
		$this->fileDao = $fileDao;
		$this->dirDao = $dirDao;
		$this->fileFormFactory = $fileForm;
		$this->dirFormFactory = $dirForm;
		$this->ajaxFileUploaderFactory = $ajaxFileUploaderFactory;
	}


	/**
	 * @return FileBrowserControl
	 */
	public function create()
	{
		$control = new FileBrowserControl(
			$this->fileControlFactory,
			$this->fileDao,
			$this->dirDao,
			$this->fileFormFactory,
			$this->dirFormFactory,
			$this->ajaxFileUploaderFactory
		);
		return $control;
	}

}
