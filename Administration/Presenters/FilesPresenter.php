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
use DoctrineModule\ORM\BaseRepository;
use Nette\Callback;

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

	/** @var Callback */
	protected $dirFormFactory;

	/** @var Callback */
	protected $fileFormFactory;


	function __construct(BaseRepository $fileRepository, BaseRepository $dirRepository, Callback $fileFormFactory, Callback $dirFormFactory)
	{
		$this->fileRepository = $fileRepository;
		$this->dirRepository = $dirRepository;
		$this->fileFormFactory = $fileFormFactory;
		$this->dirFormFactory = $dirFormFactory;
	}


	public function startup()
	{
		parent::startup();

		if (substr($this->key, 1, 1) == ':') {
			$this->key = substr($this->key, 2);
		}
	}


	public function createComponentTable()
	{
		$table = new \CmsModule\Components\TableControl;
		$table->setRepository($this->dirRepository);
		$parent = $this->key;
		$table->setDql(function(\Doctrine\ORM\QueryBuilder $dql) use ($parent)
		{
			$dql->andWhere('a.invisible = :invisible')->setParameter('invisible', false);
			if ($parent === NULL) {
				return $dql->andWhere('a.parent IS NULL');
			}
			return $dql->andWhere('a.parent = :par')->setParameter('par', $parent);
		});
		$table->setTemplateFile(__DIR__ . '/FileTable.latte');

		return $table;
	}


	public function createComponentFileTable()
	{
		$table = new \CmsModule\Components\TableControl;
		$table->setRepository($this->fileRepository);
		$parent = $this->key;
		$table->setDql(function(\Doctrine\ORM\QueryBuilder $dql) use ($parent)
		{
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

		$this->presenter->context->entityManager->flush();

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


	protected function createComponentDir($name, $key = NULL)
	{
		$repository = $this->presenter->context->cms->dirRepository;

		if ($key) {
			$entity = $this->dirRepository->find($key);
		} else {
			$entity = new \CmsModule\Content\Entities\DirEntity();
		}

		$form = $this->presenter->context->cms->createDirForm();
		$form->setEntity($entity);
		if ($this->key) {
			$form['parent']->setDefaultValue($this->dirRepository->find($this->key));
		}
		$form->onSuccess[] = function($form) use ($repository)
		{
			$repository->save($form->entity);

			if (!$form->presenter->isAjax()) {
				$form->presenter->redirect('this');
			}
		};
		return $form;
	}


	protected function createComponentFile($name, $key = NULL)
	{
		$repository = $this->presenter->context->cms->fileRepository;

		if ($key) {
			$entity = $this->fileRepository->find($key);
		} else {
			$entity = new \CmsModule\Content\Entities\FileEntity();
		}

		$form = $this->presenter->context->cms->createFileForm();
		$form->setEntity($entity);
		if ($this->key) {
			$form['parent']->setDefaultValue($this->dirRepository->find($this->key));
		}
		$form->onSuccess[] = function($form) use ($repository)
		{
			$file = $form['file']->value;
			if ($file->isOk()) {
				$form->entity->setFile($file);
				$form->entity->setName($file->name);
				$repository->save($form->entity);
			}
			$form->presenter->redirect('this');
		};
		return $form;
	}


	protected function createComponentFileEdit($name)
	{
		return $this->createComponentFile($name, $this->edit);
	}


	protected function createComponentDirEdit($name)
	{
		return $this->createComponentDir($name, $this->edit);
	}


	public function handleEdit($key2)
	{
		$type = substr($key2, 0, 1);
		$this->edit = substr($key2, 2);
		$this->template->edit = $type;
	}
}
