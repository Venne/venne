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

/**
 * @author Josef Kříž <pepakriz@gmail.com>
 *
 * @secured
 * @persistent(panel)
 */
class BasePresenter extends \CmsModule\Presenters\AdminPresenter
{


	public function startup()
	{
		parent::startup();

		if (!$this->getSignal()) {
			$this->invalidateControl('content');
		}
		$this->invalidateControl('header');
		$this->invalidateControl('toolbar');
		$this->invalidateControl('navigation');
		$this['panel']->invalidateControl('tabs');

		if ($this->isAjax()) {
			$this->template->ajax = true;
		}
	}


	public function handleLogout()
	{
		$this->user->logout(true);
		$this->flashMessage('Logout success');
		$this->redirect('this');
	}


	protected function createComponentPanel()
	{
		$panel = new \CmsModule\Components\PanelControl($this->context->cms->scannerService);
		$panel->setTemplateConfigurator($this->templateConfigurator);
		return $panel;
	}
}
