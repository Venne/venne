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

use CmsModule\Content\Entities\DirEntity;
use CmsModule\Content\Repositories\DirRepository;
use CmsModule\Content\Repositories\FileRepository;
use Nette\Http\FileUpload;
use Nette\Http\Session;
use Nette\InvalidArgumentException;
use Nette\Object;

/**
 * @author Josef Kříž <pepakriz@gmail.com>
 */
abstract class AbstractAjaxFileUploaderControlFactory extends Object
{

	/** @var FileRepository */
	protected $fileRepository;

	/** @var DirRepository */
	protected $dirRepository;

	/** @var Session */
	protected $session;

	/** @var string */
	protected $ajaxDir;

	/** @var string */
	protected $wwwDir;

	/** @var DirEntity */
	private $parentDirectory;


	/**
	 * @param $ajaxDir
	 * @param $wwwDir
	 * @param DirRepository $dirRepository
	 * @param FileRepository $fileRepository
	 * @param Session $session
	 */
	public function __construct($ajaxDir, $wwwDir, DirRepository $dirRepository, FileRepository $fileRepository, Session $session)
	{
		$this->ajaxDir = $ajaxDir;
		$this->wwwDir = $wwwDir;
		$this->dirRepository = $dirRepository;
		$this->fileRepository = $fileRepository;
		$this->session = $session;
	}


	/**
	 * @return AjaxFileUploaderControl
	 */
	public function createControl($basePath)
	{
		$control = new AjaxFileUploaderControl($this->ajaxDir, $basePath . $this->getRelativeAjaxPath(), $this->session->getSection('ajaxFileUploader'));
		$control->onFileUpload[] = $this->handleFileUpload;
		$control->onAfterFileUpload[] = $this->handleFileUploadUnlink;
		return $control;
	}


	/**
	 * @param DirEntity $parentDirectory
	 */
	public function setParentDirectory(DirEntity $parentDirectory = NULL)
	{
		$this->parentDirectory = $parentDirectory;
	}


	/**
	 * @return DirEntity
	 */
	public function getParentDirectory()
	{
		return $this->parentDirectory;
	}


	/**
	 * @param AjaxFileUploaderControl $control
	 * @param FileUpload $file
	 */
	public function handleFileUpload(AjaxFileUploaderControl $control, $fileName)
	{
		/** @var FileEntity $fileEntity */
		$fileEntity = $this->fileRepository->createNew();
		$fileEntity->setFile(new \SplFileInfo($this->ajaxDir . '/' . $fileName));
		$fileEntity->setParent($this->parentDirectory);
		$fileEntity->setAuthor($control->presenter->user->identity instanceof \CmsModule\Pages\Users\UserEntity ? $control->presenter->user->identity : NULL);
		$fileEntity->copyPermission();
		$this->fileRepository->save($fileEntity);
	}


	/**
	 * @param AjaxFileUploaderControl $control
	 * @param FileUpload $file
	 */
	public function handleFileUploadUnlink(AjaxFileUploaderControl $control, $fileName)
	{
		unlink($this->ajaxDir . '/' . $fileName);
		unlink($this->ajaxDir . '/thumbnail/' . $fileName);
	}


	/**
	 * @return AjaxFileUploaderControl
	 */
	public function invoke()
	{
		return call_user_func_array(array($this, 'createControl'), func_get_args());
	}


	private function getRelativeAjaxPath()
	{
		$pos = strpos($this->ajaxDir, $this->wwwDir);

		if ($pos !== 0) {
			throw new InvalidArgumentException("%ajaxDir% does not contain %wwwDir%");
		}

		return substr($this->ajaxDir, strlen($this->wwwDir));
	}
}
