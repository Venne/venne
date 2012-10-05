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

use Venne;
use DoctrineModule\Repositories\BaseRepository;
use CmsModule\Content\Forms\FileFormFactory;
use CmsModule\Content\Forms\DirFormFactory;

/**
 * @author Josef Kříž <pepakriz@gmail.com>
 *
 * @secured
 */
class FilesPresenter extends BasePresenter
{
	/** @persistent */
	public $key;

	/** @persistent */
	public $edit;

	/** @persistent */
	public $browserMode;

	/** @var BaseRepository */
	protected $dirRepository;

	/** @var BaseRepository */
	protected $fileRepository;

	/** @var DirFormFactory */
	protected $dirFormFactory;

	/** @var FileFormFactory */
	protected $fileFormFactory;


	function __construct(BaseRepository $fileRepository, BaseRepository $dirRepository)
	{
		$this->fileRepository = $fileRepository;
		$this->dirRepository = $dirRepository;
	}


	public function injectFileForm(FileFormFactory $fileForm)
	{
		$this->fileFormFactory = $fileForm;
	}


	public function injectDirForm(DirFormFactory $dirForm)
	{
		$this->dirFormFactory = $dirForm;
	}


	public function startup()
	{
		parent::startup();

		if (substr($this->key, 1, 1) == ':') {
			$this->key = substr($this->key, 2);
		}
	}


	/**
	 * @secured(privilege="show")
	 */
	public function actionDefault()
	{
	}


	public function handleDir($id)
	{
		if (!$this->isAjax()) {
			$this['table-navbar']->redirect('click!', array('id' => $id));
		}

		$this->invalidateControl('content');
		$this['table-navbar']->handleClick($id);

		$this->payload->url = $this['table-navbar']->link('click!', array('id' => $id));
	}


	public function createComponentTable()
	{
		$_this = $this;
		$dirRepository = $this->dirRepository;

		$table = new \CmsModule\Components\Table\TableControl;
		$table->setTemplateConfigurator($this->templateConfigurator);
		$table->setRepository($this->dirRepository);
		$parent = $this->key;

		$dql = function ($parent) {
			return function (\Doctrine\ORM\QueryBuilder $dql) use ($parent) {
				$dql->andWhere('a.invisible = :invisible')->setParameter('invisible', false);
				if ($parent === NULL) {
					return $dql->andWhere('a.parent IS NULL');
				}
				return $dql->andWhere('a.parent = :par')->setParameter('par', $parent);
			};
		};

		// forms
		$fileForm = $table->addForm($this->fileFormFactory, 'File', function () use ($dirRepository, $_this) {
			$entity = new \CmsModule\Content\Entities\FileEntity();
			$entity->setParent($_this->key ? $dirRepository->find($_this->key) : NULL);
			return $entity;
		});
		$dirForm = $table->addForm($this->dirFormFactory, 'Directory', function () use ($dirRepository, $_this) {
			$entity = new \CmsModule\Content\Entities\DirEntity();
			$entity->setParent($_this->key ? $dirRepository->find($_this->key) : NULL);
			return $entity;
		});

		// navbar
		$table->addButton('up', 'Up', 'arrow-up')->onClick[] = function ($button) use ($_this, $dirRepository, $dql) {
			$parent = $dirRepository->find($_this->key)->getParent();

			if (!$_this->getPresenter()->isAjax()) {
				$_this->redirect('this', array('key' => $parent ? $parent->id : NULL));
			}

			$_this->getPresenter()->invalidateControl('content');
			$_this->getPresenter()->payload->url = $_this->link('this', array('key' => $parent ? $parent->id : NULL));
			$_this->key = $parent ? $parent->id : NULL;
			$_this['table']->setDql($dql($parent));
		};
		$table->addButtonCreate('directory', 'New directory', $dirForm, 'folder-open');
		$table->addButtonCreate('upload', 'Upload file', $fileForm, 'upload');

		$table->setDql($dql($parent));
		$table->setTemplateFile(__DIR__ . '/FileTable.latte');

		return $table;
	}


	public function createComponentFileTable()
	{
		$table = new \CmsModule\Components\Table\TableControl;
		$table->setRepository($this->fileRepository);
		$parent = $this->key;
		$table->setDql(function (\Doctrine\ORM\QueryBuilder $dql) use ($parent) {
			$dql->andWhere('a.invisible = :invisible')->setParameter('invisible', false);
			if ($parent === NULL) {
				return $dql->andWhere('a.parent IS NULL');
			}
			return $dql->andWhere('a.parent = :par')->setParameter('par', $parent);
		});
		$table->setTemplateFile(__DIR__ . '/FileTable.latte');

		return $table;
	}


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
				true,
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

		$this->flashMessage('File has been moved', 'success');

		if (!$this->isAjax()) {
			$this->redirect('this');
		}
		$this['panel']->invalidateControl('content');
	}


	public function handleDelete($key2)
	{
		$repository = substr($key2, 0, 1) == 'd' ? $this->dirRepository : $this->fileRepository;
		$repository->delete($repository->find(substr($key2, 2)));

		$this->flashMessage("Page has been deleted", "success");

		if (!$this->isAjax()) {
			$this->redirect("this");
		}
		$this->invalidateControl('content');
		$this['panel']->invalidateControl('content');
	}


	public function beforeRender()
	{
		parent::beforeRender();

		if ($this->browserMode) {
			$this->template->hideMenuItems = true;
		}
	}


	public function renderDefault()
	{
		$this->template->dirRepository = $this->dirRepository;
	}


	public function handleEdit($key2)
	{
		$type = substr($key2, 0, 1);
		$this->edit = substr($key2, 2);
		$this->template->edit = $type;
	}
}
