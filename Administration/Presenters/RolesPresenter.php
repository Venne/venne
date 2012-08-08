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
 */
class RolesPresenter extends BasePresenter
{


	/** @persistent */
	public $id;



	public function startup()
	{
		parent::startup();

		$this->template->items = $this->context->cms->roleRepository->findBy(array("parent" => NULL));
	}



	public function createComponentForm($name)
	{
		$form = new \Venne\Application\UI\Form;
		$this->formRecursion($form, $this->template->items);
		$form->onSuccess[] = array($this, "handleSave");
		return $form;
	}



	public function createComponentFormSort($name)
	{
		$form = new \Venne\Application\UI\Form;
		$form->addHidden("hash");
		$form->addSubmit("Save", "Save")->onClick[] = array($this, "handleSortSave");
		return $form;
	}



	public function formRecursion($form, $menu)
	{
		if ($menu) {
			foreach ($menu as $item) {
				$form->addSubmit("settings_" . $item->id, "Settings");
				$form->addSubmit("delete_" . $item->id, "Delete")->getControlPrototype()->class = "grey";
				if ($item->childrens)
					$this->formRecursion($form, $item->childrens);
			}
		}
	}



	public function formSaveRecursion($form, $menu)
	{
		foreach ($menu as $key => $item) {
			if ($form["delete_" . $item->id]->isSubmittedBy()) {
				$this->context->cms->roleRepository->delete($this->context->cms->roleRepository->find($item->id));
				$this->flashMessage("Role has been deleted", "success");
				$this->redirect("this");
			}
			if ($form["settings_" . $item->id]->isSubmittedBy()) {
				$this->redirect("edit", array("id" => $item->id));
			}

			if ($item->childrens)
				$this->formSaveRecursion($form, $item->childrens);
		}
	}



	public function handleSave()
	{
		$this->formSaveRecursion($this["form"], $this->template->items);
	}



	public function handleSortSave()
	{
		$data = array();
		$val = $this["formSort"]->getValues();
		$hash = explode("&", $val["hash"]);
		foreach ($hash as $item) {
			$item = explode("=", $item);
			$depend = $item[1];
			if ($depend == "root")
				$depend = Null;
			$id = \substr($item[0], 5, -1);
			if (!isset($data[$depend]))
				$data[$depend] = array();
			$order = count($data[$depend]) + 1;
			$data[$depend][] = array("id" => $id, "order" => $order, "role_id" => $depend);
		}
		$this->context->cms->roleRepository->setStructure($data);
		$this->flashMessage("Structure has been saved.", "success");
		$this->redirect("this");
	}



	public function createComponentFormRole()
	{
		$repository = $this->context->cms->roleRepository;
		$entity = $repository->createNew();

		$form = $this->context->cms->createRoleForm();
		$form->setEntity($entity);
		$form->onSuccess[] = function($form) use ($repository) {
					try {
						$repository->save($form->entity);
						$form->getPresenter()->flashMessage("Role has been saved", "success");
					} catch (\DoctrineModule\ORM\SqlException $e) {
						if ($e->getCode() == 23000) {
							$form->presenter->flashMessage("Role {$form->entity->name} already exists", "warning");
							return;
						} else {
							throw $e;
						}
					}
					$form->getPresenter()->redirect("default");
				};
		return $form;
	}



	public function createComponentFormRoleEdit($name)
	{
		$repository = $this->context->cms->roleRepository;
		$entity = $repository->find($this->getParameter("id"));

		$form = $this->context->cms->createRoleForm();
		$form->setEntity($entity);
		$form->onSuccess[] = function($form) use ($repository) {
					try {
						$repository->save($form->entity);
						$form->getPresenter()->flashMessage("Role has been updated", "success");
					} catch (\DoctrineModule\ORM\SqlException $e) {
						if ($e->getCode() == 23000) {
							$form->presenter->flashMessage("User {$form->entity->name} already exists", "warning");
							return;
						} else {
							throw $e;
						}
					}
					$form->getPresenter()->redirect("this");
				};
		return $form;
	}

}
