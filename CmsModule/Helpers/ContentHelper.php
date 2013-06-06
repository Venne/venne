<?php

/**
 * This file is part of the Venne:CMS (https://github.com/Venne)
 *
 * Copyright (c) 2011, 2012 Josef Kříž (http://www.josef-kriz.cz)
 *
 * For the full copyright and license information, please view
 * the file license.txt that was distributed with this source code.
 */

namespace CmsModule\Helpers;

use CmsModule\Content\Forms\Controls\Events\ContentEditorArgs;
use CmsModule\Content\Forms\Controls\Events\ContentEditorEvents;
use Doctrine\Common\EventManager;
use Venne\Templating\BaseHelper;

/**
 * @author Josef Kříž <pepakriz@gmail.com>
 */
class ContentHelper extends BaseHelper
{

	/** @var EventManager */
	protected $eventManager;


	/**
	 * @param EventManager $eventManager
	 */
	public function __construct(EventManager $eventManager)
	{
		parent::__construct();
		$this->eventManager = $eventManager;
	}


	/**
	 * @param $text
	 * @return string
	 */
	public function run($text)
	{
		$args = new ContentEditorArgs;
		$args->setValue($text);

		$this->eventManager->dispatchEvent(ContentEditorEvents::onContentEditorRender, $args);

		return $args->getValue();
	}
}

