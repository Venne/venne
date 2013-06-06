<?php

/**
 * This file is part of the Venne:CMS (https://github.com/Venne)
 *
 * Copyright (c) 2011, 2012 Josef Kříž (http://www.josef-kriz.cz)
 *
 * For the full copyright and license information, please view
 * the file license.txt that was distributed with this source code.
 */

namespace CmsModule\Components\Navbar;


/**
 * @author Josef Kříž <pepakriz@gmail.com>
 */
class Section extends \Nette\ComponentModel\Container implements \ArrayAccess
{

	/** @var array */
	public $onClick;

	/** @var string */
	protected $caption;

	/** @var string */
	protected $icon;

	/** @var string */
	public static $idMask = '%s-%s';

	/** @var string */
	private $id;


	public function __construct($caption, $icon = NULL)
	{
		parent::__construct();

		$this->caption = $caption;
		$this->icon = $icon;
	}


	/**
	 * @param string $caption
	 */
	public function setCaption($caption)
	{
		$this->caption = $caption;
	}


	/**
	 * @return string
	 */
	public function getCaption()
	{
		return $this->caption;
	}


	/**
	 * @param string $icon
	 */
	public function setIcon($icon)
	{
		$this->icon = $icon;
	}


	/**
	 * @return string
	 */
	public function getIcon()
	{
		return $this->icon;
	}


	/**
	 * @param $name
	 * @param $icon
	 * @return Section
	 */
	public function addSection($name, $label, $icon = NULL)
	{
		return $this[$name] = new Section($label, $icon);
	}


	/**
	 * @param $name
	 * @return Section
	 */
	public function getSection($name)
	{
		return $this[$name];
	}


	/**
	 * Returns navbar.
	 * @param  bool   throw exception if form doesn't exist?
	 * @return \CmsModule\Components\Navbar\NavbarControl
	 */
	public function getNavbar($need = TRUE)
	{
		return $this->lookup('CmsModule\Components\Navbar\NavbarControl', $need);
	}


	/**
	 * Returns control's HTML id.
	 * @return string
	 */
	public function getId()
	{
		if ($this->id === FALSE) {
			return NULL;
		} elseif ($this->id === NULL) {
			$this->id = sprintf(self::$idMask, $this->getNavbar()->getName(), $this->lookupPath('CmsModule\Components\Navbar\NavbarControl'));
		}
		return $this->id;
	}


	/********************* interface \ArrayAccess ****************d*g**/


	/**
	 * Adds the component to the container.
	 * @param  string  component name
	 * @param  Nette\ComponentModel\IComponent
	 * @return void
	 */
	final public function offsetSet($name, $component)
	{
		$this->addComponent($component, $name);
	}


	/**
	 * Returns component specified by name. Throws exception if component doesn't exist.
	 * @param  string  component name
	 * @return Nette\ComponentModel\IComponent
	 * @throws Nette\InvalidArgumentException
	 */
	final public function offsetGet($name)
	{
		return $this->getComponent($name, TRUE);
	}


	/**
	 * Does component specified by name exists?
	 * @param  string  component name
	 * @return bool
	 */
	final public function offsetExists($name)
	{
		return $this->getComponent($name, FALSE) !== NULL;
	}


	/**
	 * Removes component from the container.
	 * @param  string  component name
	 * @return void
	 */
	final public function offsetUnset($name)
	{
		$component = $this->getComponent($name, FALSE);
		if ($component !== NULL) {
			$this->removeComponent($component);
		}
	}
}
