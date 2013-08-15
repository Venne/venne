<?php

/**
 * This file is part of the Venne:CMS (https://github.com/Venne)
 *
 * Copyright (c) 2011, 2012 Josef Kříž (http://www.josef-kriz.cz)
 *
 * For the full copyright and license information, please view
 * the file license.txt that was distributed with this source code.
 */

namespace CmsModule\Pages\Redirect;

use CmsModule\Content\Presenters\PagePresenter;

/**
 * @author Josef Kříž <pepakriz@gmail.com>
 */
class RoutePresenter extends PagePresenter
{

	protected function startup()
	{
		parent::startup();

		if ($this->extendedPage->redirect) {
			$this->redirect(301, 'this', array('route' => $this->extendedPage->redirect->mainRoute));
		} elseif ($this->extendedPage->redirectUrl) {
			$this->redirectUrl($this->extendedPage->redirectUrl);
		} else {
			throw new \Nette\Application\BadRequestException;
		}
	}
}