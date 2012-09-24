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
use CmsModule\Administration\AdministrationManager;
use Nette\Callback;

/**
 * @author Josef Kříž <pepakriz@gmail.com>
 *
 * @secured
 */
class PermissionsPresenter extends BasePresenter
{

	/** @persistent */
	public $id;

	/** @var AdministrationManager */
	protected $administrationManager;

	/** @var Callback */
	protected $form;


	/**
	 * @param BaseRepository $roleRepository
	 * @param Callback $form
	 */
	function __construct(AdministrationManager $administrationManager, Callback $form)
	{
		$this->administrationManager = $administrationManager;
		$this->form = $form;
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


	public function renderDefault()
	{
		$this->template->presenters = $this->administrationManager->getAdministrationPages();
	}

}
