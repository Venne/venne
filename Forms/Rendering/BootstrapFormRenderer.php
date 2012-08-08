<?php

/**
 * This file is part of the Venne:CMS (https://github.com/Venne)
 *
 * Copyright (c) 2011, 2012 Josef Kříž (http://www.josef-kriz.cz)
 *
 * For the full copyright and license information, please view
 * the file license.txt that was distributed with this source code.
 */

namespace CmsModule\Forms\Rendering;

use Venne;
use Nette\Utils\Html;

/**
 * @author Josef Kříž <pepakriz@gmail.com>
 */
class BootstrapFormRenderer extends \Nette\Forms\Rendering\DefaultFormRenderer
{



	public function __construct()
	{
		$pair = Html::el("div");
		$pair->class = "control-group";

		$control = Html::el("div");
		$control->class = "controls";

		$label = Html::el("div");
		$label->class = "control-label";

		$this->wrappers['controls']['container'] = null;
		$this->wrappers['pair']['container'] = $pair;
		$this->wrappers['label']['container'] = $label;
		$this->wrappers['control']['container'] = $control;
	}
	
	

	
	/**
	 * Initializes form.
	 * @return void
	 */
	protected function init()
	{
		parent::init();
		
		$this->form->getElementPrototype()->class[] = "form-horizontal";
	}
	

}
