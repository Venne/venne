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

use Nette\Application\UI\Form;
use Venne\Forms\IFormFactory;

/**
 * @author Josef Kříž <pepakriz@gmail.com>
 */
class AdministrationFormFactory extends \Nette\Object implements \Venne\Forms\IFormFactory
{

	/** @var \Venne\Forms\IFormFactory */
	private $formFactory;

	public function __construct(IFormFactory $formFactory)
	{
		$this->formFactory = $formFactory;
	}

	/**
	 * @return \Nette\Application\UI\Form
	 */
	public function create()
	{
		$form = $this->formFactory->create();

		$form->addGroup('Administration settings');
		$form->addText('routePrefix', 'Route prefix');
		$form->addText('defaultPresenter', 'Default presenter');

		$form->setCurrentGroup();
		$form->addSubmit('_submit', 'Save');

		$form->onSuccess[] = $this->handleSuccess;

		return $form;
	}

	public function handleSuccess(Form $form)
	{
		$form->getPresenter()->absoluteUrls = true;
		$url = $this->httpRequest->getUrl();

		$path = "{$url->scheme}://{$url->host}{$url->scriptPath}";

		$oldPath = $path . $this->websiteManager->routePrefix;
		$newPath = $path . $form['routePrefix']->getValue();

		if ($form['routePrefix']->getValue() == '') {
			$oldPath .= '/';
		}

		if ($this->websiteManager->routePrefix == '') {
			$newPath .= '/';
		}

		$form->getPresenter()->flashMessage('Administration settings has been updated.', 'success');
		$form->getPresenter()->redirectUrl(str_replace($oldPath, $newPath, $form->getPresenter()->link('this')));
	}

}
