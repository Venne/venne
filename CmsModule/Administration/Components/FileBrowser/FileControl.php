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

use CmsModule\Components\Table\Form;
use CmsModule\Components\Table\TableControl;
use CmsModule\Content\Forms\DirFormFactory;
use CmsModule\Content\Forms\FileFormFactory;

/**
 * @author Josef Kříž <pepakriz@gmail.com>
 */
class FileControl extends TableControl
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
	 * @return \CmsModule\Components\Table\Form
	 */
	public function getFileForm()
	{
		return $this->fileForm;
	}


	/**
	 * @return \CmsModule\Components\Table\Form
	 */
	public function getDirForm()
	{
		return $this->dirForm;
	}

}
