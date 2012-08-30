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
use Nette\Callback;
use CmsModule\Forms\RoleFormFactory;
use CmsModule\Forms\PermissionsFormFactory;

/**
 * @author Josef Kříž <pepakriz@gmail.com>
 *
 * @secured
 */
class RolesPresenter extends BasePresenter
{

	/** @persistent */
	public $id;

	/** @var BaseRepository */
	protected $roleRepository;

	/** @var RoleFormFactory */
	protected $roleForm;

	/** @var Callback */
	protected $permissionsForm;


	/**
	 * @param BaseRepository $roleRepository
	 * @param PermissionsFormFactory $permissionsForm
	 */
	function __construct(BaseRepository $roleRepository , PermissionsFormFactory $permissionsForm)
	{
		$this->roleRepository = $roleRepository;
		$this->permissionsForm = $permissionsForm;
	}


	public function injectRoleForm(RoleFormFactory $roleForm)
	{
		$this->roleForm = $roleForm;
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


	public function createComponentTable()
	{
		$table = new \CmsModule\Components\TableControl;
		$table->setRepository($this->roleRepository);
		$table->setPaginator(10);
		$table->enableSorter();

		$table->addColumn('name', 'Name', '40%');
		$table->addColumn('parent', 'Parents', '60%', function(\CmsModule\Security\Entities\RoleEntity $entity)
		{
			$entities = array();
			$en = $entity;
			while (($en = $en->getParent())) {
				$entities[] = $en->getName();
			}

			return implode(', ', $entities);
		});

		$presenter = $this;
		$table->addAction('edit', 'Edit', function($entity) use ($presenter)
		{
			if (!$presenter->isAjax()) {
				$presenter->redirect('edit', array('id' => $entity->id));
			}
			$this->invalidateControl('content');
			$presenter->payload->url = $presenter->link('edit', array('id' => $entity->id));
			$presenter->setView('edit');
			$presenter->id = $entity->id;
		});
		$table->addAction('permissions', 'Permissions', function($entity) use ($presenter)
		{
			if (!$presenter->isAjax()) {
				$presenter->redirect('permissions', array('id' => $entity->id));
			}
			$this->invalidateControl('content');
			$presenter->payload->url = $presenter->link('permissions', array('id' => $entity->id));
			$presenter->setView('permissions');
			$presenter->id = $entity->id;
		});
		$table->addAction('delete', 'Delete', function($entity) use ($presenter)
		{
			$presenter->delete($entity);
			if (!$presenter->isAjax()) {
				$presenter->redirect('default', array('id' => NULL));
			} else {
				$presenter->payload->url = $presenter->link('default', array('id' => NULL));
			}
		});

		$table->addGlobalAction('delete', 'Delete', function($entity) use ($presenter)
		{
			$presenter->delete($entity);
		});

		return $table;
	}


	public function createComponentForm()
	{
		$form = $this->roleForm->invoke($this->roleRepository->createNew());
		$form->onSuccess[] = $this->processForm;
		return $form;
	}


	public function processForm($button)
	{
		$this->flashMessage("Role has been created", "success");

		if (!$this->isAjax()) {
			$this->redirect("default");
		}
		$this->payload->url = $this->link('default', array('id' => NULL));
		$this->forward('default', array('id' => NULL, 'do' => ''));
	}


	public function createComponentFormEdit()
	{
		$form = $this->roleForm->invoke($this->roleRepository->find($this->id));
		$form->onSuccess[] = $this->processFormEdit;
		return $form;
	}


	public function processFormEdit($button)
	{
		$this->flashMessage("Role has been saved", "success");

		if (!$this->isAjax()) {
			$this->redirect("default");
		}
		$this->payload->url = $this->link('default', array('id' => NULL));
		$this->forward('default', array('id' => NULL, 'do' => ''));
	}


	public function createComponentPermissionsForm()
	{
		$form = $this->permissionsForm->invoke($this->roleRepository->find($this->id));
		$form->onSuccess[] = $this->processFormEdit;
		return $form;
	}


	public function delete($entity)
	{
		$this->roleRepository->delete($entity);

		$this->flashMessage("Role has been deleted", "success");

		if (!$this->isAjax()) {
			$this->redirect("this", array("id" => NULL));
		}
		$this->invalidateControl('content');
	}
}
