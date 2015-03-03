<?php

/**
 * This file is part of the Venne:CMS (https://github.com/Venne)
 *
 * Copyright (c) 2011, 2012 Josef Kříž (http://www.josef-kriz.cz)
 *
 * For the full copyright and license information, please view
 * the file license.txt that was distributed with this source code.
 */

namespace Venne\System\Components;

/**
 * @author Josef Kříž <pepakriz@gmail.com>
 */
class Section extends \Nette\ComponentModel\Container implements \ArrayAccess
{

	/** @var callable[] */
	public $onClick;

	/** @var string */
	protected $caption;

	/** @var string|null */
	protected $icon;

	/** @var string */
	public static $idMask = '%s-%s';

	/** @var string */
	private $id;

	public function __construct($caption, $icon = null)
	{
		parent::__construct();

		$this->caption = $caption;
		$this->icon = $icon;
	}

	/**
	 * @param string $caption
	 * @return $this
	 */
	public function setCaption($caption)
	{
		$this->caption = $caption;

		return $this;
	}

	/**
	 * @return string
	 */
	public function getCaption()
	{
		return $this->caption;
	}

	/**
	 * @param string|null $icon
	 * @return $this
	 */
	public function setIcon($icon)
	{
		$this->icon = $icon;

		return $this;
	}

	/**
	 * @return string|null
	 */
	public function getIcon()
	{
		return $this->icon;
	}

	/**
	 * @param string $name
	 * @param string $label
	 * @param string|null $icon
	 * @return \Venne\System\Components\Section
	 */
	public function addSection($name, $label, $icon = null)
	{
		return $this[$name] = new Section($label, $icon);
	}

	/**
	 * @param string $name
	 * @return \Venne\System\Components\Section
	 */
	public function getSection($name)
	{
		return $this[$name];
	}

	/**
	 * Returns navbar.
	 *
	 * @param bool
	 * @return \Venne\System\Components\NavbarControl
	 */
	public function getNavbar($need = true)
	{
		return $this->lookup('Venne\System\Components\NavbarControl', $need);
	}

	/**
	 * Returns control's HTML id.
	 *
	 * @return string
	 */
	public function getId()
	{
		if ($this->id === false) {
			return null;
		} elseif ($this->id === null) {
			$this->id = sprintf(self::$idMask, $this->getNavbar()->getName(), $this->lookupPath('Venne\System\Components\NavbarControl'));
		}

		return $this->id;
	}

	/********************* interface \ArrayAccess ****************d*g**/

	/**
	 * @param string $name
	 * @param \Nette\ComponentModel\IComponent $component
	 */
	final public function offsetSet($name, $component)
	{
		$this->addComponent($component, $name);
	}

	/**
	 * @param string $name
	 * @return \Nette\ComponentModel\IComponent
	 */
	final public function offsetGet($name)
	{
		return $this->getComponent($name, true);
	}

	/**
	 * @param string $name
	 * @return bool
	 */
	final public function offsetExists($name)
	{
		return $this->getComponent($name, false) !== null;
	}

	/**
	 * @param string $name
	 */
	final public function offsetUnset($name)
	{
		$component = $this->getComponent($name, false);
		if ($component !== null) {
			$this->removeComponent($component);
		}
	}

}
