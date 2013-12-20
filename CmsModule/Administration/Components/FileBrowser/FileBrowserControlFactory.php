<?php

/**
 * This file is part of the Venne:CMS (https://github.com/Venne)
 *
 * Copyright (c) 2011, 2012 Josef Kříž (http://www.josef-kriz.cz)
 *
 * For the full copyright and license information, please view
 * the file license.txt that was distributed with this source code.
 */

namespace CmsModule\Administration\Components\FileBrowser;

use CmsModule\Administration\Components\AjaxFileUploaderControlFactory;
use CmsModule\Content\Forms\DirFormFactory;
use CmsModule\Content\Forms\FileFormFactory;
use CmsModule\Content\Repositories\DirRepository;
use CmsModule\Content\Repositories\FileRepository;
use Venne\BaseFactory;

/**
 * @author Josef Kříž <pepakriz@gmail.com>
 */
class FileBrowserControlFactory extends BaseFactory
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

	/** @var FileControlFactory */
	protected $fileControlFactory;


	/**
	 * @param FileControlFactory $fileControlFactory
	 * @param FileRepository $fileRepository
	 * @param DirRepository $dirRepository
	 * @param FileFormFactory $fileForm
	 * @param DirFormFactory $dirForm
	 * @param AjaxFileUploaderControlFactory $ajaxFileUploaderFactory
	 */
	public function __construct(
		FileControlFactory $fileControlFactory,
		FileRepository $fileRepository,
		DirRepository $dirRepository,
		FileFormFactory $fileForm,
		DirFormFactory $dirForm,
		AjaxFileUploaderControlFactory $ajaxFileUploaderFactory
	)
	{
		$this->fileControlFactory = $fileControlFactory;
		$this->fileRepository = $fileRepository;
		$this->dirRepository = $dirRepository;
		$this->fileFormFactory = $fileForm;
		$this->dirFormFactory = $dirForm;
		$this->ajaxFileUploaderFactory = $ajaxFileUploaderFactory;
	}


	/**
	 * @return FileBrowserControl
	 */
	public function invoke()
	{
		$control = new FileBrowserControl(
			$this->fileControlFactory,
			$this->fileRepository,
			$this->dirRepository,
			$this->fileFormFactory,
			$this->dirFormFactory,
			$this->ajaxFileUploaderFactory
		);
		return $control;
	}

}
