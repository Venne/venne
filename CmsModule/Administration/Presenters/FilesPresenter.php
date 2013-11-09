<?php

/**
 * This file is part of the Venne:CMS (https://github.com/Venne)
 *
 * Copyright (c) 2011, 2012 Josef Kříž (http://www.josef-kriz.cz)
 *
 * For the full copyright and license information, please view
 * the file license.txt that was distributed with this source code.
 */

namespace CmsModule\Administration\Presenters;

use CmsModule\Administration\Components\FileBrowser\FileBrowserControlFactory;

/**
 * @author Josef Kříž <pepakriz@gmail.com>
 *
 * @secured
 */
class FilesPresenter extends BasePresenter
{

	/** @persistent */
	public $edit;

	/** @persistent */
	public $browserMode;

	/** @var FileBrowserControlFactory */
	protected $fileBrowserControlFactory;


	/**
	 * @param FileBrowserControlFactory $fileBrowserControlFactory
	 */
	public function inject(FileBrowserControlFactory $fileBrowserControlFactory)
	{
		$this->fileBrowserControlFactory = $fileBrowserControlFactory;
	}


	public function createComponentFileBrowser()
	{
		$control = $this->fileBrowserControlFactory->create();
		$control->setBrowserMode((bool)$this->browserMode);
		return $control;
	}


	/**
	 * @secured(privilege="show")
	 */
	public function actionDefault()
	{
	}


	/**
	 * @secured
	 */
	public function actionCreate()
	{
	}


	/**
	 * @secured
	 */
	public function actionEdit()
	{
	}


	/**
	 * @secured
	 */
	public function actionRemove()
	{
	}


	/**
	 * @secured(privilege="edit")
	 */
	public function handleSetParent($from, $to, $dropmode)
	{
		$dirRepository = $this->dirRepository;
		$fileRepository = $this->fileRepository;

		$fromType = substr($from, 0, 1);
		$from = substr($from, 2);

		$toType = substr($to, 0, 1);
		$to = substr($to, 2);

		$entity = $fromType == 'd' ? $dirRepository->find($from) : $fileRepository->find($from);
		$target = $toType == 'd' ? $dirRepository->find($to) : $fileRepository->find($to);

		if ($dropmode == "before" || $dropmode == "after") {
			$entity->setParent(
				$target->parent ? : NULL,
				TRUE,
				$dropmode == "after" ? $target : $target->previous
			);
		} else {
			$entity->setParent($target);
		}

		if ($fromType == 'd') {
			$dirRepository->save($entity);
		} else {
			$fileRepository->save($entity);
		}

		$this->flashMessage($this->translator->translate('File has been moved'), 'success');

		if (!$this->isAjax()) {
			$this->redirect('this');
		}
		$this['panel']->invalidateControl('content');
	}


	/**
	 * @secured(privilege="remove")
	 */
	public function handleDelete($key2)
	{
		$repository = substr($key2, 0, 1) == 'd' ? $this->dirRepository : $this->fileRepository;
		$repository->delete($repository->find(substr($key2, 2)));

		if (substr($key2, 0, 1) == 'd') {
			$this->flashMessage($this->translator->translate('Directory has been deleted'), 'success');
		} else {
			$this->flashMessage($this->translator->translate('File has been deleted'), 'success');
		}

		if (!$this->isAjax()) {
			$this->redirect('this');
		}
		$this->payload->url = $this->link('this');
		$this->invalidateControl('content');
		$this['panel']->invalidateControl('content');
	}


	public function beforeRender()
	{
		if ($this->browserMode) {
			$this->template->hideMenuItems = TRUE;
		}

		parent::beforeRender();
	}

}
