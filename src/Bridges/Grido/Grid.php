<?php

/**
 * This file is part of the Venne:CMS (https://github.com/Venne)
 *
 * Copyright (c) 2011, 2012 Josef Kříž (http://www.josef-kriz.cz)
 *
 * For the full copyright and license information, please view
 * the file license.txt that was distributed with this source code.
 */

namespace Venne\Bridges\Grido;

use Grido\PropertyAccessors\SymfonyPropertyAccessor;

/**
 * @author Josef Kříž <pepakriz@gmail.com>
 */
class Grid extends \Grido\Grid
{

	/**
	 * @return \Grido\PropertyAccessors\SymfonyPropertyAccessor
	 */
	public function getPropertyAccessor()
	{
		if ($this->propertyAccessor === null) {
			$this->propertyAccessor = new SymfonyPropertyAccessor();
		}

		return $this->propertyAccessor;
	}

}
