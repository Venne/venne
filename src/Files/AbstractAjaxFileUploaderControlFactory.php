<?php

/**
 * This file is part of the Venne:CMS (https://github.com/Venne)
 *
 * Copyright (c) 2011, 2012 Josef Kříž (http://www.josef-kriz.cz)
 *
 * For the full copyright and license information, please view
 * the file license.txt that was distributed with this source code.
 */

namespace Venne\Files;

use Kdyby\Doctrine\EntityDao;
use Nette\Http\FileUpload;
use Nette\Http\Session;
use Nette\InvalidArgumentException;

/**
 * @author Josef Kříž <pepakriz@gmail.com>
 */
abstract class AbstractAjaxFileUploaderControlFactory
{

	/** @var EntityDao */
	protected $fileDao;

	/** @var EntityDao */
	protected $dirDao;

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
	 * @param EntityDao $dirDao
	 * @param EntityDao $fileDao
	 * @param Session $session
	 */
	public function __construct($ajaxDir, $wwwDir, EntityDao $dirDao, EntityDao $fileDao, Session $session)
	{
		$this->ajaxDir = $ajaxDir;
		$this->wwwDir = $wwwDir;
		$this->dirDao = $dirDao;
		$this->fileDao = $fileDao;
		$this->session = $session;
	}


	/**
	 * @param $basePath
	 * @return AjaxFileUploaderControl
	 */
	public function create($basePath)
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
		$fileEntity = $this->fileDao->createNew();
		$fileEntity->setFile(new \SplFileInfo($this->ajaxDir . '/' . $fileName));
		$fileEntity->setParent($this->parentDirectory);
		$fileEntity->setAuthor($control->presenter->user->identity instanceof \Venne\System\Pages\Users\UserEntity ? $control->presenter->user->identity : NULL);
		$fileEntity->copyPermission();
		$this->fileDao->save($fileEntity);
	}


	/**
	 * @param AjaxFileUploaderControl $control
	 * @param FileUpload $file
	 */
	public function handleFileUploadUnlink(AjaxFileUploaderControl $control, $fileName)
	{
		@unlink($this->ajaxDir . '/' . $fileName);
		@unlink($this->ajaxDir . '/thumbnail/' . $fileName);
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
