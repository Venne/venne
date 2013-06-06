<?php

/**
 * This file is part of the Venne:CMS (https://github.com/Venne)
 *
 * Copyright (c) 2011, 2012 Josef Kříž (http://www.josef-kriz.cz)
 *
 * For the full copyright and license information, please view
 * the file license.txt that was distributed with this source code.
 */

namespace CmsModule\Components\Grido;

use CmsModule\Components\Grido\Actions\CallbackAction;
use DoctrineModule\Repositories\BaseRepository;
use Grido\Components\Actions\Action;

/**
 * @author Josef Kříž <pepakriz@gmail.com>
 */
class Grid extends \Grido\Grid
{

	/**
	 * @return AdminGrid
	 */
	public function getAdminGrid()
	{
		return $this->parent;
	}


	/**
	 * @param string $name
	 * @param string $label
	 * @param string $type starting constants with Action::TYPE_
	 * @param string $destination - first param for method $presenter->link()
	 * @param array $args - second param for method $presenter->link()
	 * @return CallbackAction
	 */
	public function addAction($name, $label, $type = CallbackAction::TYPE_CALLBACK, $destination = NULL, array $args = NULL)
	{
		$action = new $type($this, $name, $label, $destination, $args);
		if (!$action instanceof Action) {
			throw new \InvalidArgumentException('Action must be inherited from \Grido\Components\Actions\Action.');
		}
		return $action;
	}
}
