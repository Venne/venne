<?php

/**
 * This file is part of the Venne:CMS (https://github.com/Venne)
 *
 * Copyright (c) 2011, 2012 Josef Kříž (http://www.josef-kriz.cz)
 *
 * For the full copyright and license information, please view
 * the file license.txt that was distributed with this source code.
 */

namespace CmsModule\Administration\Presenters;

use Venne;
use Nette\Callback;

/**
 * @author Josef Kříž <pepakriz@gmail.com>
 *
 * @secured
 */
class EnvironmentsPresenter extends BasePresenter
{


	/** @persistent */
	public $key;

	/** @var array */
	protected $environments;



	function __construct($environments)
	{
		$this->environments = $environments;
	}



	public function renderDefault()
	{
		$this->template->modes = $this->environments;
	}

}
