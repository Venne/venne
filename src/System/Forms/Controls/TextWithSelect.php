<?php

/**
 * This file is part of the Venne:CMS (https://github.com/Venne)
 *
 * Copyright (c) 2011, 2012 Josef Kříž (http://www.josef-kriz.cz)
 *
 * For the full copyright and license information, please view
 * the file license.txt that was distributed with this source code.
 */

namespace Venne\System\Forms\Controls;

use Nette\Utils\Html;

/**
 * @author     Josef Kříž
 */
class TextWithSelect extends \Nette\Forms\Controls\TextInput
{

	/** @var \Nette\Utils\Html  container element template */
	protected $container;

	/** @var string[] */
	private $items = array();

	/** @var string[] */
	protected $allowed = array();

	/** @var bool */
	private $prompt = false;

	/** @var bool */
	private $useKeys = true;

	public function __construct($label = null, $cols = null, $maxLength = null)
	{
		$this->container = Html::el();
		$this->prompt = true;
		parent::__construct($label, $cols, $maxLength);
	}

	/**
	 * Sets items from which to choose.
	 *
	 * @param string[] $items
	 * @param bool $useKeys
	 * @return $this
	 */
	public function setItems(array $items, $useKeys = true)
	{
		$this->items = $items;
		$this->allowed = array();
		$this->useKeys = (bool) $useKeys;

		foreach ($items as $key => $value) {
			if (!is_array($value)) {
				$value = array($key => $value);
			}

			foreach ($value as $key2 => $value2) {
				if (!$this->useKeys) {
					if (!is_scalar($value2)) {
						throw new \Nette\InvalidArgumentException("All items must be scalar.");
					}
					$key2 = $value2;
				}

				if (isset($this->allowed[$key2])) {
					throw new \Nette\InvalidArgumentException("Items contain duplication for key '$key2'.");
				}

				$this->allowed[$key2] = $value2;
			}
		}

		return $this;
	}

	/**
	 * Returns items from which to choose.
	 *
	 * @return string[]
	 */
	final public function getItems()
	{
		return $this->items;
	}

	/**
	 * Generates control's HTML element.
	 *
	 * @return \Nette\Utils\Html
	 */
	public function getControl()
	{
		$container = clone $this->container;
		$container->add(' <div class="input-group">' . parent::getControl());

		$dest = '';
		$s = null;

		foreach ($this->items as $key => $value) {

			if (!is_array($value)) {
				$value = array($key => $value);
			}

			foreach ($value as $key2 => $value2) {
				if ($value2 instanceof \Nette\Utils\Html) {
					$dest .= '<li><a href="#">' . $value2 . '</a></li>';
				} else {
					$key2 = $this->useKeys ? $key2 : $value2;
					$value2 = $this->translate((string) $value2);

					if ($key2 == $this->value) {
						$s = $value2;
					}

					$dest .= '<li><a href="#">' . $value2 . '</a></li>';
				}
			}
		}

		$container->add('<div class="input-group-btn">
        <button type="button" class="btn btn-default btn-sm dropdown-toggle" data-toggle="dropdown"><span class="textWithSelect-text">' . $s . '</span> <span class="caret"></span></button>
        <ul class="dropdown-menu pull-right">');

		$container->add($dest);

		$container->add('</div></div>');

		return $container;
	}

}
