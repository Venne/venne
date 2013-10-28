<?php

/**
 * This file is part of the Venne:CMS (https://github.com/Venne)
 *
 * Copyright (c) 2011, 2012 Josef Kříž (http://www.josef-kriz.cz)
 *
 * For the full copyright and license information, please view
 * the file license.txt that was distributed with this source code.
 */

namespace CmsModule\Pages\Text\ImageElement;

/**
 * @author Josef Kříž <pepakriz@gmail.com>
 */
class ImageElement extends AbstractImageElement
{

	/** @var ImageFormFactory */
	protected $setupFormFactory;


	/**
	 * @param ImageFormFactory $setupForm
	 */
	public function injectSetupForm(ImageFormFactory $setupForm)
	{
		$this->setupFormFactory = $setupForm;
	}

}
