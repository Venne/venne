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
		$this->redirect("this");
	}

	
	public function createComponentTable()
	{
		$table = new \CmsModule\Components\TableControl;
		$table->setRepository($this->context->cms->userRepository);
		$table->setPaginator(2);
		
		$table->addColumn('email', 'E-mail', '60%');
		$table->addColumn('roles', 'Roles', '40%', function($entity){return implode(", ", $entity->roles);});
		
		$presenter = $this;
		$table->addAction('edit', 'Edit', function($entity) use ($presenter){
			$presenter->redirect('edit', array('id'=>$entity->id));
		});
		$table->addAction('delete', 'Delete', function($entity) use ($presenter){
			$presenter->redirect('translate', array('id'=>$entity->id));
		});
		
		return $table;
	}


	public function createComponentForm()
	{
		$repository = $this->userRepository;
		$entity = $this->userRepository->createNew();

		$form = $this->context->cms->createUserForm();
		$form->setEntity($entity);
		$form->addSubmit("_submit", "Save");
		$form->onSuccess[] = function($form) use ($repository)
		{
			$form->entity->enable = 1;
			try {
				$repository->save($form->entity);
				$form->presenter->flashMessage("User has been created", "success");
			} catch (\DoctrineModule\ORM\SqlException $e) {
				if ($e->getCode() == 23000) {
					$form->presenter->flashMessage("User {$form->entity->name} already exists", "warning");
					return ;
				} else {
					throw $e;
				}
			}
			$form->presenter->redirect("default");
		};
		return $form;
	}



	public function createComponentFormEdit()
	{
		$repository = $this->userRepository;
		$entity = $this->userRepository->find($this->getParameter("id"));

		$form = $this->context->cms->createUserForm();
		$form->setEntity($entity);
		$form->addSubmit("_submit", "Save");
		$form->onSuccess[] = function($form) use ($repository)
		{
			try {
				$repository->save($form->entity);
				$form->presenter->flashMessage("User has been updated", "success");
			} catch (\DoctrineModule\ORM\SqlException $e) {
				if ($e->getCode() == 23000) {
					$form->presenter->flashMessage("User {$form->entity->name} already exists", "warning");
					return ;
				} else {
					throw $e;
				}
			}
			$form->presenter->redirect("this");
		};
		return $form;
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
