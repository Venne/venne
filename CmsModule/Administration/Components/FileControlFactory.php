<?php

/**
 * This file is part of the Venne:CMS (https://github.com/Venne)
 *
 * Copyright (c) 2011, 2012 Josef Kříž (http://www.josef-kriz.cz)
 *
 * For the full copyright and license information, please view
 * the file license.txt that was distributed with this source code.
 */

namespace CmsModule\Administration\Components;

use CmsModule\Content\Forms\DirFormFactory;
use CmsModule\Content\Forms\FileFormFactory;
use CmsModule\Content\Repositories\DirRepository;
use CmsModule\Content\Repositories\FileRepository;
use Nette\Object;

/**
 * @author Josef Kříž <pepakriz@gmail.com>
 */
class FileControlFactory extends Object
{

	/** @var string */
	protected $filePath;

	/** @var DirRepository */
	protected $dirRepository;

	/** @var FileRepository */
	protected $fileRepository;

	/** @var DirFormFactory */
	protected $dirFormFactory;

	/** @var FileFormFactory */
	protected $fileFormFactory;

	/** @var AjaxFileUploaderControlFactory */
	protected $ajaxFileUploaderFactory;


	/**
	 * @param $filePath
	 * @param FileRepository $fileRepository
	 * @param DirRepository $dirRepository
	 * @param FileFormFactory $fileForm
	 * @param DirFormFactory $dirForm
	 * @param AjaxFileUploaderControlFactory $ajaxFileUploaderFactory
	 */
	public function __construct(
		$filePath,
		FileRepository $fileRepository,
		DirRepository $dirRepository,
		FileFormFactory $fileForm,
		DirFormFactory $dirForm,
		AjaxFileUploaderControlFactory $ajaxFileUploaderFactory
	)
	{
		$this->filePath = $filePath;
		$this->fileRepository = $fileRepository;
		$this->dirRepository = $dirRepository;
		$this->fileFormFactory = $fileForm;
		$this->dirFormFactory = $dirForm;
		$this->ajaxFileUploaderFactory = $ajaxFileUploaderFactory;
	}


	/**
	 * @return FileControl
	 */
	public function create()
	{
		$control = new FileControl(
			$this->filePath,
			$this->fileRepository,
			$this->dirRepository,
			$this->fileFormFactory,
			$this->dirFormFactory,
			$this->ajaxFileUploaderFactory
		);
		return $control;
	}


	/**
	 * @return FileControl
	 */
	public function __invoke()
	{
		return $this->create();
	}
}
