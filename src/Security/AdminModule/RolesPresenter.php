<?php

/**
 * This file is part of the Venne:CMS (https://github.com/Venne)
 *
 * Copyright (c) 2011, 2012 Josef Kříž (http://www.josef-kriz.cz)
 *
 * For the full copyright and license information, please view
 * the file license.txt that was distributed with this source code.
 */

namespace Venne\Security\AdminModule;

/**
 * @author Josef Kříž <pepakriz@gmail.com>
 *
 * @secured
 */
class RolesPresenter extends \Nette\Application\UI\Presenter
{

	use \Venne\System\AdminPresenterTrait;

	/** @var \Venne\Security\AdminModule\RolesTableFactory */
	private $rolesTableFactory;

	public function __construct(RolesTableFactory $rolesTableFactory)
	{
		$this->rolesTableFactory = $rolesTableFactory;
	}

	protected function createComponentTable()
	{
		$admin = $this->rolesTableFactory->create();
		$form = $admin->getForm('role');
		$form->onSuccess[] = function () {
			$this->flashMessage('Role has been saved.', 'success');
			$this->redrawControl('flashes');
		};
		$form->onError[] = function () {
			$this->flashMessage('Failed.', 'warning');
			$this->redrawControl('flashes');
		};

		return $admin;
	}

}
