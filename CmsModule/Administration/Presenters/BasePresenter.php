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

use CmsModule\Presenters\AdminPresenter;

/**
 * @author Josef Kříž <pepakriz@gmail.com>
 *
 * @secured
 */
class BasePresenter extends AdminPresenter
{

	public function startup()
	{
		parent::startup();

		if ($this->getParameter('do') === NULL && $this->isAjax()) {
			$this->invalidateControl('navigation');
			$this->invalidateControl('content');
			$this->invalidateControl('header');
			$this->invalidateControl('toolbar');
		}
	}


	public function handleLogout()
	{
		$this->user->logout(TRUE);
		$this->flashMessage('Logout success');
		$this->redirect('this');
	}
}
