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
use CmsModule\Forms\UserFormFactory;

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

	/** @var UserFormFactory */
	protected $form;


	/**
	 * @param BaseRepository $userRepository
	 */
	public function __construct(BaseRepository $userRepository)
	{
		parent::__construct();

		$this->userRepository = $userRepository;
	}


	/**
	 * @param UserFormFactory $form
	 */
	public function injectForm(UserFormFactory $form)
	{
		$this->form = $form;
	}


	/**
	 * @secured(privilege="show")
	 */
	public function actionDefault()
	{
	}


	/**
	 * @secured(privilege="create")
	 */
	public function actionCreate()
	{
	}


	/**
	 * @secured(privilege="edit")
	 */
	public function actionEdit()
	{
	}


	/**
	 * @secured(privilege="edit")
	 */
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
		$table->setRepository($this->userRepository);
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
				$presenter->invalidateControl('content');
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
		$form = $this->form->createForm($this->userRepository->createNew());
		$form->onSuccess[] = $this->formProcess;
		return $form;
	}


	public function formProcess($form)
	{
		$this->flashMessage("User has been created", "success");

		if (!$this->isAjax()) {
			$this->redirect("default");
		}
		$this->payload->url = $this->link('default', array('id' => NULL));
		$this->forward('default', array('id' => NULL, 'do' => ''));
	}


	public function createComponentFormEdit()
	{
		$form = $this->form->createForm($this->userRepository->find($this->id));
		$form->onSuccess[] = $this->processFormEdit;
		return $form;
	}


	public function processFormEdit($form)
	{
		$this->flashMessage("User has been updated", "success");

		if (!$this->isAjax()) {
			$this->redirect("this");
		}
	}
}
