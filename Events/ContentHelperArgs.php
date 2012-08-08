<?php

/**
 * This file is part of the Venne:CMS (https://github.com/Venne)
 *
 * Copyright (c) 2011, 2012 Josef Kříž (http://www.josef-kriz.cz)
 *
 * For the full copyright and license information, please view
 * the file license.txt that was distributed with this source code.
 */

namespace CmsModule\Events;

use Venne;

/**
 * @author Josef Kříž <pepakriz@gmail.com>
 */
class ContentHelperArgs extends \Doctrine\Common\EventArgs
{


	/** @var string */
	protected $text;


	public function getText()
	{
		return $this->text;
	}



	public function setText($text)
	{
		$this->text = $text;
	}



}
