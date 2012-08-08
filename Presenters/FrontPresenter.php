<?php

/**
 * This file is part of the Venne:CMS (https://github.com/Venne)
 *
 * Copyright (c) 2011, 2012 Josef Kříž (http://www.josef-kriz.cz)
 *
 * For the full copyright and license information, please view
 * the file license.txt that was distributed with this source code.
 */

namespace CmsModule\Presenters;

use Venne;

/**
 * @author Josef Kříž <pepakriz@gmail.com>
 */
class FrontPresenter extends BasePresenter
{

	const MODE_NORMAL = 0;

	const MODE_MODULE = 1;

	const MODE_LAYOUT = 2;

	const MODE_ELEMENTS = 3;

	/** @persistent */
	public $mode = 0;



	public function checkRequirements($element)
	{
		if ($this->mode != self::MODE_NORMAL && !$this->user->isLoggedIn()) {
			throw new \Nette\Application\ForbiddenRequestException;
		}
		parent::checkRequirements($element);
	}



	public function isModeNormal()
	{
		return ($this->mode == self::MODE_NORMAL);
	}



	public function isModeLayout()
	{
		return ($this->mode == self::MODE_LAYOUT);
	}



	public function isModeModule()
	{
		return ($this->mode == self::MODE_MODULE);
	}



	public function isModeElements()
	{
		return ($this->mode == self::MODE_ELEMENTS);
	}

}

