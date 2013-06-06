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

use Doctrine\Common\EventArgs;
use Nette\Application\IPresenter;

/**
 * @author Josef Kříž <pepakriz@gmail.com>
 */
class RenderArgs extends EventArgs
{

	/** @var IPresenter */
	private $presenter;


	/**
	 * @param IPresenter $presenter
	 */
	public function setPresenter(IPresenter $presenter)
	{
		$this->presenter = $presenter;
	}


	/**
	 * @return IPresenter
	 */
	public function getPresenter()
	{
		return $this->presenter;
	}
}
