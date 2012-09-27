<?php

/**
 * This file is part of the Venne:CMS (https://github.com/Venne)
 *
 * Copyright (c) 2011, 2012 Josef Kříž (http://www.josef-kriz.cz)
 *
 * For the full copyright and license information, please view
 * the file license.txt that was distributed with this source code.
 */

namespace CmsModule\Events;

use Nette;

/**
 * @author Josef Kříž <pepakriz@gmail.com>
 */
class RenderArgs extends \Doctrine\Common\EventArgs
{


	/** @var \Nette\Application\UI\Presenter */
	private $presenter;


	/**
	 * @param \Nette\Application\UI\Presenter $presenter
	 */
	public function setPresenter($presenter)
	{
		$this->presenter = $presenter;
	}


	/**
	 * @return \Nette\Application\UI\Presenter
	 */
	public function getPresenter()
	{
		return $this->presenter;
	}
}
