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

/**
 * @author Josef Kříž <pepakriz@gmail.com>
 */
class TrayControl extends \Venne\System\UI\Control
{

	/** @var \Venne\System\AdministrationManager */
	private $administrationManager;

	/**
	 * @param \Venne\System\AdministrationManager $administrationManager
	 */
	public function __construct(AdministrationManager $administrationManager)
	{
		$this->administrationManager = $administrationManager;
	}

	/**
	 * @return string[]
	 */
	public function getTrayComponents()
	{
		return $this->administrationManager->getTrayWidgetManager()->getWidgetNames();
	}

	public function render()
	{
		$this->template->administrationManager = $this->administrationManager;
		$this->template->render();
	}

	/**
	 * @param string $name
	 * @return \Nette\Application\UI\Control
	 */
	protected function createComponent($name)
	{
		return $this->administrationManager->getTrayWidgetManager()->getWidget($name);
	}

}
