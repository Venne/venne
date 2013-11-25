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

use Venne;
use Nette\Application\UI\Presenter;

/**
 * @author Josef Kříž <pepakriz@gmail.com>
 */
class CachedPresenter extends Presenter
{

	/** @var \Nette\Caching\Cache */
	protected $_templateCache;


	public function injectTemplateCache(\Nette\Caching\IStorage $cache)
	{
		$this->_templateCache = new \Nette\Caching\Cache($cache, \CmsModule\Content\Presenters\PagePresenter::CACHE_OUTPUT);
	}


	public function startup()
	{
		$key = $this->getHttpRequest()->getUrl()->getAbsoluteUrl() . ($this->getUser()->isLoggedIn() ? '|logged' : '');
		$output = $this->_templateCache->load($key);
		$this->sendResponse(new \Nette\Application\Responses\TextResponse($output));
	}
}
