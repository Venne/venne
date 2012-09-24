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

use CmsModule\Managers\DependencyNotExistsException;
use CmsModule\Managers\DependencyException;

/**
 * @author Josef Kříž <pepakriz@gmail.com>
 *
 * @secured
 */
class TagsPresenter extends BasePresenter {

	/** @persistent */
	public $type;

	/** @persistent */
	public $key;



	public function startup()
	{
		parent::startup();

		if(!$this->type){
			$this->type = 'route';
		}
	}


	public function createComponentForm()
	{
		$form = new \Venne\Application\UI\Form();
		$form->setMethod("GET");

		$form->addGroup("Type");
		$form->addSelect("type", "Type")->setItems(array('route', 'service', 'component'), false);
		$form->addSubmit("_submit", "Select");
		return $form;
	}


	public function createComponentEditForm()
	{
		$form = $this->context->cms->createModuleTagsForm();
		$form->setService(str_replace(".", "\.", $this->key), $this->type);
		$form->addSubmit("_submit", "Save");
		$form->onSuccess[] = function($form)
		{
			$form->getPresenter()->flashMessage("Changes has been saved", "success");
			$form->getPresenter()->redirect("this");
		};
		return $form;
	}

	public function renderDefault(){
		$this["form"]["type"]->setDefaultValue($this->type);

		$this->template->type = $this->type;
		$this->template->items = array();
		foreach($this->context->findByTag($this->type) as $name=>$meta){
			$this->template->items[isset($meta["priority"]) ? $meta["priority"] : 0][$name] = $meta;
		}
		krsort($this->template->items);
	}

}
