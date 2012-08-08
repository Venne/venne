<?php

/**
 * This file is part of the Venne:CMS (https://github.com/Venne)
 *
 * Copyright (c) 2011, 2012 Josef Kříž (http://www.josef-kriz.cz)
 *
 * For the full copyright and license information, please view
 * the file license.txt that was distributed with this source code.
 */

namespace CmsModule\Forms;

use Venne;

/**
 * @author Josef Kříž <pepakriz@gmail.com>
 */
class ModulesDefaultForm extends BaseConfigForm {


	public function startup()
	{
		parent::startup();

		$this->addGroup("Default modules");
		$this->addTextWithSelect("defaultPresenter", "Default presenter");
		$this->addTextWithSelect("errorPresenter", "Error presenter");
	}



	public function setup()
	{
		$model = $this->presenter->context->cms->scannerService;

		$this["defaultPresenter"]->setItems($model->getLinksOfModulesPresenters(), false)->setDefaultValue($this->presenter->context->parameters["website"]["defaultPresenter"]);

		$this["errorPresenter"]->setItems($model->getLinksOfModulesPresenters(), false)->setDefaultValue($this->presenter->context->parameters["website"]["errorPresenter"]);
	}

}
