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

use CmsModule\Content\Repositories\PageTagRepository;

/**
 * @author Josef Kříž <pepakriz@gmail.com>
 */
class ErrorPresenter extends FrontPresenter
{

	/** @var PageTagRepository */
	protected $pageTagRepository;


	/**
	 * @param PageTagRepository $pageTagRepository
	 */
	public function injectPageTagRepository(PageTagRepository $pageTagRepository)
	{
		$this->pageTagRepository = $pageTagRepository;
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
			$specialPage = $this->pageTagRepository->findOneBy(array('tag' => "error_$code"));

			if ($specialPage && $specialPage->page) {
				$params = $specialPage->page->mainRoute->params + array('route' => $specialPage->page->mainRoute);
				$this->forward(':' . $specialPage->page->mainRoute->type, $params);
			}
		}
	}
}

