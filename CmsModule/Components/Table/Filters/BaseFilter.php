<?php

/**
 * This file is part of the Venne:CMS (https://github.com/Venne)
 *
 * Copyright (c) 2011, 2012 Josef Kříž (http://www.josef-kriz.cz)
 *
 * For the full copyright and license information, please view
 * the file license.txt that was distributed with this source code.
 */

namespace CmsModule\Components\Table\Filters;

use CmsModule\Components\Table\IColumn;
use CmsModule\Components\Table\IFilter;
use Doctrine\ORM\QueryBuilder;
use Nette\ComponentModel\Component;
use Venne\Forms\Container;

/**
 * @author Josef Kříž <pepakriz@gmail.com>
 */
class BaseFilter extends Component implements IFilter
{

	/** @var IColumn */
	protected $column;


	/**
	 * @param IColumn $column
	 */
	public function __construct(IColumn $column)
	{
		parent::__construct();

		$this->column = $column;
	}


	public function getControl(Container $form)
	{
		$form->addText($this->column->name);
	}


	public function setDql(QueryBuilder $dql, $value)
	{
		return $dql->andWhere('a.' . $this->column->name . ' LIKE :column_' . $this->column->name)->setParameter('column_' . $this->column->name, "%{$value}%");
	}
}
