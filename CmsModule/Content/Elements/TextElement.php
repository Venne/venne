<?php

/**
 * This file is part of the Venne:CMS (https://github.com/Venne)
 *
 * Copyright (c) 2011, 2012 Josef Kříž (http://www.josef-kriz.cz)
 *
 * For the full copyright and license information, please view
 * the file license.txt that was distributed with this source code.
 */

namespace CmsModule\Content\Elements;

use Venne;
use CmsModule\Content\Elements\Entities\TextEntity;
use CmsModule\Content\Elements\Forms\TextFormFactory;

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
	public function injectSetupForm(TextFormFactory $setupForm)
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
		return get_class(new TextEntity);
	}


	public function render()
	{
		echo $this->getEntity()->getText();
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
		$form = $this->setupFormFactory->invoke($this->getEntity());
		$form->onSuccess[] = $this->processForm;
		return $form;
	}


	public function processForm($form)
	{
		$this->getPresenter()->redirect('this');
	}
}
