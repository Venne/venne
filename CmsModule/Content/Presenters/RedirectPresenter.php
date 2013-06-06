<?php

/**
 * This file is part of the Venne:CMS (https://github.com/Venne)
 *
 * Copyright (c) 2011, 2012 Josef Kříž (http://www.josef-kriz.cz)
 *
 * For the full copyright and license information, please view
 * the file license.txt that was distributed with this source code.
 */

namespace CmsModule\Content\Presenters;


/**
 * @author Josef Kříž <pepakriz@gmail.com>
 */
class RedirectPresenter extends PagePresenter
{

	public function startup()
	{
		parent::startup();

		if ($this->page->page) {
			$this->redirect(301, "this", array('route' => $this->page->page->mainRoute));
		} elseif ($this->page->redirectUrl) {
			$this->redirectUrl($this->page->redirectUrl);
		} else {
			throw new \Nette\Application\BadRequestException;
		}
	}
}