<?php

/**
 * This file is part of the Venne:CMS (https://github.com/Venne)
 *
 * Copyright (c) 2011, 2012 Josef Kříž (http://www.josef-kriz.cz)
 *
 * For the full copyright and license information, please view
 * the file license.txt that was distributed with this source code.
 */

namespace CmsModule\Pages\Text\ThumbnailElement;

use CmsModule\Pages\Text\ImageElement\AbstractImageElement;

/**
 * @author Josef Kříž <pepakriz@gmail.com>
 */
class ThumbnailElement extends AbstractImageElement
{


	/** @var ThumbnailFormFactory */
	protected $setupFormFactory;


	/**
	 * @param ThumbnailFormFactory $setupForm
	 */
	public function injectSetupForm(ThumbnailFormFactory $setupForm)
	{
		$this->setupFormFactory = $setupForm;
	}

	/**
	 * @return string
	 */
	protected function getEntityName()
	{
		return __NAMESPACE__ . '\ThumbnailEntity';
	}


	public function renderDefault()
	{
		parent::renderDefault();

		$this->template->description = $this->getExtendedElement()->description;
	}

}
