<?php

/**
 * This file is part of the Venne:CMS (https://github.com/Venne)
 *
 * Copyright (c) 2011, 2012 Josef Kříž (http://www.josef-kriz.cz)
 *
 * For the full copyright and license information, please view
 * the file license.txt that was distributed with this source code.
 */

namespace CmsModule\Components\Table\Columns;

use CmsModule\Components\Table\TableControl;
use Nette\ComponentModel\Component;

/**
 * @author Josef Kříž <pepakriz@gmail.com>
 */
class DateTimeColumn extends BaseColumn
{

	public function __construct(TableControl $table, $name, $title)
	{
		parent::__construct($table, $name, $title);

		$_this = $this;
		$this->callback = function ($entity) use ($_this) {
			$column = $entity->{$_this->name};
			return $column ? $column->format('Y-m-d h:i:s') : '';
		};
	}
}
