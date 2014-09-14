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

use Venne\Bridges\Grido\PropertyAccessors\SymfonyPropertyAccessor;

/**
 * @author Josef Kříž <pepakriz@gmail.com>
 */
class Grid extends \Grido\Grid
{

	/**
	 * @return \Venne\Bridges\Grido\PropertyAccessors\SymfonyPropertyAccessor
	 * @internal
	 */
	public function getPropertyAccessor()
	{
		if ($this->propertyAccessor === null) {
			$this->propertyAccessor = new SymfonyPropertyAccessor();
		}

		return $this->propertyAccessor;
	}

	/**
	 * @param string $name
	 * @param boolean $need
	 * @return \Grido\Components\Columns\Editable
	 */
	public function getColumn($name, $need = true)
	{
		return parent::getColumn($this->formatName($name), $need);
	}

	/**
	 * @param string $name
	 * @param string $label
	 * @return \Grido\Components\Columns\Text
	 */
	public function addColumnText($name, $label)
	{
		return parent::addColumnText($this->formatName($name), $label);
	}

	/**
	 * @param string $name
	 * @param string $label
	 * @return \Grido\Components\Columns\Email
	 */
	public function addColumnEmail($name, $label)
	{
		return parent::addColumnEmail($this->formatName($name), $label);
	}

	/**
	 * @param string $name
	 * @param string $label
	 * @return \Grido\Components\Columns\Href
	 */
	public function addColumnHref($name, $label)
	{
		return parent::addColumnHref($this->formatName($name), $label);
	}

	/**
	 * @param string $name
	 * @param string $label
	 * @param string $dateFormat
	 * @return \Grido\Components\Columns\Date
	 */
	public function addColumnDate($name, $label, $dateFormat = null)
	{
		return parent::addColumnDate($this->formatName($name), $label, $dateFormat);
	}

	/**
	 * @param string $name
	 * @param string $label
	 * @param int $decimals number of decimal points
	 * @param string $decPoint separator for the decimal point
	 * @param string $thousandsSep thousands separator
	 * @return \Grido\Components\Columns\Number
	 */
	public function addColumnNumber($name, $label, $decimals = null, $decPoint = null, $thousandsSep = null)
	{
		return parent::addColumnNumber($this->formatName($name), $label, $decimals, $decPoint, $thousandsSep);
	}

	/**
	 * @param string $name
	 * @return string
	 */
	private function formatName($name)
	{
		return str_replace('.', '__', $name);
	}

}
