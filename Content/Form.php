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

/**
 * @author Josef Kříž <pepakriz@gmail.com>
 */
class Form extends \CmsModule\Forms\BaseDoctrineForm
{

	/** @var bool */
	protected $_created;


	/**
	 * Application form constructor.
	 */
	public function create()
	{

	}


	/**
	 * @param Nette\ComponentModel\Container $obj
	 */
	protected function attached($obj)
	{
		if (!$this->_created) {
			$this->_created = true;
			$this->create();
		}

		parent::attached($obj);
	}


	/**
	 * Adds multi-line text input control to the form.
	 * @param  string  control name
	 * @param  string  label
	 * @param  int  width of the control
	 * @param  int  height of the control in text lines
	 * @return Nette\Forms\Controls\TextArea
	 */
	public function addContentEditor($name, $label = NULL, $cols = 40, $rows = 10)
	{
		$evm = $this->entityManager->getEventManager();
		return $this[$name] = new \CmsModule\Content\Forms\Controls\ContentEditor($evm, $label, $cols, $rows);
	}

}
