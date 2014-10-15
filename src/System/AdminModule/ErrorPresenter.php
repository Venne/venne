<?php

/**
 * This file is part of the Venne:CMS (https://github.com/Venne)
 *
 * Copyright (c) 2011, 2012 Josef Kříž (http://www.josef-kriz.cz)
 *
 * For the full copyright and license information, please view
 * the file license.txt that was distributed with this source code.
 */

namespace Venne\System\AdminModule;

use Tracy\Debugger;
use Venne\System\Content\Repositories\PageRepository;

/**
 * @author Josef Kříž <pepakriz@gmail.com>
 */
class ErrorPresenter extends \Nette\Application\UI\Presenter
{

	/** @var PageRepository */
	protected $pageRepository;

	/**
	 * @param PageRepository $pageRepository
	 */
	public function __construct(PageRepository $pageRepository)
	{
		parent::__construct();

		$this->pageRepository = $pageRepository;
	}

	/**
	 * @param string $exception
	 */
	public function renderDefault($exception)
	{
		if ($this->isAjax()) { // AJAX request? Just note this error in payload.
			$this->payload->error = true;
			$this->terminate();
		}

		$code = $exception->getCode();
		Debugger::log(sprintf(
			'HTTP code %s: %s in %s:%s',
			$code,
			$exception->getMessage(),
			$exception->getFile(),
			$exception->getLine()
		), 'access');

		if (in_array($code, array(403, 404, 500))) {
			$page = $this->pageRepository->findOneBy(array('special' => $code));

			if ($page) {
				$this->forward(':Cms:Pages:Text:Route:', array('routeId' => $page->mainRoute->id, 'pageId' => $page->id));
			}
		}
	}

}
