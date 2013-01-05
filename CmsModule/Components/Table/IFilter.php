<?php

/**
 * This file is part of the Venne:CMS (https://github.com/Venne)
 *
 * Copyright (c) 2011, 2012 Josef Kříž (http://www.josef-kriz.cz)
 *
 * For the full copyright and license information, please view
 * the file license.txt that was distributed with this source code.
 */

namespace CmsModule\Components\Table;

use Doctrine\ORM\QueryBuilder;
use Venne;
use Nette\ComponentModel\IContainer;

/**
 * @author Josef Kříž <pepakriz@gmail.com>
 */
interface IFilter
{


	/**
	 * @param Column $column
	 */
	public function __construct(IColumn $column);


	/**
	 * @param \Venne\Forms\Container $form
	 * @return mixed
	 */
	public function getControl(Venne\Forms\Container $form);


	/**
	 * @param \Doctrine\ORM\QueryBuilder $dql
	 * @param $value
	 * @return \Doctrine\ORM\QueryBuilder
	 */
	public function setDql(QueryBuilder $dql, $value);
}
