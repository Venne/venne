<?php

/**
 * This file is part of the Venne:CMS (https://github.com/Venne)
 *
 * Copyright (c) 2011, 2012 Josef Kříž (http://www.josef-kriz.cz)
 *
 * For the full copyright and license information, please view
 * the file license.txt that was distributed with this source code.
 */

namespace CmsModule\Content\Forms\Controls;

use CmsModule\Content\Forms\Controls\Events\ContentEditorArgs;
use CmsModule\Content\Forms\Controls\Events\ContentEditorEvents;
use Doctrine\Common\EventManager;

/**
 * @author     Josef Kříž
 */
class ContentEditor extends \Nette\Forms\Controls\TextArea
{

	/** @var EventManager */
	protected $eventManager;


	public function __construct(EventManager $eventManager, $label = NULL, $cols = NULL, $rows = NULL)
	{
		parent::__construct($label, $cols, $rows);

		$this->eventManager = $eventManager;
		$this->setAttribute('venne-form-editor', true);
	}


	public function setValue($value)
	{
		$args = new ContentEditorArgs;
		$args->setValue($value);
		$this->eventManager->dispatchEvent(ContentEditorEvents::onContentEditorLoad, $args);
		$value = $args->getValue();

		return parent::setValue($value);
	}


	public function getValue()
	{
		$args = new ContentEditorArgs;
		$args->setValue(parent::getValue());
		$this->eventManager->dispatchEvent(ContentEditorEvents::onContentEditorSave, $args);
		$value = $args->getValue();

		return $value;
	}
}
