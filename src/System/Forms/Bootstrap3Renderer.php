<?php

/**
 * This file is part of the Kdyby (http://www.kdyby.org)
 *
 * Copyright (c) 2008 Filip Procházka (filip@prochazka.su)
 *
 * For the full copyright and license information, please view the file license.md that was distributed with this source code.
 */

namespace Venne\System\Forms;

use Nette;
use Nette\Forms\Controls;
use Nette\Forms\Rendering\DefaultFormRenderer;

/**
 * @author Josef Kříž <pepakriz@gmail.com>
 */
class Bootstrap3Renderer extends DefaultFormRenderer
{

	public function __construct()
	{
		$this->wrappers['controls']['container'] = null;
		$this->wrappers['pair']['container'] = 'div class=form-group';
		$this->wrappers['pair']['.error'] = 'has-error';
		$this->wrappers['control']['container'] = 'div class=col-sm-9';
		$this->wrappers['label']['container'] = 'div class="col-sm-3 control-label"';
		$this->wrappers['control']['description'] = 'span class=help-block';
		$this->wrappers['control']['errorcontainer'] = 'span class=help-block';
	}

	/**
	 * @param \Nette\Forms\Form $form
	 * @param string|null $mode
	 * @return string
	 */
	public function render(Nette\Forms\Form $form, $mode = null)
	{
		$form->getElementPrototype()->class[] = 'form-horizontal';

		foreach ($form->getControls() as $control) {
			if ($control instanceof Controls\Button) {
				$control->setAttribute('class', empty($usedPrimary) ? 'btn btn-primary' : 'btn btn-default');
				$usedPrimary = true;

			} elseif ($control instanceof Controls\TextBase || $control instanceof Controls\SelectBox || $control instanceof Controls\MultiSelectBox) {
				$control->setAttribute('class', 'form-control');

			} elseif ($control instanceof Controls\Checkbox || $control instanceof Controls\CheckboxList || $control instanceof Controls\RadioList) {
				$control->getSeparatorPrototype()->setName('div')->class($control->getControlPrototype()->type);
			}
		}

		return parent::render($form, $mode);
	}

}
