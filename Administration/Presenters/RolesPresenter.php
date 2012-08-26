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
class RolesPresenter extends BasePresenter
{

	/** @persistent */
	public $id;

	/** @var BaseRepository */
	protected $roleRepository;

	/** @var Callback */
	protected $form;


	/**
	 * @param BaseRepository $roleRepository
	 * @param Callback $form
	 */
	function __construct(BaseRepository $roleRepository, Callback $form)
	{
		$this->roleRepository = $roleRepository;
		$this->form = $form;
	}


	public function createComponentTable()
	{
		$table = new \CmsModule\Components\TableControl;
		$table->setRepository($this->roleRepository);
		$table->setPaginator(10);
		$table->enableSorter();

		$table->addColumn('name', 'Name', '40%');
		$table->addColumn('parent', 'Parents', '60%', function(\CmsModule\Security\Entities\RoleEntity $entity){
			$entities = array();
			$en = $entity;
			while(($en = $en->getParent())){
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
		$repository = $this->roleRepository;
		$entity = $repository->createNew();

		$form = $this->form->invoke();
		$form->setEntity($entity);
		$form['_submit']->onClick[] = $this->processForm;
		return $form;
	}


	public function processForm($button)
	{
		$form = $button->getForm();
		$repository = $this->roleRepository;

		try {
			$repository->save($form->entity);
			$form->getPresenter()->flashMessage("Role has been created", "success");
		} catch (\DoctrineModule\ORM\SqlException $e) {
			if ($e->getCode() == 23000) {
				$form->presenter->flashMessage("Role is not unique", "warning");
				if (!$this->isAjax()) {
					$this->redirect('this');
				}
				$this->invalidateControl('content');
				return;
			} else {
				throw $e;
			}
		} catch (\Nette\InvalidArgumentException $e) {
			$form->presenter->flashMessage($e->getMessage(), "warning");
			if (!$this->isAjax()) {
				$this->redirect('this');
			}
			$this->invalidateControl('content');
			return;
		}

		if (!$this->isAjax()) {
			$this->redirect("default");
		}
		$this->payload->url = $this->link('default', array('id' => NULL));
		$this->forward('default', array('id' => NULL, 'do' => ''));
	}


	public function createComponentFormEdit()
	{
		$repository = $this->roleRepository;
		$entity = $repository->find($this->id);

		$form = $this->form->invoke();
		$form->setEntity($entity);
		$form['_submit']->onClick[] = $this->processFormEdit;
		return $form;
	}


	public function processFormEdit($button)
	{
		$form = $button->getForm();
		$repository = $this->roleRepository;

		try {
			$repository->save($form->entity);
			$form->getPresenter()->flashMessage("Role has been saved", "success");
		} catch (\DoctrineModule\ORM\SqlException $e) {
			if ($e->getCode() == 23000) {
				$form->presenter->flashMessage("Role is not unique", "warning");
				if (!$this->isAjax()) {
					$this->redirect('this');
				}
				$this->invalidateControl('content');
				return;
			} else {
				throw $e;
			}
		} catch (\Nette\InvalidArgumentException $e) {
			$form->presenter->flashMessage($e->getMessage(), "warning");
			if (!$this->isAjax()) {
				$this->redirect('this');
			}
			$this->invalidateControl('content');
			return;
		}

		if (!$this->isAjax()) {
			$this->redirect("default");
		}
		$this->payload->url = $this->link('default', array('id' => NULL));
		$this->forward('default', array('id' => NULL, 'do' => ''));
	}


	public function delete($entity)
	{
		$repository = $this->roleRepository;
		$repository->delete($entity);

		$this->flashMessage("Role has been deleted", "success");

		if (!$this->isAjax()) {
			$this->redirect("this", array("id" => NULL));
		}
		$this->invalidateControl('content');
	}
}
