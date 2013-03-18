<?php

/**
 * This file is part of the Venne:CMS (https://github.com/Venne)
 *
 * Copyright (c) 2011, 2012 Josef Kříž (http://www.josef-kriz.cz)
 *
 * For the full copyright and license information, please view
 * the file license.txt that was distributed with this source code.
 */

namespace CmsModule\Presenters;

use CmsModule\Content\Repositories\PageRepository;

/**
 * @author Josef Kříž <pepakriz@gmail.com>
 */
class ErrorPresenter extends FrontPresenter
{

	/** @var PageRepository */
	protected $pageRepository;


	/**
	 * @param PageRepository $pageRepository
	 */
	public function injectPageTagRepository(PageRepository $pageRepository)
	{
		$this->pageRepository = $pageRepository;
	}


	/**
	 * @param  Exception
	 * @return void
	 */
	public function renderDefault($exception)
	{
		if ($this->isAjax()) { // AJAX request? Just note this error in payload.
			$this->payload->error = TRUE;
			$this->terminate();
		}

		$code = $exception->getCode();
		\Nette\Diagnostics\Debugger::log("HTTP code $code: {$exception->getMessage()} in {$exception->getFile()}:{$exception->getLine()}", 'access');

		if (in_array($code, array(403, 404, 405, 410, 500))) {
			$page = $this->pageRepository->findOneBy(array('tag' => "error_$code", 'published' => TRUE));

			if ($page && $page->mainRoute->published) {
				$params = $page->mainRoute->params + array('route' => $page->mainRoute);
				$this->forward(':' . $page->mainRoute->type, $params);
			}
		}
	}
}

