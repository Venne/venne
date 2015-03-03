<?php

/**
 * This file is part of the Venne:CMS (https://github.com/Venne)
 *
 * Copyright (c) 2011, 2012 Josef Kříž (http://www.josef-kriz.cz)
 *
 * For the full copyright and license information, please view
 * the file license.txt that was distributed with this source code.
 */

namespace Venne\Security\AdminModule\Role;

use Venne\Forms\IFormFactory;
use Venne\Security\Role\Form\RoleContainerFactory;
use Venne\Security\Role\Role;
use Venne\Security\Role\RoleFacade;
use Venne\Security\Role\RoleMapper;
use Venne\System\UI\Control;

/**
 * @author Josef Kříž <pepakriz@gmail.com>
 *
 * @method onSave(\Venne\Security\AdminModule\Role\RoleControl $control, \Nette\Application\UI\Form $form)
 */
class RoleControl extends Control
{

	/** @var \Closure[] */
	public $onSave = array();

	/** @var int|null */
	private $roleId;

	/** @var \Venne\Forms\FormFactory */
	private $formFactory;

	/** @var \Venne\Security\Role\RoleFacade */
	private $roleFacade;

	/** @var \Venne\Security\Role\Form\RoleContainerFactory */
	private $roleContainerFactory;

	/** @var \Venne\Security\Role\RoleMapper */
	private $roleMapper;

	/**
	 * @param int|null $roleId
	 * @param \Venne\Forms\IFormFactory $formFactory
	 * @param \Venne\Security\Role\RoleFacade $roleFacade
	 * @param \Venne\Security\Role\Form\RoleContainerFactory $roleContainerFactory
	 * @param \Venne\Security\Role\RoleMapper $roleMapper
	 */
	public function __construct(
		$roleId,
		IFormFactory $formFactory,
		RoleFacade $roleFacade,
		RoleContainerFactory $roleContainerFactory,
		RoleMapper $roleMapper
	) {
		$this->roleId = $roleId !== null ? (int) $roleId : null;
		$this->formFactory = $formFactory;
		$this->roleFacade = $roleFacade;
		$this->roleContainerFactory = $roleContainerFactory;
		$this->roleMapper = $roleMapper;
	}

	/**
	 * @return \Nette\Application\UI\Form
	 */
	protected function createComponentForm()
	{
		$form = $this->formFactory->create();
		$form['role'] = $roleContainer = $this->roleContainerFactory->create();

		$roleContainer->addName();
		$roleContainer->addParent();
		$roleContainer->addChildren();

		if ($this->roleId !== null) {
			$roleContainer->setDefaults($this->roleMapper->load(
				$this->roleFacade->getById($this->roleId)
			));
		}

		$form->addSubmit('_submit', 'Save')->onClick[] = function () use ($form, $roleContainer) {
			$role = $this->roleId !== null
				? $this->roleFacade->getById($this->roleId)
				: new Role('default');
			$this->roleMapper->save($role, $roleContainer->getValues(true));
			$this->roleFacade->saveRole($role);

			$this->onSave($this, $form);
			$this->redirect('this');
		};

		return $form;
	}

	public function render()
	{
		echo $this['form'];
	}

}
