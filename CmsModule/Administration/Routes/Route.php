<?php

/**
 * This file is part of the Venne:CMS (https://github.com/Venne)
 *
 * Copyright (c) 2011, 2012 Josef Kříž (http://www.josef-kriz.cz)
 *
 * For the full copyright and license information, please view
 * the file license.txt that was distributed with this source code.
 */

namespace CmsModule\Administration\Routes;

use CmsModule\Content\Listeners\PageListener;
use CmsModule\Content\Repositories\LanguageRepository;
use Nette;
use Nette\Application;
use Nette\Utils\Strings;

/**
 * @author Josef Kříž <pepakriz@gmail.com>
 */
class Route extends Nette\Application\Routers\Route
{

	/** @var PageListener */
	private $pageListener;

	/** @var LanguageRepository */
	private $languageRepository;

	/** @var string */
	private $defaultLanguage;

	/** @var bool */
	private $locale;


	/**
	 * @param PageListener $pageListener
	 */
	public function injectPageListener(PageListener $pageListener)
	{
		$this->pageListener = $pageListener;
	}


	/**
	 * @param LanguageRepository $languageRepository
	 */
	public function injectLanguageRepository(LanguageRepository $languageRepository)
	{
		$this->languageRepository = $languageRepository;
	}


	/**
	 * @param string $defaultLanguage
	 */
	public function injectDefaultLanguage($defaultLanguage)
	{
		$this->defaultLanguage = $defaultLanguage;
	}


	public function match(Nette\Http\IRequest $httpRequest)
	{
		if (($request = parent::match($httpRequest)) === NULL) {
			return;
		}

		if (!$this->locale) {
			$parameters = $request->getParameters();

			if (isset($parameters['contentLang']) && $parameters['contentLang'] !== $this->defaultLanguage) {
				$this->pageListener->setLocale($this->languageRepository->findOneBy(array('alias' => $parameters['contentLang'])));
			}
			$this->locale = TRUE;
		}

		return $request;
	}
}

