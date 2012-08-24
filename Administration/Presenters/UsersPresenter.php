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

/**
 * @author Josef Kříž <pepakriz@gmail.com>
 *
 * @secured
 * @persistent (vp)
 */
class UsersPresenter extends BasePresenter
{


	/** @persistent */
	public $id;

	/** @persistent */
	public $page;

	/** @var \DoctrineModule\ORM\BaseRepository */
	protected $userRepository;


	public function startup()
	{
		parent::startup();
		$this->userRepository = $this->context->cms->userRepository;
	}


	public function handleDelete($id)
	{
		$this->userRepository->delete($this->userRepository->find($id));
		$this->flashMessage("User has been deleted", "success");

		if (!$this->isAjax()) {
			$this->redirect('default');
		}
		$this->invalidateControl('content');
	}


	public function createComponentTable()
	{
		$table = new \CmsModule\Components\TableControl;
		$table->setRepository($this->context->cms->userRepository);
		$table->setPaginator(2);

		$table->addColumn('email', 'E-mail', '60%');
		$table->addColumn('roles', 'Roles', '40%', function($entity)
		{
			return implode(", ", $entity->roles);
		});

		$presenter = $this;
		$table->addAction('edit', 'Edit', function($entity) use ($presenter)
		{
			if (!$presenter->isAjax()) {
				$presenter->redirect('edit', array('id' => $entity->id));
			} else {
				$presenter->payload->url = $presenter->link('edit', array('id' => $entity->id));
				$presenter->setView('edit');
				$presenter->id = $entity->id;
			}
		});
		$table->addAction('delete', 'Delete', function($entity) use ($presenter)
		{
			$presenter->redirect('translate', array('id' => $entity->id));
		});

		return $table;
	}


	public function createComponentForm()
	{
		$repository = $this->userRepository;
		$entity = $repository->createNew();

		$form = $this->context->cms->createUserForm();
		$form->setEntity($entity);
		$form->addSubmit("_submit", "Save");
		$form->onSuccess[] = $this->formProcess;
		return $form;
	}


	public function formProcess($form)
	{
		$repository = $this->userRepository;

		$form->entity->enable = 1;
		try {
			$repository->save($form->entity);
			$this->flashMessage("User has been created", "success");
		} catch (\DoctrineModule\ORM\SqlException $e) {
			if ($e->getCode() == 23000) {
				$this->flashMessage("User {$form->entity->name} already exists", "warning");
				return;
			} else {
				throw $e;
			}
		}

		if (!$this->isAjax()) {
			$this->redirect("default");
		}
		$this->payload->url = $this->link('default', array('id' => NULL));
		$this->forward('default', array('id' => NULL, 'do' => ''));
	}


	public function createComponentFormEdit()
	{
		$repository = $this->userRepository;
		$entity = $repository->find($this->getParameter("id"));

		$form = $this->context->cms->createUserForm();
		$form->setEntity($entity);
		$form->addSubmit("_submit", "Save");
		$form->onSuccess[] = $this->processFormEdit;
		return $form;
	}


	public function processFormEdit($form)
	{
		$repository = $this->userRepository;

		try {
			$repository->save($form->entity);
			$this->flashMessage("User has been updated", "success");
		} catch (\DoctrineModule\ORM\SqlException $e) {
			if ($e->getCode() == 23000) {
				$this->flashMessage("User {$form->entity->name} already exists", "warning");
				return;
			} else {
				throw $e;
			}
		}

		if (!$this->isAjax()) {
			$this->redirect("this");
		}
	}


	public function createComponentVp()
	{
		$vp = new \CmsModule\Components\VisualPaginator;
		$pg = $vp->getPaginator();
		$pg->setItemsPerPage(20);
		$pg->setItemCount($this->userRepository->createQueryBuilder("a")->select("COUNT(a.id)")->getQuery()->getSingleScalarResult());
		return $vp;
	}


	public function renderDefault()
	{
		$this->template->userRepository = $this->userRepository;
	}
}
