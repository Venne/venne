<?php

/**
 * This file is part of the Venne:CMS (https://github.com/Venne)
 *
 * Copyright (c) 2011, 2012 Josef Kříž (http://www.josef-kriz.cz)
 *
 * For the full copyright and license information, please view
 * the file license.txt that was distributed with this source code.
 */

namespace CmsModule\Components;

use CmsModule\Content\Control;
use CmsModule\Content\Entities\ExtendedRouteEntity;
use CmsModule\Content\Entities\RouteEntity;
use Nette\InvalidArgumentException;

/**
 * @author Josef Kříž <pepakriz@gmail.com>
 */
class ItemInfoControl extends Control
{

	public function renderDefault($args = NULL)
	{
		if (is_array($args)) {
			$this->template->route = $args[0];
			$this->template->hideTags = (isset($args['tags']) && !$args['tags']);
			$this->template->hideAuthor = (isset($args['author']) && !$args['author']);
			$this->template->hideDates = (isset($args['dates']) && !$args['dates']);
		} else {
			$this->template->route = $args;
			$this->template->hideTags = FALSE;
			$this->template->hideAuthor = FALSE;
			$this->template->hideDates = FALSE;
		}
	}
}
