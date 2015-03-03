<?php

/**
 * This file is part of the Venne:CMS (https://github.com/Venne)
 *
 * Copyright (c) 2011, 2012 Josef Kříž (http://www.josef-kriz.cz)
 *
 * For the full copyright and license information, please view
 * the file license.txt that was distributed with this source code.
 */

namespace Venne\Security\Role\Form;

use Nette\Forms\Container;
use Venne\Security\Role\RoleFacade;
use Venne\System\Registration\LoginProviderMode;

/**
 * @author Josef Kříž <pepakriz@gmail.com>
 */
class RoleContainer extends Container
{

	/** @var \Venne\Security\Role\RoleFacade */
	private $roleFacade;

	public function __construct(
		RoleFacade $roleFacade
	) {
		parent::__construct();

		$this->roleFacade = $roleFacade;
	}

	/**
	 * @return \Nette\Forms\Controls\TextInput
	 */
	public function addName()
	{
		return $this->addText('name', 'Name');
	}

	/**
	 * @return \Nette\Forms\Controls\SelectBox
	 */
	public function addParent()
	{
		return $this->addSelect('parent', 'Parent', $this->roleFacade->getRoleOptions())
			->setPrompt('');
	}

	/**
	 * @return \Nette\Forms\Controls\MultiSelectBox
	 */
	public function addChildren()
	{
		return $this->addMultiSelect('children', 'Children', $this->roleFacade->getRoleOptions());
	}

}
