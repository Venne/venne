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

use Venne\System\Administration\Components\AjaxFileUploaderControl;
use Venne\System\Administration\Components\AjaxFileUploaderControlFactory;
use Venne\System\Content\Control;
use Venne\System\Content\Entities\BaseFileEntity;
use Venne\System\Content\Entities\DirEntity;
use Venne\System\Content\Entities\FileEntity;
use Venne\System\Content\Forms\DirFormFactory;
use Venne\System\Content\Forms\FileFormFactory;
use Venne\System\Content\Repositories\DirRepository;
use Venne\System\Content\Repositories\FileRepository;
use Doctrine\ORM\QueryBuilder;
use Nette\Application\BadRequestException;

/**
 * @author Josef Kříž <pepakriz@gmail.com>
 */
class FileBrowserControl extends Control
{

	/** @persistent */
	public $key;

	/** @var bool */
	protected $browserMode = FALSE;

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

	/** @var DirEntity|NULL */
	protected $root;


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


	/**
	 * @param boolean $browserMode
	 */
	public function setBrowserMode($browserMode)
	{
		$this->browserMode = $browserMode;
	}


	/**
	 * @return boolean
	 */
	public function getBrowserMode()
	{
		return $this->browserMode;
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

		if ($this->root && !$this->key) {
			$this->key = $this->root->getId();
		}

		if (!$this->checkCurrentDir()) {
			throw new BadRequestException;
		}
	}


	/**
	 * @return bool
	 */
	public function checkCurrentDir()
	{
		if ($this->root) {
			if ($this->key) {
				$entity = $this->getCurrentDir();
				$t = FALSE;
				while ($entity) {
					if ($entity->id === $this->root->id) {
						$t = TRUE;
						break;
					}
					$entity = $entity->parent;
				}

				if (!$t) {
					return FALSE;
				}
			} else {
				return FALSE;
			}
		}

		return TRUE;
	}


	/**
	 * @secured(privilege="show")
	 */
	public function handleChangeDir()
	{
		if (!$this->presenter->isAjax()) {
			$this->redirect('this');
		}

		$this->invalidateControl('content');

		if (!$this->browserMode) {
			$this->presenter->payload->url = $this->link('this');
		}
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

		if (!$this->browserMode) {
			$this->presenter->payload->url = $this['table-navbar']->link('click!', array('id' => $id));
		}
	}


	protected function createComponentAjaxFileUploader()
	{
		$_this = $this;

		$this->ajaxFileUploaderFactory->setParentDirectory($this->getCurrentDir());

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
		$table->setDefaultSort(array('name' => 'ASC'));

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
			$parent = $_this->getCurrentDir()->getParent();

			if (!$_this->getPresenter()->isAjax()) {
				$_this->redirect('this', array('key' => $parent ? $parent->id : NULL));
			}

			$_this->invalidateControl('content');
			$_this->key = $parent ? $parent->id : NULL;
			if (!$_this->checkCurrentDir()) {
				throw new BadRequestException;
			}

			if (!$_this->browserMode) {
				$_this->presenter->payload->url = $_this->link('this', array('key' => $parent ? $parent->id : NULL));
			}

			$_this['table']->setDql($dql($parent));
		};

		$table->setDql($dql($parent));

		return $table;
	}


	protected function createComponentFileTable()
	{
		$table = $this->createTable();
		$table->setRepository($this->fileRepository);
		$table->setDefaultSort(array('name' => 'ASC'));

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
	 * @return \Venne\System\Components\Table\TableControl
	 */
	protected function createTable()
	{
		/** @var FileControl $table */
		$table = $this->fileControlFactory->invoke();
		$table->setTemplateConfigurator($this->templateConfigurator);
		$table->getFileForm()->setEntityFactory($this->createFileEntity);
		$table->getDirForm()->setEntityFactory($this->createDirEntity);
		$table->setDefaultPerPage(99999999999);
		return $table;
	}


	/**
	 * @return FileEntity
	 */
	public function createFileEntity()
	{
		$entity = new FileEntity;
		return $this->configureFileEntity($entity);
	}


	/**
	 * @return DirEntity
	 */
	public function createDirEntity()
	{
		$entity = new DirEntity;
		return $this->configureFileEntity($entity);
	}


	/**
	 * @param BaseFileEntity $entity
	 * @return BaseFileEntity
	 */
	public function configureFileEntity(BaseFileEntity $entity)
	{
		if ($this->key) {
			$entity->setParent($this->getCurrentDir());
			$entity->copyPermission();
		}
		return $entity;
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

		if (!$this->presenter->isAjax()) {
			$this->redirect('this');
		}

		$this->invalidateControl('content');

		if (!$this->browserMode) {
			$this->presenter->payload->url = $this->link('this');
		}
	}


	/**
	 * @return null|DirEntity
	 */
	public function getCurrentDir()
	{
		return $this->key ? $this->dirRepository->find($this->key) : NULL;
	}
}
