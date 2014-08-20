<?php

/**
 * This file is part of the Venne:CMS (https://github.com/Venne)
 *
 * Copyright (c) 2011, 2012 Josef Kříž (http://www.josef-kriz.cz)
 *
 * For the full copyright and license information, please view
 * the file license.txt that was distributed with this source code.
 */

namespace Venne\System\AdminModule\Components;

use Venne\System\AdministrationManager;
use Venne\System\UI\Control;

/**
 * @author Josef Kříž <pepakriz@gmail.com>
 */
class TrayControl extends Control
{

	/** @var AdministrationManager */
	private $administrationManager;


	/**
	 * @param AdministrationManager $administrationManager
	 */
	public function __construct(AdministrationManager $administrationManager)
	{
		$this->administrationManager = $administrationManager;
	}


	public function getTrayComponents()
	{
		return $this->administrationManager->getTrayWidgetManager()->getWidgetNames();
	}


	public function render()
	{
		$this->template->administrationManager = $this->administrationManager;
		$this->template->render();
	}


	protected function createComponent($name)
	{
		return $this->administrationManager->getTrayWidgetManager()->getWidget($name);
	}

}
