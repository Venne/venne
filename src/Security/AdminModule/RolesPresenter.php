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

use Nette\Application\UI\Presenter;
use Venne\System\AdminPresenterTrait;

/**
 * @author Josef Kříž <pepakriz@gmail.com>
 *
 * @secured
 */
class RolesPresenter extends Presenter
{

	use AdminPresenterTrait;

	/** @var RolesTableFactory */
	private $rolesTableFactory;


	public function __construct(RolesTableFactory $rolesTableFactory)
	{
		$this->rolesTableFactory = $rolesTableFactory;
	}


	protected function createComponentTable()
	{
		return $this->rolesTableFactory->create();
	}

}
