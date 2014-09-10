<?php

/**
 * This file is part of the Venne:CMS (https://github.com/Venne)
 *
 * Copyright (c) 2011, 2012 Josef Kříž (http://www.josef-kriz.cz)
 *
 * For the full copyright and license information, please view
 * the file license.txt that was distributed with this source code.
 */

namespace Venne\System\Components;

use Venne\System\AdministrationManager;

/**
 * @author Josef Kříž <pepakriz@gmail.com>
 */
class CssControl extends \Venne\System\UI\Control
{

	/** @var \Venne\System\AdministrationManager */
	private $administrationManager;

	public function inject(AdministrationManager $administrationManager)
	{
		$this->administrationManager = $administrationManager;
	}

	public function render()
	{
		$this->template->files = $this->administrationManager->getCssFiles();
		$this->template->render();
	}

}
