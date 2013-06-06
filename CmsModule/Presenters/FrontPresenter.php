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

/**
 * @author Josef Kříž <pepakriz@gmail.com>
 */
abstract class FrontPresenter extends BasePresenter
{

	protected function checkLanguage()
	{
		if (count($this->context->parameters["website"]["languages"]) > 1) {
			if (!$this->lang && !$this->getParameter("lang")) {
				$this->lang = $this->getDefaultLanguageAlias();
			}
		} else {
			$this->lang = $this->context->parameters["website"]["defaultLanguage"];
		}
	}


	/**
	 * @return string
	 */
	protected function getDefaultLanguageAlias()
	{
		$httpRequest = $this->context->httpRequest;

		$lang = $httpRequest->detectLanguage($this->context->parameters['website']['languages']);
		if (!$lang) {
			$lang = $this->context->parameters['website']['defaultLanguage'];
		}
		return $lang;
	}


	/**
	 * Redirect to other language.
	 *
	 * @param string $alias
	 */
	public function handleChangeLanguage($alias)
	{
		$this->redirect("this", array("lang" => $alias));
	}
}

