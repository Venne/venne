<?php

/**
 * This file is part of the Venne:CMS (https://github.com/Venne)
 *
 * Copyright (c) 2011, 2012 Josef Kříž (http://www.josef-kriz.cz)
 *
 * For the full copyright and license information, please view
 * the file license.txt that was distributed with this source code.
 */

namespace Venne\System\Components\AdminGrid;

use Nette\ComponentModel\Component;

/**
 * @author Josef Kříž <pepakriz@gmail.com>
 *
 * @method onSuccess(\Nette\Application\UI\Form $form)
 */
class Form extends Component
{

	const TYPE_NORMAL = '';

	const TYPE_LARGE = 'modal-large';

	const TYPE_XLARGE = 'modal-xlarge';

	const TYPE_XXLARGE = 'modal-xxlarge';

	const TYPE_FULL = 'modal-full';

	/** @var callable[] */
	public $onSuccess;

	/** @var callable[] */
	public $onError;

	/** @var \Venne\Forms\IFormFactory|\Closure */
	private $factory;

	/** @var string */
	private $title;

	/** @var string */
	private $type;

	/**
	 * @param \Venne\Forms\IFormFactory|\Closure $factory
	 * @param string $title
	 * @param null $type
	 */
	public function __construct($factory, $title, $type = null)
	{
		parent::__construct();

		$this->factory = $factory;
		$this->title = $title;
		$this->type = $type;
	}

	/**
	 * @return \Venne\Forms\IFormFactory|\Closure
	 */
	public function getFactory()
	{
		return $this->factory;
	}

	/**
	 * @return string
	 */
	public function getType()
	{
		return $this->type;
	}

	/**
	 * @return string
	 */
	public function getTitle()
	{
		return $this->title;
	}

}
