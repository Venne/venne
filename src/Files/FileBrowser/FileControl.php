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

use Venne\System\Content\Control;
use Venne\System\Content\Forms\DirFormFactory;
use Venne\System\Content\Forms\FileFormFactory;

/**
 * @author Josef Kříž <pepakriz@gmail.com>
 */
class FileControl extends Control
{


	/** @var DirFormFactory */
	protected $dirFormFactory;

	/** @var FileFormFactory */
	protected $fileFormFactory;

	/** @var Form */
	private $fileForm;

	/** @var Form */
	private $dirForm;


	public function __construct(DirFormFactory $dirFormFactory, FileFormFactory $fileFormFactory)
	{
		parent::__construct();

		$this->setDefaultPerPage(99999999999);

		// forms
		$this->fileForm = $this->addForm($fileFormFactory, 'File', NULL, Form::TYPE_LARGE);
		$this->dirForm = $this->addForm($dirFormFactory, 'Directory', NULL, Form::TYPE_LARGE);

		$this->addButtonCreate('directory', 'New directory', $this->dirForm, 'folder-open');
		$this->addButtonCreate('upload', 'Upload file', $this->fileForm, 'upload');

		$this->addActionEdit('editDir', 'Edit', $this->dirForm);
		$this->addActionEdit('editFile', 'Edit', $this->fileForm);
	}


	/**
	 * @return \Venne\System\Components\AdminGrid\Form
	 */
	public function getFileForm()
	{
		return $this->fileForm;
	}


	/**
	 * @return \Venne\System\Components\AdminGrid\Form
	 */
	public function getDirForm()
	{
		return $this->dirForm;
	}

}
