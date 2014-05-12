<?php

/**
 * This file is part of the Venne:CMS (https://github.com/Venne)
 *
 * Copyright (c) 2011, 2012 Josef Kříž (http://www.josef-kriz.cz)
 *
 * For the full copyright and license information, please view
 * the file license.txt that was distributed with this source code.
 */

namespace Venne\Security\AdminModule;

use Venne\Forms\IFormFactory;
use Venne\Security\SecurityManager;

/**
 * @author Josef Kříž <pepakriz@gmail.com>
 */
class ProviderFormFactory implements IFormFactory
{

	/** @var string */
	private $provider;

	/** @var SecurityManager */
	private $securityManager;

	/** @var IFormFactory */
	private $formFactory;


	/**
	 * @param IFormFactory $formFactory
	 * @param SecurityManager $securityManager
	 */
	public function __construct(IFormFactory $formFactory, SecurityManager $securityManager)
	{
		$this->formFactory = $formFactory;
		$this->securityManager = $securityManager;
	}


	/**
	 * @param string $provider
	 */
	public function setProvider($provider)
	{
		$this->provider = $provider;
	}


	/**
	 * @return \Nette\Application\UI\Form
	 */
	public function create()
	{
		$form = $this->formFactory->create();

		$form->addHidden('provider')->setValue($this->provider);
		$form['parameters'] = $this->securityManager
			->getLoginProviderByName($this->provider)
			->getFormContainer();

		$form->addSubmit('_submit', 'Save')
			->getControlPrototype()->class[] = 'btn-primary';
		$form->addSubmit('cancel', 'Cancel')
			->setValidationScope(FALSE);

		return $form;
	}

}
