<?php

/**
 * This file is part of the Venne:CMS (https://github.com/Venne)
 *
 * Copyright (c) 2011, 2012 Josef Kříž (http://www.josef-kriz.cz)
 *
 * For the full copyright and license information, please view
 * the file license.txt that was distributed with this source code.
 */

namespace CmsModule\Pages\Text\TextElement;

use CmsModule\Content\Elements\BaseElement;
use Venne\Forms\FormFactory;

/**
 * @author Josef Kříž <pepakriz@gmail.com>
 */
class TextElement extends BaseElement
{

	/** @var TextFormFactory */
	protected $setupFormFactory;


	/**
	 * @param TextFormFactory $setupForm
	 */
	public function injectSetupForm(FormFactory $setupForm)
	{
		$this->setupFormFactory = $setupForm;
	}


	/**
	 * @return array
	 */
	public function getViews()
	{
		return array(
			'setup' => 'Edit element',
		) + parent::getViews();
	}


	/**
	 * @return string
	 */
	protected function getEntityName()
	{
		return __NAMESPACE__ . '\TextEntity';
	}


	public function renderDefault()
	{
		echo $this->getExtendedElement()->getText();
	}


	public function renderSetup()
	{
		echo $this['form']->render();
	}


	/**
	 * @return \Venne\Forms\Form
	 */
	protected function createComponentForm()
	{
		$form = $this->setupFormFactory->invoke($this->getExtendedElement());
		$form->onSuccess[] = $this->processForm;
		return $form;
	}


	public function processForm()
	{
		$this->getPresenter()->redirect('this');
	}
}
