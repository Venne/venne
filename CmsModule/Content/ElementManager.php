<?php

/**
 * This file is part of the Venne:CMS (https://github.com/Venne)
 *
 * Copyright (c) 2011, 2012 Josef Kříž (http://www.josef-kriz.cz)
 *
 * For the full copyright and license information, please view
 * the file license.txt that was distributed with this source code.
 */

namespace CmsModule\Content;

use Venne;
use Nette\Object;
use Venne\Widget\WidgetManager;

/**
 * @author Josef Kříž <pepakriz@gmail.com>
 */
class ElementManager extends Object
{

	const ELEMENT_PREFIX = '_venne_element_';

	/** @var WidgetManager */
	protected $widgetManager;


	public function __construct(WidgetManager $widgetManager)
	{
		$this->widgetManager = $widgetManager;
	}


	public function createInstance($element)
	{
		return $this->widgetManager->getWidget(self::ELEMENT_PREFIX . $element)->invoke();
	}
}

