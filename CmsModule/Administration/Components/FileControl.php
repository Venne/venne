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

use CmsModule\Components\Table\Form;
use CmsModule\Components\Table\TableControl;
use CmsModule\Content\Control;
use CmsModule\Content\Entities\DirEntity;
use CmsModule\Content\Entities\FileEntity;
use CmsModule\Content\Forms\DirFormFactory;
use CmsModule\Content\Forms\FileFormFactory;
use CmsModule\Content\Repositories\DirRepository;
use CmsModule\Content\Repositories\FileRepository;
use Doctrine\ORM\QueryBuilder;
use Nette\InvalidStateException;

/**
 * @author Josef Kříž <pepakriz@gmail.com>
 */
class FileControl extends Control
{

	/** @persistent */
	public $key;

	/** @persistent */
	public $edit;

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

	/** @var DirEntity|NULL */
	protected $root;


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
	 * @param DirEntity|NULL $root
	 */
	public function setRoot(DirEntity $root = NULL)
	{
		$this->root = $root;
	}


	/**
	 * @return DirEntity|NULL
	 */
	public function getRoot()
	{
		return $this->root;
	}


	protected function startup()
	{
		parent::startup();

		if (substr($this->key, 1, 1) == ':') {
			$this->key = substr($this->key, 2);
		}
	}


	protected function attached($presenter)
	{
		parent::attached($presenter);

		if ($this->root) {
			if ($this->key) {
				$entity = $this->dirRepository->find($this->key);
				$t = FALSE;
				while ($entity) {
					if ($entity->id === $this->key) {
						$t = TRUE;
						break;
					}
					$entity = $entity->parent;
				}

				if (!$t) {
					throw new InvalidStateException("Current directory is not children of root directory.");
				}
			} else {
				$this->key = $this->root->getId();
			}
		}
	}


	/**
	 * @secured(privilege="show")
	 */
	public function handleChangeDir()
	{
		if (!$this->presenter->isAjax()) {
			$this->redirect('this');
		}

		$this->payload->url = $this->link('this');
	}


	/**
	 * @secured(privilege="create")
	 */
	public function handleDir($id)
	{
		if (!$this->isAjax()) {
			$this['table-navbar']->redirect('click!', array('id' => $id));
		}

		$this->invalidateControl('content');
		$this['table-navbar']->handleClick($id);

		$this->payload->url = $this['table-navbar']->link('click!', array('id' => $id));
	}


	protected function createComponentAjaxFileUploader()
	{
		$_this = $this;

		$this->ajaxFileUploaderFactory->setParentDirectory($this->key ? $this->dirRepository->find($this->key) : NULL);

		$control = $this->ajaxFileUploaderFactory->invoke($this->template->basePath);
		$control->onSuccess[] = function () use ($_this) {
			$_this->invalidateControl('content');
		};
		$control->onError[] = function (AjaxFileUploaderControl $control) use ($_this) {
			foreach ($control->getErrors() as $e) {
				if ($e['class'] === 'Doctrine\DBAL\DBALException' && strpos($e['message'], 'Duplicate entry') !== false) {
					$_this->flashMessage($this->translator->translate('Duplicate entry'), 'warning');
				} else {
					$_this->flashMessage($e['message']);
				}
			}
			$_this->invalidateControl('content');
		};
		return $control;
	}


	protected function createComponentTable()
	{
		$_this = $this;
		$parent = $this->key;
		$dirRepository = $this->dirRepository;

		$table = $this->createTable();
		$table->setRepository($this->dirRepository);

		$dql = function ($parent) {
			return function (QueryBuilder $dql) use ($parent) {
				$dql->andWhere('a.invisible = :invisible')->setParameter('invisible', FALSE);
				if ($parent === NULL) {
					return $dql->andWhere('a.parent IS NULL');
				}
				return $dql->andWhere('a.parent = :par')->setParameter('par', $parent);
			};
		};

		// navbar
		$table->addButton('up', 'Up', 'arrow-up')->onClick[] = function () use ($_this, $dirRepository, $dql) {
			$parent = $dirRepository->find($_this->key)->getParent();

			if (!$_this->getPresenter()->isAjax()) {
				$_this->redirect('this', array('key' => $parent ? $parent->id : NULL));
			}

			$_this->getPresenter()->invalidateControl('content');
			$_this->getPresenter()->payload->url = $_this->link('this', array('key' => $parent ? $parent->id : NULL));
			$_this->key = $parent ? $parent->id : NULL;
			$_this['table']->setDql($dql($parent));
		};

		$table->setDql($dql($parent));

		return $table;
	}


	protected function createComponentFileTable()
	{
		$table = $this->createTable();
		$table->setRepository($this->fileRepository);

		$parent = $this->key;

		$table->setDql(function (QueryBuilder $dql) use ($parent) {
			$dql->andWhere('a.invisible = :invisible')->setParameter('invisible', FALSE);
			if ($parent === NULL) {
				return $dql->andWhere('a.parent IS NULL');
			}
			return $dql->andWhere('a.parent = :par')->setParameter('par', $parent);
		});

		$table->setNavbar();

		return $table;
	}


	/**
	 * @return \CmsModule\Components\Table\TableControl
	 */
	protected function createTable()
	{
		$_this = $this;
		$dirRepository = $this->dirRepository;

		$table = new TableControl;
		$table->setDefaultPerPage(99999999999);
		$table->setTemplateConfigurator($this->templateConfigurator);

		// forms
		$fileForm = $table->addForm($this->fileFormFactory, 'File', function () use ($dirRepository, $_this) {
			$entity = new FileEntity;
			$entity->setParent($_this->key ? $dirRepository->find($_this->key) : NULL);
			return $entity;
		}, Form::TYPE_LARGE);
		$dirForm = $table->addForm($this->dirFormFactory, 'Directory', function () use ($dirRepository, $_this) {
			$entity = new DirEntity;
			$entity->setParent($_this->key ? $dirRepository->find($_this->key) : NULL);
			return $entity;
		}, Form::TYPE_LARGE);

		$table->addButtonCreate('directory', 'New directory', $dirForm, 'folder-open');
		$table->addButtonCreate('upload', 'Upload file', $fileForm, 'upload');

		$table->addActionEdit('editDir', 'Edit', $dirForm);
		$table->addActionEdit('editFile', 'Edit', $fileForm);

		$table->setTemplateFile($this->filePath);

		return $table;
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
	}
}
